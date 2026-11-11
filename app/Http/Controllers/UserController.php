<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class UserController extends Controller
{
    public function home(): View
    {
        return view('auth.home');
    }

    public function profile(): View
    {
        return view('auth.profile');
    }
}
