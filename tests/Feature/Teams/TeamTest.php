<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

test('teams switch page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('teams.switch'));

    $response->assertOk();
});

test('team show page can be rendered', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $response = $this
        ->actingAs($user)
        ->get(route('teams.settings', $team));

    $response->assertOk();
    $response->assertSee($user->name);
});

test('teams can be updated by owners', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Original Name']);

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user);

    Livewire::test('pages::teams.settings', ['team' => $team])
        ->set('teamForm.name', 'Updated Name')
        ->call('updateTeamName')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'Updated Name',
    ]);
});

test('updating team name redirects to team show page', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Original']);
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user);

    Livewire::test('pages::teams.settings', ['team' => $team])
        ->set('teamForm.name', 'Updated')
        ->call('updateTeamName')
        ->assertRedirect(route('teams.settings', ['team' => $team->fresh()->slug]));
});

test('teams cannot be updated by members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($member);

    Livewire::test('pages::teams.settings', ['team' => $team])
        ->set('teamForm.name', 'Updated Name')
        ->call('updateTeamName')
        ->assertForbidden();
});

test('teams can be deleted by owners', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user);

    Livewire::test('pages::teams.settings', ['team' => $team])
        ->set('deleteForm.confirmName', $team->name)
        ->call('deleteTeam')
        ->assertHasNoErrors();

    $this->assertSoftDeleted('teams', [
        'id' => $team->id,
    ]);
});

test('team deletion requires name confirmation', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user);

    Livewire::test('pages::teams.settings', ['team' => $team])
        ->set('deleteForm.confirmName', 'Wrong Name')
        ->call('deleteTeam')
        ->assertHasErrors(['deleteForm.confirmName']);

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'deleted_at' => null,
    ]);
});

test('deleting current team switches to alphabetically first remaining team', function () {
    $user = User::factory()->create(['name' => 'Mike']);

    $zuluTeam = Team::factory()->create(['name' => 'Zulu Team']);
    $zuluTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $alphaTeam = Team::factory()->create(['name' => 'Alpha Team']);
    $alphaTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $betaTeam = Team::factory()->create(['name' => 'Beta Team']);
    $betaTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $user->update(['current_team_id' => $zuluTeam->id]);

    $this->actingAs($user);

    Livewire::test('pages::teams.settings', ['team' => $zuluTeam])

        ->set('deleteForm.confirmName', $zuluTeam->name)
        ->call('deleteTeam')
        ->assertHasNoErrors();

    $this->assertSoftDeleted('teams', [
        'id' => $zuluTeam->id,
    ]);

    expect($user->fresh()->current_team_id)->toEqual($alphaTeam->id);
});

test('deleting current team falls back to personal team when alphabetically first', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();
    $team = Team::factory()->create(['name' => 'Zulu Team']);
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $user->update(['current_team_id' => $team->id]);

    $this->actingAs($user);

    Livewire::test('pages::teams.settings', ['team' => $team])

        ->set('deleteForm.confirmName', $team->name)
        ->call('deleteTeam')
        ->assertHasNoErrors();

    $this->assertSoftDeleted('teams', [
        'id' => $team->id,
    ]);

    expect($user->fresh()->current_team_id)->toEqual($personalTeam->id);
});

test('deleting non current team leaves current team unchanged', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $user->update(['current_team_id' => $personalTeam->id]);

    $this->actingAs($user);

    Livewire::test('pages::teams.settings', ['team' => $team])

        ->set('deleteForm.confirmName', $team->name)
        ->call('deleteTeam')
        ->assertHasNoErrors();

    $this->assertSoftDeleted('teams', [
        'id' => $team->id,
    ]);

    expect($user->fresh()->current_team_id)->toEqual($personalTeam->id);
});

test('deleting team switches other affected users to their personal team', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $owner->update(['current_team_id' => $team->id]);
    $member->update(['current_team_id' => $team->id]);

    $this->actingAs($owner);

    Livewire::test('pages::teams.settings', ['team' => $team])

        ->set('deleteForm.confirmName', $team->name)
        ->call('deleteTeam')
        ->assertHasNoErrors();

    expect($member->fresh()->current_team_id)->toEqual($member->personalTeam()->id);
});

test('personal teams cannot be deleted', function () {
    $user = User::factory()->create();

    $personalTeam = $user->personalTeam();

    $this->actingAs($user);

    Livewire::test('pages::teams.settings', ['team' => $personalTeam])

        ->set('deleteForm.confirmName', $personalTeam->name)
        ->call('deleteTeam')
        ->assertForbidden();

    $this->assertDatabaseHas('teams', [
        'id' => $personalTeam->id,
        'deleted_at' => null,
    ]);
});

test('teams cannot be deleted by non owners', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($member);

    Livewire::test('pages::teams.settings', ['team' => $team])

        ->set('deleteForm.confirmName', $team->name)
        ->call('deleteTeam')
        ->assertForbidden();
});

test('guests cannot access teams', function () {
    $response = $this->get(route('teams.switch'));

    $response->assertRedirect(route('login'));
});

test('team show page shows team name form for owners', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['name' => 'My Team']);

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user)
        ->get(route('teams.settings', $team))
        ->assertOk()
        ->assertSee('team-name-input')
        ->assertSee('team-save-button')
        ->assertSee($team->name);
});

test('team show page shows delete form directly for non-personal teams', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Deletable']);

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user)
        ->get(route('teams.settings', $team))
        ->assertOk()
        ->assertSee('delete-team-name')
        ->assertSee('Delete team');
});

test('toUserTeams includes member count', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $secondMember = User::factory()->create();
    $team->members()->attach($secondMember, ['role' => TeamRole::Member->value]);

    $userTeam = $user->toUserTeams(includeCurrent: true)->first(
        fn ($t) => $t->id === $team->id,
    );

    expect($userTeam)->not->toBeNull();
    expect($userTeam->memberCount)->toBe(2);
});

test('toUserTeams includes current team with isCurrent flag when requested', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();

    $userTeam = $user->toUserTeams(includeCurrent: true)->first(
        fn ($t) => $t->id === $personalTeam->id,
    );

    expect($userTeam)->not->toBeNull();
    expect($userTeam->isCurrent)->toBeTrue();
});

test('toUserTeams excludes current team when not requested', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();

    $userTeam = $user->toUserTeams(includeCurrent: false)->first(
        fn ($t) => $t->id === $personalTeam->id,
    );

    expect($userTeam)->toBeNull();
});

test('switching to another team updates current team', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();
    $team = Team::factory()->create(['name' => 'Other Team']);
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this->actingAs($user);

    Livewire::test('pages::teams.switch')
        ->set('selectedTeam', $team->slug)
        ->assertHasNoErrors();

    expect($user->fresh()->current_team_id)->toEqual($team->id);
});

test('switching to another team redirects to dashboard', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Other Team']);
    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this->actingAs($user);

    Livewire::test('pages::teams.switch')
        ->set('selectedTeam', $team->slug)
        ->assertRedirect(route('dashboard', ['current_team' => $team->slug]));
});

test('switching to a team the user does not belong to is forbidden', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Forbidden Team']);

    $this->actingAs($user);

    Livewire::test('pages::teams.switch')
        ->call('switchTeam', $team->slug)
        ->assertForbidden();
});

test('selected team defaults to current team on mount', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();

    $this->actingAs($user);

    Livewire::test('pages::teams.switch')
        ->assertSet('selectedTeam', $personalTeam->slug);
});

test('creating a team from switch page redirects to dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::teams.switch')
        ->set('name', 'New Team')
        ->call('createTeam')
        ->assertHasNoErrors();

    $team = Team::where('name', 'New Team')->first();
    test()->assertNotNull($team);
});

test('creating a team from switch page switches to the new team', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::teams.switch')
        ->set('name', 'Switch Test Team')
        ->call('createTeam')
        ->assertHasNoErrors();

    $team = Team::where('name', 'Switch Test Team')->first();
    expect($user->fresh()->current_team_id)->toEqual($team->id);
});

test('creating a team from switch page with invalid name shows errors', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::teams.switch')
        ->set('name', '')
        ->call('createTeam')
        ->assertHasErrors(['name']);
});
