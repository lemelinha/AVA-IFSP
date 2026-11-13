<?php

use App\Enums\ProcessStatus;
use App\Models\Activity;
use App\Models\Process;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    (new RoleSeeder)->run();

    $this->cti = User::factory()->create();
    $this->cti->roles()->attach(Role::where('name', 'cti')->first());
});

test('usuário sem perfil CTI não acessa os processos', function () {
    $aluno = User::factory()->create();
    $aluno->roles()->attach(Role::where('name', 'student')->first());

    $this->actingAs($aluno);

    $this->get(route('cti.processes.index'))->assertForbidden();
});

test('CTI lista os processos RED do campus', function () {
    $this->actingAs($this->cti);

    Process::factory()->count(3)->create();

    $this->get(route('cti.processes.index'))->assertOk();
});

test('CTI conclui e reativa um processo', function () {
    $this->actingAs($this->cti);

    $process = Process::factory()->create(['status' => ProcessStatus::Active]);

    $this->patch(route('cti.processes.conclude', $process))->assertRedirect();

    $process->refresh();

    expect($process->status)->toBe(ProcessStatus::Concluded)
        ->and($process->concluded_at)->not->toBeNull();

    $this->patch(route('cti.processes.activate', $process))->assertRedirect();

    $process->refresh();

    expect($process->status)->toBe(ProcessStatus::Active)
        ->and($process->concluded_at)->toBeNull();
});

test('concluir um processo já concluído não muda nada', function () {
    $this->actingAs($this->cti);

    $process = Process::factory()->concluded()->create();

    $this->patch(route('cti.processes.conclude', $process))
        ->assertRedirect()
        ->assertSessionHasErrors('process');
});

test('CTI não reativa processo se o aluno já tem outro ativo', function () {
    $this->actingAs($this->cti);

    $student = User::factory()->create();
    Process::factory()->create([
        'user_id' => $student->id,
        'status' => ProcessStatus::Active,
    ]);
    $concluded = Process::factory()->concluded()->create(['user_id' => $student->id]);

    $this->patch(route('cti.processes.activate', $concluded))
        ->assertRedirect()
        ->assertSessionHasErrors('process');

    expect($concluded->fresh()->status)->toBe(ProcessStatus::Concluded);
});

test('CTI acessa os detalhes do processo e baixa os arquivos', function () {
    Storage::fake('local');
    Storage::disk('local')->put('activities/enunciado.pdf', 'pdf');
    Storage::disk('local')->put('answers/resposta.pdf', 'pdf');

    $this->actingAs($this->cti);

    $process = Process::factory()->create(['public_id' => 'RED-CTI001']);
    $activity = Activity::factory()->for($process)->create([
        'activity_type' => 'pdf',
        'url_activity' => 'activities/enunciado.pdf',
        'answer_type' => 'pdf',
        'url_answer' => 'answers/resposta.pdf',
    ]);

    $this->get(route('cti.processes.show', $process))
        ->assertOk()
        ->assertSee('RED-CTI001')
        ->assertSee($activity->title);

    $this->get(route('cti.processes.activities.file', [$process, $activity]))->assertOk();
    $this->get(route('cti.processes.activities.answer', [$process, $activity]))->assertOk();
});

test('CTI exclui um processo com as atividades', function () {
    $this->actingAs($this->cti);

    $process = Process::factory()->create();
    $activity = Activity::factory()->for($process)->create();

    $activityId = $activity->id;
    $processId = $process->id;

    $this->delete(route('cti.processes.destroy', $process))->assertRedirect(route('cti.processes.index'));

    expect(Process::find($processId))->toBeNull()
        ->and(Activity::find($activityId))->toBeNull();
});
