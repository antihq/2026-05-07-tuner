<?php

use App\Models\User;
use Livewire\Livewire;

test('event show page can be rendered', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Registrations', 'url_key' => 'key12345']);
    $event = $channel->events()->create(['title' => 'User Signed Up']);

    $response = $this
        ->actingAs($user)
        ->get(route('events.show', ['event' => $event->id]));

    $response->assertOk();
});

test('event show page displays event details', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Registrations', 'url_key' => 'key12345']);
    $event = $channel->events()->create([
        'title' => 'User Signed Up',
        'user_id' => 'usr_42',
        'description' => 'New user registered via Google OAuth.',
    ]);

    $this->actingAs($user);

    Livewire::test('pages::events.show', ['event' => $event->id])
        ->assertSee('User Signed Up')
        ->assertSee('usr_42')
        ->assertSee('New user registered via Google OAuth.')
        ->assertSee('My Project')
        ->assertSee('Registrations');
});

test('event show page renders without description', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Registrations', 'url_key' => 'key12345']);
    $event = $channel->events()->create(['title' => 'No Description Event']);

    $response = $this
        ->actingAs($user)
        ->get(route('events.show', ['event' => $event->id]));

    $response->assertOk();
    $response->assertSee('No Description Event');
    $response->assertDontSee('New user registered via Google OAuth.');
});

test('event show page fails for another teams event', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherProject = $otherUser->currentTeam->projects()->create(['name' => 'Other Project']);
    $channel = $otherProject->channels()->create(['name' => 'Private Channel', 'url_key' => 'key12345']);
    $event = $channel->events()->create(['title' => 'Secret Event']);

    $this->actingAs($user);

    $response = $this->get(route('events.show', ['event' => $event->id]));

    $response->assertForbidden();
});

test('event show page fails for non-existent event', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('events.show', ['event' => 99999]));

    $response->assertNotFound();
});
