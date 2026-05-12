<?php

use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Events')] class extends Component
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

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
    public function events()
    {
        return Auth::user()->currentTeam->events()
            ->with('channel.project')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(50);
    }
}; ?>

<section class="w-full">
    <div class="flex items-end justify-between gap-4">
        <flux:heading size="xl" level="1">Events</flux:heading>
    </div>

    <div class="mt-8">
        <flux:table>
            <flux:table.columns>
                <flux:table.column
                sortable
                :sorted="$sortField === 'created_at'"
                :direction="$sortField === 'created_at' ? $sortDirection : null"
                wire:click="sortBy('created_at')"
                sticky
                class="bg-white dark:bg-zinc-900"
            >Time</flux:table.column>
            <flux:table.column
                sortable
                :sorted="$sortField === 'title'"
                :direction="$sortField === 'title' ? $sortDirection : null"
                wire:click="sortBy('title')"
            >Title</flux:table.column>
            <flux:table.column>Project</flux:table.column>
            <flux:table.column>Channel</flux:table.column>
            <flux:table.column>User ID</flux:table.column>
            <flux:table.column>Description</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->events as $event)
                <flux:table.row>
                    <flux:table.cell sticky class="bg-white group-hover:bg-zinc-50 dark:bg-zinc-900 dark:group-hover:bg-zinc-800 tabular-nums whitespace-nowrap relative">
                        <x-table-row-link :href="route('events.show', $event->id)" wire:navigate :first="true" aria-label="View {{ $event->title }}" />
                        {{ $event->created_at->format('Y-m-d H:i:s') }}
                    </flux:table.cell>
                    <flux:table.cell class="relative">
                        <x-table-row-link :href="route('events.show', $event->id)" wire:navigate />
                        {{ $event->title }}
                    </flux:table.cell>
                    <flux:table.cell class="relative">
                        <x-table-row-link :href="route('events.show', $event->id)" wire:navigate />
                        {{ $event->channel->project->name }}
                    </flux:table.cell>
                    <flux:table.cell class="relative">
                        <x-table-row-link :href="route('events.show', $event->id)" wire:navigate />
                        {{ $event->channel->name }}
                    </flux:table.cell>
                    <flux:table.cell class="font-mono relative">
                        <x-table-row-link :href="route('events.show', $event->id)" wire:navigate />
                        {{ $event->user_id ?? '—' }}
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-600 dark:text-zinc-400 max-w-xs truncate relative">
                        <x-table-row-link :href="route('events.show', $event->id)" wire:navigate />
                        {{ $event->description ?? '—' }}
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    @if ($this->events->hasPages())
        <div class="mt-4">
            {{ $this->events->links() }}
        </div>
    @endif
    </div>
</section>
