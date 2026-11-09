<?php

namespace App\Http\Controllers;

class UserController extends Controller
{
    public function home()
    {
        return view('auth.home');
    }

    public function profile()
    {
        return view('auth.profile');
    }
}
