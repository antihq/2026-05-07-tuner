<?php

use App\Models\User;
use Laravel\Fortify\Features;
use Livewire\Livewire;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);
});

test('authenticator delete page can be rendered', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('authenticator.delete'))
        ->assertOk()
        ->assertSee('Disable authenticator');
});

test('authenticator delete page requires password confirmation', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user)
        ->get(route('authenticator.delete'))
        ->assertRedirect(route('password.confirm'));
});

test('authenticator delete page redirects if two factor not enabled', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('authenticator.delete'))
        ->assertRedirect(route('authenticator.show'));
});

test('authenticator can be disabled with correct password', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::authenticator.delete')
        ->set('password', 'password')
        ->call('disable');

    $component->assertHasNoErrors()
        ->assertRedirect(route('authenticator.show'));

    expect($user->fresh()->two_factor_secret)->toBeNull();
});

test('authenticator disable fails with wrong password', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::authenticator.delete')
        ->set('password', 'wrong-password')
        ->call('disable');

    $component->assertHasErrors(['password']);

    expect($user->fresh()->two_factor_secret)->not->toBeNull();
});
