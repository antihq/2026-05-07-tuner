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

test('authenticator show page redirects to create when two factor disabled', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('authenticator.show'))
        ->assertRedirect(route('authenticator.create'));
});

test('authenticator show page requires password confirmation when enabled', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('authenticator.show'))
        ->assertRedirect(route('password.confirm'));
});

test('authenticator show page renders without two factor when feature is disabled', function () {
    config(['fortify.features' => []]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('authenticator.show'))
        ->assertOk()
        ->assertDontSeeHtml('data-test="enable-two-factor-button"');
});

test('authenticator disabled when confirmation abandoned between requests', function () {
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => null,
    ])->save();

    $this->actingAs($user);

    $component = Livewire::test('pages::authenticator.show');

    $component->assertSet('twoFactorEnabled', false);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
    ]);
});

test('authenticator show page shows disable and recovery codes buttons when two factor enabled', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('authenticator.show'))
        ->assertOk()
        ->assertSee('Disable')
        ->assertSee('Recovery codes')
        ->assertDontSeeHtml('data-test="enable-two-factor-button"');
});

test('authenticator show page shows recovery codes remaining when two factor enabled', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::authenticator.show');

    $component->assertSet('recoveryCodesRemaining', 1);
});
