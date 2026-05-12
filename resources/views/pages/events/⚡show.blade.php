<?php

use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Event')] class extends Component
{
    public int $eventId;

    public function mount(int $event): void
    {
        $this->eventId = $event;
    }

    #[Computed]
    public function event()
    {
        return Event::where('id', $this->eventId)
            ->where('team_id', Auth::user()->currentTeam->id)
            ->with('channel.project')
            ->firstOrFail();
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">{{ $this->event->title }}</flux:heading>

    <x-description.list class="mt-4">
        <x-description.term>Project</x-description.term>
        <x-description.details>
            <flux:link :accent="false" :href="route('projects.show', $this->event->channel->project->id)" wire:navigate>{{ $this->event->channel->project->name }}</flux:link>
        </x-description.details>

        <x-description.term>Channel</x-description.term>
        <x-description.details>
            <flux:link :accent="false" :href="route('channels.show', $this->event->channel->id)" wire:navigate>{{ $this->event->channel->name }}</flux:link>
        </x-description.details>

        <x-description.term>User ID</x-description.term>
        <x-description.details class="font-mono">{{ $this->event->user_id ?? '—' }}</x-description.details>

        <x-description.term>Created</x-description.term>
        <x-description.details class="tabular-nums">{{ $this->event->created_at->format('Y-m-d H:i:s') }}</x-description.details>

        @if ($this->event->description)
            <x-description.term>Description</x-description.term>
            <x-description.details>
                {{ $this->event->description }}
            </x-description.details>
        @endif
    </x-description.list>
</section>
