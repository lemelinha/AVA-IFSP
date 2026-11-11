<?php

namespace Database\Factories;

use App\Enums\ProcessStatus;
use App\Models\Process;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Process>
 */
class ProcessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => 'RED-'.$this->fakeProcessCode(),
            'user_id' => User::factory(),
            'coordinator_id' => User::factory(),
            'status' => ProcessStatus::Active,
            'started_at' => now(),
        ];
    }

    /**
     * Process concluded: the student returned to in-person classes.
     */
    public function concluded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProcessStatus::Concluded,
            'concluded_at' => now(),
        ]);
    }

    protected function fakeProcessCode(): string
    {
        $processCode = Str::upper(Str::random(8));

        while (Process::where('public_id', 'RED-'.$processCode)->exists()) {
            $processCode = Str::upper(Str::random(8));
        }

        return $processCode;
    }
}
