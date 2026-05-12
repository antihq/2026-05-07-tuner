<?php

use App\Models\Team;
use App\Models\User;
use App\Support\TeamPermissions;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Team $teamModel;

    public User $userModel;

    public function mount(Team $team, User $user): void
    {
        $this->teamModel = $team;
        $this->userModel = $user;
    }

    public function removeMember(): void
    {
        Gate::authorize('removeMember', $this->teamModel);

        $this->teamModel->memberships()
            ->where('user_id', $this->userModel->id)
            ->delete();

        if ($this->userModel->isCurrentTeam($this->teamModel)) {
            $this->userModel->switchTeam($this->userModel->personalTeam());
        }

        Flux::toast(variant: 'success', text: 'Member removed.');

        $this->redirectRoute('teams.members', ['team' => $this->teamModel->slug], navigate: true);
    }

    #[Computed]
    public function membership()
    {
        return $this->teamModel->memberships()->where('user_id', $this->userModel->id)->firstOrFail();
    }

    #[Computed]
    public function permissions(): TeamPermissions
    {
        return Auth::user()->toTeamPermissions($this->teamModel);
    }

    public function render()
    {
        return $this->view()->title($this->userModel->name . ' — ' . $this->teamModel->name);
    }
}; ?>

<section class="w-full">
    <div class="flex items-end justify-between gap-4">
        <flux:heading size="xl" level="1">{{ $userModel->name }}</flux:heading>
        @if ($this->membership->role->value !== 'owner' && $this->permissions->canUpdateMember)
            <flux:button variant="primary" :href="route('teams.members.edit', ['team' => $teamModel, 'user' => $userModel])" wire:navigate data-test="member-edit-button">
                Edit
            </flux:button>
        @endif
    </div>

    <x-description.list class="mt-2.5">
        <x-description.term>Email</x-description.term>
        <x-description.details>{{ $userModel->email }}</x-description.details>

        <x-description.term>Role</x-description.term>
        <x-description.details>
            <flux:badge color="zinc" size="sm" inset="top bottom">{{ $this->membership->role->label() }}</flux:badge>
        </x-description.details>

        <x-description.term>Joined</x-description.term>
        <x-description.details class="tabular-nums">{{ $this->membership->created_at->format('Y-m-d H:i') }}</x-description.details>
    </x-description.list>

    @if ($this->membership->role->value !== 'owner' && $this->permissions->canRemoveMember)
        <flux:separator class="mt-8" />

        <div class="mt-4">
            <flux:button
                variant="danger"
                wire:click="removeMember"
                data-test="member-remove-button"
            >
                Remove member
            </flux:button>
        </div>
    @endif
</section>
