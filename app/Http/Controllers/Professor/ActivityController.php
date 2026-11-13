<?php

namespace App\Http\Controllers\Professor;

use App\Enums\ActivityStatus;
use App\Enums\ProcessStatus;
use App\Http\Controllers\Concerns\HandlesActivityFiles;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Discipline;
use App\Models\Process;
use App\Notifications\ActivityCorrected;
use App\Notifications\ActivityPublished;
use App\Notifications\ActivityUpdated;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as LaravelValidator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityController extends Controller
{
    use HandlesActivityFiles;

    public function create(Request $request): View
    {
        $processId = $request->query('process');
        $disciplineId = $request->query('discipline');

        $process = $processId ? Process::findOrFail($processId) : null;
        $discipline = $disciplineId ? Discipline::findOrFail($disciplineId) : null;

        if ($process) {
            $this->authorizeProcess($process);
            abort_if($process->status === ProcessStatus::Concluded, 403, 'O processo está finalizado e não aceita novas atividades.');
        }

        $processes = $this->linkedProcesses();
        $disciplines = $process
            ? $process->disciplines()->wherePivot('professor_id', Auth::id())->get()
            : $processes->flatMap(fn ($p) => $p->disciplines()->wherePivot('professor_id', Auth::id())->get())->unique('id')->values();

        return view('professor.activity_form', [
            'activity' => null,
            'process' => $process,
            'discipline' => $discipline,
            'processes' => $processes,
            'disciplines' => $disciplines,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->mergeIfMissing(['activity_type' => 'file']);

        $validated = $request->validate([
            'process_id' => ['required', 'uuid', 'exists:processes,id'],
            'discipline_id' => ['required', 'uuid', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'deadline' => ['required', 'date'],
            'activity_type' => ['required', 'string', 'in:file,link,text'],
            'activity_file' => ['required_if:activity_type,file', 'nullable', 'file', 'mimes:pdf', 'max:5120'],
            'activity_link' => ['required_if:activity_type,link', 'nullable', 'url', 'max:2048'],
            'activity_text' => ['required_if:activity_type,text', 'nullable', 'string', 'max:20000'],
        ], ValidationMessages::for('process_id', 'discipline_id', 'title', 'description', 'deadline', 'activity_type', 'activity_file', 'activity_link', 'activity_text'));

        $process = Process::findOrFail($validated['process_id']);
        $this->authorizeProcess($process);

        if ($process->status === ProcessStatus::Concluded) {
            return back()->withErrors(['process_id' => 'O processo está finalizado e não aceita novas atividades.'])->withInput();
        }

        $isLinked = $process->disciplines()
            ->where('classes.id', $validated['discipline_id'])
            ->wherePivot('professor_id', Auth::id())
            ->exists();

        if (! $isLinked) {
            return back()->withErrors(['discipline_id' => 'Você não é o professor responsável por esta disciplina neste processo.'])->withInput();
        }

        $activity = Activity::create([
            'process_id' => $validated['process_id'],
            'discipline_id' => $validated['discipline_id'],
            'professor_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'deadline' => $validated['deadline'],
            ...$this->materialFromInput($request, $validated),
            'status' => ActivityStatus::Pending,
        ]);

        return redirect()
            ->route('professor.processes.show', $process)
            ->with('status', 'Atividade "'.$activity->title.'" criada. Publique-a para o aluno.');
    }

    public function edit(Activity $activity): View
    {
        $this->authorizeActivity($activity);
        $this->ensureEditable($activity);

        $process = $activity->process;
        $disciplines = $process->disciplines()->wherePivot('professor_id', Auth::id())->get();

        return view('professor.activity_form', [
            'activity' => $activity,
            'process' => $process,
            'discipline' => $activity->discipline,
            'processes' => $this->linkedProcesses(),
            'disciplines' => $disciplines,
        ]);
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $this->authorizeActivity($activity);
        $this->ensureEditable($activity);

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'deadline' => ['required', 'date'],
            'activity_type' => ['required', 'string', 'in:file,link,text'],
            'activity_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'activity_link' => ['nullable', 'url', 'max:2048'],
            'activity_text' => ['nullable', 'string', 'max:20000'],
        ], ValidationMessages::for('title', 'description', 'deadline', 'activity_type', 'activity_file', 'activity_link', 'activity_text'));

        $validator->after(function (LaravelValidator $validator) use ($request, $activity): void {
            $type = $request->input('activity_type', $activity->activity_type);

            if ($type === 'file' && ! $request->hasFile('activity_file') && empty($activity->url_activity)) {
                $validator->errors()->add('activity_file', 'Envie o arquivo PDF da atividade.');
            }

            if ($type === 'link' && empty($request->input('activity_link')) && empty($activity->url_activity)) {
                $validator->errors()->add('activity_link', 'Informe o link do enunciado.');
            }

            if ($type === 'text' && empty($request->input('activity_text')) && empty($activity->activity_text)) {
                $validator->errors()->add('activity_text', 'Escreva o enunciado da atividade.');
            }
        });

        $validated = $validator->validate();

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'deadline' => $validated['deadline'],
            ...$this->materialFromInput($request, $validated, $activity),
        ];

        $activity->update($data);

        if ($activity->status === ActivityStatus::Sent) {
            $activity->process->student->notify(new ActivityUpdated(
                $activity->title,
                $activity->discipline->label,
                $activity->deadline?->format('d/m/Y') ?? '—',
                route('aluno.activity.show', $activity),
            ));
        }

        return redirect()
            ->route('professor.processes.show', $activity->process)
            ->with('status', 'Atividade "'.$activity->title.'" atualizada.');
    }

    public function publish(Activity $activity): RedirectResponse
    {
        $this->authorizeActivity($activity);

        if ($activity->status !== ActivityStatus::Pending) {
            return back()->withErrors(['activity' => 'A atividade só pode ser publicada quando estiver pendente.']);
        }

        if (! $this->hasMaterial($activity)) {
            return back()->withErrors(['activity' => 'Preencha o enunciado da atividade antes de publicar.']);
        }

        $activity->update([
            'status' => ActivityStatus::Sent,
            'published_at' => now(),
        ]);

        $activity->process->student->notify(new ActivityPublished(
            $activity->title,
            $activity->discipline->label,
            $activity->deadline?->format('d/m/Y') ?? '—',
            route('aluno.activity.show', $activity),
        ));

        return back()->with('status', 'Atividade "'.$activity->title.'" publicada para o aluno.');
    }

    public function correct(Request $request, Activity $activity): RedirectResponse
    {
        $this->authorizeActivity($activity);

        if ($activity->status !== ActivityStatus::InCorrection) {
            return back()->withErrors(['activity' => 'A atividade precisa ter sido enviada pelo aluno para ser corrigida.']);
        }

        $validated = $request->validate([
            'grade' => ['required', 'numeric', 'between:0,10'],
            'feedback' => ['required', 'string'],
        ], ValidationMessages::for('grade', 'feedback'));

        $activity->update([
            'status' => ActivityStatus::Corrected,
            'grade' => $validated['grade'],
            'feedback' => $validated['feedback'],
            'corrected_at' => now(),
        ]);

        $activity->process->student->notify(new ActivityCorrected(
            $activity->title,
            $activity->discipline->label,
            number_format((float) $activity->grade, 2, ',', '.'),
            route('aluno.activity.show', $activity),
        ));

        return back()->with('status', 'Atividade "'.$activity->title.'" corrigida.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $this->authorizeActivity($activity);

        if ($activity->status === ActivityStatus::InCorrection || $activity->status === ActivityStatus::Corrected || $activity->status === ActivityStatus::Concluded) {
            return back()->withErrors(['activity' => 'Não é possível excluir uma atividade que já foi enviada ou corrigida.']);
        }

        $process = $activity->process;

        if ($activity->activity_type === 'pdf' && $activity->url_activity) {
            Storage::disk('local')->delete($activity->url_activity);
        }

        $activity->delete();

        return redirect()
            ->route('professor.processes.show', $process)
            ->with('status', 'Atividade excluída.');
    }

    public function downloadActivityFile(Activity $activity): StreamedResponse
    {
        $this->authorizeActivity($activity);

        return $this->downloadActivityMaterial($activity);
    }

    public function downloadAnswer(Activity $activity): StreamedResponse
    {
        $this->authorizeActivity($activity);

        return $this->downloadActivityAnswer($activity);
    }

    /**
     * Material fields for the activity, according to the chosen type.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function materialFromInput(Request $request, array $validated, ?Activity $activity = null): array
    {
        $type = $validated['activity_type'] ?? $activity?->activity_type ?? 'file';
        $replacesOldFile = $type !== 'file' || $request->hasFile('activity_file');

        if ($activity && $replacesOldFile && $activity->activity_type === 'pdf' && $activity->url_activity) {
            Storage::disk('local')->delete($activity->url_activity);
        }

        if ($type === 'file') {
            return [
                'activity_type' => 'pdf',
                'url_activity' => $request->hasFile('activity_file')
                    ? $request->file('activity_file')->store('activities', 'local')
                    : $activity?->url_activity,
                'activity_text' => null,
            ];
        }

        if ($type === 'link') {
            return [
                'activity_type' => 'link',
                'url_activity' => $validated['activity_link'] ?? $activity?->url_activity,
                'activity_text' => null,
            ];
        }

        return [
            'activity_type' => 'text',
            'url_activity' => null,
            'activity_text' => $validated['activity_text'] ?? $activity?->activity_text,
        ];
    }

    private function hasMaterial(Activity $activity): bool
    {
        return match ($activity->activity_type) {
            'pdf', 'link' => ! empty($activity->url_activity),
            'text' => ! empty($activity->activity_text),
            default => false,
        };
    }

    /**
     * @return Collection<int, Process>
     */
    private function linkedProcesses()
    {
        return Process::whereHas('disciplines', function ($builder) {
            $builder->where('professor_id', Auth::id());
        })
            ->with(['student'])
            ->orderByDesc('created_at')
            ->get();
    }

    private function authorizeProcess(Process $process): void
    {
        $isLinked = $process->disciplines()
            ->wherePivot('professor_id', Auth::id())
            ->exists();

        if (! $isLinked) {
            abort(403, 'Você não tem permissão para acessar este processo.');
        }
    }

    private function authorizeActivity(Activity $activity): void
    {
        if ($activity->professor_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para gerenciar esta atividade.');
        }
    }

    private function ensureEditable(Activity $activity): void
    {
        if (! in_array($activity->status, [ActivityStatus::Pending, ActivityStatus::Sent], true)) {
            abort(403, 'Esta atividade já foi enviada pelo aluno e não pode mais ser editada.');
        }
    }
}
