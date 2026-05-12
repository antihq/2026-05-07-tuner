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

test('authenticator create page can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('authenticator.create'))
        ->assertOk()
        ->assertSee('Enable authenticator')
        ->assertSee('Step 1')
        ->assertSee('Manual setup key')
        ->assertSee('Step 2')
        ->assertSee('Confirm');
});

test('authenticator create page requires password confirmation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('authenticator.create'))
        ->assertRedirect(route('password.confirm'));
});

test('authenticator create page redirects if two factor already enabled', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('authenticator.create'))
        ->assertRedirect(route('authenticator.show'));
});

test('authenticator create enables two factor and shows qr code on mount', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::authenticator.create');

    $component->assertSet('requiresConfirmation', true)
        ->assertSet('qrCodeSvg', fn ($svg) => str_contains($svg, '<svg'))
        ->assertSet('manualSetupKey', fn ($key) => filled($key));

    expect($user->fresh()->two_factor_secret)->not->toBeNull();
});

test('authenticator confirmation fails with invalid code', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::authenticator.create')
        ->set('code', '000000')
        ->call('confirmTwoFactor');

    $component->assertHasErrors(['code']);
});

test('authenticator confirmation succeeds with valid code', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::authenticator.create');

    $user->refresh();
    $secret = decrypt($user->two_factor_secret);

    $totp = (new Google2FA);
    $validCode = $totp->getCurrentOtp($secret);

    $component = Livewire::test('pages::authenticator.create')
        ->set('code', $validCode)
        ->call('confirmTwoFactor');

    $component->assertHasNoErrors()
        ->assertRedirect(route('authenticator.show'));

    expect($user->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

test('authenticator create page without confirmation shows enable button', function () {
    Features::twoFactorAuthentication([
        'confirm' => false,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('authenticator.create'))
        ->assertOk()
        ->assertSee('Enable')
        ->assertDontSee('Step 2');
});
