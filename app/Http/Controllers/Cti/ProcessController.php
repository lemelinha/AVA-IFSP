<?php

namespace App\Http\Controllers\Cti;

use App\Enums\ActivityStatus;
use App\Enums\ProcessStatus;
use App\Http\Controllers\Concerns\HandlesActivityFiles;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Process;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProcessController extends Controller
{
    use HandlesActivityFiles;

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
        $awaitingValidationCount = Activity::where('status', ActivityStatus::Corrected)->count();
        $totalCount = Process::count();
        $activityCount = Activity::count();

        return view('cti.processes', [
            'processes' => $processes,
            'activeCount' => $activeCount,
            'awaitingValidationCount' => $awaitingValidationCount,
            'totalCount' => $totalCount,
            'activityCount' => $activityCount,
            'filters' => [
                'q' => $query,
                'status' => $status,
            ],
        ]);
    }

    public function show(Process $process): View
    {
        $process->load([
            'student',
            'coordinator',
            'disciplines.professors',
            'activities.discipline',
            'activities.professor',
        ]);

        return view('cti.process_show', [
            'process' => $process,
        ]);
    }

    public function downloadActivityFile(Process $process, Activity $activity): StreamedResponse
    {
        abort_unless($activity->process_id === $process->id, 404);

        return $this->downloadActivityMaterial($activity);
    }

    public function downloadAnswer(Process $process, Activity $activity): StreamedResponse
    {
        abort_unless($activity->process_id === $process->id, 404);

        return $this->downloadActivityAnswer($activity);
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
        if ($process->status === ProcessStatus::Active) {
            return back()->withErrors(['process' => 'O processo já está ativo.']);
        }

        $hasAnotherActiveProcess = Process::where('user_id', $process->user_id)
            ->where('status', ProcessStatus::Active)
            ->whereKeyNot($process->id)
            ->exists();

        if ($hasAnotherActiveProcess) {
            return back()->withErrors(['process' => 'O aluno já possui outro processo RED ativo.']);
        }

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
