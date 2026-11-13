<?php

use App\Enums\ActivityStatus;
use App\Enums\ProcessStatus;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Discipline;
use App\Models\Process;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ActivityCorrected;
use App\Notifications\ActivityPublished;
use App\Notifications\ActivitySubmitted;
use App\Notifications\ActivityUpdated;
use App\Notifications\StudentLinkedToDiscipline;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Notification;

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
    $this->student->courses()->attach($this->course);

    $this->discipline = Discipline::factory()->for($this->course)->create();
    $this->discipline->professors()->attach($this->prof);

    $this->process = Process::create([
        'public_id' => 'RED-NOTIFY',
        'user_id' => $this->student->id,
        'coordinator_id' => $this->coord->id,
        'status' => ProcessStatus::Active,
        'started_at' => now(),
    ]);
    $this->process->disciplines()->attach($this->discipline->id, ['professor_id' => $this->prof->id]);
});

test('coordenador notifica o professor uma única vez ao vincular o aluno', function () {
    Notification::fake();

    $newStudent = User::factory()->create();
    $newStudent->roles()->attach(Role::where('name', 'student')->first());
    $newStudent->courses()->attach($this->course);

    $secondDiscipline = Discipline::factory()->for($this->course)->create();
    $secondDiscipline->professors()->attach($this->prof);

    $this->actingAs($this->coord)
        ->post(route('coordinator.processes.store'), [
            'user_id' => $newStudent->id,
            'disciplines' => [$this->discipline->id, $secondDiscipline->id],
            'professors' => [
                $this->discipline->id => $this->prof->id,
                $secondDiscipline->id => $this->prof->id,
            ],
        ])->assertRedirect();

    Notification::assertSentTo(
        $this->prof,
        StudentLinkedToDiscipline::class,
        fn (StudentLinkedToDiscipline $notification) => $notification->disciplineLabels === [$this->discipline->label, $secondDiscipline->label],
    );
});

test('professor publica atividade e o aluno é notificado', function () {
    Notification::fake();

    $activity = Activity::factory()->for($this->process)->create([
        'discipline_id' => $this->discipline->id,
        'professor_id' => $this->prof->id,
        'title' => 'Atividade notificada',
        'description' => 'Desc',
        'deadline' => now()->addDays(7),
        'status' => ActivityStatus::Pending,
        'activity_type' => 'text',
        'activity_text' => 'Enunciado em texto',
    ]);

    $this->actingAs($this->prof)
        ->patch(route('professor.activities.publish', $activity))
        ->assertRedirect();

    Notification::assertSentTo($this->student, ActivityPublished::class);
});

test('aluno envia resolução e o professor é notificado', function () {
    Notification::fake();

    $activity = Activity::factory()->for($this->process)->create([
        'discipline_id' => $this->discipline->id,
        'professor_id' => $this->prof->id,
        'title' => 'Atividade resposta',
        'description' => 'Desc',
        'deadline' => now()->addDays(7),
        'status' => ActivityStatus::Sent,
    ]);

    $this->actingAs($this->student)
        ->post(route('aluno.activity.submit', $activity), [
            'resolution_type' => 'text',
            'resolution_text' => 'Resolução',
        ])
        ->assertRedirect();

    Notification::assertSentTo($this->prof, ActivitySubmitted::class);
});

test('professor atualiza atividade publicada e o aluno é notificado', function () {
    Notification::fake();

    $activity = Activity::factory()->for($this->process)->create([
        'discipline_id' => $this->discipline->id,
        'professor_id' => $this->prof->id,
        'title' => 'Atividade com prazo',
        'description' => 'Desc',
        'deadline' => now()->addDays(7),
        'status' => ActivityStatus::Sent,
        'activity_type' => 'text',
        'activity_text' => 'Enunciado original',
    ]);

    $this->actingAs($this->prof)
        ->put(route('professor.activities.update', $activity), [
            'title' => 'Atividade com novo prazo',
            'description' => 'Desc',
            'deadline' => now()->addDays(14)->format('Y-m-d'),
            'activity_type' => 'text',
            'activity_text' => 'Enunciado atualizado',
        ])
        ->assertRedirect();

    Notification::assertSentTo($this->student, ActivityUpdated::class);
});

test('professor corrige atividade e o aluno é notificado', function () {
    Notification::fake();

    $activity = Activity::factory()->for($this->process)->create([
        'discipline_id' => $this->discipline->id,
        'professor_id' => $this->prof->id,
        'title' => 'Atividade corrigida',
        'description' => 'Desc',
        'deadline' => now()->addDays(7),
        'status' => ActivityStatus::InCorrection,
        'answer_type' => 'text',
        'answer_text' => 'Resolução',
        'answered_at' => now(),
    ]);

    $this->actingAs($this->prof)
        ->patch(route('professor.activities.correct', $activity), [
            'grade' => 9.5,
            'feedback' => 'Excelente',
        ])
        ->assertRedirect();

    Notification::assertSentTo($this->student, ActivityCorrected::class);
});
