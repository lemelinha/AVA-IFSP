<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::any('/logout', [LoginController::class, 'logout'])->name('login.logout');
    Route::get('/', [UserController::class, 'home'])->name('home');

    Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login.index');
    Route::post('/login', [LoginController::class, 'login'])->name('login.login');
});
