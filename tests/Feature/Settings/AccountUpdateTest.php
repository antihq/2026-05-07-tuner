<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

test('account show page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('account.show'));

    $response->assertOk();
});

test('account show page shows active sessions count', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('account.show'))
        ->assertOk()
        ->assertSee('Active sessions');
});

test('account show page shows registered date', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('account.show'))
        ->assertOk()
        ->assertSee('Registered');
});

test('account show page shows two factor status', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('account.show'))
        ->assertOk()
        ->assertSee('Two-factor');
});

test('account show page shows team count', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('account.show'))
        ->assertOk()
        ->assertSee('Teams');
});

test('account show page shows current team name', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);
    $user->switchTeam($team);

    $this->actingAs($user);

    $this->get(route('account.show'))
        ->assertOk()
        ->assertSee($team->name);
});

test('account show page shows edit button', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('account.show'))
        ->assertOk()
        ->assertSee('Edit');
});

test('account show page links to delete page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('account.show'))
        ->assertOk()
        ->assertSee('Delete account');
});

test('account edit page can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this
        ->get(route('account.edit'));

    $response->assertOk();
});

test('account information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::account.edit')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::account.edit')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('account update redirects to show page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::account.edit')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation')
        ->assertHasNoErrors()
        ->assertRedirect(route('account.show'));
});

test('account delete page can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this
        ->get(route('account.delete'));

    $response->assertOk();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::account.delete')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::account.delete')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});
