<?php

use App\Models\Process;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    (new RoleSeeder)->run();

    $this->cti = User::factory()->create([
        'name' => 'CTI Tester',
        'prontuario' => 'JC999001',
        'email' => 'cti-tester@ifsp.edu.br',
        'password' => Hash::make('password'),
    ]);

    $this->cti->roles()->attach(Role::where('name', 'cti')->first());

    $this->student = User::factory()->create([
        'name' => 'Aluno Comum',
        'prontuario' => 'JC999002',
        'password' => Hash::make('password'),
    ]);

    $this->student->roles()->attach(Role::where('name', 'student')->first());
});

test('usuário anônimo é redirecionado para o login', function () {
    $this->get(route('cti.users.index'))->assertRedirect('/login');
});

test('usuário sem perfil CTI recebe 403', function () {
    $this->actingAs($this->student);

    $this->get(route('cti.users.index'))->assertForbidden();
});

test('CTI lista os usuários da plataforma', function () {
    $this->actingAs($this->cti);

    $this->get(route('cti.users.index'))
        ->assertOk()
        ->assertSee($this->student->name);
});

test('CTI cadastra um usuário novo com perfis de acesso', function () {
    $this->actingAs($this->cti);

    $response = $this->post(route('cti.users.store'), [
        'name' => 'Professora Novata',
        'prontuario' => 'JC999010',
        'email' => 'professora@ifsp.edu.br',
        'password' => 'senha-segura-1',
        'roles' => ['professor'],
    ]);

    $response->assertRedirect(route('cti.users.index'));

    $user = User::where('prontuario', 'JC999010')->first();

    expect($user->name)->toBe('Professora Novata')
        ->and($user->hasRole('professor'))->toBeTrue()
        ->and($user->blocked)->toBeFalse();
});

test('não permite prontuário duplicado', function () {
    $this->actingAs($this->cti);

    $this->post(route('cti.users.store'), [
        'name' => 'Duplicado',
        'prontuario' => $this->student->prontuario,
        'email' => 'novo@ifsp.edu.br',
        'password' => 'senha-segura-1',
    ])->assertSessionHasErrors('prontuario');
});

test('CTI edita dados e perfis de um usuário', function () {
    $this->actingAs($this->cti);
    $originalPassword = $this->student->getRawOriginal('password');

    $response = $this->put(route('cti.users.update', $this->student), [
        'name' => 'Aluno Renomeado',
        'prontuario' => $this->student->prontuario,
        'email' => $this->student->email,
        'password' => null,
        'roles' => ['student', 'coordinator'],
    ]);

    $response->assertRedirect(route('cti.users.index'));

    $this->student->refresh();

    expect($this->student->name)->toBe('Aluno Renomeado')
        ->and($this->student->hasRole('coordinator'))->toBeTrue()
        ->and($this->student->getRawOriginal('password'))->toBe($originalPassword);
});

test('CTI bloqueia e desbloqueia um usuário', function () {
    $this->actingAs($this->cti);

    $this->patch(route('cti.users.block', $this->student))->assertRedirect();
    expect($this->student->fresh()->blocked)->toBeTrue();

    $this->patch(route('cti.users.unblock', $this->student))->assertRedirect();
    expect($this->student->fresh()->blocked)->toBeFalse();
});

test('usuário bloqueado não consegue entrar no sistema', function () {
    $this->cti->update(['blocked' => true]);

    $this->post(route('login.login'), [
        'prontuario' => $this->cti->prontuario,
        'password' => 'password',
    ]);

    expect(Auth::check())->toBeFalse();
});

test('usuário bloqueado perde a sessão ativa', function () {
    $this->actingAs($this->student);
    $this->student->update(['blocked' => true]);

    $this->get(route('aluno.process.show'))->assertRedirect(route('login.index'));

    expect(Auth::check())->toBeFalse();
});

test('CTI não bloqueia nem exclui a própria conta', function () {
    $this->actingAs($this->cti);

    $this->patch(route('cti.users.block', $this->cti))->assertForbidden();

    $this->delete(route('cti.users.destroy', $this->cti))->assertForbidden();
});

test('CTI não exclui usuário com processos ou atividades', function () {
    $this->actingAs($this->cti);

    Process::factory()->for($this->student, 'student')->create();

    $this->delete(route('cti.users.destroy', $this->student))
        ->assertRedirect()
        ->assertSessionHasErrors('user');

    expect($this->student->fresh())->not->toBeNull();
});

test('CTI exclui usuário sem vínculos acadêmicos', function () {
    $this->actingAs($this->cti);

    $avulso = User::factory()->create();
    $avulso->roles()->attach(Role::where('name', 'professor')->first());

    $id = $avulso->id;

    $response = $this->delete(route('cti.users.destroy', $avulso));

    $response->assertRedirect(route('cti.users.index'));
    expect(User::find($id))->toBeNull();
});

test('formulário de usuário renderiza na criação e na edição', function () {
    $this->actingAs($this->cti);

    $professor = User::factory()->create();
    $professor->roles()->attach(Role::where('name', 'professor')->first());

    $this->get(route('cti.users.create'))->assertOk();
    $this->get(route('cti.users.edit', $professor))
        ->assertOk()
        ->assertSee('value="PUT"', false);
});
