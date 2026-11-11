<?php

use App\Http\Controllers\Coordinator\ProcessController as CoordinatorProcessController;
use App\Http\Controllers\Coordinator\StudentController;
use App\Http\Controllers\Cti\CourseController;
use App\Http\Controllers\Cti\DisciplineController;
use App\Http\Controllers\Cti\ProcessController;
use App\Http\Controllers\Cti\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Professor\ActivityController;
use App\Http\Controllers\Professor\ProcessController as ProfessorProcessController;
use App\Http\Controllers\Student\ActivityController as StudentActivityController;
use App\Http\Controllers\Student\ProcessController as StudentProcessController;
use App\Http\Controllers\UserController as BaseUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('login.logout');
    Route::get('/', [BaseUserController::class, 'home'])->name('home');

    Route::get('/profile', [BaseUserController::class, 'profile'])->name('user.profile');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login.index');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1')->name('login.login');
});

Route::middleware(['auth', 'role:cti'])
    ->prefix('cti')
    ->name('cti.')
    ->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
        Route::get('/usuarios/novo', [UserController::class, 'create'])->name('users.create');
        Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
        Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/usuarios/{user}/bloquear', [UserController::class, 'block'])->name('users.block');
        Route::patch('/usuarios/{user}/desbloquear', [UserController::class, 'unblock'])->name('users.unblock');
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/cursos', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/cursos/novo', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/cursos', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/cursos/{course}/editar', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/cursos/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/cursos/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

        Route::get('/disciplinas', [DisciplineController::class, 'index'])->name('disciplines.index');
        Route::get('/disciplinas/nova', [DisciplineController::class, 'create'])->name('disciplines.create');
        Route::post('/disciplinas', [DisciplineController::class, 'store'])->name('disciplines.store');
        Route::get('/disciplinas/{discipline}/editar', [DisciplineController::class, 'edit'])->name('disciplines.edit');
        Route::put('/disciplinas/{discipline}', [DisciplineController::class, 'update'])->name('disciplines.update');
        Route::delete('/disciplinas/{discipline}', [DisciplineController::class, 'destroy'])->name('disciplines.destroy');

        Route::get('/processos', [ProcessController::class, 'index'])->name('processes.index');
        Route::patch('/processos/{process}/concluir', [ProcessController::class, 'conclude'])->name('processes.conclude');
        Route::patch('/processos/{process}/reativar', [ProcessController::class, 'activate'])->name('processes.activate');
        Route::delete('/processos/{process}', [ProcessController::class, 'destroy'])->name('processes.destroy');
    });

Route::middleware(['auth', 'role:coordinator'])
    ->prefix('coordinator')
    ->name('coordinator.')
    ->group(function () {
        Route::get('/processos', [CoordinatorProcessController::class, 'index'])->name('processes.index');
        Route::get('/processos/novo', [CoordinatorProcessController::class, 'create'])->name('processes.create');
        Route::post('/processos', [CoordinatorProcessController::class, 'store'])->name('processes.store');
        Route::get('/processos/{process}', [CoordinatorProcessController::class, 'show'])->name('processes.show');
        Route::patch('/processos/{process}/concluir', [CoordinatorProcessController::class, 'conclude'])->name('processes.conclude');
        Route::patch('/processos/{process}/atividades/{activity}/validar', [CoordinatorProcessController::class, 'validateActivity'])->name('processes.activities.validate');

        Route::get('/alunos', [StudentController::class, 'index'])->name('students.index');
        Route::get('/alunos/novo', [StudentController::class, 'create'])->name('students.create');
        Route::post('/alunos', [StudentController::class, 'store'])->name('students.store');
        Route::get('/alunos/{user}/editar', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/alunos/{user}', [StudentController::class, 'update'])->name('students.update');
    });

Route::middleware(['auth', 'role:professor'])
    ->prefix('professor')
    ->name('professor.')
    ->group(function () {
        Route::get('/processos', [ProfessorProcessController::class, 'index'])->name('processes.index');
        Route::get('/processos/{process}', [ProfessorProcessController::class, 'show'])->name('processes.show');

        Route::get('/atividades/nova', [ActivityController::class, 'create'])->name('activities.create');
        Route::post('/atividades', [ActivityController::class, 'store'])->name('activities.store');
        Route::get('/atividades/{activity}/editar', [ActivityController::class, 'edit'])->name('activities.edit');
        Route::put('/atividades/{activity}', [ActivityController::class, 'update'])->name('activities.update');
        Route::patch('/atividades/{activity}/publicar', [ActivityController::class, 'publish'])->name('activities.publish');
        Route::patch('/atividades/{activity}/corrigir', [ActivityController::class, 'correct'])->name('activities.correct');
        Route::delete('/atividades/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
    });

Route::middleware(['auth', 'role:student'])
    ->prefix('aluno')
    ->name('aluno.')
    ->group(function () {
        Route::get('/processo', [StudentProcessController::class, 'show'])->name('process.show');
        Route::get('/atividades/{activity}', [StudentActivityController::class, 'show'])->name('activity.show');
        Route::post('/atividades/{activity}/enviar', [StudentActivityController::class, 'submit'])->name('activity.submit');
    });
