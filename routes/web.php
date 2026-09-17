<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'index')->name('login.index');

    Route::post('/login', 'login')->name('login.login');
});
