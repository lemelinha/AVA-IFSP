<?php

use App\Models\Course;
use App\Models\Discipline;
use App\Models\Process;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    (new RoleSeeder)->run();

    $this->coord = User::factory()->create();
    $this->coord->roles()->attach(Role::where('name', 'coordinator')->first());

    $this->course = Course::factory()->create();
    $this->coord->coordinatedCourses()->attach($this->course);

    $this->discipline = Discipline::factory()->for($this->course)->create();
    $this->professor = User::factory()->create();
    $this->professor->roles()->attach(Role::where('name', 'professor')->first());
    $this->discipline->professors()->attach($this->professor);

    $this->student = User::factory()->create();
    $this->student->roles()->attach(Role::where('name', 'student')->first());
    $this->student->courses()->attach($this->course);

    $this->actingAs($this->coord);
});

test('coordenador acessa a lista de processos', function () {
    $this->get(route('coordinator.processes.index'))->assertOk();
});

test('coordenador acessa o formulário de novo processo', function () {
    $this->get(route('coordinator.processes.create'))->assertOk();
});

test('coordenador abre um processo RED com disciplina e professor', function () {
    $response = $this->post(route('coordinator.processes.store'), [
        'user_id' => $this->student->id,
        'course_id' => $this->course->id,
        'disciplines' => [$this->discipline->id],
        'professors' => [$this->discipline->id => $this->professor->id],
    ]);

    $response->assertRedirect();

    // Process has no student_id column; check via user_id
    $process = Process::where('user_id', $this->student->id)->first();
    expect($process)->not->toBeNull()
        ->and($process->coordinator_id)->toBe($this->coord->id)
        ->and($process->disciplines)->toHaveCount(1);

    $this->get(route('coordinator.processes.show', $process))->assertOk();
});

test('coordenador acessa a lista e o formulário de alunos', function () {
    $this->get(route('coordinator.students.index'))->assertOk();
    $this->get(route('coordinator.students.create'))->assertOk();
});

test('coordenador cadastra um aluno no curso', function () {
    $response = $this->post(route('coordinator.students.store'), [
        'name' => 'Aluno Teste',
        'prontuario' => 'JC999999',
        'email' => 'aluno-teste@ifsp.edu.br',
        'password' => 'senha-segura-1',
        'course_id' => $this->course->id,
    ]);

    $response->assertRedirect(route('coordinator.students.index'));

    $student = User::where('prontuario', 'JC999999')->first();
    expect($student)->not->toBeNull()
        ->and($student->hasRole('student'))->toBeTrue()
        ->and($student->courses->pluck('id'))->toContain($this->course->id);
});

test('coordenador não acessa processos de outro coordenador', function () {
    $other = User::factory()->create();
    $other->roles()->attach(Role::where('name', 'coordinator')->first());
    $otherCourse = Course::factory()->create();
    $other->coordinatedCourses()->attach($otherCourse);
    $otherDiscipline = Discipline::factory()->for($otherCourse)->create();
    $otherStudent = User::factory()->create();
    $otherStudent->roles()->attach(Role::where('name', 'student')->first());
    $otherStudent->courses()->attach($otherCourse);

    $process = Process::factory()->create([
        'user_id' => $otherStudent->id,
        'coordinator_id' => $other->id,
    ]);

    $this->get(route('coordinator.processes.show', $process))->assertForbidden();
});

test('usuário sem perfil coordenador não acessa a área', function () {
    $aluno = User::factory()->create();
    $aluno->roles()->attach(Role::where('name', 'student')->first());

    $this->actingAs($aluno);
    $this->get(route('coordinator.processes.index'))->assertForbidden();
});
