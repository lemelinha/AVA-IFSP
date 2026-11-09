<?php

use App\Models\User;

test('guests are redirected to login', function () {
    $this->get('/profile')->assertRedirect('/login');
});

test('authenticated users can view their profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk()
        ->assertSee($user->name)
        ->assertSee($user->prontuario)
        ->assertSee($user->email);
});
