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
            ->with(['activities.discipline', 'activities.professor', 'coordinator'])
            ->latest()
            ->first();

        if (! $process) {
            abort(404, 'Nenhum processo RED encontrado.');
        }

        return view('student.process', [
            'process' => $process,
        ]);
    }
}
