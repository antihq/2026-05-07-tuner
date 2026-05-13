<?php

use App\Models\Team;
use App\Support\UserTeam;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public string $selectedTeam = '';

    public function mount(): void
    {
        $this->selectedTeam = Auth::user()->currentTeam?->slug ?? '';
    }

    /**
     * @return Collection<int, UserTeam>
     */
    public function teams(): Collection
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

        $currentTeamSlug = $user->currentTeam?->slug;

        $user->switchTeam($team);

        if (! request()->header('Referer')) {
            $this->redirectRoute('dashboard', ['current_team' => $team->slug], navigate: true);

            return;
        }

        if (! $currentTeamSlug) {
            $this->redirect(request()->header('Referer'), navigate: true);

            return;
        }

        $redirectTo = $this->replaceCurrentTeamInReferer(
            request()->header('Referer'),
            $currentTeamSlug,
            $team->slug,
        );

        $this->redirect($redirectTo ?? request()->header('Referer'), navigate: true);
    }

    protected function replaceCurrentTeamInReferer(string $referer, string $currentTeamSlug, string $newTeamSlug): ?string
    {
        $redirectTo = preg_replace(
            '#/'.preg_quote($currentTeamSlug, '#').'(?=/|\?|$)#',
            '/'.$newTeamSlug,
            $referer,
            1,
        );

        return preg_replace(
            '#([?&]current_team=)'.preg_quote($currentTeamSlug, '#').'(?=&|$)#',
            '$1'.$newTeamSlug,
            $redirectTo ?? $referer,
            1,
        );
    }
}; ?>

<flux:dropdown data-test="team-switcher">
    <button class="h-11 sm:h-9 relative flex items-center gap-3 rounded-lg w-full px-2 py-0 text-start font-medium text-zinc-950 dark:text-white hover:text-zinc-950 dark:hover:text-white dark:hover:bg-white/5 hover:bg-zinc-950/5">
        <flux:avatar src="https://avatars.laravel.cloud/team-{{ Auth::user()->currentTeam?->id }}" class="size-7! sm:size-6!" circle />
        <span class="flex-1 text-base/6 sm:text-sm/5 truncate">{{ Auth::user()->currentTeam?->name }}</span>
        <flux:icon icon="chevron-down" variant="micro" class="size-5 sm:size-4 text-zinc-500 dark:text-zinc-400" />
    </button>

    <flux:menu class="min-w-80 lg:min-w-64">
        <flux:menu.item href="{{ route('teams.show', ['team' => Auth::user()->currentTeam?->slug]) }}" wire:navigate>
            Details
        </flux:menu.item>
        <flux:menu.item href="{{ route('teams.members', ['team' => Auth::user()->currentTeam?->slug]) }}" wire:navigate>
            Members
        </flux:menu.item>
        <flux:menu.item href="{{ route('teams.invitations', ['team' => Auth::user()->currentTeam?->slug]) }}" wire:navigate>
            Invitations
        </flux:menu.item>

        <flux:menu.separator />

        <flux:menu.radio.group wire:model.live="selectedTeam">
            @foreach ($this->teams() as $team)
                <flux:menu.radio value="{{ $team->slug }}">{{ $team->name }}</flux:menu.radio>
            @endforeach
        </flux:menu.radio.group>

        <flux:menu.separator />

        <flux:menu.item href="{{ route('teams.create') }}">
            New team
        </flux:menu.item>
    </flux:menu>
</flux:dropdown>
