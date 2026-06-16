<?php

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app'), Title('Dashboard')] class extends Component
{
    public string $sortField = 'events_count';

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
    public function projectBreakdown()
    {
        $team = Auth::user()->currentTeam;

        return $team->projects()
            ->withCount(['channels', 'events'])
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
            ]);
    }

    #[Computed]
    public function systemHealth()
    {
        $projects = collect($this->projectBreakdown);

        $lastEvent = Event::whereHas('channel.project', fn ($q) => $q->where('team_id', Auth::user()->currentTeam->id))
            ->with('channel')
            ->latest()
            ->first();

        return [
            'events_last_24h' => $projects->sum('events_last_24h'),
            'last_event_at' => $lastEvent?->created_at,
            'last_event_id' => $lastEvent?->id,
            'last_event_project' => $lastEvent ? $projects->first(fn ($p) => $p['id'] === $lastEvent->channel->project_id) : null,
            'most_active_project' => $projects->sortByDesc('events_last_24h')->first(),
        ];
    }

    #[Computed]
    public function recentEvents()
    {
        return Event::query()
            ->whereHas('channel.project', fn ($q) => $q->where('team_id', Auth::user()->currentTeam->id))
            ->with('channel.project')
            ->orderByDesc('created_at')
            ->limit(25)
            ->get()
            ->groupBy(fn ($event) => $event->created_at->format('Y-m-d D'));
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Dashboard</flux:heading>

    <x-description.list class="mt-4">
        <x-description.term>Events (last 24h)</x-description.term>
        <x-description.details class="tabular-nums">
            @if ($this->systemHealth['most_active_project'])
                <flux:link :accent="false" :href="route('projects.show', $this->systemHealth['most_active_project']['id'])" wire:navigate>
                    {{ $this->systemHealth['events_last_24h'] }} {{ str()->plural('event', $this->systemHealth['events_last_24h']) }}
                </flux:link>
            @else
                {{ $this->systemHealth['events_last_24h'] }} {{ str()->plural('event', $this->systemHealth['events_last_24h']) }}
            @endif
        </x-description.details>

        <x-description.term>Last event received</x-description.term>
        <x-description.details class="tabular-nums">
            @if ($this->systemHealth['last_event_id'])
                <flux:link :accent="false" :href="route('events.show', $this->systemHealth['last_event_id'])" wire:navigate>
                    {{ $this->systemHealth['last_event_at']->format('Y-m-d H:i:s') }}
                </flux:link>
            @else
                —
            @endif
        </x-description.details>
    </x-description.list>

    <flux:heading class="mt-12" level="2">Projects</flux:heading>

    <flux:table class="mt-4">
        <flux:table.columns>
            <flux:table.column
                sortable
                :sorted="$sortField === 'name'"
                :direction="$sortField === 'name' ? $sortDirection : null"
                wire:click="sortBy('name')"
                sticky
                class="bg-white dark:bg-zinc-900"
            >Project</flux:table.column>
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
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->projectBreakdown as $project)
                <flux:table.row>
                    <flux:table.cell sticky class="bg-white group-hover:bg-zinc-50 dark:bg-zinc-900 dark:group-hover:bg-zinc-800 relative">
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
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:heading class="mt-12" level="2">Recent Events</flux:heading>

    @foreach ($this->recentEvents as $date => $events)
        <flux:heading class="mt-4" level="3">{{ $date }}</flux:heading>

        <flux:table class="mt-4">
            <flux:table.columns>
                <flux:table.column sticky class="bg-white dark:bg-zinc-900">Time</flux:table.column>
                <flux:table.column>Project</flux:table.column>
                <flux:table.column>Channel</flux:table.column>
                <flux:table.column>Title</flux:table.column>
                <flux:table.column>User ID</flux:table.column>
                <flux:table.column>Description</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($events as $event)
                    <flux:table.row>
                        <flux:table.cell sticky class="bg-white group-hover:bg-zinc-50 dark:bg-zinc-900 dark:group-hover:bg-zinc-800 tabular-nums whitespace-nowrap relative">
                            <x-table-row-link :href="route('events.show', $event->id)" wire:navigate :first="true" aria-label="View {{ $event->title }}" />
                            {{ $event->created_at->format('H:i:s') }}
                        </flux:table.cell>
                        <flux:table.cell class="relative">
                            <x-table-row-link :href="route('events.show', $event->id)" wire:navigate />
                            {{ $event->channel->project->name }}
                        </flux:table.cell>
                        <flux:table.cell class="relative">
                            <x-table-row-link :href="route('events.show', $event->id)" wire:navigate />
                            {{ $event->channel->name }}
                        </flux:table.cell>
                        <flux:table.cell class="relative">
                            <x-table-row-link :href="route('events.show', $event->id)" wire:navigate />
                            {{ $event->title }}
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
    @endforeach
</section>
