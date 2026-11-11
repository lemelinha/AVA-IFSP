<?php

use App\Models\Course;
use App\Models\Discipline;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    (new RoleSeeder)->run();

    $this->cti = User::factory()->create();
    $this->cti->roles()->attach(Role::where('name', 'cti')->first());
    $this->actingAs($this->cti);
});

test('CTI lista e cadastra cursos', function () {
    $this->get(route('cti.courses.index'))->assertOk()->assertSee('Cadastrar curso');
    $this->get(route('cti.courses.create'))->assertOk();

    $this->post(route('cti.courses.store'), [
        'course_code' => '20261',
        'label' => 'Técnico em Informática',
    ])->assertRedirect(route('cti.courses.index'));

    expect(Course::where('course_code', '20261')->first()->label)->toBe('Técnico em Informática');
});

test('código de curso precisa ser único', function () {
    Course::factory()->create(['course_code' => '20261']);

    $this->post(route('cti.courses.store'), [
        'course_code' => '20261',
        'label' => 'Curso Duplicado',
    ])->assertSessionHasErrors('course_code');
});

test('CTI atualiza um curso', function () {
    $course = Course::factory()->create();

    $this->put(route('cti.courses.update', $course), [
        'course_code' => $course->course_code,
        'label' => 'Curso Renomeado',
    ])->assertRedirect(route('cti.courses.index'));

    expect($course->fresh()->label)->toBe('Curso Renomeado');
});

test('não exclui curso com disciplinas vinculadas', function () {
    $course = Course::factory()->create();
    Discipline::factory()->for($course)->create();

    $this->delete(route('cti.courses.destroy', $course))
        ->assertRedirect()
        ->assertSessionHasErrors('course');

    expect($course->fresh())->not->toBeNull();
});

test('exclui curso sem disciplinas vinculas', function () {
    $course = Course::factory()->create();

    $this->delete(route('cti.courses.destroy', $course))->assertRedirect(route('cti.courses.index'));

    expect(Course::find($course->id))->toBeNull();
});

test('CTI lista e cadastra disciplinas', function () {
    $course = Course::factory()->create();

    $this->get(route('cti.disciplines.index'))->assertOk();
    $this->get(route('cti.disciplines.create'))->assertOk();

    $this->get(route('cti.disciplines.index'))->assertOk();

    $this->post(route('cti.disciplines.store'), [
        'course_id' => $course->id,
        'class_code' => 'N76543B',
        'label' => 'Matemática',
    ])->assertRedirect(route('cti.disciplines.index'));

    $discipline = Discipline::where('class_code', 'N76543B')->first();

    expect($discipline->label)->toBe('Matemática')
        ->and($discipline->course_id)->toBe($course->id);
});

test('código de disciplina precisa ser único', function () {
    Discipline::factory()->create(['class_code' => 'N76543B']);

    $this->post(route('cti.disciplines.store'), [
        'course_id' => Course::factory()->create()->id,
        'class_code' => 'N76543B',
        'label' => 'Duplicada',
    ])->assertSessionHasErrors('class_code');
});

test('não exclui disciplina com atividades');
