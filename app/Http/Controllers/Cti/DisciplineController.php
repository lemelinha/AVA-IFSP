<?php

namespace App\Http\Controllers\Cti;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Discipline;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DisciplineController extends Controller
{
    public function index(): View
    {
        return view('cti.disciplines', [
            'disciplines' => Discipline::with('course')->withCount('activities')->orderBy('label')->get(),
            'courses' => Course::orderBy('label')->get(),
        ]);
    }

    public function create(): View
    {
        return view('cti.discipline_form', [
            'discipline' => null,
            'courses' => Course::orderBy('label')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules(),
            ValidationMessages::for('course_id', 'class_code', 'label'),
        );

        Discipline::create($validated);

        return redirect()
            ->route('cti.disciplines.index')
            ->with('status', 'Disciplina "'.$validated['label'].'" cadastrada.');
    }

    public function edit(Discipline $discipline): View
    {
        return view('cti.discipline_form', [
            'discipline' => $discipline,
            'courses' => Course::orderBy('label')->get(),
        ]);
    }

    public function update(Request $request, Discipline $discipline): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules($discipline),
            ValidationMessages::for('course_id', 'class_code', 'label'),
        );

        $discipline->update($validated);

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
        ];
    }
}
