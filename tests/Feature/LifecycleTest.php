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

test('ciclo completo: coordenador abre, professor publica/corrige, coordenador valida e finaliza', function () {
    (new RoleSeeder)->run();

    $coord = User::factory()->create();
    $coord->roles()->attach(Role::where('name', 'coordinator')->first());
    $prof = User::factory()->create();
    $prof->roles()->attach(Role::where('name', 'professor')->first());
    $student = User::factory()->create();
    $student->roles()->attach(Role::where('name', 'student')->first());

    $course = Course::factory()->create();
    $coord->coordinatedCourses()->attach($course);
    $discipline = Discipline::factory()->for($course)->create();
    $discipline->professors()->attach($prof);
    $student->courses()->attach($course);

    // Coordenador abre processo
    $this->actingAs($coord);
    $this->post(route('coordinator.processes.store'), [
        'user_id' => $student->id,
        'course_id' => $course->id,
        'disciplines' => [$discipline->id],
        'professors' => [$discipline->id => $prof->id],
    ])->assertRedirect();
    $process = Process::where('user_id', $student->id)->first();
    expect($process->status)->toBe(ProcessStatus::Active);

    // Professor cria e publica atividade
    $this->actingAs($prof);
    $this->post(route('professor.activities.store'), [
        'process_id' => $process->id,
        'discipline_id' => $discipline->id,
        'title' => 'Atividade 1',
        'description' => 'Enunciado',
        'activity_file' => UploadedFile::fake()->create('atividade.pdf', 100, 'application/pdf'),
        'deadline' => now()->addDays(7)->format('Y-m-d'),
    ])->assertRedirect();
    $activity = Activity::where('title', 'Atividade 1')->first();
    $this->patch(route('professor.activities.publish', $activity))->assertRedirect();
    expect($activity->fresh()->status)->toBe(ActivityStatus::Sent);

    // Simula envio do aluno
    $activity->update([
        'status' => ActivityStatus::InCorrection,
        'url_answer' => 'answers/x.pdf',
        'answered_at' => now(),
    ]);

    // Professor corrige
    $this->patch(route('professor.activities.correct', $activity), [
        'grade' => 9.0,
        'feedback' => 'Ótimo',
    ])->assertRedirect();
    expect($activity->fresh()->status)->toBe(ActivityStatus::Corrected);

    // Coordenador valida
    $this->actingAs($coord);
    $this->patch(route('coordinator.processes.activities.validate', [$process, $activity]))->assertRedirect();
    expect($activity->fresh()->status)->toBe(ActivityStatus::Concluded);

    // Coordenador finaliza processo
    $this->patch(route('coordinator.processes.conclude', $process))->assertRedirect();
    expect($process->fresh()->status)->toBe(ProcessStatus::Concluded);
});
