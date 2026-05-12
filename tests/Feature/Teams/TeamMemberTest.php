<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

test('team member role can be updated by owner', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($owner);

    Livewire::test('pages::teams.members.edit', ['team' => $team, 'user' => $member])
        ->set('selectedRole', TeamRole::Admin->value)
        ->call('updateRole')
        ->assertHasNoErrors();

    expect($team->members()->where('user_id', $member->id)->first()->pivot->role->value)->toEqual(TeamRole::Admin->value);
});

test('team member role cannot be updated by non owner', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($admin, ['role' => TeamRole::Admin->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($admin);

    Livewire::test('pages::teams.members.edit', ['team' => $team, 'user' => $member])
        ->set('selectedRole', TeamRole::Admin->value)
        ->call('updateRole')
        ->assertForbidden();
});

test('team member role update redirects to member show page', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($owner);

    Livewire::test('pages::teams.members.edit', ['team' => $team, 'user' => $member])
        ->set('selectedRole', TeamRole::Admin->value)
        ->call('updateRole')
        ->assertHasNoErrors()
        ->assertRedirect(route('teams.members.show', ['team' => $team->slug, 'user' => $member->id]));
});

test('members list page can be rendered', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $response = $this
        ->actingAs($owner)
        ->get(route('teams.members', $team));

    $response->assertOk();
});

test('member show page can be rendered', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $response = $this
        ->actingAs($owner)
        ->get(route('teams.members.show', ['team' => $team, 'user' => $member]));

    $response->assertOk();
});

test('member edit page can be rendered', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $response = $this
        ->actingAs($owner)
        ->get(route('teams.members.edit', ['team' => $team, 'user' => $member]));

    $response->assertOk();
});

test('team member can be removed by owner', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($owner);

    Livewire::test('pages::teams.members.show', ['team' => $team, 'user' => $member])
        ->call('removeMember')
        ->assertHasNoErrors();

    expect($member->fresh()->belongsToTeam($team))->toBeFalse();
});

test('team member cannot be removed by non owners', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($admin, ['role' => TeamRole::Admin->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($admin);

    Livewire::test('pages::teams.members.show', ['team' => $team, 'user' => $member])
        ->call('removeMember')
        ->assertForbidden();
});

test('removed members current team is set to personal team', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $personalTeam = $member->personalTeam();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $member->update(['current_team_id' => $team->id]);

    $this->actingAs($owner);

    Livewire::test('pages::teams.members.show', ['team' => $team, 'user' => $member])
        ->call('removeMember')
        ->assertHasNoErrors();

    expect($member->fresh()->current_team_id)->toEqual($personalTeam->id);
});

test('member show page shows edit button for non-owner members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($owner)
        ->get(route('teams.members.show', ['team' => $team, 'user' => $member]))
        ->assertOk()
        ->assertSee('Edit');
});

test('member show page hides edit and remove buttons for owner', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($owner)
        ->get(route('teams.members.show', ['team' => $team, 'user' => $owner]))
        ->assertOk()
        ->assertDontSee('Edit')
        ->assertDontSee('Remove member');
});

test('member show page shows remove button for non-owner members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($owner)
        ->get(route('teams.members.show', ['team' => $team, 'user' => $member]))
        ->assertOk()
        ->assertSee('Remove member');
});
