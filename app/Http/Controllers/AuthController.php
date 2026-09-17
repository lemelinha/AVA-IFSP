<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'prontuario' => 'required',
            'password' => 'required',
        ]);

        if (! $token = JWTAuth::attempt($credentials)) {
            return back()->withErrors([
                'credentials' => 'Prontuário ou senha inválidos',
            ]);
        }
    }
}
