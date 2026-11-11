<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * @return array<int, Role>
     */
    public function run(): array
    {
        $cti = Role::create(['name' => 'cti', 'label' => 'CTI']);
        $coord = Role::create(['name' => 'coordinator', 'label' => 'Coordenador']);
        $prof = Role::create(['name' => 'professor', 'label' => 'Professor']);
        $aluno = Role::create(['name' => 'student', 'label' => 'Aluno']);

        return [$cti, $coord, $prof, $aluno];
    }
}
