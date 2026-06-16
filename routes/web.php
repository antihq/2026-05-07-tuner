<?php

use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages::home')->name('home')->middleware('guest');

Route::get('/manifest', function () {
    return response()->view('manifest-json')->header('Content-Type', 'application/json');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

        Route::livewire('projects', 'pages::projects.index')->name('projects.index');
        Route::livewire('projects/create', 'pages::projects.create')->name('projects.create');
        Route::livewire('projects/{project}', 'pages::projects.show')->name('projects.show');

        Route::livewire('channels', 'pages::channels.index')->name('channels.index');
        Route::livewire('channels/create', 'pages::channels.create')->name('channels.create');
        Route::livewire('channels/{channel}', 'pages::channels.show')->name('channels.show');

        Route::livewire('events', 'pages::events.index')->name('events.index');
        Route::livewire('events/{event}', 'pages::events.show')->name('events.show');
    });

Route::middleware(['auth'])->group(function () {
    Route::livewire('invitations/{invitation}', 'pages::invitations.show')->name('invitations.show');
});

require __DIR__.'/settings.php';
