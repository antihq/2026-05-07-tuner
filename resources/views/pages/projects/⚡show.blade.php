<?php

use App\Models\Event;
use App\Models\Project;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Project')] class extends Component
{
    public int $projectId;

    public string $deleteProjectName = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function mount(int $project): void
    {
        $this->projectId = $project;
    }

    #[Computed]
    public function project()
    {
        return Project::where('id', $this->projectId)
            ->where('team_id', Auth::user()->currentTeam->id)
            ->firstOrFail();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function deleteProject(): void
    {
        $validated = $this->validate([
            'deleteProjectName' => ['required', 'string'],
        ]);

        if ($validated['deleteProjectName'] !== $this->project->name) {
            $this->addError('deleteProjectName', 'The project name does not match.');

            return;
        }

        $this->project->delete();

        Flux::toast(variant: 'success', text: 'Project deleted.');

        $this->redirect(route('projects.index'), navigate: true);
    }

    #[Computed]
    public function channels()
    {
        return $this->project->channels()
            ->withCount('events')
            ->withCount(['events as events_last_24h' => fn ($q) => $q->where('events.created_at', '>=', now()->subDay())])
            ->addSelect([
                'last_event_at' => Event::query()
                    ->select('events.created_at')
                    ->whereColumn('events.channel_id', 'channels.id')
                    ->orderByDesc('events.created_at')
                    ->limit(1),
            ])
            ->orderBy($this->sortField, $this->sortDirection)
            ->get()
            ->map(fn ($channel) => [
                'id' => $channel->id,
                'name' => $channel->name,
                'events_count' => $channel->events_count,
                'events_last_24h' => $channel->events_last_24h,
                'last_event_at' => $channel->last_event_at ? Carbon::parse($channel->last_event_at) : null,
                'created_at' => $channel->created_at,
            ]);
    }

    #[Computed]
    public function projectStats()
    {
        $channels = collect($this->channels);

        $lastEvent = Event::whereHas('channel', fn ($q) => $q->where('project_id', $this->project->id))
            ->latest()
            ->first();

        return [
            'total_events' => $channels->sum('events_count'),
            'events_last_24h' => $channels->sum('events_last_24h'),
            'last_event_at' => $lastEvent?->created_at,
            'last_event_id' => $lastEvent?->id,
        ];
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">{{ $this->project->name }}</flux:heading>

    <x-description.list class="mt-2.5">
        <x-description.term>Events</x-description.term>
        <x-description.details class="tabular-nums">{{ $this->projectStats['total_events'] }} {{ str()->plural('event', $this->projectStats['total_events']) }}</x-description.details>

        <x-description.term>Events (last 24h)</x-description.term>
        <x-description.details class="tabular-nums">{{ $this->projectStats['events_last_24h'] }} {{ str()->plural('event', $this->projectStats['events_last_24h']) }}</x-description.details>

        <x-description.term>Last event received</x-description.term>
        <x-description.details class="tabular-nums">
            @if ($this->projectStats['last_event_id'])
                <flux:link :accent="false" :href="route('events.show', $this->projectStats['last_event_id'])" wire:navigate>{{ $this->projectStats['last_event_at']->format('Y-m-d H:i:s') }}</flux:link>
            @else
                —
            @endif
        </x-description.details>
    </x-description.list>

    <flux:heading size="lg" level="2" class="mt-12">Channels</flux:heading>

    <flux:table class="mt-4">
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
            @foreach ($this->channels as $channel)
                <flux:table.row>
                    <flux:table.cell variant="strong" sticky class="bg-white group-hover:bg-zinc-50 dark:bg-zinc-900 dark:group-hover:bg-zinc-800 relative">
                        <x-table-row-link :href="route('channels.show', $channel['id'])" wire:navigate :first="true" aria-label="View {{ $channel['name'] }}" />
                        {{ $channel['name'] }}
                    </flux:table.cell>
                    <flux:table.cell class="tabular-nums relative">
                        <x-table-row-link :href="route('channels.show', $channel['id'])" wire:navigate />
                        {{ $channel['events_count'] }}
                    </flux:table.cell>
                    <flux:table.cell class="tabular-nums relative">
                        <x-table-row-link :href="route('channels.show', $channel['id'])" wire:navigate />
                        {{ $channel['events_last_24h'] }}
                    </flux:table.cell>
                    <flux:table.cell class="tabular-nums whitespace-nowrap relative">
                        <x-table-row-link :href="route('channels.show', $channel['id'])" wire:navigate />
                        {{ $channel['last_event_at']?->format('Y-m-d H:i:s') ?? '—' }}
                    </flux:table.cell>
                    <flux:table.cell class="tabular-nums whitespace-nowrap relative">
                        <x-table-row-link :href="route('channels.show', $channel['id'])" wire:navigate />
                        {{ $channel['created_at']->format('Y-m-d H:i') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:separator class="mt-12" />

    <flux:heading class="mt-12">Delete project</flux:heading>

    <form wire:submit="deleteProject" class="mt-4 space-y-5 max-w-xl">
        <flux:field>
            <flux:label>Type "{{ $this->project->name }}" to confirm</flux:label>
            <flux:input wire:model="deleteProjectName" type="text" required />
            <flux:error name="deleteProjectName" />
        </flux:field>

        <flux:button variant="danger" type="submit">
            Delete project
        </flux:button>
    </form>
</section>
