<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->query('q', '');
        $courseId = $request->query('course', '');
        $courseIds = $this->coordinatedCourseIds();

        $students = User::whereHas('roles', fn ($builder) => $builder->where('name', 'student'))
            ->whereHas('courses', fn ($builder) => $builder->whereIn('courses.id', $courseIds))
            ->with(['courses'])
            ->withCount('processes')
            ->when($query, function ($builder) use ($query) {
                $term = '%'.mb_strtolower($query).'%';

                $builder->where(function ($builder) use ($term) {
                    $builder->whereRaw('lower(name) like ?', [$term])
                        ->orWhereRaw('lower(prontuario) like ?', [$term])
                        ->orWhereRaw('lower(email) like ?', [$term]);
                });
            })
            ->when($courseId, fn ($builder) => $builder->whereHas('courses', fn ($builder) => $builder->where('courses.id', $courseId)))
            ->orderBy('name')
            ->get();

        return view('coordinator.students', [
            'students' => $students,
            'courses' => Auth::user()->coordinatedCourses()->orderBy('label')->get(),
            'filters' => [
                'q' => $query,
                'course' => $courseId,
            ],
        ]);
    }

    public function create(): View
    {
        return view('coordinator.student_form', [
            'student' => null,
            'courses' => Auth::user()->coordinatedCourses()->orderBy('label')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'prontuario' => ['required', 'string', 'max:255', Rule::unique('users', 'prontuario')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'course_id' => ['required', 'uuid', Rule::in($this->coordinatedCourseIds())],
        ], ValidationMessages::for('name', 'prontuario', 'email', 'password', 'course_id'));

        $student = User::create([
            'name' => $validated['name'],
            'prontuario' => strtoupper($validated['prontuario']),
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
        ]);

        $student->roles()->attach(Role::where('name', 'student')->pluck('id'));
        $student->courses()->attach($validated['course_id']);

        return redirect()
            ->route('coordinator.students.index')
            ->with('status', 'Aluno "'.$student->name.'" cadastrado.');
    }

    public function edit(User $user): View
    {
        $this->authorizeStudent($user);

        return view('coordinator.student_form', [
            'student' => $user->load('courses'),
            'courses' => Auth::user()->coordinatedCourses()->orderBy('label')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeStudent($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'prontuario' => ['required', 'string', 'max:255', Rule::unique('users', 'prontuario')->ignore($user)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8'],
            'course_id' => ['required', 'uuid', Rule::in($this->coordinatedCourseIds())],
        ], ValidationMessages::for('name', 'prontuario', 'email', 'password', 'course_id'));

        $user->fill([
            'name' => $validated['name'],
            'prontuario' => strtoupper($validated['prontuario']),
            'email' => strtolower($validated['email']),
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        if (! $user->hasRole('student')) {
            $user->roles()->attach(Role::where('name', 'student')->pluck('id'));
        }

        $user->courses()->sync($validated['course_id']);

        return redirect()
            ->route('coordinator.students.index')
            ->with('status', 'Aluno "'.$user->name.'" atualizado.');
    }

    /**
     * @return Collection<int, string>
     */
    private function coordinatedCourseIds()
    {
        return Auth::user()->coordinatedCourses()->pluck('courses.id');
    }

    private function authorizeStudent(User $user): void
    {
        if (! $user->hasRole('student')) {
            abort(404);
        }

        $isManaged = $user->courses()
            ->whereIn('courses.id', $this->coordinatedCourseIds())
            ->exists();

        if (! $isManaged) {
            abort(403, 'Você não tem permissão para gerenciar este aluno.');
        }
    }
}
