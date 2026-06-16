<?php

use App\Models\Channel;
use App\Models\Event;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app'), Title('Channel')] class extends Component
{
    public int $channelId;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function mount(int $channel): void
    {
        $this->channelId = $channel;
    }

    #[Computed]
    public function channel()
    {
        return Channel::where('id', $this->channelId)
            ->where('team_id', Auth::user()->currentTeam->id)
            ->with('project')
            ->firstOrFail();
    }

    #[Computed]
    public function ingestionUrl(): string
    {
        return $this->channel->signedIngestionUrl();
    }

    #[Computed]
    public function exampleCurl(): string
    {
        $url = $this->ingestionUrl;

        return 'curl -X POST ' . "'" . $url . "'" . " \\\n" .
            "  -H 'Content-Type: application/json' \\\n" .
            "  -d '{\"title\":\"User registered\",\"user_id\":\"123\",\"description\":\"optional context\"}'";
    }

    #[Computed]
    public function exampleLaravel(): string
    {
        $url = $this->ingestionUrl;

        return "Http::post('{$url}', [\n" .
            "    'title' => 'User registered',\n" .
            "    'user_id' => '123',\n" .
            "    'description' => 'optional context',\n" .
            "]);";
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

    public function rotateUrlKey(): void
    {
        $this->channel->rotateUrlKey();

        Flux::toast(variant: 'success', text: 'URL key rotated. Old ingestion URLs are now invalid.');

        unset($this->ingestionUrl);
    }

    public function deleteEvent(int $id): void
    {
        Event::where('id', $id)
            ->where('channel_id', $this->channelId)
            ->delete();

        Flux::toast(variant: 'success', text: 'Event deleted.');

        unset($this->events);
    }

    #[Computed]
    public function events()
    {
        return $this->channel->events()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(50);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">{{ $this->channel->name }}</flux:heading>

    <x-description.list class="mt-2.5">
        <x-description.term>Project</x-description.term>
        <x-description.details>
            <flux:link :href="route('projects.show', $this->channel->project->id)" :accent="false" wire:navigate>{{ $this->channel->project->name }}</flux:link>
        </x-description.details>

        <x-description.term>Events</x-description.term>
        <x-description.details class="tabular-nums">{{ $this->events->total() }} {{ str()->plural('event', $this->events->total()) }}</x-description.details>

        <x-description.term>URL key</x-description.term>
        <x-description.details class="flex items-center gap-4">
            <span class="font-mono">{{ $this->channel->url_key }}</span>
            <flux:button wire:click="rotateUrlKey" wire:confirm="This will invalidate all existing ingestion URLs. Continue?">Rotate</flux:button>
        </x-description.details>
    </x-description.list>

    <flux:heading class="mt-12">Events</flux:heading>

    <flux:table class="mt-4">
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
            <flux:table.column>Description</flux:table.column>
            <flux:table.column>User ID</flux:table.column>
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
                    <flux:table.cell class="max-w-xs truncate relative">
                        <x-table-row-link :href="route('events.show', $event->id)" wire:navigate />
                        {{ $event->description ?? '—' }}
                    </flux:table.cell>
                    <flux:table.cell class="font-mono relative">
                        <x-table-row-link :href="route('events.show', $event->id)" wire:navigate />
                        {{ $event->user_id ?? '—' }}
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

    <flux:heading class="mt-12">Ingestion URL</flux:heading>

    <flux:text class="mt-2">
        POST to this URL from your Laravel projects.
        <x-code>title</x-code> is required. <x-code>user_id</x-code> and <x-code>description</x-code> are optional.
    </flux:text>

    <pre x-ref="ingestionUrl" class="mt-3 overflow-x-auto rounded-md bg-white dark:bg-zinc-900 border border-zinc-950/10 dark:border-white/10 px-3.5 sm:px-3 h-11 sm:h-9 text-base/6 sm:text-sm/6 font-mono break-all flex items-center">{{ $this->ingestionUrl }}</pre>

    <flux:button class="mt-3" @click="navigator.clipboard.writeText($refs.ingestionUrl.textContent).then(() => $flux.toast('Copied to clipboard.'))">Copy</flux:button>

    <flux:heading class="mt-8" level="3">Example (curl)</flux:heading>

    <pre x-ref="exampleCurl" class="mt-3 overflow-x-auto rounded-md bg-white dark:bg-zinc-900 border border-zinc-950/10 dark:border-white/10 px-3.5 sm:px-3 py-1 text-base/6 sm:text-sm/6 font-mono">{{ $this->exampleCurl }}</pre>

    <flux:button class="mt-3" @click="navigator.clipboard.writeText($refs.exampleCurl.textContent).then(() => $flux.toast('Copied to clipboard.'))">Copy</flux:button>

    <flux:heading class="mt-8" level="3">Example (Laravel)</flux:heading>

    <pre x-ref="exampleLaravel" class="overflow-x-auto rounded-md bg-white dark:bg-zinc-900 border border-zinc-950/10 dark:border-white/10 px-3.5 sm:px-3 py-1 text-base/6 sm:text-sm/6 font-mono mt-3">{{ $this->exampleLaravel }}</pre>

    <flux:button class="mt-3" @click="navigator.clipboard.writeText($refs.exampleLaravel.textContent).then(() => $flux.toast('Copied to clipboard.'))">Copy</flux:button>
</section>
