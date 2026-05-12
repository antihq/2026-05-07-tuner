<?php

use App\Models\User;
use Livewire\Livewire;

test('events index page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('events.index'));

    $response->assertOk();
});

test('guests cannot access events index', function () {
    $response = $this->get(route('events.index', ['current_team' => 'fake']));

    $response->assertRedirect(route('login'));
});

test('events index shows all team events with project and channel columns', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Registrations', 'url_key' => 'key12345']);
    $channel->events()->create([
        'title' => 'User Signed Up',
        'user_id' => 'usr_42',
    ]);

    $this->actingAs($user);

    Livewire::test('pages::events.index')
        ->assertSee('User Signed Up')
        ->assertSee('My Project')
        ->assertSee('Registrations')
        ->assertSee('usr_42');
});

test('events index is scoped to current team', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherProject = $otherUser->currentTeam->projects()->create(['name' => 'Other Project']);
    $otherChannel = $otherProject->channels()->create(['name' => 'Secret', 'url_key' => 'key_secret']);
    $otherChannel->events()->create(['title' => 'Hidden Event']);

    $this->actingAs($user);

    Livewire::test('pages::events.index')
        ->assertSet('events', function ($events) {
            expect($events->count())->toBe(0);

            return true;
        })
        ->assertDontSee('Hidden Event');
});

test('events index is sortable', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::events.index')
        ->call('sortBy', 'title')
        ->assertSet('sortField', 'title')
        ->assertSet('sortDirection', 'asc')
        ->call('sortBy', 'title')
        ->assertSet('sortDirection', 'desc')
        ->call('sortBy', 'created_at')
        ->assertSet('sortField', 'created_at')
        ->assertSet('sortDirection', 'asc');
});

test('events index shows dashes for missing optional fields', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Events', 'url_key' => 'key12345']);
    $channel->events()->create(['title' => 'No Extras']);

    $this->actingAs($user);

    Livewire::test('pages::events.index')
        ->assertSee('No Extras')
        ->assertSee('—');
});
