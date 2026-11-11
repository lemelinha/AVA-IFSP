<?php

namespace Database\Factories;

use App\Enums\ActivityStatus;
use App\Models\Activity;
use App\Models\Discipline;
use App\Models\Process;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'process_id' => Process::factory(),
            'discipline_id' => Discipline::factory(),
            'professor_id' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'activity_type' => 'pdf',
            'url_activity' => 'activities/'.fake()->uuid().'.pdf',
            'status' => ActivityStatus::Pending,
            'deadline' => fake()->dateTimeBetween('+3 days', '+30 days')->format('Y-m-d'),
        ];
    }

    /**
     * Activity published by the professor, waiting for the student's answer.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ActivityStatus::Sent,
            'published_at' => now(),
        ]);
    }

    /**
     * Activity answered by the student, waiting for correction.
     */
    public function answered(): static
    {
        return $this->sent()->state(fn (array $attributes) => [
            'status' => ActivityStatus::InCorrection,
            'url_answer' => 'answers/'.fake()->uuid().'.pdf',
            'answer_type' => 'pdf',
            'answered_at' => fake()->dateTimeBetween('-3 days', 'now'),
        ]);
    }

    /**
     * Activity corrected by the professor, waiting for the coordinator.
     */
    public function corrected(): static
    {
        return $this->answered()->state(fn (array $attributes) => [
            'status' => ActivityStatus::Corrected,
            'grade' => fake()->randomFloat(2, 0, 10),
            'feedback' => fake()->paragraph(),
            'corrected_at' => fake()->dateTimeBetween('-2 days', 'now'),
        ]);
    }

    /**
     * Activity concluded by the coordinator.
     */
    public function concluded(): static
    {
        return $this->corrected()->state(fn (array $attributes) => [
            'status' => ActivityStatus::Concluded,
            'concluded_at' => Carbon::now(),
        ]);
    }
}
