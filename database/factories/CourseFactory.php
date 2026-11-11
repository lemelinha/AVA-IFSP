<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_code' => $this->fakeCourseCode(),
            'label' => $this->fakeCourseLabel(),
        ];
    }

    protected function fakeCourseCode(): string
    {
        $courseCode = strtoupper(Str::random(3));
        $courseCode .= fake()->unique()->numberBetween(100, 999);

        while (Course::where('course_code', $courseCode)->exists()) {
            $courseCode = strtoupper(Str::random(3));
            $courseCode .= fake()->unique()->numberBetween(100, 999);
        }

        return $courseCode;
    }

    protected function fakeCourseLabel(): string
    {
        $prefixes = [
            'Técnico em Informática',
            'Técnico em Eletrotécnica',
            'Técnico em Mecânica',
            'Técnico em Administração',
            'Técnico em Enfermagem',
            'Técnico em Edificações',
            'Licenciatura em Física',
            'Bacharelado em Engenharia de Computação',
        ];

        return $prefixes[array_rand($prefixes)];
    }
}
