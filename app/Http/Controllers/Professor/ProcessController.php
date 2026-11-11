<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProcessController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->query('q', '');

        $processes = Process::whereHas('disciplines', function ($builder) {
            $builder->where('professor_id', Auth::id());
        })
            ->with(['student', 'activities' => function ($query) {
                $query->where('professor_id', Auth::id());
            }])
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
            ->orderByDesc('created_at')
            ->get();

        return view('professor.processes', [
            'processes' => $processes,
            'filters' => ['q' => $query],
        ]);
    }

    public function show(Process $process): View
    {
        $this->authorizeProcess($process);

        $process->load(['student', 'activities.discipline', 'activities' => function ($query) {
            $query->where('professor_id', Auth::id())->with('discipline');
        }]);

        $disciplines = $process->disciplines()
            ->wherePivot('professor_id', Auth::id())
            ->get();

        return view('professor.process_show', [
            'process' => $process,
            'disciplines' => $disciplines,
        ]);
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
}
