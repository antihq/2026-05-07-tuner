<?php

use App\Models\User;
use Laravel\Fortify\Features;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);
});

test('two factor confirmation fails with invalid code', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::settings')
        ->call('enableTwoFactor')
        ->set('code', '000000')
        ->call('confirmTwoFactor');

    $component->assertHasErrors(['code']);
});

test('two factor confirmation succeeds with valid code', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::settings')
        ->call('enableTwoFactor');

    $user->refresh();
    $secret = decrypt($user->two_factor_secret);

    $totp = (new Google2FA);
    $validCode = $totp->getCurrentOtp($secret);

    $component
        ->set('code', $validCode)
        ->call('confirmTwoFactor');

    $component->assertHasNoErrors()
        ->assertSet('twoFactorEnabled', true)
        ->assertSet('showQrCode', false);

    expect($user->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

test('two factor setup can be cancelled', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::settings')
        ->call('enableTwoFactor')
        ->call('cancelTwoFactorSetup');

    $component->assertSet('showQrCode', false);

    expect($user->fresh()->two_factor_secret)->toBeNull();
});

test('two factor enable without confirmation shows enable button', function () {
    Features::twoFactorAuthentication([
        'confirm' => false,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::settings')
        ->call('enableTwoFactor');

    $component->assertSee('Enable')
        ->assertDontSee('Step 2');
});
