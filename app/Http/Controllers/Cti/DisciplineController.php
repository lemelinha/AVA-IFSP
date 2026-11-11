<?php

namespace App\Http\Controllers\Cti;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Discipline;
use App\Models\User;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DisciplineController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->query('q', '');
        $courseId = $request->query('course', '');

        $disciplines = Discipline::with('course')->withCount('activities')
            ->when($query, function ($builder) use ($query) {
                $term = '%'.mb_strtolower($query).'%';

                $builder->where(function ($builder) use ($term) {
                    $builder->whereRaw('lower(label) like ?', [$term])
                        ->orWhereRaw('lower(class_code) like ?', [$term])
                        ->orWhereHas('course', fn ($builder) => $builder->whereRaw('lower(label) like ?', [$term]));
                });
            })
            ->when($courseId, fn ($builder) => $builder->where('course_id', $courseId))
            ->orderBy('label')
            ->get();

        return view('cti.disciplines', [
            'disciplines' => $disciplines,
            'courses' => Course::orderBy('label')->get(),
            'filters' => [
                'q' => $query,
                'course' => $courseId,
            ],
        ]);
    }

    public function create(): View
    {
        return view('cti.discipline_form', [
            'discipline' => null,
            'courses' => Course::orderBy('label')->get(),
            'professors' => $this->professors(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules(),
            ValidationMessages::for('course_id', 'class_code', 'label', 'professors.*'),
        );

        $discipline = Discipline::create($validated);
        $discipline->professors()->sync($validated['professors'] ?? []);

        return redirect()
            ->route('cti.disciplines.index')
            ->with('status', 'Disciplina "'.$validated['label'].'" cadastrada.');
    }

    public function edit(Discipline $discipline): View
    {
        return view('cti.discipline_form', [
            'discipline' => $discipline->load('professors'),
            'courses' => Course::orderBy('label')->get(),
            'professors' => $this->professors(),
        ]);
    }

    public function update(Request $request, Discipline $discipline): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules($discipline),
            ValidationMessages::for('course_id', 'class_code', 'label', 'professors.*'),
        );

        $discipline->update($validated);
        $discipline->professors()->sync($validated['professors'] ?? []);

        return redirect()
            ->route('cti.disciplines.index')
            ->with('status', 'Disciplina "'.$discipline->label.'" atualizada.');
    }

    public function destroy(Discipline $discipline): RedirectResponse
    {
        if ($discipline->activities()->exists()) {
            return back()->withErrors([
                'discipline' => 'A disciplina "'.$discipline->label.'" possui atividades vinculadas a processos RED. Remova-as antes de excluir.',
            ]);
        }

        $discipline->professors()->detach();
        $discipline->delete();

        return redirect()
            ->route('cti.disciplines.index')
            ->with('status', 'Disciplina "'.$discipline->label.'" excluída.');
    }

    /**
     * Rules for creating and editing a discipline.
     *
     * @return array<string, mixed>
     */
    private function rules(?Discipline $discipline = null): array
    {
        $classCodeRule = $discipline !== null
            ? Rule::unique('classes', 'class_code')->ignore($discipline)
            : Rule::unique('classes', 'class_code');

        return [
            'course_id' => ['required', 'uuid', 'exists:courses,id'],
            'class_code' => ['required', 'string', 'max:255', $classCodeRule],
            'label' => ['required', 'string', 'max:255'],
            'professors' => ['array'],
            'professors.*' => ['exists:users,id'],
        ];
    }

    /**
     * Users with the professor role, available for linking to disciplines.
     *
     * @return Collection<int, User>
     */
    private function professors()
    {
        return User::whereHas('roles', fn ($builder) => $builder->where('name', 'professor'))
            ->orderBy('name')
            ->get();
    }
}
