<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Discipline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Discipline>
 */
class DisciplineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'class_code' => 'N'.fake()->unique()->numerify('#####A'),
            'label' => fake()->unique()->randomElement(DisciplineFactory::DISCIPLINE_LABELS),
        ];
    }

    public const DISCIPLINE_LABELS = [
        'Matemática',
        'Física',
        'Química',
        'Português',
        'Inglês',
        'Algoritmos e Técnicas de Programação',
        'Estrutura de Dados',
        'Banco de Dados',
        'Redes de Computadores',
        'Sistemas Operacionais',
        'Engenharia de Software',
        'Eletrônica Analógica',
        'Programação de Sistemas',
        'Arquitetura de Redes',
        'Contabilidade',
        'Gestão de Projetos',
    ];
}
