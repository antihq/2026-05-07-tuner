<?php

use App\Support\UserTeam;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Teams')] class extends Component {

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function teams()
    {
        return Auth::user()
            ->teams()
            ->withCount('members')
            ->orderBy($this->sortField, $this->sortDirection)
            ->get()
            ->map(fn ($team) => with(Auth::user()->teamRole($team), fn ($role) => new UserTeam(
                id: $team->id,
                name: $team->name,
                slug: $team->slug,
                role: $role?->value,
                roleLabel: $role?->label(),
                isCurrent: Auth::user()->isCurrentTeam($team),
                memberCount: $team->members_count,
            )));
    }
}; ?>

<section class="w-full">
    <div class="flex items-end justify-between gap-4">
        <flux:heading size="xl" level="1">Teams</flux:heading>
        <flux:button variant="primary" :href="route('teams.create')" wire:navigate data-test="teams-new-team-button">
            New team
        </flux:button>
    </div>

    <div class="mt-8">
        <flux:table bleed>
            <flux:table.columns>
                <flux:table.column
                    sortable
                    :sorted="$sortField === 'name'"
                    :direction="$sortField === 'name' ? $sortDirection : null"
                    wire:click="sortBy('name')"
                >
                    Name
                </flux:table.column>
                <flux:table.column
                    sortable
                    :sorted="$sortField === 'members_count'"
                    :direction="$sortField === 'members_count' ? $sortDirection : null"
                    wire:click="sortBy('members_count')"
                >
                    Members
                </flux:table.column>
                <flux:table.column>Your Role</flux:table.column>
                <flux:table.column>Current</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->teams as $team)
                    <flux:table.row :key="$team->slug" data-test="team-row">
                        <flux:table.cell class="relative">
                            <x-table-row-link :href="route('teams.show', $team->slug)" wire:navigate :first="true" aria-label="{{ $team->name }}" />
                            {{ $team->name }}
                        </flux:table.cell>

                        <flux:table.cell class="relative">
                            <x-table-row-link :href="route('teams.show', $team->slug)" wire:navigate />
                            {{ number_format($team->memberCount ?? 0) }}
                        </flux:table.cell>

                        <flux:table.cell class="relative">
                            <x-table-row-link :href="route('teams.show', $team->slug)" wire:navigate />
                            {{ $team->roleLabel }}
                        </flux:table.cell>

                        <flux:table.cell class="relative">
                            <x-table-row-link :href="route('teams.show', $team->slug)" wire:navigate />
                            @if ($team->isCurrent)
                                <flux:badge color="green" size="sm" inset="top bottom">Active</flux:badge>
                            @endif
                        </flux:table.cell>

                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</section>
