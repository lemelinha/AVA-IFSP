<?php

namespace App\Http\Controllers\Cti;

use App\Http\Controllers\Controller;
use App\Models\Activity;
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
    public function index(): View
    {
        return view('cti.users', [
            'users' => User::with('roles')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('cti.user_form', [
            'user' => null,
            'roles' => Role::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules(),
            ValidationMessages::for('name', 'prontuario', 'email', 'password', 'roles.*'),
        );

        $user = User::create([
            'name' => $validated['name'],
            'prontuario' => strtoupper($validated['prontuario']),
            'email' => strtolower($validated['email']),
            'password' => $validated['password'],
            'blocked' => false,
        ]);

        $user->roles()->attach(Role::whereIn('name', $validated['roles'] ?? [])->pluck('id'));

        return redirect()
            ->route('cti.users.index')
            ->with('status', 'Usuário "'.$user->name.'" cadastrado.');
    }

    public function edit(User $user): View
    {
        return view('cti.user_form', [
            'user' => $user->load('roles'),
            'roles' => Role::all(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules($user),
            ValidationMessages::for('name', 'prontuario', 'email', 'password', 'roles.*'),
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

        $user->roles()->sync(Role::whereIn('name', $validated['roles'] ?? [])->pluck('id'));

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
        ];
    }

    private function abortIfSameUser(User $user): void
    {
        if (Auth::user()->is($user)) {
            abort(403, 'Um usuário da CTI não pode bloquear ou excluir a própria conta.');
        }
    }
}
