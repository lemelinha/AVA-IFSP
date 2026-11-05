<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;

Route::middleware('auth')->group(function () {
    Route::any('/logout', [LoginController::class, 'logout'])->name('login.logout');
    Route::get('/', [UserController::class, 'home'])->name('home');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login.index');
    Route::post('/login', [LoginController::class, 'login'])->name('login.login');
});
