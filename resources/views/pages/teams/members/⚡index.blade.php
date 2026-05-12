<?php

use App\Models\Team;
use Livewire\Component;

new class extends Component
{
    public Team $teamModel;

    public array $members = [];

    public function mount(Team $team): void
    {
        $this->teamModel = $team;

        $this->populateMembers();
    }

    private function populateMembers(): void
    {
        $this->members = $this->teamModel->members()->get()->map(fn ($member) => [
            'id' => $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'role_label' => $member->pivot->role?->label(),
        ])->toArray();
    }

    public function render()
    {
        return $this->view()->title('Members — ' . $this->teamModel->name);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Members</flux:heading>

    <flux:table class="mt-6">
        <flux:table.columns>
            <flux:table.column sticky class="bg-white dark:bg-zinc-900">Name</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Role</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($members as $member)
                <flux:table.row :key="$member['id']" data-test="member-row">
                    <flux:table.cell variant="strong" sticky class="bg-white group-hover:bg-zinc-50 dark:bg-zinc-900 dark:group-hover:bg-zinc-800 relative">
                        <x-table-row-link :href="route('teams.members.show', ['team' => $teamModel, 'user' => $member['id']])" wire:navigate :first="true" aria-label="View {{ $member['name'] }}" />
                        {{ $member['name'] }}
                    </flux:table.cell>

                    <flux:table.cell class="relative">
                        <x-table-row-link :href="route('teams.members.show', ['team' => $teamModel, 'user' => $member['id']])" wire:navigate />
                        {{ $member['email'] }}
                    </flux:table.cell>

                    <flux:table.cell class="relative">
                        <x-table-row-link :href="route('teams.members.show', ['team' => $teamModel, 'user' => $member['id']])" wire:navigate />
                        <flux:badge color="zinc" size="sm" inset="top bottom">{{ $member['role_label'] }}</flux:badge>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</section>
