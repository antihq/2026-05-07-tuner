<?php

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Projects')] class extends Component
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
    public function projects()
    {
        return Auth::user()->currentTeam->projects()
            ->withCount('channels')
            ->withCount('events')
            ->withCount(['events as events_last_24h' => fn ($q) => $q->where('events.created_at', '>=', now()->subDay())])
            ->addSelect([
                'last_event_at' => Event::query()
                    ->select('events.created_at')
                    ->join('channels', 'events.channel_id', '=', 'channels.id')
                    ->whereColumn('channels.project_id', 'projects.id')
                    ->orderByDesc('events.created_at')
                    ->limit(1),
            ])
            ->orderBy($this->sortField, $this->sortDirection)
            ->get()
            ->map(fn ($project) => [
                'id' => $project->id,
                'name' => $project->name,
                'channels_count' => $project->channels_count,
                'events_count' => $project->events_count,
                'events_last_24h' => $project->events_last_24h,
                'last_event_at' => $project->last_event_at ? Carbon::parse($project->last_event_at) : null,
                'created_at' => $project->created_at,
            ]);
    }
}; ?>

<section class="w-full">
    <div class="flex items-end justify-between gap-4">
        <flux:heading size="xl" level="1">Projects</flux:heading>
        <flux:button variant="primary" :href="route('projects.create')" wire:navigate>
            New project
        </flux:button>
    </div>

    <div class="mt-8">
        <flux:table>
            <flux:table.columns>
                <flux:table.column
                sortable
                :sorted="$sortField === 'name'"
                :direction="$sortField === 'name' ? $sortDirection : null"
                wire:click="sortBy('name')"
                sticky
                class="bg-white dark:bg-zinc-900"
            >Name</flux:table.column>
            <flux:table.column
                sortable
                :sorted="$sortField === 'channels_count'"
                :direction="$sortField === 'channels_count' ? $sortDirection : null"
                wire:click="sortBy('channels_count')"
            >Channels</flux:table.column>
            <flux:table.column
                sortable
                :sorted="$sortField === 'events_count'"
                :direction="$sortField === 'events_count' ? $sortDirection : null"
                wire:click="sortBy('events_count')"
            >Events</flux:table.column>
            <flux:table.column
                sortable
                :sorted="$sortField === 'events_last_24h'"
                :direction="$sortField === 'events_last_24h' ? $sortDirection : null"
                wire:click="sortBy('events_last_24h')"
            >Events (24h)</flux:table.column>
            <flux:table.column
                sortable
                :sorted="$sortField === 'last_event_at'"
                :direction="$sortField === 'last_event_at' ? $sortDirection : null"
                wire:click="sortBy('last_event_at')"
            >Last Event</flux:table.column>
            <flux:table.column
                sortable
                :sorted="$sortField === 'created_at'"
                :direction="$sortField === 'created_at' ? $sortDirection : null"
                wire:click="sortBy('created_at')"
            >Created</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->projects as $project)
                <flux:table.row>
                    <flux:table.cell variant="strong" sticky class="bg-white group-hover:bg-zinc-50 dark:bg-zinc-900 dark:group-hover:bg-zinc-800 relative">
                        <x-table-row-link :href="route('projects.show', $project['id'])" wire:navigate :first="true" aria-label="View {{ $project['name'] }}" />
                        {{ $project['name'] }}
                    </flux:table.cell>
                    <flux:table.cell class="tabular-nums relative">
                        <x-table-row-link :href="route('projects.show', $project['id'])" wire:navigate />
                        {{ $project['channels_count'] }}
                    </flux:table.cell>
                    <flux:table.cell class="tabular-nums relative">
                        <x-table-row-link :href="route('projects.show', $project['id'])" wire:navigate />
                        {{ $project['events_count'] }}
                    </flux:table.cell>
                    <flux:table.cell class="tabular-nums relative">
                        <x-table-row-link :href="route('projects.show', $project['id'])" wire:navigate />
                        {{ $project['events_last_24h'] }}
                    </flux:table.cell>
                    <flux:table.cell class="tabular-nums whitespace-nowrap relative">
                        <x-table-row-link :href="route('projects.show', $project['id'])" wire:navigate />
                        {{ $project['last_event_at']?->format('Y-m-d H:i:s') ?? '—' }}
                    </flux:table.cell>
                    <flux:table.cell class="tabular-nums whitespace-nowrap relative">
                        <x-table-row-link :href="route('projects.show', $project['id'])" wire:navigate />
                        {{ $project['created_at']->format('Y-m-d H:i') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
    </div>
</section>
