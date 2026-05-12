<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

test('teams index page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('teams.index'));

    $response->assertOk();
});

test('teams can be created', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::teams.create')
        ->set('name', 'Test Team')
        ->call('createTeam')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('teams', [
        'name' => 'Test Team',
        'is_personal' => false,
    ]);
});

test('team slug uses next available suffix', function () {
    $user = User::factory()->create();

    Team::factory()->create(['name' => 'Acme', 'slug' => 'acme']);
    Team::factory()->create(['name' => 'Acme One', 'slug' => 'acme-1']);
    Team::factory()->create(['name' => 'Acme Ten', 'slug' => 'acme-10']);

    $this->actingAs($user);

    Livewire::test('pages::teams.create')
        ->set('name', 'Acme')
        ->call('createTeam')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('teams', [
        'name' => 'Acme',
        'slug' => 'acme-11',
    ]);
});

test('team show page can be rendered', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $response = $this
        ->actingAs($user)
        ->get(route('teams.show', $team));

    $response->assertOk();
    $response->assertSee($user->name);
});

test('teams can be updated by owners', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Original Name']);

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user);

    Livewire::test('pages::teams.edit', ['team' => $team])
        ->set('teamName', 'Updated Name')
        ->call('updateTeam')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'Updated Name',
    ]);
});

test('team update redirects to show page', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Original Name']);

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user);

    Livewire::test('pages::teams.edit', ['team' => $team])
        ->set('teamName', 'Updated Name')
        ->call('updateTeam')
        ->assertHasNoErrors()
        ->assertRedirect(route('teams.show', Team::where('name', 'Updated Name')->first()->slug));
});

test('teams cannot be updated by members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($member);

    Livewire::test('pages::teams.edit', ['team' => $team])
        ->set('teamName', 'Updated Name')
        ->call('updateTeam')
        ->assertForbidden();
});

test('teams can be deleted by owners', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user);

    Livewire::test('pages::teams.delete', ['team' => $team])
        ->set('deleteTeamName', $team->name)
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

    Livewire::test('pages::teams.delete', ['team' => $team])
        ->set('deleteTeamName', 'Wrong Name')
        ->call('deleteTeam')
        ->assertHasErrors(['deleteTeamName']);

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

    Livewire::test('pages::teams.delete', ['team' => $zuluTeam])
        ->set('deleteTeamName', $zuluTeam->name)
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

    Livewire::test('pages::teams.delete', ['team' => $team])
        ->set('deleteTeamName', $team->name)
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

    Livewire::test('pages::teams.delete', ['team' => $team])
        ->set('deleteTeamName', $team->name)
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

    Livewire::test('pages::teams.delete', ['team' => $team])
        ->set('deleteTeamName', $team->name)
        ->call('deleteTeam')
        ->assertHasNoErrors();

    expect($member->fresh()->current_team_id)->toEqual($member->personalTeam()->id);
});

test('personal teams cannot be deleted', function () {
    $user = User::factory()->create();

    $personalTeam = $user->personalTeam();

    $this->actingAs($user);

    Livewire::test('pages::teams.delete', ['team' => $personalTeam])
        ->set('deleteTeamName', $personalTeam->name)
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

    Livewire::test('pages::teams.delete', ['team' => $team])
        ->set('deleteTeamName', $team->name)
        ->call('deleteTeam')
        ->assertForbidden();
});

test('guests cannot access teams', function () {
    $response = $this->get(route('teams.index'));

    $response->assertRedirect(route('login'));
});

test('teams create page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('teams.create'));

    $response->assertOk();
});

test('team delete page can be rendered', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $response = $this
        ->actingAs($user)
        ->get(route('teams.delete', $team));

    $response->assertOk();
});

test('team show page shows delete button for non-personal teams', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this->actingAs($user)
        ->get(route('teams.show', $team))
        ->assertOk()
        ->assertSee('Delete team');
});

test('team show page hides delete button for personal teams', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();

    $this->actingAs($user)
        ->get(route('teams.show', $personalTeam))
        ->assertOk()
        ->assertDontSee('Delete team');
});

test('team show page hides edit button for members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $this->actingAs($member)
        ->get(route('teams.show', $team))
        ->assertOk()
        ->assertDontSee('Edit');
});

test('creating a team redirects to show page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::teams.create')
        ->set('name', 'Redirect Test Team')
        ->call('createTeam')
        ->assertHasNoErrors()
        ->assertRedirect(route('teams.show', Team::where('name', 'Redirect Test Team')->first()->slug));
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
