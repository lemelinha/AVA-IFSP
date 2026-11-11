<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Discipline;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
        $defaultPassword = Hash::make('password');

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

        // Cursos, disciplinas e vínculos de demonstração
        $courses = collect([
            ['course_code' => '20261', 'label' => 'Técnico em Informática'],
            ['course_code' => '20262', 'label' => 'Técnico em Eletrotécnica'],
            ['course_code' => '20263', 'label' => 'Técnico em Mecânica'],
        ])->map(fn ($data) => Course::create($data));

        $disciplines = collect();
        foreach ($courses as $course) {
            foreach (['Matemática', 'Programação de Sistemas', 'Eletrônica Analógica'] as $label) {
                $disciplines->push(Discipline::create([
                    'course_id' => $course->id,
                    'class_code' => 'N'.random_int(10000, 99999).'A',
                    'label' => $label,
                ]));
            }
        }

        $professors = User::whereHas('roles', fn ($query) => $query->where('name', 'professor'))->get();
        $coordinators = User::whereHas('roles', fn ($query) => $query->where('name', 'coordinator'))->get();
        $students = User::whereHas('roles', fn ($query) => $query->where('name', 'student'))->get();

        // Professores vinculados às disciplinas (round-robin; inclui quem também é coordenador)
        foreach ($disciplines as $index => $discipline) {
            if ($professors->isNotEmpty()) {
                $discipline->professors()->attach($professors[$index % $professors->count()]);
            }
        }

        // Coordenadores que também são professores lecionam em curso diferente do que coordenam
        $coordProfUsers = $coordinators->filter(fn ($user) => $user->hasRole('professor'));
        foreach ($coordProfUsers as $i => $coordProf) {
            $targetCourse = $courses[($i + 1) % $courses->count()];
            $targetDiscipline = $disciplines->firstWhere('course_id', $targetCourse->id);
            if ($targetDiscipline && ! $targetDiscipline->professors->contains($coordProf->id)) {
                $targetDiscipline->professors()->attach($coordProf);
            }
        }

        // Coordenadores vinculados aos cursos (coordFixo a todos; demais distribuídos)
        foreach ($coordinators as $i => $coordinator) {
            if ($coordinator->is($coordFixo)) {
                $coordinator->coordinatedCourses()->sync($courses->pluck('id'));
            } else {
                $coordinator->coordinatedCourses()->attach($courses[$i % $courses->count()]);
            }
        }

        // Alunos vinculados aos cursos (distribuídos)
        foreach ($students as $i => $student) {
            $student->courses()->attach($courses[$i % $courses->count()]);
        }
    }
}
