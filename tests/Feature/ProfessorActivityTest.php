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
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    (new RoleSeeder)->run();

    $this->coord = User::factory()->create();
    $this->coord->roles()->attach(Role::where('name', 'coordinator')->first());

    $this->prof = User::factory()->create();
    $this->prof->roles()->attach(Role::where('name', 'professor')->first());

    $this->course = Course::factory()->create();
    $this->coord->coordinatedCourses()->attach($this->course);

    $this->discipline = Discipline::factory()->for($this->course)->create();
    $this->discipline->professors()->attach($this->prof);

    $this->student = User::factory()->create();
    $this->student->roles()->attach(Role::where('name', 'student')->first());
    $this->student->courses()->attach($this->course);

    $this->process = Process::create([
        'public_id' => 'RED-TESTPROC',
        'user_id' => $this->student->id,
        'coordinator_id' => $this->coord->id,
        'status' => ProcessStatus::Active,
        'started_at' => now(),
    ]);
    $this->process->disciplines()->attach($this->discipline->id, ['professor_id' => $this->prof->id]);

    $this->actingAs($this->prof);
});

test('professor acessa lista de processos', function () {
    $this->get(route('professor.processes.index'))->assertOk();
});

test('professor acessa detalhe do processo', function () {
    $this->get(route('professor.processes.show', $this->process))->assertOk();
});

test('professor cria atividade', function () {
    $this->post(route('professor.activities.store'), [
        'process_id' => $this->process->id,
        'discipline_id' => $this->discipline->id,
        'title' => 'Atividade 1',
        'description' => 'Enunciado',
        'activity_file' => UploadedFile::fake()->create('atividade.pdf', 100, 'application/pdf'),
        'deadline' => now()->addDays(7)->format('Y-m-d'),
    ])->assertRedirect();

    $activity = Activity::where('title', 'Atividade 1')->first();
    expect($activity)->not->toBeNull()
        ->and($activity->professor_id)->toBe($this->prof->id)
        ->and($activity->status)->toBe(ActivityStatus::Pending);
});

test('professor publica atividade', function () {
    $activity = Activity::factory()->for($this->process)->create([
        'discipline_id' => $this->discipline->id,
        'professor_id' => $this->prof->id,
        'title' => 'Atividade 2',
        'description' => 'Desc',
        'deadline' => now()->addDays(7),
        'status' => ActivityStatus::Pending,
        'activity_type' => 'pdf',
        'url_activity' => 'activities/x.pdf',
    ]);

    $this->patch(route('professor.activities.publish', $activity))->assertRedirect();
    expect($activity->fresh()->status)->toBe(ActivityStatus::Sent);
});

test('professor corrige atividade enviada', function () {
    $activity = Activity::factory()->for($this->process)->create([
        'discipline_id' => $this->discipline->id,
        'professor_id' => $this->prof->id,
        'title' => 'Atividade 3',
        'description' => 'Desc',
        'deadline' => now()->addDays(7),
        'status' => ActivityStatus::InCorrection,
        'url_answer' => 'answers/x.pdf',
        'answered_at' => now(),
    ]);

    $this->patch(route('professor.activities.correct', $activity), [
        'grade' => 8.5,
        'feedback' => 'Bom trabalho',
    ])->assertRedirect();

    expect($activity->fresh()->status)->toBe(ActivityStatus::Corrected)
        ->and((float) $activity->fresh()->grade)->toBe(8.5);
});

test('professor acessa o formulário de edição com o método PUT', function () {
    $activity = Activity::factory()->for($this->process)->create([
        'discipline_id' => $this->discipline->id,
        'professor_id' => $this->prof->id,
        'status' => ActivityStatus::Pending,
        'activity_type' => 'text',
        'activity_text' => 'Enunciado',
    ]);

    $this->get(route('professor.activities.edit', $activity))
        ->assertOk()
        ->assertSee('name="_method"', false)
        ->assertSee('value="PUT"', false);
});

test('professor não edita atividade já enviada pelo aluno', function () {
    $activity = Activity::factory()->for($this->process)->create([
        'discipline_id' => $this->discipline->id,
        'professor_id' => $this->prof->id,
        'status' => ActivityStatus::InCorrection,
        'answer_type' => 'text',
        'answer_text' => 'Resposta do aluno',
        'answered_at' => now(),
    ]);

    $this->get(route('professor.activities.edit', $activity))->assertForbidden();

    $this->put(route('professor.activities.update', $activity), [
        'title' => 'Alteração indevida',
        'description' => 'Desc',
        'deadline' => now()->addDays(7)->format('Y-m-d'),
        'activity_type' => 'text',
        'activity_text' => 'Novo enunciado',
    ])->assertForbidden();
});

test('professor não cria atividade em processo finalizado', function () {
    $this->process->update(['status' => ProcessStatus::Concluded]);

    $this->post(route('professor.activities.store'), [
        'process_id' => $this->process->id,
        'discipline_id' => $this->discipline->id,
        'title' => 'Atividade tardia',
        'description' => 'Enunciado',
        'activity_file' => UploadedFile::fake()->create('atividade.pdf', 100, 'application/pdf'),
        'deadline' => now()->addDays(7)->format('Y-m-d'),
    ])->assertSessionHasErrors('process_id');

    expect(Activity::where('title', 'Atividade tardia')->exists())->toBeFalse();
});

test('professor não acessa processo de outro professor', function () {
    $other = User::factory()->create();
    $other->roles()->attach(Role::where('name', 'professor')->first());

    $otherCourse = Course::factory()->create();
    $otherDisc = Discipline::factory()->for($otherCourse)->create();
    $otherDisc->professors()->attach($other);
    $otherStudent = User::factory()->create();
    $otherStudent->roles()->attach(Role::where('name', 'student')->first());
    $otherStudent->courses()->attach($otherCourse);
    $otherProcess = Process::create([
        'public_id' => 'RED-OTHER',
        'user_id' => $otherStudent->id,
        'coordinator_id' => $this->coord->id,
        'status' => ProcessStatus::Active,
        'started_at' => now(),
    ]);
    $otherProcess->disciplines()->attach($otherDisc->id, ['professor_id' => $other->id]);

    $this->get(route('professor.processes.show', $otherProcess))->assertForbidden();
});

test('usuário sem perfil professor não acessa a área', function () {
    $aluno = User::factory()->create();
    $aluno->roles()->attach(Role::where('name', 'student')->first());
    $this->actingAs($aluno);
    $this->get(route('professor.processes.index'))->assertForbidden();
});
