<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('team invitations can be created', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $this->actingAs($owner);

    Livewire::test('pages::teams.invitations.create', ['team' => $team])
        ->set('inviteEmail', 'invited@example.com')
        ->set('inviteRole', TeamRole::Member->value)
        ->call('createInvitation')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('team_invitations', [
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'role' => TeamRole::Member->value,
    ]);
});

test('team invitations cannot be created by members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($member);

    Livewire::test('pages::teams.invitations.create', ['team' => $team])
        ->set('inviteEmail', 'invited@example.com')
        ->set('inviteRole', TeamRole::Member->value)
        ->call('createInvitation')
        ->assertForbidden();
});

test('team invitations can be cancelled by owner', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($owner);

    Livewire::test('pages::teams.invitations.show', ['team' => $team, 'invitation' => $invitation])
        ->call('cancelInvitation')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('team_invitations', [
        'id' => $invitation->id,
    ]);
});

test('team invitations cannot be cancelled by members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($member);

    Livewire::test('pages::teams.invitations.show', ['team' => $team, 'invitation' => $invitation])
        ->call('cancelInvitation')
        ->assertForbidden();

    $this->assertDatabaseHas('team_invitations', [
        'id' => $invitation->id,
    ]);
});

test('invitation cancel redirects to invitations list', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($owner);

    Livewire::test('pages::teams.invitations.show', ['team' => $team, 'invitation' => $invitation])
        ->call('cancelInvitation')
        ->assertHasNoErrors()
        ->assertRedirect(route('teams.invitations', $team));
});

test('invitation show returns 404 for mismatched team', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();
    $otherTeam = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $otherTeam->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $otherTeam->id,
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($owner);

    $response = $this
        ->get(route('teams.invitations.show', ['team' => $team, 'invitation' => $invitation]));

    $response->assertNotFound();
});

test('invitations list page can be rendered', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $response = $this
        ->actingAs($owner)
        ->get(route('teams.invitations', $team));

    $response->assertOk();
});

test('invitation show page can be rendered', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($owner);

    $response = $this
        ->get(route('teams.invitations.show', ['team' => $team, 'invitation' => $invitation]));

    $response->assertOk();
});

test('invite page can be rendered', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $this->actingAs($owner);

    $response = $this
        ->get(route('teams.invitations.create', $team));

    $response->assertOk();
});

test('invitation creation redirects to invitations list', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $this->actingAs($owner);

    Livewire::test('pages::teams.invitations.create', ['team' => $team])
        ->set('inviteEmail', 'invited@example.com')
        ->set('inviteRole', TeamRole::Member->value)
        ->call('createInvitation')
        ->assertHasNoErrors()
        ->assertRedirect(route('teams.invitations', $team));

    $this->assertDatabaseHas('team_invitations', [
        'team_id' => $team->id,
        'email' => 'invited@example.com',
    ]);
});

test('team invitations can be accepted', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'role' => TeamRole::Member,
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($invitedUser);

    Livewire::test('pages::invitations.show', ['invitation' => $invitation])
        ->call('accept')
        ->assertRedirect(route('dashboard'));

    expect($invitation->fresh()->accepted_at)->not->toBeNull();
    expect($invitedUser->fresh()->belongsToTeam($team))->toBeTrue();
});

test('team invitations cannot be accepted by user that wasnt invited', function () {
    $owner = User::factory()->create();
    $uninvitedUser = User::factory()->create(['email' => 'uninvited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($uninvitedUser);

    Livewire::test('pages::invitations.show', ['invitation' => $invitation])
        ->call('accept')
        ->assertHasErrors(['invitation']);

    expect($uninvitedUser->fresh()->belongsToTeam($team))->toBeFalse();
});

test('expired invitations cannot be accepted', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->expired()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($invitedUser);

    Livewire::test('pages::invitations.show', ['invitation' => $invitation])
        ->call('accept')
        ->assertHasErrors(['invitation']);

    expect($invitedUser->fresh()->belongsToTeam($team))->toBeFalse();
});

test('invitation accept page can be rendered', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($invitedUser)
        ->get(route('invitations.show', $invitation))
        ->assertOk()
        ->assertSee($team->name)
        ->assertSee('Accept invitation');
});

test('already accepted invitation cannot be accepted again', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $invitation->update(['accepted_at' => now()]);

    $this->actingAs($invitedUser);

    Livewire::test('pages::invitations.show', ['invitation' => $invitation])
        ->call('accept')
        ->assertHasErrors(['invitation']);
});

test('already accepted invitation shows no accept button', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $invitation->update(['accepted_at' => now()]);

    $this->actingAs($invitedUser)
        ->get(route('invitations.show', $invitation))
        ->assertOk()
        ->assertDontSee('Accept invitation');
});

test('invitation show page shows cancel button for pending invitations', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'invited_by' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->get(route('teams.invitations.show', ['team' => $team, 'invitation' => $invitation]))
        ->assertOk()
        ->assertSee('Cancel invitation');
});
