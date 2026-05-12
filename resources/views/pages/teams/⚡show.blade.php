<?php

use App\Models\Team;
use App\Support\TeamPermissions;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Team $teamModel;

    public function mount(Team $team): void
    {
        $this->teamModel = $team;
    }

    #[Computed]
    public function members(): int
    {
        return $this->teamModel->members()->count();
    }

    #[Computed]
    public function invitations(): int
    {
        return $this->teamModel->invitations()->whereNull('accepted_at')->count();
    }

    #[Computed]
    public function ownerName(): ?string
    {
        return $this->teamModel->owner()?->name;
    }

    #[Computed]
    public function permissions(): TeamPermissions
    {
        return Auth::user()->toTeamPermissions($this->teamModel);
    }

    public function render()
    {
        return $this->view()->title($this->teamModel->name);
    }
}; ?>

<section class="w-full">
    <div class="flex items-end justify-between gap-4">
        <flux:heading size="xl" level="1">{{ $teamModel->name }}</flux:heading>
        @if ($this->permissions->canUpdateTeam)
            <flux:button variant="primary" :href="route('teams.edit', $teamModel)" wire:navigate data-test="team-edit-button">
                Edit
            </flux:button>
        @endif
    </div>

    <x-description.list class="mt-2.5">
        <x-description.term>Owner</x-description.term>
        <x-description.details>{{ $this->ownerName }}</x-description.details>

        <x-description.term>Members</x-description.term>
        <x-description.details>
            <flux:link :accent="false" :href="route('teams.members', $teamModel)" wire:navigate>
                {{ $this->members }} {{ str()->plural('member', $this->members) }}
            </flux:link>
        </x-description.details>

        <x-description.term>Invitations</x-description.term>
        <x-description.details>
            <flux:link :accent="false" :href="route('teams.invitations', $teamModel)" wire:navigate>
                {{ $this->invitations }} {{ str()->plural('invitation', $this->invitations) }}
            </flux:link>
        </x-description.details>
    </x-description.list>

    @if ($this->permissions->canDeleteTeam && ! $teamModel->is_personal)
        <flux:separator class="mt-8" />

        <div class="mt-4">
            <flux:button variant="danger" :href="route('teams.delete', $teamModel)" wire:navigate data-test="team-delete-button">
                Delete team
            </flux:button>
        </div>
    @endif
</section>
