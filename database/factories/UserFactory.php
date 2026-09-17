<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'prontuario' => $this->fakeProntuario(),
            'name' => fake()->name(),
            'password' => static::$password ??= Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
            'user_role' => Role::select('uuid')->inRandomOrder()->first()->uuid,
        ];
    }

    protected function fakeProntuario()
    {
        $prontuario = 'JC';

        $prontuario .= random_int(100000, 999999);

        return $prontuario;
    }
}
