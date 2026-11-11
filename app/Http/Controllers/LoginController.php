<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'prontuario' => 'required',
            'password' => 'required',
        ], [
            'prontuario.required' => 'O campo prontuário é obrigatório.',
            'password.required' => 'O campo senha é obrigatório.',
        ]);
        $remember = $request->filled('remember');

        $prontuario = strtoupper($validated['prontuario']);
        $password = $validated['password'];

        if (Auth::attempt(['prontuario' => $prontuario, 'password' => $password], $remember)) {
            $user = Auth::user();

            if ($user->blocked) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'credentials' => 'Conta bloqueada. Procure a CTI.',
                ])->withInput($request->only('prontuario'));
            }

            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'credentials' => 'Prontuário ou senha errados.',
        ])->withInput($request->only('prontuario'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.index');
    }
}
