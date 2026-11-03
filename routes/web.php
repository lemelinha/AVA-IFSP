<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;

Route::view('/', 'welcome')->name('home');

Route::get('/login', [LoginController::class, 'index'])->name('login.index');
Route::post('/login', [LoginController::class, 'login'])->name('login.login');
