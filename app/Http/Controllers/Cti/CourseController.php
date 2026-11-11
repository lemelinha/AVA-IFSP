<?php

namespace App\Http\Controllers\Cti;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        return view('cti.courses', [
            'courses' => Course::withCount('disciplines')->orderBy('label')->get(),
        ]);
    }

    public function create(): View
    {
        return view('cti.course_form', ['course' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules(),
            ValidationMessages::for('course_code', 'label'),
        );

        Course::create($validated);

        return redirect()
            ->route('cti.courses.index')
            ->with('status', 'Curso "'.$validated['label'].'" cadastrado.');
    }

    public function edit(Course $course): View
    {
        return view('cti.course_form', ['course' => $course]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules($course),
            ValidationMessages::for('course_code', 'label'),
        );

        $course->update($validated);

        return redirect()
            ->route('cti.courses.index')
            ->with('status', 'Curso "'.$course->label.'" atualizado.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        if ($course->disciplines()->exists()) {
            return back()->withErrors([
                'course' => 'O curso "'.$course->label.'" possui disciplinas vinculadas. Remova-as antes de excluir o curso.',
            ]);
        }

        $course->users()->detach();
        $course->delete();

        return redirect()
            ->route('cti.courses.index')
            ->with('status', 'Curso "'.$course->label.'" excluído.');
    }

    /**
     * Rules for creating and editing a course.
     *
     * @return array<string, mixed>
     */
    private function rules(?Course $course = null): array
    {
        $courseCodeRule = $course !== null
            ? Rule::unique('courses', 'course_code')->ignore($course)
            : Rule::unique('courses', 'course_code');

        return [
            'course_code' => ['required', 'string', 'max:255', $courseCodeRule],
            'label' => ['required', 'string', 'max:255'],
        ];
    }
}
