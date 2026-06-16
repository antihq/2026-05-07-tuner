<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('settings page shows password section', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('settings'))
        ->assertOk()
        ->assertSee('Current password')
        ->assertSee('New password')
        ->assertSee('Confirm password');
});

test('password can be updated', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $response = Livewire::test('pages::settings')
        ->set('passwordForm.current_password', 'password')
        ->set('passwordForm.password', 'new-password')
        ->set('passwordForm.password_confirmation', 'new-password')
        ->call('updatePassword');

    $response->assertHasNoErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $response = Livewire::test('pages::settings')
        ->set('passwordForm.current_password', 'wrong-password')
        ->set('passwordForm.password', 'new-password')
        ->set('passwordForm.password_confirmation', 'new-password')
        ->call('updatePassword');

    $response->assertHasErrors(['passwordForm.current_password']);
});
