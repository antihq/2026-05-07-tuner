<?php

use App\Models\Channel;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('projects index page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('projects.index'));

    $response->assertOk();
});

test('projects index shows project breakdown', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Registrations', 'url_key' => 'key12345']);
    $channel->events()->create(['title' => 'User Signed Up']);
    $channel->events()->create(['title' => 'User Logged In']);

    $this->actingAs($user);

    Livewire::test('pages::projects.index')
        ->assertSee('My Project')
        ->assertSee('2')
        ->assertSee('1');
});

test('projects index shows events last 24h count', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Registrations', 'url_key' => 'key12345']);
    $channel->events()->create(['title' => 'Recent Event']);
    $oldEvent = $channel->events()->create(['title' => 'Old Event']);
    $oldEvent->created_at = now()->subDays(2);
    $oldEvent->save();

    $this->actingAs($user);

    Livewire::test('pages::projects.index')
        ->assertSet('projects', function ($projects) {
            expect($projects)->toHaveCount(1);
            expect($projects[0]['events_last_24h'])->toBe(1);
            expect($projects[0]['events_count'])->toBe(2);

            return true;
        });
});

test('projects index shows last event received timestamp', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Registrations', 'url_key' => 'key12345']);
    $channel->events()->create(['title' => 'User Signed Up']);

    $this->actingAs($user);

    Livewire::test('pages::projects.index')
        ->assertSet('projects', function ($projects) {
            expect($projects[0]['last_event_at'])->not->toBeNull();

            return true;
        });
});

test('projects index shows dashes for projects with no events', function () {
    $user = User::factory()->create();
    $user->currentTeam->projects()->create(['name' => 'Empty Project']);

    $this->actingAs($user);

    Livewire::test('pages::projects.index')
        ->assertSet('projects', function ($projects) {
            expect($projects[0]['last_event_at'])->toBeNull();
            expect($projects[0]['events_count'])->toBe(0);
            expect($projects[0]['events_last_24h'])->toBe(0);

            return true;
        })
        ->assertSee('—');
});

test('projects index is sortable', function () {
    $user = User::factory()->create();
    $projectA = $user->currentTeam->projects()->create(['name' => 'Alpha']);
    $projectB = $user->currentTeam->projects()->create(['name' => 'Bravo']);

    $this->actingAs($user);

    Livewire::test('pages::projects.index')
        ->call('sortBy', 'name')
        ->assertSet('sortField', 'name')
        ->assertSet('sortDirection', 'asc')
        ->call('sortBy', 'name')
        ->assertSet('sortDirection', 'desc');
});

test('projects index is scoped to current team', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherProject = $otherUser->currentTeam->projects()->create(['name' => 'Other Project']);
    $otherChannel = $otherProject->channels()->create(['name' => 'Secret', 'url_key' => 'key_secret']);
    $otherChannel->events()->create(['title' => 'Hidden Event']);

    $this->actingAs($user);

    Livewire::test('pages::projects.index')
        ->assertSet('projects', function ($projects) {
            expect($projects)->toHaveCount(0);

            return true;
        })
        ->assertDontSee('Other Project');
});

test('projects index last event at picks latest across channels', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channelA = $project->channels()->create(['name' => 'Auth', 'url_key' => 'key_auth']);
    $channelB = $project->channels()->create(['name' => 'Billing', 'url_key' => 'key_bill']);

    $oldEvent = $channelA->events()->create(['title' => 'Login']);
    $oldEvent->created_at = now()->subDays(3);
    $oldEvent->save();

    $recentEvent = $channelB->events()->create(['title' => 'Payment']);

    $this->actingAs($user);

    Livewire::test('pages::projects.index')
        ->assertSet('projects', function ($projects) use ($recentEvent) {
            expect($projects[0]['last_event_at'])->not->toBeNull();
            expect($projects[0]['last_event_at']->eq($recentEvent->created_at))->toBeTrue();
            expect($projects[0]['events_count'])->toBe(2);

            return true;
        });
});

test('projects create page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('projects.create'));

    $response->assertOk();
});

test('projects can be created', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::projects.create')
        ->set('name', 'My SaaS App')
        ->call('create')
        ->assertHasNoErrors();

    $project = Project::where('name', 'My SaaS App')->first();
    expect($project)->not->toBeNull();
    expect($project->team_id)->toBe($user->currentTeam->id);
});

test('creating a project redirects to show page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::projects.create')
        ->set('name', 'Redirect Test')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect(route('projects.show', ['project' => Project::where('name', 'Redirect Test')->first()->id]));
});

test('projects can be deleted', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'To Delete']);

    $this->actingAs($user);

    Livewire::test('pages::projects.show', ['project' => $project->id])
        ->set('deleteProjectName', 'To Delete')
        ->call('deleteProject');

    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
    ]);
});

test('projects are scoped to current team', function () {
    $user = User::factory()->create();
    $otherTeam = Team::factory()->create();
    $otherProject = $otherTeam->projects()->create(['name' => 'Other Project']);

    $this->actingAs($user);

    $this->expectException(ModelNotFoundException::class);

    Livewire::test('pages::projects.show', ['project' => $otherProject->id]);

    $this->assertDatabaseHas('projects', [
        'id' => $otherProject->id,
    ]);
});

test('project show page can be rendered', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);

    $response = $this
        ->actingAs($user)
        ->get(route('projects.show', ['project' => $project->id]));

    $response->assertOk();
});

test('project show page displays project stats', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channelA = $project->channels()->create(['name' => 'Auth', 'url_key' => 'key_auth']);
    $channelB = $project->channels()->create(['name' => 'Billing', 'url_key' => 'key_bill']);
    $channelA->events()->create(['title' => 'Login']);
    $channelA->events()->create(['title' => 'Signup']);
    $channelB->events()->create(['title' => 'Payment']);

    $this->actingAs($user);

    Livewire::test('pages::projects.show', ['project' => $project->id])
        ->assertSet('projectStats', function ($stats) {
            expect($stats['total_events'])->toBe(3);
            expect($stats['events_last_24h'])->toBe(3);
            expect($stats['last_event_at'])->not->toBeNull();

            return true;
        });
});

test('project show page shows channels with activity columns', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Registrations', 'url_key' => 'key12345']);
    $channel->events()->create(['title' => 'User Signed Up']);

    $this->actingAs($user);

    Livewire::test('pages::projects.show', ['project' => $project->id])
        ->assertSet('channels', function ($channels) {
            expect($channels)->toHaveCount(1);
            expect($channels[0]['events_count'])->toBe(1);
            expect($channels[0]['events_last_24h'])->toBe(1);
            expect($channels[0]['last_event_at'])->not->toBeNull();

            return true;
        });
});

test('project show page shows dashes for channels with no events', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $project->channels()->create(['name' => 'Empty Channel', 'url_key' => 'key_empty']);

    $this->actingAs($user);

    Livewire::test('pages::projects.show', ['project' => $project->id])
        ->assertSet('channels', function ($channels) {
            expect($channels[0]['last_event_at'])->toBeNull();
            expect($channels[0]['events_count'])->toBe(0);
            expect($channels[0]['events_last_24h'])->toBe(0);

            return true;
        })
        ->assertSee('—');
});

test('project show page channels are sortable', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $project->channels()->create(['name' => 'Alpha', 'url_key' => 'key_a']);
    $project->channels()->create(['name' => 'Bravo', 'url_key' => 'key_b']);

    $this->actingAs($user);

    Livewire::test('pages::projects.show', ['project' => $project->id])
        ->call('sortBy', 'name')
        ->assertSet('sortField', 'name')
        ->assertSet('sortDirection', 'asc')
        ->call('sortBy', 'name')
        ->assertSet('sortDirection', 'desc');
});

test('project show page shows last event across all channels', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channelA = $project->channels()->create(['name' => 'Auth', 'url_key' => 'key_auth']);
    $channelB = $project->channels()->create(['name' => 'Billing', 'url_key' => 'key_bill']);

    $oldEvent = $channelA->events()->create(['title' => 'Login']);
    $oldEvent->created_at = now()->subDays(3);
    $oldEvent->save();

    $recentEvent = $channelB->events()->create(['title' => 'Payment']);

    $this->actingAs($user);

    Livewire::test('pages::projects.show', ['project' => $project->id])
        ->assertSet('projectStats', function ($stats) use ($recentEvent) {
            expect($stats['last_event_at'])->not->toBeNull();
            expect($stats['last_event_at']->eq($recentEvent->created_at))->toBeTrue();
            expect($stats['total_events'])->toBe(2);

            return true;
        });
});

test('channel show page displays events count from paginator', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Events', 'url_key' => 'key12345']);
    $channel->events()->create(['title' => 'Event 1']);
    $channel->events()->create(['title' => 'Event 2']);

    $this->actingAs($user);

    Livewire::test('pages::channels.show', ['channel' => $channel->id])
        ->assertSee('2');
});

test('channels can be created', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);

    $this->actingAs($user);

    Livewire::test('pages::channels.create')
        ->set('projectId', $project->id)
        ->set('name', 'Registrations')
        ->call('create')
        ->assertHasNoErrors();

    $channel = Channel::where('name', 'Registrations')->first();
    expect($channel)->not->toBeNull();
    expect($channel->url_key)->not->toBeEmpty();
    expect($channel->project_id)->toBe($project->id);
    expect($channel->team_id)->toBe($user->currentTeam->id);
});

test('channel url_key is auto-generated on creation', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);

    $this->actingAs($user);

    Livewire::test('pages::channels.create')
        ->set('projectId', $project->id)
        ->set('name', 'User Signups')
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('channels', [
        'project_id' => $project->id,
        'name' => 'User Signups',
    ]);
});

test('creating a channel redirects to channel show page', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);

    $this->actingAs($user);

    Livewire::test('pages::channels.create')
        ->set('projectId', $project->id)
        ->set('name', 'Payments')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect(route('channels.show', [
            'channel' => Channel::where('name', 'Payments')->first()->id,
        ]));
});

test('deleting a project cascades to channels and events', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'Cascade Test']);
    $channel = $project->channels()->create(['name' => 'Ch', 'url_key' => 'key12345']);
    $channel->events()->create(['title' => 'Event 1']);

    $this->actingAs($user);

    Livewire::test('pages::projects.show', ['project' => $project->id])
        ->set('deleteProjectName', 'Cascade Test')
        ->call('deleteProject');

    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    $this->assertDatabaseMissing('channels', ['id' => $channel->id]);
    $this->assertDatabaseMissing('events', ['channel_id' => $channel->id]);
});

test('project deletion requires confirmation name', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'To Delete']);

    $this->actingAs($user);

    Livewire::test('pages::projects.show', ['project' => $project->id])
        ->call('deleteProject')
        ->assertHasErrors(['deleteProjectName' => 'required']);

    $this->assertDatabaseHas('projects', ['id' => $project->id]);
});

test('project deletion fails with wrong name', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'To Delete']);

    $this->actingAs($user);

    Livewire::test('pages::projects.show', ['project' => $project->id])
        ->set('deleteProjectName', 'Wrong Name')
        ->call('deleteProject')
        ->assertHasErrors(['deleteProjectName']);

    $this->assertDatabaseHas('projects', ['id' => $project->id]);
});

test('project deletion redirects to projects index', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'Redirect After Delete']);

    $this->actingAs($user);

    Livewire::test('pages::projects.show', ['project' => $project->id])
        ->set('deleteProjectName', 'Redirect After Delete')
        ->call('deleteProject')
        ->assertRedirect(route('projects.index'));
});

test('channel show page can be rendered', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Registrations', 'url_key' => 'key12345']);

    $response = $this
        ->actingAs($user)
        ->get(route('channels.show', ['channel' => $channel->id]));

    $response->assertOk();
});

test('channel show page displays signed ingestion url', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Registrations', 'url_key' => 'key12345']);

    $this->actingAs($user);

    Livewire::test('pages::channels.show', ['channel' => $channel->id])
        ->assertSet('ingestionUrl', function (string $url) use ($channel) {
            expect($url)->toContain('/api/ingest/'.$channel->id);
            expect($url)->toContain('url_key='.$channel->url_key);
            expect($url)->toContain('signature=');

            return true;
        });
});

test('channel url key can be rotated', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Registrations', 'url_key' => 'oldkey123']);

    $this->actingAs($user);

    Livewire::test('pages::channels.show', ['channel' => $channel->id])
        ->call('rotateUrlKey')
        ->assertHasNoErrors();

    expect($channel->fresh()->url_key)->not->toBe('oldkey123');
});

test('events can be deleted from channel show page', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);
    $channel = $project->channels()->create(['name' => 'Registrations', 'url_key' => 'key12345']);
    $event = $channel->events()->create(['title' => 'Delete Me']);

    $this->actingAs($user);

    Livewire::test('pages::channels.show', ['channel' => $channel->id])
        ->call('deleteEvent', $event->id);

    $this->assertDatabaseMissing('events', [
        'id' => $event->id,
    ]);
});

test('guests cannot access projects', function () {
    $response = $this->get(route('projects.index', ['current_team' => 'fake']));

    $response->assertRedirect(route('login'));
});

test('guests cannot access channels', function () {
    $response = $this->get(route('channels.create', ['current_team' => 'fake']));

    $response->assertRedirect(route('login'));
});

test('channel name is required when creating', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);

    $this->actingAs($user);

    Livewire::test('pages::channels.create')
        ->set('projectId', $project->id)
        ->set('name', '')
        ->call('create')
        ->assertHasErrors(['name' => 'required']);
});

test('channel name must not exceed 255 characters', function () {
    $user = User::factory()->create();
    $project = $user->currentTeam->projects()->create(['name' => 'My Project']);

    $this->actingAs($user);

    Livewire::test('pages::channels.create')
        ->set('projectId', $project->id)
        ->set('name', str_repeat('a', 256))
        ->call('create')
        ->assertHasErrors(['name' => 'max']);
});

test('channel creation fails for another teams project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherProject = $otherUser->currentTeam->projects()->create(['name' => 'Other Project']);

    $this->actingAs($user);

    $this->expectException(ModelNotFoundException::class);

    Livewire::test('pages::channels.create')
        ->set('projectId', $otherProject->id)
        ->set('name', 'Should Fail')
        ->call('create');
});

test('channel show page fails for another teams channel', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherProject = $otherUser->currentTeam->projects()->create(['name' => 'Other Project']);
    $channel = $otherProject->channels()->create(['name' => 'Private Channel', 'url_key' => 'key12345']);

    $this->actingAs($user);

    $response = $this->get(route('channels.show', ['channel' => $channel->id]));

    $response->assertForbidden();
});
