<?php

namespace App\Http\Controllers\Cti;

use App\Enums\ActivityStatus;
use App\Enums\ProcessStatus;
use App\Http\Controllers\Controller;
use App\Models\Process;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProcessController extends Controller
{
    public function index(): View
    {
        $processes = Process::with(['student', 'coordinator', 'activities'])
            ->orderByDesc('created_at')
            ->get();

        $activeCount = Process::where('status', ProcessStatus::Active)->count();
        $readyCount = $processes->filter(function ($process) {
            return $process->activities->contains(fn ($activity) => $activity->status === ActivityStatus::Corrected);
        })->count();

        return view('cti.processes', [
            'processes' => $processes,
            'activeCount' => $activeCount,
            'readyCount' => $readyCount,
        ]);
    }

    public function conclude(Process $process): RedirectResponse
    {
        if ($process->status === ProcessStatus::Concluded) {
            return back()->withErrors(['process' => 'O processo está finalizado.']);
        }

        $process->update([
            'status' => ProcessStatus::Concluded,
            'concluded_at' => now(),
        ]);

        return back()->with('status', 'Processo '.$process->public_id.' concluído.');
    }

    public function activate(Process $process): RedirectResponse
    {
        $process->update([
            'status' => ProcessStatus::Active,
            'concluded_at' => null,
        ]);

        return back()->with('status', 'Processo '.$process->public_id.' reativado.');
    }

    public function destroy(Process $process): RedirectResponse
    {
        $publicId = $process->public_id;
        $process->delete();

        return redirect()
            ->route('cti.processes.index')
            ->with('status', 'Processo '.$publicId.' excluído com suas atividades.');
    }
}
