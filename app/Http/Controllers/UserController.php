<?php

namespace App\Http\Controllers;

use App\Enums\ActivityStatus;
use App\Enums\ProcessStatus;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Process;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function home(): View|RedirectResponse
    {
        $user = Auth::user()->loadMissing('roles');

        if ($user->hasRole('cti')) {
            return $this->ctiHome();
        }

        if ($user->hasRole('coordinator')) {
            return $this->coordinatorHome($user);
        }

        if ($user->hasRole('professor')) {
            return $this->professorHome($user);
        }

        if ($user->hasRole('student')) {
            return redirect()->route('aluno.process.show');
        }

        return view('auth.home', [
            'view' => 'none',
            'counters' => [],
            'processes' => collect(),
        ]);
    }

    public function profile(): View
    {
        return view('auth.profile');
    }

    private function ctiHome(): View
    {
        return view('auth.home', [
            'view' => 'cti',
            'counters' => [
                ['n' => Process::where('status', ProcessStatus::Active)->count(), 'l' => 'Processos ativos', 'warn' => false],
                ['n' => Process::where('status', ProcessStatus::Concluded)->count(), 'l' => 'Processos concluídos', 'warn' => false],
                ['n' => User::count(), 'l' => 'Usuários cadastrados', 'warn' => false],
                ['n' => Course::count(), 'l' => 'Cursos cadastrados', 'warn' => false],
            ],
            'processes' => Process::with(['student', 'disciplines'])
                ->withCount('activities')
                ->orderByDesc('created_at')
                ->limit(8)
                ->get(),
        ]);
    }

    private function coordinatorHome(User $user): View
    {
        $allProcesses = Process::where('coordinator_id', $user->id)
            ->with([
                'student',
                'disciplines',
                'activities' => fn ($query) => $query->select('id', 'process_id', 'status'),
            ])
            ->orderByDesc('created_at')
            ->get();

        $processes = $allProcesses->take(8);
        $activeProcesses = $allProcesses->where('status', ProcessStatus::Active);
        $awaitingValidation = $allProcesses->flatMap->activities
            ->where('status', ActivityStatus::Corrected)
            ->count();
        $readyToConclude = $activeProcesses->filter(
            fn (Process $process) => $process->activities->isNotEmpty()
                && $process->activities->every(fn (Activity $activity) => $activity->status === ActivityStatus::Concluded)
        )->count();

        return view('auth.home', [
            'view' => 'coordinator',
            'counters' => [
                ['n' => $activeProcesses->count(), 'l' => 'Processos ativos', 'warn' => false],
                ['n' => $awaitingValidation, 'l' => 'Atividades para validar', 'warn' => $awaitingValidation > 0],
                ['n' => $readyToConclude, 'l' => 'Prontos para finalizar', 'warn' => $readyToConclude > 0],
                ['n' => $allProcesses->pluck('user_id')->unique()->count(), 'l' => 'Alunos em RED', 'warn' => false],
            ],
            'processes' => $processes,
        ]);
    }

    private function professorHome(User $user): View
    {
        $activities = Activity::where('professor_id', $user->id)
            ->get(['id', 'process_id', 'status']);

        $toPublish = $activities->where('status', ActivityStatus::Pending)->count();
        $toCorrect = $activities->where('status', ActivityStatus::InCorrection)->count();

        return view('auth.home', [
            'view' => 'professor',
            'counters' => [
                ['n' => $activities->pluck('process_id')->unique()->count(), 'l' => 'Processos vinculados', 'warn' => false],
                ['n' => $toPublish, 'l' => 'Aguardando publicação', 'warn' => $toPublish > 0],
                ['n' => $toCorrect, 'l' => 'Resoluções para corrigir', 'warn' => $toCorrect > 0],
                ['n' => $activities->whereIn('status', [ActivityStatus::Corrected, ActivityStatus::Concluded])->count(), 'l' => 'Atividades corrigidas', 'warn' => false],
            ],
            'processes' => Process::whereHas('activities', fn ($query) => $query->where('professor_id', $user->id))
                ->with([
                    'student',
                    'disciplines' => fn ($query) => $query->wherePivot('professor_id', $user->id),
                ])
                ->withCount([
                    'activities as own_activities_count' => fn ($query) => $query->where('professor_id', $user->id),
                ])
                ->orderByDesc('created_at')
                ->limit(8)
                ->get(),
        ]);
    }
}
