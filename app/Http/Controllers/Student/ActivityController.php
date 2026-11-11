<?php

namespace App\Http\Controllers\Student;

use App\Enums\ActivityStatus;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ActivityController extends Controller
{
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

        if ($activity->status !== ActivityStatus::Sent) {
            return back()->withErrors(['activity' => 'Esta atividade não está aguardando sua resposta.']);
        }

        $validated = $request->validate([
            'resolution_type' => ['required', 'string', 'in:file,link'],
            'resolution_file' => ['required_if:resolution_type,file', 'nullable', 'file', 'mimes:pdf', 'max:10240'],
            'resolution_link' => ['required_if:resolution_type,link', 'nullable', 'url', 'max:2048'],
        ], ValidationMessages::for('resolution_type', 'resolution_file', 'resolution_link'));

        $urlAnswer = null;
        $answerType = null;

        if ($validated['resolution_type'] === 'file') {
            $urlAnswer = $request->file('resolution_file')->store('answers', 'public');
            $answerType = 'pdf';
        } else {
            $urlAnswer = $validated['resolution_link'];
            $answerType = 'link';
        }

        $activity->update([
            'url_answer' => $urlAnswer,
            'answer_type' => $answerType,
            'status' => ActivityStatus::InCorrection,
            'answered_at' => now(),
        ]);

        return redirect()
            ->route('aluno.activity.show', $activity)
            ->with('status', 'Resolução enviada. Aguarde a correção do professor.');
    }

    private function authorizeActivity(Activity $activity): void
    {
        if ($activity->process->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para acessar esta atividade.');
        }
    }
}
