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
use Illuminate\Support\Facades\Storage;

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
    $this->get(route('aluno.process.show'))
        ->assertOk()
        ->assertSee($this->discipline->label)
        ->assertSee($this->prof->name);
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

test('aluno sem processo vê estado vazio', function () {
    $student = User::factory()->create();
    $student->roles()->attach(Role::where('name', 'student')->first());

    $this->actingAs($student)
        ->get(route('aluno.process.show'))
        ->assertOk()
        ->assertSee('Você ainda não possui um processo RED');
});

test('aluno envia resolução em texto', function () {
    $this->post(route('aluno.activity.submit', $this->activity), [
        'resolution_type' => 'text',
        'resolution_text' => 'Minha resolução escrita direto no sistema.',
    ])->assertRedirect();

    $fresh = $this->activity->fresh();
    expect($fresh->status)->toBe(ActivityStatus::InCorrection)
        ->and($fresh->answer_type)->toBe('text')
        ->and($fresh->answer_text)->toBe('Minha resolução escrita direto no sistema.')
        ->and($fresh->url_answer)->toBeNull();
});

test('aluno pode reenviar enquanto aguarda correção', function () {
    $this->activity->update([
        'status' => ActivityStatus::InCorrection,
        'url_answer' => 'answers/x.pdf',
        'answer_type' => 'pdf',
        'answered_at' => now(),
    ]);

    $this->post(route('aluno.activity.submit', $this->activity), [
        'resolution_type' => 'link',
        'resolution_link' => 'https://example.com/nova-resolucao',
    ])->assertRedirect();

    $fresh = $this->activity->fresh();
    expect($fresh->status)->toBe(ActivityStatus::InCorrection)
        ->and($fresh->answer_type)->toBe('link')
        ->and($fresh->url_answer)->toBe('https://example.com/nova-resolucao');
});

test('aluno não envia após a correção do professor', function () {
    $this->activity->update(['status' => ActivityStatus::Corrected]);

    $this->post(route('aluno.activity.submit', $this->activity), [
        'resolution_type' => 'text',
        'resolution_text' => 'Tentativa tardia',
    ])->assertSessionHasErrors('activity');

    expect($this->activity->fresh()->answer_text)->toBeNull();
});

test('aluno não envia com o processo finalizado', function () {
    $this->process->update(['status' => ProcessStatus::Concluded]);

    $this->post(route('aluno.activity.submit', $this->activity), [
        'resolution_type' => 'text',
        'resolution_text' => 'Tentativa tardia',
    ])->assertSessionHasErrors('activity');
});

test('arquivo maior que 5 MB é rejeitado', function () {
    $this->post(route('aluno.activity.submit', $this->activity), [
        'resolution_type' => 'file',
        'resolution_file' => UploadedFile::fake()->create('grande.pdf', 6000, 'application/pdf'),
    ])->assertSessionHasErrors('resolution_file');
});

test('aluno não acessa atividade de outro processo', function () {
    $otherStudent = User::factory()->create();
    $otherStudent->roles()->attach(Role::where('name', 'student')->first());

    $otherProcess = Process::factory()->for($otherStudent, 'student')->create(['status' => ProcessStatus::Active]);
    $otherActivity = Activity::factory()->for($otherProcess)->create(['status' => ActivityStatus::Sent]);

    $this->get(route('aluno.activity.show', $otherActivity))->assertForbidden();
});

test('aluno baixa o PDF do enunciado e da própria resposta', function () {
    Storage::fake('local');
    Storage::disk('local')->put('activities/enunciado.pdf', 'pdf');
    Storage::disk('local')->put('answers/resolucao.pdf', 'pdf');

    $this->activity->update([
        'activity_type' => 'pdf',
        'url_activity' => 'activities/enunciado.pdf',
        'status' => ActivityStatus::InCorrection,
        'answer_type' => 'pdf',
        'url_answer' => 'answers/resolucao.pdf',
        'answered_at' => now(),
    ]);

    $this->get(route('aluno.activity.file', $this->activity))->assertOk();
    $this->get(route('aluno.activity.answer', $this->activity))->assertOk();
});

test('aluno não baixa resposta de atividade de outro processo', function () {
    Storage::fake('local');
    Storage::disk('local')->put('answers/outra.pdf', 'pdf');

    $otherStudent = User::factory()->create();
    $otherStudent->roles()->attach(Role::where('name', 'student')->first());
    $otherProcess = Process::factory()->for($otherStudent, 'student')->create(['status' => ProcessStatus::Active]);
    $otherActivity = Activity::factory()->for($otherProcess)->create([
        'status' => ActivityStatus::InCorrection,
        'answer_type' => 'pdf',
        'url_answer' => 'answers/outra.pdf',
    ]);

    $this->get(route('aluno.activity.answer', $otherActivity))->assertForbidden();
});
