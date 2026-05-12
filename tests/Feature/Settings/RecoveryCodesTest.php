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

test('recovery codes page can be rendered', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('recovery-codes.show'))
        ->assertOk()
        ->assertSee('Recovery codes')
        ->assertSee('Regenerate codes')
        ->assertSee('recovery-code-1');
});

test('recovery codes page requires password confirmation', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user)
        ->get(route('recovery-codes.show'))
        ->assertRedirect(route('password.confirm'));
});

test('recovery codes page redirects if two factor not enabled', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('recovery-codes.show'))
        ->assertRedirect(route('authenticator.show'));
});

test('recovery codes can be regenerated', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::recovery-codes.show');

    $originalCodes = $component->get('recoveryCodes');
    expect($originalCodes)->not->toBeEmpty();

    $component->call('regenerateRecoveryCodes');

    $newCodes = $component->get('recoveryCodes');

    expect($newCodes)->not->toBeEmpty()
        ->and($newCodes)->not->toEqual($originalCodes);
});
