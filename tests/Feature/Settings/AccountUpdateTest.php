<?php

use App\Models\User;
use Livewire\Livewire;

test('settings page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('settings'));

    $response->assertOk();
});

test('settings page shows delete account section', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('settings'))
        ->assertOk()
        ->assertSee('Delete account');
});

test('account information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings')
        ->set('profileForm.name', 'Test User')
        ->set('profileForm.email', 'test@example.com')
        ->call('updateProfile');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings')
        ->set('profileForm.name', 'Test User')
        ->set('profileForm.email', $user->email)
        ->call('updateProfile');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings')
        ->set('deleteForm.password', 'password')
        ->call('deleteAccount');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings')
        ->set('deleteForm.password', 'wrong-password')
        ->call('deleteAccount');

    $response->assertHasErrors(['deleteForm.password']);

    expect($user->fresh())->not->toBeNull();
});
