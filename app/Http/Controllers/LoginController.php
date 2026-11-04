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

        
        if (Auth::attempt(['prontuario' => $validated['prontuario'], 'password' => $validated['password']], $remember))
        {
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }
        
        return back()->withErrors([
            'credentials' => 'Prontuário ou senha errados.',
        ])->withInput($request->only('prontuario'));
    }
}
