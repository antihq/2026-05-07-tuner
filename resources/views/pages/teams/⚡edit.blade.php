<?php

use App\Models\Team;
use App\Rules\TeamName;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component
{
    public Team $teamModel;

    public string $teamName = '';

    public function mount(Team $team): void
    {
        $this->teamModel = $team;
        $this->teamName = $team->name;
    }

    public function updateTeam(): void
    {
        Gate::authorize('update', $this->teamModel);

        $validated = $this->validate([
            'teamName' => ['required', 'string', 'max:255', new TeamName],
        ]);

        $team = DB::transaction(function () use ($validated) {
            $team = Team::whereKey($this->teamModel->id)->lockForUpdate()->firstOrFail();

            $team->update(['name' => $validated['teamName']]);

            return $team;
        });

        $this->teamModel = $team;

        Flux::toast(variant: 'success', text: 'Team updated.');

        $this->redirectRoute('teams.show', ['team' => $this->teamModel->fresh()->slug], navigate: true);
    }

    public function render()
    {
        return $this->view()->title('Edit team — ' . $this->teamModel->name);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Edit team</flux:heading>

    <form wire:submit="updateTeam" class="mt-6 space-y-8 max-w-xl">
        <flux:field>
            <flux:label>Team name</flux:label>
            <flux:input wire:model="teamName" type="text" required data-test="team-name-input" />
            <flux:error name="teamName" />
        </flux:field>

        <flux:button variant="primary" type="submit" data-test="team-save-button">
            Save
        </flux:button>
    </form>
</section>
