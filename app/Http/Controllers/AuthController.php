<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        if (Auth::user()) {
            return 'logado';
        }

        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'prontuario' => 'required',
            'password' => 'required',
        ]);

        if(!$token = Auth::attempt($credentials)) {
            return back()->withErrors([
                'credentials' => 'Prontuário ou senha inválidos',
            ]);
        }

        $user = Auth::user();

        $redirectUrl = $request->only('redirect_url') ?: '/' . strtolower($user->role->role);

        dump($redirectUrl);
    }
}
