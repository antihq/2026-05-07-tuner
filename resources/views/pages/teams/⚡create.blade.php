<?php

use App\Actions\Teams\CreateTeam;
use App\Enums\TeamPermission;
use App\Rules\TeamName;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Create Team')] class extends Component {
    public string $name = '';

    #[Computed]
    public function ownerPermissions(): array
    {
        return collect(TeamPermission::cases())
            ->map(fn ($p) => $p->value)
            ->values()
            ->all();
    }

    public function createTeam(CreateTeam $createTeam): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', new TeamName],
        ]);

        $team = $createTeam->handle(Auth::user(), $validated['name']);

        $this->reset('name');

        Flux::toast(variant: 'success', text: 'Team created.');

        $this->redirectRoute('teams.show', ['team' => $team->slug], navigate: true);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Create a new team</flux:heading>

    <form wire:submit="createTeam" class="mt-6 space-y-8">
        <div class="max-w-md">
            <flux:input wire:model.live.debounce.300ms="name" label="Team name" type="text" required autofocus data-test="create-team-name" />
        </div>

        <flux:button variant="primary" type="submit" data-test="create-team-submit">
            Create team
        </flux:button>
    </form>

    <flux:heading level="2" class="mt-12">On creation</flux:heading>

    <flux:separator class="mt-2" />

    <x-description.list>
        <x-description.term>Role assigned</x-description.term>
        <x-description.details>
            You are assigned the Owner role with all permissions:
            @foreach($this->ownerPermissions as $permission)
                <x-code>{{ $permission }}</x-code>{{ $loop->last ? '' : ',' }}
            @endforeach
        </x-description.details>

        <x-description.term>Active team</x-description.term>
        <x-description.details>This team becomes your active team across the application.</x-description.details>
    </x-description.list>
</section>
