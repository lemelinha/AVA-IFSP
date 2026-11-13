<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Process;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProcessController extends Controller
{
    public function show(): View
    {
        $process = Process::where('user_id', Auth::id())
            ->with(['activities.discipline', 'activities.professor', 'coordinator', 'disciplines.professors'])
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderByDesc('started_at')
            ->orderByDesc('created_at')
            ->first();

        return view('student.process', [
            'process' => $process,
        ]);
    }
}
