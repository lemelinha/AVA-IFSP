<?php

use App\Enums\ActivityStatus;
use App\Enums\ProcessStatus;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Discipline;
use App\Models\Process;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Storage;

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

test('coordenador gerencia os vínculos de um processo ativo', function () {
    $process = Process::factory()->create([
        'user_id' => $this->student->id,
        'coordinator_id' => $this->coord->id,
        'status' => ProcessStatus::Active,
    ]);
    $process->disciplines()->attach($this->discipline->id, ['professor_id' => $this->professor->id]);

    $secondDiscipline = Discipline::factory()->for($this->course)->create();
    $secondProfessor = User::factory()->create();
    $secondProfessor->roles()->attach(Role::where('name', 'professor')->first());
    $secondDiscipline->professors()->attach($secondProfessor);

    $this->get(route('coordinator.processes.edit', $process))
        ->assertOk()
        ->assertSee($this->discipline->label);

    $this->put(route('coordinator.processes.update', $process), [
        'disciplines' => [$this->discipline->id, $secondDiscipline->id],
        'professors' => [
            $this->discipline->id => $this->professor->id,
            $secondDiscipline->id => $secondProfessor->id,
        ],
    ])->assertRedirect(route('coordinator.processes.show', $process));

    expect($process->fresh()->disciplines)->toHaveCount(2);
});

test('coordenador não remove disciplina que já possui atividades', function () {
    $process = Process::factory()->create([
        'user_id' => $this->student->id,
        'coordinator_id' => $this->coord->id,
        'status' => ProcessStatus::Active,
    ]);
    $process->disciplines()->attach($this->discipline->id, ['professor_id' => $this->professor->id]);

    Activity::factory()->for($process)->create([
        'discipline_id' => $this->discipline->id,
        'professor_id' => $this->professor->id,
        'status' => ActivityStatus::Pending,
    ]);

    $secondDiscipline = Discipline::factory()->for($this->course)->create();
    $secondDiscipline->professors()->attach($this->professor);

    $this->put(route('coordinator.processes.update', $process), [
        'disciplines' => [$secondDiscipline->id],
        'professors' => [$secondDiscipline->id => $this->professor->id],
    ])->assertSessionHasErrors('disciplines');

    expect($process->fresh()->disciplines)->toHaveCount(1);
});

test('coordenador não abre um segundo processo ativo para o mesmo aluno', function () {
    Process::factory()->create([
        'user_id' => $this->student->id,
        'coordinator_id' => $this->coord->id,
        'status' => ProcessStatus::Active,
    ]);

    $this->post(route('coordinator.processes.store'), [
        'user_id' => $this->student->id,
        'disciplines' => [$this->discipline->id],
        'professors' => [$this->discipline->id => $this->professor->id],
    ])->assertSessionHasErrors('user_id');

    expect(Process::where('user_id', $this->student->id)->count())->toBe(1);
});

test('coordenador não vincula disciplina de outro curso ao aluno', function () {
    $otherCourse = Course::factory()->create();
    $otherDiscipline = Discipline::factory()->for($otherCourse)->create();
    $otherDiscipline->professors()->attach($this->professor);

    $this->post(route('coordinator.processes.store'), [
        'user_id' => $this->student->id,
        'disciplines' => [$otherDiscipline->id],
        'professors' => [$otherDiscipline->id => $this->professor->id],
    ])->assertSessionHasErrors('disciplines');

    expect(Process::where('user_id', $this->student->id)->exists())->toBeFalse();
});

test('coordenador não vincula disciplina de curso que não coordena', function () {
    $otherCourse = Course::factory()->create();
    $otherDiscipline = Discipline::factory()->for($otherCourse)->create();
    $otherDiscipline->professors()->attach($this->professor);
    $this->student->courses()->attach($otherCourse);

    $this->post(route('coordinator.processes.store'), [
        'user_id' => $this->student->id,
        'disciplines' => [$otherDiscipline->id],
        'professors' => [$otherDiscipline->id => $this->professor->id],
    ])->assertSessionHasErrors('disciplines');

    expect(Process::where('user_id', $this->student->id)->exists())->toBeFalse();
});

test('coordenador baixa o enunciado e a resposta em PDF', function () {
    Storage::fake('local');
    Storage::disk('local')->put('activities/enunciado.pdf', 'pdf');
    Storage::disk('local')->put('answers/resposta.pdf', 'pdf');

    $process = Process::factory()->create([
        'user_id' => $this->student->id,
        'coordinator_id' => $this->coord->id,
        'status' => ProcessStatus::Active,
    ]);

    $activity = Activity::factory()->for($process)->create([
        'discipline_id' => $this->discipline->id,
        'professor_id' => $this->professor->id,
        'activity_type' => 'pdf',
        'url_activity' => 'activities/enunciado.pdf',
        'answer_type' => 'pdf',
        'url_answer' => 'answers/resposta.pdf',
        'status' => ActivityStatus::Corrected,
    ]);

    $this->get(route('coordinator.processes.activities.file', [$process, $activity]))->assertOk();
    $this->get(route('coordinator.processes.activities.answer', [$process, $activity]))->assertOk();
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
