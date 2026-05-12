<?php

use App\Models\Project;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Create Channel')] class extends Component
{
    public int $projectId = 0;

    public string $name = '';

    #[Computed]
    public function projects()
    {
        return Auth::user()->currentTeam->projects()->orderBy('name')->get();
    }

    public function create(): void
    {
        $validated = $this->validate([
            'projectId' => ['required', 'integer', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $project = Project::where('id', $validated['projectId'])
            ->where('team_id', Auth::user()->currentTeam->id)
            ->firstOrFail();

        $channel = $project->channels()->create([
            'name' => $validated['name'],
            'team_id' => Auth::user()->currentTeam->id,
        ]);

        Flux::toast(variant: 'success', text: 'Channel created.');

        $this->redirectRoute('channels.show', ['channel' => $channel->id], navigate: true);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Create a new channel</flux:heading>

    <form wire:submit="create" class="mt-6 space-y-8">
        <div class="max-w-md space-y-5">
            <flux:select wire:model="projectId" label="Project" required>
                <flux:select.option value="">Select a project</flux:select.option>
                @foreach ($this->projects as $project)
                    <flux:select.option value="{{ $project->id }}">{{ $project->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="projectId" />

            <flux:input wire:model="name" label="Channel name" type="text" required autofocus />
            <flux:error name="name" />
        </div>

        <flux:button variant="primary" type="submit">Create channel</flux:button>
    </form>
</section>
