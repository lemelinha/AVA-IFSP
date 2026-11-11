<?php

namespace App\Http\Controllers\Professor;

use App\Enums\ActivityStatus;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Discipline;
use App\Models\Process;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function create(Request $request): View
    {
        $processId = $request->query('process');
        $disciplineId = $request->query('discipline');

        $process = $processId ? Process::findOrFail($processId) : null;
        $discipline = $disciplineId ? Discipline::findOrFail($disciplineId) : null;

        if ($process) {
            $this->authorizeProcess($process);
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
        $validated = $request->validate([
            'process_id' => ['required', 'uuid', 'exists:processes,id'],
            'discipline_id' => ['required', 'uuid', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'activity_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'deadline' => ['required', 'date'],
        ], ValidationMessages::for('process_id', 'discipline_id', 'title', 'description', 'deadline', 'activity_file'));

        $process = Process::findOrFail($validated['process_id']);
        $this->authorizeProcess($process);

        $isLinked = $process->disciplines()
            ->where('classes.id', $validated['discipline_id'])
            ->wherePivot('professor_id', Auth::id())
            ->exists();

        if (! $isLinked) {
            return back()->withErrors(['discipline_id' => 'Você não é o professor responsável por esta disciplina neste processo.'])->withInput();
        }

        $path = $request->file('activity_file')->store('activities', 'public');

        $activity = Activity::create([
            'process_id' => $validated['process_id'],
            'discipline_id' => $validated['discipline_id'],
            'professor_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'url_activity' => $path,
            'activity_type' => 'pdf',
            'status' => ActivityStatus::Pending,
            'deadline' => $validated['deadline'],
        ]);

        return redirect()
            ->route('professor.processes.show', $process)
            ->with('status', 'Atividade "'.$activity->title.'" criada. Publique-a para o aluno.');
    }

    public function edit(Activity $activity): View
    {
        $this->authorizeActivity($activity);

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

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'activity_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'deadline' => ['required', 'date'],
        ], ValidationMessages::for('title', 'description', 'deadline', 'activity_file'));

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'deadline' => $validated['deadline'],
        ];

        if ($request->hasFile('activity_file')) {
            if ($activity->url_activity) {
                Storage::disk('public')->delete($activity->url_activity);
            }
            $data['url_activity'] = $request->file('activity_file')->store('activities', 'public');
            $data['activity_type'] = 'pdf';
        }

        $activity->update($data);

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

        if (empty($activity->title) || empty($activity->description) || empty($activity->deadline)) {
            return back()->withErrors(['activity' => 'Preencha título, descrição e prazo antes de publicar.']);
        }

        $activity->update([
            'status' => ActivityStatus::Sent,
            'published_at' => now(),
        ]);

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

        return back()->with('status', 'Atividade "'.$activity->title.'" corrigida.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $this->authorizeActivity($activity);

        if ($activity->status === ActivityStatus::InCorrection || $activity->status === ActivityStatus::Corrected || $activity->status === ActivityStatus::Concluded) {
            return back()->withErrors(['activity' => 'Não é possível excluir uma atividade que já foi enviada ou corrigida.']);
        }

        $process = $activity->process;
        $activity->delete();

        return redirect()
            ->route('professor.processes.show', $process)
            ->with('status', 'Atividade excluída.');
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
}
