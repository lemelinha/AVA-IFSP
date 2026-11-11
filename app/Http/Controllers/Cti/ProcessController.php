<?php

namespace App\Http\Controllers\Cti;

use App\Enums\ActivityStatus;
use App\Enums\ProcessStatus;
use App\Http\Controllers\Controller;
use App\Models\Process;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcessController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->query('q', '');
        $status = $request->query('status', '');

        $processes = Process::with(['student', 'coordinator', 'activities'])
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

        $activeCount = Process::where('status', ProcessStatus::Active)->count();
        $readyCount = $processes->filter(function ($process) {
            return $process->activities->contains(fn ($activity) => $activity->status === ActivityStatus::Corrected);
        })->count();

        return view('cti.processes', [
            'processes' => $processes,
            'activeCount' => $activeCount,
            'readyCount' => $readyCount,
            'filters' => [
                'q' => $query,
                'status' => $status,
            ],
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
