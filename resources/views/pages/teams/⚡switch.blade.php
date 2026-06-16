<?php

use App\Actions\Teams\CreateTeam;
use App\Models\Team;
use App\Rules\TeamName;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.account'), Title('Switch Team')] class extends Component {
    public string $selectedTeam = '';

    public string $name = '';

    public function mount(): void
    {
        $this->selectedTeam = Auth::user()->currentTeam?->slug ?? '';
    }

    public function teams()
    {
        return Auth::user()->toUserTeams(includeCurrent: true);
    }

    public function updatedSelectedTeam(string $slug): void
    {
        $this->switchTeam($slug);
    }

    public function switchTeam(string $slug): void
    {
        $user = Auth::user();

        abort_unless(
            $user->belongsToTeam($team = Team::where('slug', $slug)->firstOrFail()),
            403
        );

        $user->switchTeam($team);

        Flux::toast(variant: 'success', text: 'Switched to ' . $team->name);

        $this->redirectRoute('dashboard', ['current_team' => $team->slug], navigate: true);
    }

    public function createTeam(CreateTeam $createTeam): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', new TeamName],
        ]);

        $team = $createTeam->handle(Auth::user(), $validated['name']);

        $this->reset('name');

        Flux::toast(variant: 'success', text: 'Team created.');

        $this->redirectRoute('dashboard', ['current_team' => $team->slug], navigate: true);
    }
}; ?>

<section class="max-w-2xl">
    <flux:heading level="1">switch team</flux:heading>

    <div class="flex items-center gap-2 mt-2">
        <flux:heading class="lowercase" level="2">Your teams</flux:heading>
        <span class="text-zinc-500 dark:text-zinc-400 text-sm/5 sm:text-xs/5">{{ $this->teams()->count() }}</span>
    </div>

    <flux:field class="mt-2 max-w-sm">
        <flux:radio.group wire:model.live="selectedTeam">
            @foreach ($this->teams() as $team)
                <flux:radio value="{{ $team->slug }}" label="{{ $team->name }}" />
            @endforeach
        </flux:radio.group>
    </flux:field>

    <form wire:submit="createTeam" class="mt-8">
        <flux:fieldset>
            <flux:legend class="lowercase" level="2">Create team</flux:legend>

            <flux:field class="max-w-sm">
                <flux:label class="lowercase">Team name</flux:label>
                <flux:input wire:model.live.debounce.300ms="name" type="text" required />
                <flux:error name="name" />
            </flux:field>
        </flux:fieldset>

        <div class="mt-4">
            <flux:button type="submit" variant="primary" color="lime" class="lowercase">Create team</flux:button>
        </div>
    </form>
</section>
