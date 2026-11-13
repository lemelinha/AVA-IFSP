<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\ActivityStatus;
use App\Enums\ProcessStatus;
use App\Http\Controllers\Concerns\HandlesActivityFiles;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Discipline;
use App\Models\Process;
use App\Models\User;
use App\Notifications\StudentLinkedToDiscipline;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProcessController extends Controller
{
    use HandlesActivityFiles;

    public function index(Request $request): View
    {
        $query = $request->query('q', '');
        $status = $request->query('status', '');

        $processes = Process::where('coordinator_id', Auth::id())
            ->with(['student', 'disciplines', 'activities'])
            ->when($query, function ($builder) use ($query) {
                $term = '%'.mb_strtolower($query).'%';

                $builder->where(function ($builder) use ($term) {
                    $builder->whereRaw('lower(public_id) like ?', [$term])
                        ->orWhereHas('student', function ($builder) use ($term) {
                            $builder->whereRaw('lower(name) like ?', [$term])
                                ->orWhereRaw('lower(prontuario) like ?', [$term]);
                        });
                });
            })
            ->when($status, fn ($builder) => $builder->where('status', $status))
            ->orderByDesc('created_at')
            ->get();

        return view('coordinator.processes', [
            'processes' => $processes,
            'activeCount' => $processes->where('status', ProcessStatus::Active)->count(),
            'readyCount' => $processes->filter(fn ($process) => $this->isReadyToConclude($process))->count(),
            'disciplineCount' => $processes->sum(fn ($process) => $process->disciplines->count()),
            'activityCount' => $processes->sum(fn ($process) => $process->activities->count()),
            'filters' => [
                'q' => $query,
                'status' => $status,
            ],
        ]);
    }

    public function create(): View
    {
        $courseIds = $this->coordinatedCourseIds();
        $students = $this->students($courseIds, onlyAvailable: true);

        return view('coordinator.process_form', [
            'process' => null,
            'students' => $students,
            'studentCourses' => $students->mapWithKeys(fn (User $student) => [
                $student->id => $student->courses
                    ->pluck('id')
                    ->intersect($courseIds)
                    ->values()
                    ->all(),
            ]),
            'disciplines' => Discipline::with('professors', 'course')
                ->whereIn('course_id', $courseIds)
                ->orderBy('label')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $courseIds = $this->coordinatedCourseIds();

        $validated = $request->validate([
            'user_id' => ['required', 'uuid', Rule::in($this->students($courseIds)->pluck('id'))],
            'disciplines' => ['required', 'array', 'min:1'],
            'disciplines.*' => ['uuid'],
            'professors' => ['required', 'array'],
            'professors.*' => ['nullable', 'uuid', 'exists:users,id'],
        ], ValidationMessages::for('user_id', 'disciplines', 'professors'));

        $student = User::findOrFail($validated['user_id']);

        $hasActiveProcess = Process::where('user_id', $student->id)
            ->where('status', ProcessStatus::Active)
            ->exists();

        if ($hasActiveProcess) {
            throw ValidationException::withMessages([
                'user_id' => 'Este aluno já possui um processo RED ativo.',
            ]);
        }

        $studentCourseIds = $student->courses()
            ->whereIn('courses.id', $courseIds)
            ->pluck('courses.id');

        $disciplines = Discipline::with('professors')
            ->whereIn('course_id', $studentCourseIds)
            ->get();

        $selectedDisciplines = $disciplines->whereIn('id', $validated['disciplines']);

        if ($selectedDisciplines->count() !== count($validated['disciplines'])) {
            throw ValidationException::withMessages([
                'disciplines' => 'Selecione apenas disciplinas do curso do aluno.',
            ]);
        }

        if ($selectedDisciplines->pluck('course_id')->unique()->count() > 1) {
            throw ValidationException::withMessages([
                'disciplines' => 'Selecione disciplinas de um único curso.',
            ]);
        }

        $professorIds = $this->resolveProfessorIds($validated, $selectedDisciplines);

        $process = Process::create([
            'public_id' => $this->newPublicId(),
            'user_id' => $validated['user_id'],
            'coordinator_id' => Auth::id(),
            'status' => ProcessStatus::Active,
            'started_at' => now(),
        ]);

        foreach ($validated['disciplines'] as $disciplineId) {
            $process->disciplines()->attach($disciplineId, [
                'professor_id' => $professorIds[$disciplineId],
            ]);
        }

        $this->notifyProfessors($process, $validated['disciplines'], $professorIds, $selectedDisciplines);

        return redirect()
            ->route('coordinator.processes.show', $process)
            ->with('status', 'Processo '.$process->public_id.' aberto com '.count($validated['disciplines']).' disciplina(s).');
    }

    public function show(Process $process): View
    {
        $this->authorizeProcess($process);

        $process->load(['student', 'activities.discipline', 'activities.professor', 'disciplines.professors']);

        return view('coordinator.process_show', [
            'process' => $process,
        ]);
    }

    public function edit(Process $process): View
    {
        $this->authorizeProcess($process);
        $this->ensureProcessIsActive($process);

        $process->load(['student', 'disciplines']);

        $studentCourseIds = $process->student->courses()
            ->whereIn('courses.id', $this->coordinatedCourseIds())
            ->pluck('courses.id');

        return view('coordinator.process_edit', [
            'process' => $process,
            'disciplines' => Discipline::with('professors', 'course')
                ->whereIn('course_id', $studentCourseIds)
                ->orderBy('label')
                ->get(),
            'selectedDisciplines' => $process->disciplines->keyBy('id'),
            'lockedDisciplineIds' => $process->activities()->pluck('discipline_id')->unique()->all(),
        ]);
    }

    public function update(Request $request, Process $process): RedirectResponse
    {
        $this->authorizeProcess($process);
        $this->ensureProcessIsActive($process);

        $validated = $request->validate([
            'disciplines' => ['required', 'array', 'min:1'],
            'disciplines.*' => ['uuid'],
            'professors' => ['required', 'array'],
            'professors.*' => ['nullable', 'uuid', 'exists:users,id'],
        ], ValidationMessages::for('disciplines', 'professors'));

        $student = $process->student;
        $courseIds = $this->coordinatedCourseIds();

        $studentCourseIds = $student->courses()
            ->whereIn('courses.id', $courseIds)
            ->pluck('courses.id');

        $disciplines = Discipline::with('professors')
            ->whereIn('course_id', $studentCourseIds)
            ->get();

        $selectedDisciplines = $disciplines->whereIn('id', $validated['disciplines']);

        if ($selectedDisciplines->count() !== count($validated['disciplines'])) {
            throw ValidationException::withMessages([
                'disciplines' => 'Selecione apenas disciplinas do curso do aluno.',
            ]);
        }

        if ($selectedDisciplines->pluck('course_id')->unique()->count() > 1) {
            throw ValidationException::withMessages([
                'disciplines' => 'Selecione disciplinas de um único curso.',
            ]);
        }

        $lockedDisciplineIds = $process->activities()->pluck('discipline_id')->unique();

        if ($lockedDisciplineIds->diff($validated['disciplines'])->isNotEmpty()) {
            throw ValidationException::withMessages([
                'disciplines' => 'Não é possível remover disciplinas que já possuem atividades no processo.',
            ]);
        }

        $professorIds = $this->resolveProfessorIds($validated, $selectedDisciplines);

        $existingDisciplineIds = $process->disciplines()->pluck('classes.id')->all();
        $newDisciplineIds = array_values(array_diff($validated['disciplines'], $existingDisciplineIds));

        $process->disciplines()->sync(
            collect($validated['disciplines'])
                ->mapWithKeys(fn (string $disciplineId) => [
                    $disciplineId => ['professor_id' => $professorIds[$disciplineId]],
                ])
                ->all()
        );

        if ($newDisciplineIds !== []) {
            $this->notifyProfessors(
                $process,
                $newDisciplineIds,
                $professorIds,
                $selectedDisciplines->whereIn('id', $newDisciplineIds)
            );
        }

        return redirect()
            ->route('coordinator.processes.show', $process)
            ->with('status', 'Vínculos do processo '.$process->public_id.' atualizados.');
    }

    public function downloadActivityFile(Process $process, Activity $activity): StreamedResponse
    {
        $this->authorizeProcess($process);
        abort_unless($activity->process_id === $process->id, 404);

        return $this->downloadActivityMaterial($activity);
    }

    public function downloadAnswer(Process $process, Activity $activity): StreamedResponse
    {
        $this->authorizeProcess($process);
        abort_unless($activity->process_id === $process->id, 404);

        return $this->downloadActivityAnswer($activity);
    }

    public function conclude(Process $process): RedirectResponse
    {
        $this->authorizeProcess($process);
        $process->load('activities');

        if ($process->status === ProcessStatus::Concluded) {
            return back()->withErrors(['process' => 'O processo já está finalizado.']);
        }

        if (! $this->isReadyToConclude($process)) {
            return back()->withErrors(['process' => 'Finalize apenas quando todas as atividades estiverem concluídas.']);
        }

        $process->update([
            'status' => ProcessStatus::Concluded,
            'concluded_at' => now(),
        ]);

        return back()->with('status', 'Processo '.$process->public_id.' finalizado.');
    }

    public function validateActivity(Process $process, Activity $activity): RedirectResponse
    {
        $this->authorizeProcess($process);

        if ($activity->process_id !== $process->id) {
            abort(404);
        }

        if ($activity->status !== ActivityStatus::Corrected) {
            return back()->withErrors(['activity' => 'A atividade precisa estar corrigida para ser validada.']);
        }

        $activity->update([
            'status' => ActivityStatus::Concluded,
            'concluded_at' => now(),
        ]);

        return back()->with('status', 'Atividade "'.$activity->title.'" validada.');
    }

    /**
     * @return Collection<int, string>
     */
    private function coordinatedCourseIds()
    {
        return Auth::user()->coordinatedCourses()->pluck('courses.id');
    }

    /**
     * @param  Collection<int, string>  $courseIds
     * @return Collection<int, User>
     */
    private function students($courseIds, bool $onlyAvailable = false)
    {
        return User::whereHas('roles', fn ($builder) => $builder->where('name', 'student'))
            ->whereHas('courses', fn ($builder) => $builder->whereIn('courses.id', $courseIds))
            ->with('courses')
            ->when($onlyAvailable, fn ($builder) => $builder->whereDoesntHave(
                'processes',
                fn ($builder) => $builder->where('status', ProcessStatus::Active)
            ))
            ->orderBy('name')
            ->get();
    }

    /**
     * Validate that every selected discipline has a linked professor.
     *
     * @param  array<string, mixed>  $validated
     * @param  Collection<int, Discipline>  $disciplines
     * @return array<string, string>
     */
    private function resolveProfessorIds(array $validated, $disciplines): array
    {
        $professorIds = [];

        foreach ($validated['disciplines'] as $disciplineId) {
            $discipline = $disciplines->firstWhere('id', $disciplineId);
            $professorId = $validated['professors'][$disciplineId] ?? null;
            $label = $discipline->label ?? 'a disciplina';

            if (! $professorId) {
                throw ValidationException::withMessages([
                    'professors.'.$disciplineId => 'Selecione o professor de '.$label.'.',
                ]);
            }

            $isLinked = DB::table('professor_class')
                ->where('class_id', $disciplineId)
                ->where('professor_id', $professorId)
                ->exists();

            if (! $isLinked) {
                throw ValidationException::withMessages([
                    'professors.'.$disciplineId => 'O professor selecionado não está vinculado à disciplina '.$label.'.',
                ]);
            }

            $professorIds[$disciplineId] = $professorId;
        }

        return $professorIds;
    }

    private function authorizeProcess(Process $process): void
    {
        if ($process->coordinator_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para acessar este processo.');
        }
    }

    private function ensureProcessIsActive(Process $process): void
    {
        if ($process->status !== ProcessStatus::Active) {
            abort(403, 'Processos finalizados não podem ter os vínculos alterados.');
        }
    }

    /**
     * Notifies each professor once, grouping the disciplines of the process.
     *
     * @param  array<int, string>  $disciplineIds
     * @param  array<string, string>  $professorIds
     * @param  Collection<int, Discipline>  $disciplines
     */
    private function notifyProfessors(Process $process, array $disciplineIds, array $professorIds, Collection $disciplines): void
    {
        $labelsByProfessor = [];

        foreach ($disciplineIds as $disciplineId) {
            $labelsByProfessor[$professorIds[$disciplineId]][] = $disciplines->firstWhere('id', $disciplineId)->label;
        }

        foreach ($labelsByProfessor as $professorId => $labels) {
            User::findOrFail($professorId)->notify(new StudentLinkedToDiscipline(
                $process->student->name,
                $labels,
                $process->public_id,
                route('professor.processes.show', $process),
            ));
        }
    }

    private function isReadyToConclude(Process $process): bool
    {
        if ($process->activities->isEmpty()) {
            return false;
        }

        return $process->activities->every(fn ($activity) => $activity->status === ActivityStatus::Concluded);
    }

    private function newPublicId(): string
    {
        do {
            $publicId = 'RED-'.strtoupper(Str::random(8));
        } while (Process::where('public_id', $publicId)->exists());

        return $publicId;
    }
}
