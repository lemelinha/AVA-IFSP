<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\ActivityStatus;
use App\Enums\ProcessStatus;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Discipline;
use App\Models\Process;
use App\Models\User;
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

class ProcessController extends Controller
{
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

        return view('coordinator.process_form', [
            'process' => null,
            'students' => $this->students($courseIds),
            'courses' => Auth::user()->coordinatedCourses()->orderBy('label')->get(),
            'disciplines' => Discipline::with('professors', 'course')
                ->whereIn('course_id', $courseIds)
                ->orderBy('label')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $courseIds = $this->coordinatedCourseIds();
        $disciplines = Discipline::with('professors')
            ->whereIn('course_id', $courseIds)
            ->get();

        $validated = $request->validate([
            'user_id' => ['required', 'uuid', Rule::in($this->students($courseIds)->pluck('id'))],
            'course_id' => ['required', 'uuid', Rule::in($courseIds)],
            'disciplines' => ['required', 'array', 'min:1'],
            'disciplines.*' => ['uuid', Rule::in($disciplines->pluck('id'))],
            'professors' => ['required', 'array'],
            'professors.*' => ['nullable', 'uuid', 'exists:users,id'],
        ], ValidationMessages::for('user_id', 'course_id', 'disciplines', 'professors'));

        $professorIds = $this->resolveProfessorIds($validated, $disciplines);

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
    private function students($courseIds)
    {
        return User::whereHas('roles', fn ($builder) => $builder->where('name', 'student'))
            ->whereHas('courses', fn ($builder) => $builder->whereIn('courses.id', $courseIds))
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
