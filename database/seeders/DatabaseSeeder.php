<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        [$cti, $coord, $prof, $aluno] = (new RoleSeeder)->run();
        $defaultPassword = \Illuminate\Support\Facades\Hash::make('password');

        // CTI fixo
        $ctiFixo = User::factory()->create([
            'name' => 'Admin CTI',
            'email' => 'cti@ifsp.edu.br',
            'prontuario' => 'JC000001',
            'password' => $defaultPassword,
        ]);
        $ctiFixo->roles()->attach($cti);

        // CTI
        User::factory(2)->create()->each(function ($user) use ($cti) {
            $user->roles()->attach($cti);
        });
        
        // Coordenador fixo
        $coordFixo = User::factory()->create([
            'name' => 'Coordenador Padrão',
            'email' => 'coordenador@ifsp.edu.br',
            'prontuario' => 'JC000002',
            'password' => $defaultPassword,
        ]);
        $coordFixo->roles()->attach($coord);

        // Coordenadores
        User::factory(2)->create()->each(function ($user) use ($coord) {
            $user->roles()->attach($coord);
        });
        
        // Coordenador fixo
        $coordProfFixo = User::factory()->create([
            'name' => 'Coordenador e Professor Padrão',
            'email' => 'coordenador_professor@ifsp.edu.br',
            'prontuario' => 'JC000003',
            'password' => $defaultPassword,
        ]);
        $coordProfFixo->roles()->attach([$coord, $prof]);

        // Coordenadores e professores
        User::factory(1)->create()->each(function ($user) use ($coord, $prof) {
            $user->roles()->attach([$coord, $prof]);
        });
        
        // Professor fixo
        $profFixo = User::factory()->create([
            'name' => 'Professor Padrão',
            'email' => 'professor@ifsp.edu.br',
            'prontuario' => 'JC000004',
            'password' => $defaultPassword,
        ]);
        $profFixo->roles()->attach($prof);

        // Professores
        User::factory(12)->create()->each(function ($user) use ($prof) {
            $user->roles()->attach($prof);
        });
        
        // Aluno fixo
        $alunoFixo = User::factory()->create([
            'name' => 'Aluno Padrão',
            'email' => 'aluno@ifsp.edu.br',
            'prontuario' => 'JC000005',
            'password' => $defaultPassword,
        ]);
        $alunoFixo->roles()->attach($aluno);

        // Alunos
        User::factory(39)->create()->each(function ($user) use ($aluno) {
            $user->roles()->attach($aluno);
        });
    }
}
