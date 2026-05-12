<?php

use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard', ['current_team' => 'any']));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
});

test('dashboard renders with no projects', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('0');
    $response->assertSee('—');
});

test('dashboard shows project breakdown', function () {
    $user = User::factory()->create();
    $projectA = $user->currentTeam->projects()->create(['name' => 'Alpha']);
    $projectB = $user->currentTeam->projects()->create(['name' => 'Bravo']);
    $channelA = $projectA->channels()->create(['name' => 'Events', 'url_key' => 'key_aaa']);
    $channelB = $projectB->channels()->create(['name' => 'Logs', 'url_key' => 'key_bbb']);
    $channelA->events()->create(['title' => 'Signup']);
    $channelA->events()->create(['title' => 'Login']);
    $channelB->events()->create(['title' => 'Page view']);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Alpha');
    $response->assertSee('Bravo');
});

test('dashboard shows events last 24h count', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Events', 'url_key' => 'key_24h']);

    $channel->events()->create(['title' => 'Recent Event']);
    $oldEvent = $channel->events()->create(['title' => 'Old Event']);
    $oldEvent->created_at = now()->subDays(2);
    $oldEvent->save();

    $this->actingAs($user);

    Livewire::test('pages::dashboard')
        ->assertSet('systemHealth.events_last_24h', 1)
        ->assertSee('Recent Event')
        ->assertSee('Old Event');
});

test('dashboard shows last event received timestamp', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Events', 'url_key' => 'key_ts']);
    $channel->events()->create(['title' => 'Timestamped Event']);
    $this->actingAs($user);

    Livewire::test('pages::dashboard')
        ->assertSee($channel->events()->first()->created_at->format('Y-m-d H:i:s'));
});

test('dashboard shows event descriptions in recent events', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Events', 'url_key' => 'key_desc']);
    $channel->events()->create([
        'title' => 'User Registered',
        'description' => 'Signed up via GitHub OAuth',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('User Registered');
    $response->assertSee('Signed up via GitHub OAuth');
});

test('dashboard recent events are scoped to current team', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherProject = $otherUser->currentTeam->projects()->create(['name' => 'Other Project']);
    $otherChannel = $otherProject->channels()->create(['name' => 'Secret', 'url_key' => 'key_secret']);
    $otherChannel->events()->create(['title' => 'Hidden Event']);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard', ['current_team' => $user->currentTeam->slug]));

    $response->assertOk();
    $response->assertDontSee('Hidden Event');
});

test('dashboard project breakdown is sortable', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('pages::dashboard')
        ->set('sortField', 'name')
        ->assertSet('sortField', 'name')
        ->call('sortBy', 'name')
        ->assertSet('sortDirection', 'asc')
        ->call('sortBy', 'name')
        ->assertSet('sortDirection', 'desc')
        ->call('sortBy', 'events_count')
        ->assertSet('sortField', 'events_count')
        ->assertSet('sortDirection', 'asc');
});

test('dashboard recent events are grouped by date', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Events', 'url_key' => 'key_dates']);

    $oldEvent = $channel->events()->create(['title' => 'Old Event']);
    $oldEvent->created_at = now()->subDays(2);
    $oldEvent->save();

    $channel->events()->create(['title' => 'New Event']);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee(now()->subDays(2)->format('Y-m-d D'));
    $response->assertSee(now()->format('Y-m-d D'));
});

test('dashboard recent events show user id', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Events', 'url_key' => 'key_uid']);
    $channel->events()->create(['title' => 'Signup', 'user_id' => 'usr_42']);
    $channel->events()->create(['title' => 'Anonymous']);

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('usr_42');
    $response->assertSee('Anonymous');
});

test('dashboard system health links to most active project', function () {
    $user = User::factory()->create();
    $projectA = $user->currentTeam->projects()->create(['name' => 'Active']);
    $projectB = $user->currentTeam->projects()->create(['name' => 'Quiet']);
    $channelA = $projectA->channels()->create(['name' => 'Events', 'url_key' => 'key_active']);
    $channelB = $projectB->channels()->create(['name' => 'Events', 'url_key' => 'key_quiet']);

    $channelA->events()->create(['title' => 'Event 1']);
    $channelA->events()->create(['title' => 'Event 2']);
    $channelA->events()->create(['title' => 'Event 3']);
    $channelB->events()->create(['title' => 'Event 1']);

    $this->actingAs($user);

    Livewire::test('pages::dashboard')
        ->assertSet('systemHealth.most_active_project', function ($project) use ($projectA) {
            expect($project)->not->toBeNull();
            expect($project['id'])->toBe($projectA->id);
            expect($project['events_last_24h'])->toBe(3);

            return true;
        })
        ->assertSet('systemHealth.last_event_project', function ($project) use ($projectA) {
            expect($project)->not->toBeNull();
            expect($project['id'])->toBe($projectA->id);

            return true;
        });
});
