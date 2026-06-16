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

test('authenticator can be disabled with correct password', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::settings')
        ->set('disablePassword', 'password')
        ->call('disableTwoFactor');

    $component->assertHasNoErrors()
        ->assertSet('twoFactorEnabled', false);

    expect($user->fresh()->two_factor_secret)->toBeNull();
});

test('authenticator disable fails with wrong password', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::settings')
        ->set('disablePassword', 'wrong-password')
        ->call('disableTwoFactor');

    $component->assertHasErrors(['disablePassword']);

    expect($user->fresh()->two_factor_secret)->not->toBeNull();
});

test('disabling two factor clears recovery codes', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::settings');

    $component->assertSet('twoFactorEnabled', true)
        ->assertSet('recoveryCodes', fn ($codes) => ! empty($codes));

    $component->set('disablePassword', 'password')
        ->call('disableTwoFactor');

    $component->assertSet('twoFactorEnabled', false)
        ->assertSet('recoveryCodes', []);
});
