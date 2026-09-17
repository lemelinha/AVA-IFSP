<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            'uuid' => fake()->uuid(),
            'role' => 'CTI',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('roles')->insert([
            'uuid' => fake()->uuid(),
            'role' => 'Coordenador',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('roles')->insert([
            'uuid' => fake()->uuid(),
            'role' => 'Professor',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('roles')->insert([
            'uuid' => fake()->uuid(),
            'role' => 'Aluno',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
    }
}
