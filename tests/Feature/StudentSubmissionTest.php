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
    $this->student = User::factory()->create();
    $this->student->roles()->attach(Role::where('name', 'student')->first());

    $this->course = Course::factory()->create();
    $this->coord->coordinatedCourses()->attach($this->course);
    $this->discipline = Discipline::factory()->for($this->course)->create();
    $this->discipline->professors()->attach($this->prof);
    $this->student->courses()->attach($this->course);

    $this->process = Process::create([
        'public_id' => 'RED-STUDENT',
        'user_id' => $this->student->id,
        'coordinator_id' => $this->coord->id,
        'status' => ProcessStatus::Active,
        'started_at' => now(),
    ]);
    $this->process->disciplines()->attach($this->discipline->id, ['professor_id' => $this->prof->id]);

    $this->activity = Activity::factory()->for($this->process)->create([
        'discipline_id' => $this->discipline->id,
        'professor_id' => $this->prof->id,
        'title' => 'Atividade',
        'description' => 'Desc',
        'deadline' => now()->addDays(7),
        'status' => ActivityStatus::Sent,
        'url_activity' => 'activities/x.pdf',
    ]);

    $this->actingAs($this->student);
});

test('aluno acessa seu processo', function () {
    $this->get(route('aluno.process.show'))->assertOk();
});

test('aluno envia resolução em PDF', function () {
    $this->post(route('aluno.activity.submit', $this->activity), [
        'resolution_type' => 'file',
        'resolution_file' => UploadedFile::fake()->create('resolucao.pdf', 100, 'application/pdf'),
    ])->assertRedirect();

    $fresh = $this->activity->fresh();
    expect($fresh->status)->toBe(ActivityStatus::InCorrection)
        ->and($fresh->answer_type)->toBe('pdf');
});

test('aluno envia resolução por link', function () {
    $this->post(route('aluno.activity.submit', $this->activity), [
        'resolution_type' => 'link',
        'resolution_link' => 'https://example.com/resolucao',
    ])->assertRedirect();

    $fresh = $this->activity->fresh();
    expect($fresh->status)->toBe(ActivityStatus::InCorrection)
        ->and($fresh->answer_type)->toBe('link')
        ->and($fresh->url_answer)->toBe('https://example.com/resolucao');
});

test('aluno não envia sem escolher forma', function () {
    $this->post(route('aluno.activity.submit', $this->activity), [
        'resolution_type' => 'file',
    ])->assertSessionHasErrors('resolution_file');
});

test('usuário sem perfil aluno não acessa a área', function () {
    $prof = User::factory()->create();
    $prof->roles()->attach(Role::where('name', 'professor')->first());
    $this->actingAs($prof);
    $this->get(route('aluno.process.show'))->assertForbidden();
});
