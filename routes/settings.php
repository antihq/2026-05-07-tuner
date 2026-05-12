<?php

use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['auth'])->group(function () {
    Route::livewire('account', 'pages::account.show')->name('account.show');
    Route::livewire('account/edit', 'pages::account.edit')->name('account.edit');
    Route::livewire('account/delete', 'pages::account.delete')->name('account.delete');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('appearance', 'pages::appearance.edit')->name('appearance.edit');

    Route::livewire('password', 'pages::password.edit')->name('password.edit');

    Route::livewire('authenticator', 'pages::authenticator.show')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('authenticator.show');

    Route::livewire('authenticator/create', 'pages::authenticator.create')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('authenticator.create');

    Route::livewire('authenticator/delete', 'pages::authenticator.delete')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('authenticator.delete');

    Route::livewire('recovery-codes', 'pages::recovery-codes.show')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('recovery-codes.show');

    Route::livewire('teams', 'pages::teams.index')->name('teams.index');
    Route::livewire('teams/create', 'pages::teams.create')->name('teams.create');

    Route::middleware(EnsureTeamMembership::class)->group(function () {
        Route::livewire('teams/{team}', 'pages::teams.show')->name('teams.show');
        Route::livewire('teams/{team}/edit', 'pages::teams.edit')->name('teams.edit');
        Route::livewire('teams/{team}/delete', 'pages::teams.delete')->name('teams.delete');
        Route::livewire('teams/{team}/members', 'pages::teams.members.index')->name('teams.members');
        Route::livewire('teams/{team}/members/{user}', 'pages::teams.members.show')->name('teams.members.show');
        Route::livewire('teams/{team}/members/{user}/edit', 'pages::teams.members.edit')->name('teams.members.edit');
        Route::livewire('teams/{team}/invitations', 'pages::teams.invitations.index')->name('teams.invitations');
        Route::livewire('teams/{team}/invitations/create', 'pages::teams.invitations.create')->name('teams.invitations.create');
        Route::livewire('teams/{team}/invitations/{invitation}', 'pages::teams.invitations.show')->name('teams.invitations.show');
    });
});
