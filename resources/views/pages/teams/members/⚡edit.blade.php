<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use App\Support\TeamPermissions;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Team $teamModel;

    public User $userModel;

    public array $availableRoles = [];

    public string $selectedRole = '';

    public function mount(Team $team, User $user): void
    {
        $this->teamModel = $team;
        $this->userModel = $user;

        $this->availableRoles = TeamRole::assignable();

        $membership = $this->teamModel->memberships()->where('user_id', $user->id)->firstOrFail();

        $this->selectedRole = $membership->role->value;
    }

    public function updateRole(): void
    {
        Gate::authorize('updateMember', $this->teamModel);

        $validated = Validator::make(['role' => $this->selectedRole], [
            'role' => ['required', 'string', Rule::enum(TeamRole::class)],
        ])->validate();

        $this->teamModel->memberships()
            ->where('user_id', $this->userModel->id)
            ->firstOrFail()
            ->update(['role' => TeamRole::from($validated['role'])]);

        Flux::toast(variant: 'success', text: 'Member role updated.');

        $this->redirectRoute('teams.members.show', ['team' => $this->teamModel->slug, 'user' => $this->userModel->id], navigate: true);
    }

    #[Computed]
    public function permissions(): TeamPermissions
    {
        return Auth::user()->toTeamPermissions($this->teamModel);
    }

    public function render()
    {
        return $this->view()->title('Edit member — ' . $this->userModel->name);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Edit member</flux:heading>

    <form wire:submit="updateRole" class="mt-6 space-y-8">
        <flux:field>
            <flux:label>Email address</flux:label>
            <flux:input readonly variant="filled" :value="$userModel->email" class="max-w-lg" />
        </flux:field>

        <flux:field>
            <flux:label>Role</flux:label>
            <flux:select wire:model="selectedRole" class="max-w-lg" data-test="member-role-select">
                @foreach ($availableRoles as $role)
                    <flux:select.option value="{{ $role['value'] }}">{{ $role['label'] }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:button variant="primary" type="submit" data-test="member-role-save">
            Save
        </flux:button>
    </form>
</section>
