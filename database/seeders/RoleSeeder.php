<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): array
    {
        $cti = Role::create(['name' => 'cti', 'label' => 'CTI']);
        $coord = Role::create(['name' => 'cti', 'label' => 'CTI']);
        $prof = Role::create(['name' => 'professor', 'label' => 'Professor']);
        $aluno = Role::create(['name' => 'aluno', 'label' => 'Aluno']);

        return [$cti, $coord, $prof, $aluno];
    }
}
