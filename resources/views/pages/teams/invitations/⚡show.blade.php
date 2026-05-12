<?php

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Support\TeamPermissions;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Team $teamModel;

    public TeamInvitation $invitationModel;

    public function mount(Team $team, TeamInvitation $invitation): void
    {
        abort_unless($invitation->team_id === $team->id, 404);

        $this->teamModel = $team;
        $this->invitationModel = $invitation;
    }

    public function cancelInvitation(): void
    {
        Gate::authorize('cancelInvitation', $this->teamModel);

        $this->invitationModel->delete();

        Flux::toast(variant: 'success', text: 'Invitation cancelled.');

        $this->redirectRoute('teams.invitations', ['team' => $this->teamModel->slug], navigate: true);
    }

    #[Computed]
    public function permissions(): TeamPermissions
    {
        return Auth::user()->toTeamPermissions($this->teamModel);
    }

    public function render()
    {
        return $this->view()->title($this->invitationModel->email . ' — ' . $this->teamModel->name);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">{{ $invitationModel->email }}</flux:heading>

    <x-description.list class="mt-2.5">
        <x-description.term>Role</x-description.term>
        <x-description.details>
            <flux:badge color="zinc" size="sm" inset="top bottom">{{ $invitationModel->role->label() }}</flux:badge>
        </x-description.details>

        <x-description.term>Sent</x-description.term>
        <x-description.details class="tabular-nums">{{ $invitationModel->created_at->format('Y-m-d H:i') }}</x-description.details>

        <x-description.term>Expires</x-description.term>
        <x-description.details class="tabular-nums">{{ $invitationModel->expires_at?->format('Y-m-d H:i') ?? '—' }}</x-description.details>

        <x-description.term>Status</x-description.term>
        <x-description.details>
            @if ($invitationModel->isExpired())
                <flux:badge color="red" size="sm" inset="top bottom">Expired</flux:badge>
            @elseif ($invitationModel->isPending())
                <flux:badge color="amber" size="sm" inset="top bottom">Pending</flux:badge>
            @else
                <flux:badge color="green" size="sm" inset="top bottom">Accepted</flux:badge>
            @endif
        </x-description.details>
    </x-description.list>

    @if ($invitationModel->isPending() && $this->permissions->canCancelInvitation)
        <flux:separator class="mt-8" />

        <div class="mt-4">
            <flux:button
                variant="danger"
                wire:click="cancelInvitation"
                data-test="invitation-cancel-button"
            >
                Cancel invitation
            </flux:button>
        </div>
    @endif
</section>
