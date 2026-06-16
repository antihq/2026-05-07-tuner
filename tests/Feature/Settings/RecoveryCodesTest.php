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

test('recovery codes are shown when two factor enabled', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::settings');

    $codes = $component->get('recoveryCodes');
    expect($codes)->not->toBeEmpty();
});

test('recovery codes can be regenerated', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::settings');

    $originalCodes = $component->get('recoveryCodes');
    expect($originalCodes)->not->toBeEmpty();

    $component->call('regenerateRecoveryCodes');

    $newCodes = $component->get('recoveryCodes');

    expect($newCodes)->not->toBeEmpty()
        ->and($newCodes)->not->toEqual($originalCodes);
});
