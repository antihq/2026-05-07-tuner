<?php

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Create Project')] class extends Component
{
    public string $name = '';

    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $project = Auth::user()->currentTeam->projects()->create($validated);

        Flux::toast(variant: 'success', text: 'Project created.');

        $this->redirectRoute('projects.show', ['project' => $project->id], navigate: true);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Create a new project</flux:heading>

    <form wire:submit="create" class="mt-6 space-y-8">
        <div class="max-w-md">
            <flux:input wire:model="name" label="Project name" type="text" required autofocus />
            <flux:error name="name" />
        </div>

        <flux:button variant="primary" type="submit">Create project</flux:button>
    </form>
</section>
