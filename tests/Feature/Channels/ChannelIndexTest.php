<?php

use App\Models\User;
use Livewire\Livewire;

test('channels index page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('channels.index'));

    $response->assertOk();
});

test('guests cannot access channels index', function () {
    $response = $this->get(route('channels.index', ['current_team' => 'fake']));

    $response->assertRedirect(route('login'));
});

test('channels index shows all team channels with project column', function () {
    $user = User::factory()->create();
    $projectA = $user->currentTeam->projects()->create(['name' => 'Alpha']);
    $projectB = $user->currentTeam->projects()->create(['name' => 'Bravo']);
    $channelA = $projectA->channels()->create(['name' => 'Registrations', 'url_key' => 'key_aaa']);
    $channelB = $projectB->channels()->create(['name' => 'Payments', 'url_key' => 'key_bbb']);

    $this->actingAs($user);

    Livewire::test('pages::channels.index')
        ->assertSee('Registrations')
        ->assertSee('Payments')
        ->assertSee('Alpha')
        ->assertSee('Bravo');
});

test('channels index is scoped to current team', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherProject = $otherUser->currentTeam->projects()->create(['name' => 'Other Project']);
    $otherChannel = $otherProject->channels()->create(['name' => 'Secret Channel', 'url_key' => 'key_secret']);
    $otherChannel->events()->create(['title' => 'Hidden Event']);

    $this->actingAs($user);

    Livewire::test('pages::channels.index')
        ->assertSet('channels', function ($channels) {
            expect($channels)->toHaveCount(0);

            return true;
        })
        ->assertDontSee('Secret Channel');
});

test('channels index shows dashes for channels with no events', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $project->channels()->create(['name' => 'Empty Channel', 'url_key' => 'key_empty']);

    $this->actingAs($user);

    Livewire::test('pages::channels.index')
        ->assertSet('channels', function ($channels) {
            expect($channels[0]['last_event_at'])->toBeNull();
            expect($channels[0]['events_count'])->toBe(0);
            expect($channels[0]['events_last_24h'])->toBe(0);

            return true;
        })
        ->assertSee('—');
});

test('channels index is sortable', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::channels.index')
        ->call('sortBy', 'name')
        ->assertSet('sortField', 'name')
        ->assertSet('sortDirection', 'asc')
        ->call('sortBy', 'name')
        ->assertSet('sortDirection', 'desc')
        ->call('sortBy', 'events_count')
        ->assertSet('sortField', 'events_count')
        ->assertSet('sortDirection', 'asc');
});

test('channels index has new channel button', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('channels.index'));

    $response->assertOk();
    $response->assertSee('New channel');
});
