<?php

namespace App\Http\Controllers\Student;

use App\Enums\ActivityStatus;
use App\Enums\ProcessStatus;
use App\Http\Controllers\Concerns\HandlesActivityFiles;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Notifications\ActivitySubmitted;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityController extends Controller
{
    use HandlesActivityFiles;

    public function show(Activity $activity): View
    {
        $this->authorizeActivity($activity);

        return view('student.activity_show', [
            'activity' => $activity->load(['process', 'discipline', 'professor']),
        ]);
    }

    public function submit(Request $request, Activity $activity): RedirectResponse
    {
        $this->authorizeActivity($activity);

        if ($activity->process->status !== ProcessStatus::Active) {
            return back()->withErrors(['activity' => 'O processo está finalizado e não aceita mais respostas.']);
        }

        if (! in_array($activity->status, [ActivityStatus::Sent, ActivityStatus::InCorrection], true)) {
            return back()->withErrors(['activity' => 'Esta atividade não está aguardando sua resposta.']);
        }

        $validated = $request->validate([
            'resolution_type' => ['required', 'string', 'in:file,link,text'],
            'resolution_file' => ['required_if:resolution_type,file', 'nullable', 'file', 'mimes:pdf', 'max:5120'],
            'resolution_link' => ['required_if:resolution_type,link', 'nullable', 'url', 'max:2048'],
            'resolution_text' => ['required_if:resolution_type,text', 'nullable', 'string', 'max:20000'],
        ], ValidationMessages::for('resolution_type', 'resolution_file', 'resolution_link', 'resolution_text'));

        if ($activity->answer_type === 'pdf' && $activity->url_answer) {
            Storage::disk('local')->delete($activity->url_answer);
        }

        if ($validated['resolution_type'] === 'file') {
            $urlAnswer = $request->file('resolution_file')->store('answers', 'local');
            $answerType = 'pdf';
            $answerText = null;
        } elseif ($validated['resolution_type'] === 'link') {
            $urlAnswer = $validated['resolution_link'];
            $answerType = 'link';
            $answerText = null;
        } else {
            $urlAnswer = null;
            $answerType = 'text';
            $answerText = $validated['resolution_text'];
        }

        $activity->update([
            'url_answer' => $urlAnswer,
            'answer_type' => $answerType,
            'answer_text' => $answerText,
            'status' => ActivityStatus::InCorrection,
            'answered_at' => now(),
        ]);

        $activity->professor->notify(new ActivitySubmitted(
            $activity->title,
            $activity->process->student->name,
            $activity->process->public_id,
            route('professor.processes.show', $activity->process),
        ));

        return redirect()
            ->route('aluno.activity.show', $activity)
            ->with('status', 'Resolução enviada. Aguarde a correção do professor.');
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

    private function authorizeActivity(Activity $activity): void
    {
        $activity->loadMissing('process');

        if ($activity->process->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para acessar esta atividade.');
        }
    }
}
