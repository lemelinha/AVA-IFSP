<?php

namespace App\Http\Controllers\Cti;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Role;
use App\Models\User;
use App\Support\ValidationMessages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->query('q', '');
        $role = $request->query('role', '');

        $users = User::with('roles')
            ->when($query, function ($builder) use ($query) {
                $term = '%'.mb_strtolower($query).'%';

                $builder->where(function ($builder) use ($term) {
                    $builder->whereRaw('lower(name) like ?', [$term])
                        ->orWhereRaw('lower(prontuario) like ?', [$term])
                        ->orWhereRaw('lower(email) like ?', [$term]);
                });
            })
            ->when($role, function ($builder) use ($role) {
                $builder->whereHas('roles', fn ($builder) => $builder->where('name', $role));
            })
            ->orderBy('name')
            ->get();

        return view('cti.users', [
            'users' => $users,
            'roles' => Role::orderBy('label')->get(),
            'filters' => [
                'q' => $query,
                'role' => $role,
            ],
        ]);
    }

    public function create(): View
    {
        return view('cti.user_form', [
            'user' => null,
            'roles' => Role::all(),
            'courses' => Course::orderBy('label')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules(),
            ValidationMessages::for('name', 'prontuario', 'email', 'password', 'roles.*', 'courses.*'),
        );

        $user = User::create([
            'name' => $validated['name'],
            'prontuario' => strtoupper($validated['prontuario']),
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
            'blocked' => false,
        ]);

        $roleNames = $validated['roles'] ?? [];
        $user->roles()->attach(Role::whereIn('name', $roleNames)->pluck('id'));
        $this->syncCoordinatedCourses($user, $roleNames, $validated['courses'] ?? []);

        return redirect()
            ->route('cti.users.index')
            ->with('status', 'Usuário "'.$user->name.'" cadastrado.');
    }

    public function edit(User $user): View
    {
        return view('cti.user_form', [
            'user' => $user->load('roles'),
            'roles' => Role::all(),
            'courses' => Course::orderBy('label')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules($user),
            ValidationMessages::for('name', 'prontuario', 'email', 'password', 'roles.*', 'courses.*'),
        );

        $user->fill([
            'name' => $validated['name'],
            'prontuario' => strtoupper($validated['prontuario']),
            'email' => strtolower($validated['email']),
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        $roleNames = $validated['roles'] ?? [];
        $user->roles()->sync(Role::whereIn('name', $roleNames)->pluck('id'));
        $this->syncCoordinatedCourses($user, $roleNames, $validated['courses'] ?? []);

        return redirect()
            ->route('cti.users.index')
            ->with('status', 'Usuário "'.$user->name.'" atualizado.');
    }

    public function block(User $user): RedirectResponse
    {
        $this->abortIfSameUser($user);

        $user->update(['blocked' => true]);

        return back()->with('status', 'Usuário "'.$user->name.'" bloqueado.');
    }

    public function unblock(User $user): RedirectResponse
    {
        $user->update(['blocked' => false]);

        return back()->with('status', 'Usuário "'.$user->name.'" desbloqueado.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->abortIfSameUser($user);

        $hasAcademicData = $user->processes()->exists()
            || $user->coordinatedProcesses()->exists()
            || Activity::where('professor_id', $user->id)->exists();

        if ($hasAcademicData) {
            return back()->withErrors([
                'user' => 'O usuário "'.$user->name.'" possui processos ou atividades vinculadas. Bloqueie a conta em vez de excluir.',
            ]);
        }

        $user->roles()->detach();
        $user->delete();

        return redirect()
            ->route('cti.users.index')
            ->with('status', 'Usuário "'.$user->name.'" excluído.');
    }

    /**
     * Rules for creating and editing a user.
     *
     * @return array<string, mixed>
     */
    private function rules(?User $user = null): array
    {
        $isUpdate = $user !== null;

        $prontuarioRule = $isUpdate
            ? Rule::unique('users', 'prontuario')->ignore($user)
            : Rule::unique('users', 'prontuario');

        $emailRule = $isUpdate
            ? Rule::unique('users', 'email')->ignore($user)
            : Rule::unique('users', 'email');

        return [
            'name' => ['required', 'string', 'max:255'],
            'prontuario' => ['required', 'string', 'max:255', $prontuarioRule],
            'email' => ['required', 'string', 'email', 'max:255', $emailRule],
            'password' => $isUpdate ? ['nullable', 'string', 'min:8'] : ['required', 'string', 'min:8'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,name'],
            'courses' => ['array'],
            'courses.*' => ['exists:courses,id'],
        ];
    }

    /**
     * Keep the coordinator-course links in sync with the selected roles.
     *
     * @param  array<int, string>  $roleNames
     * @param  array<int, string>  $courseIds
     */
    private function syncCoordinatedCourses(User $user, array $roleNames, array $courseIds): void
    {
        if (! in_array('coordinator', $roleNames, true)) {
            $user->coordinatedCourses()->detach();

            return;
        }

        $user->coordinatedCourses()->sync($courseIds);
    }

    private function abortIfSameUser(User $user): void
    {
        if (Auth::user()->is($user)) {
            abort(403, 'Um usuário da CTI não pode bloquear ou excluir a própria conta.');
        }
    }
}
