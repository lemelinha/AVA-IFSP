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

test('authenticated users can view the home page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('Atividades vinculadas')
        ->assertDontSee('@hasanyrole')
        ->assertDontSee('@role');
});

test('user can log out', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->post('/logout')
        ->assertRedirect('/login');

    $this->assertGuest();
});

test('logout requires a post request', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/logout')
        ->assertStatus(405);
});
