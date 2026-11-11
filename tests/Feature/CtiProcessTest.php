<?php

use App\Enums\ProcessStatus;
use App\Models\Activity;
use App\Models\Process;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;

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
