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

beforeEach(function () {
    (new RoleSeeder)->run();
});

function userWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::where('name', $role)->first());

    return $user;
}

test('aluno é redirecionado da home para o próprio processo', function () {
    $student = userWithRole('student');

    $this->actingAs($student)
        ->get('/')
        ->assertRedirect(route('aluno.process.show'));
});

test('coordenador vê os processos reais no painel', function () {
    $coord = userWithRole('coordinator');
    $student = userWithRole('student');
    $course = Course::factory()->create();
    $coord->coordinatedCourses()->attach($course);
    $student->courses()->attach($course);

    $process = Process::factory()->create([
        'user_id' => $student->id,
        'coordinator_id' => $coord->id,
        'public_id' => 'RED-PAINEL1',
        'status' => ProcessStatus::Active,
    ]);

    $this->actingAs($coord)
        ->get('/')
        ->assertOk()
        ->assertSee('Seus processos RED')
        ->assertSee('RED-PAINEL1')
        ->assertDontSee('Dados de demonstração');
});

test('professor vê as próprias atividades no painel', function () {
    $coord = userWithRole('coordinator');
    $prof = userWithRole('professor');
    $student = userWithRole('student');
    $course = Course::factory()->create();
    $coord->coordinatedCourses()->attach($course);
    $student->courses()->attach($course);
    $discipline = Discipline::factory()->for($course)->create();
    $discipline->professors()->attach($prof);

    $process = Process::factory()->create([
        'user_id' => $student->id,
        'coordinator_id' => $coord->id,
        'public_id' => 'RED-PAINEL2',
        'status' => ProcessStatus::Active,
    ]);
    $process->disciplines()->attach($discipline->id, ['professor_id' => $prof->id]);

    Activity::factory()->for($process)->create([
        'discipline_id' => $discipline->id,
        'professor_id' => $prof->id,
        'status' => ActivityStatus::Pending,
    ]);

    $this->actingAs($prof)
        ->get('/')
        ->assertOk()
        ->assertSee('Suas atividades RED')
        ->assertSee('RED-PAINEL2')
        ->assertSee('Aguardando publicação');
});

test('professor vê processo vinculado mesmo sem atividades', function () {
    $coord = userWithRole('coordinator');
    $prof = userWithRole('professor');
    $student = userWithRole('student');
    $course = Course::factory()->create();
    $coord->coordinatedCourses()->attach($course);
    $student->courses()->attach($course);
    $discipline = Discipline::factory()->for($course)->create();
    $discipline->professors()->attach($prof);

    $process = Process::factory()->create([
        'user_id' => $student->id,
        'coordinator_id' => $coord->id,
        'public_id' => 'RED-PAINEL3',
        'status' => ProcessStatus::Active,
    ]);
    $process->disciplines()->attach($discipline->id, ['professor_id' => $prof->id]);

    $this->actingAs($prof)
        ->get('/')
        ->assertOk()
        ->assertSee('RED-PAINEL3')
        ->assertSee('Processos vinculados');
});

test('CTI vê o resumo do campus no painel', function () {
    $cti = userWithRole('cti');

    $this->actingAs($cti)
        ->get('/')
        ->assertOk()
        ->assertSee('Painel do campus')
        ->assertSee('Processos ativos')
        ->assertSee('Usuários cadastrados');
});
