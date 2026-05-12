<?php

use App\Models\Team;
use App\Support\TeamPermissions;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Team $teamModel;

    public array $invitations = [];

    public function mount(Team $team): void
    {
        $this->teamModel = $team;

        $this->populateInvitations();
    }

    private function populateInvitations(): void
    {
        $this->invitations = $this->teamModel->invitations()
            ->whereNull('accepted_at')
            ->get()
            ->map(fn ($invitation) => [
                'code' => $invitation->code,
                'email' => $invitation->email,
                'role_label' => $invitation->role->label(),
            ])->toArray();
    }

    #[Computed]
    public function permissions(): TeamPermissions
    {
        return Auth::user()->toTeamPermissions($this->teamModel);
    }

    public function render()
    {
        return $this->view()->title('Invitations — ' . $this->teamModel->name);
    }
}; ?>

<section class="w-full">
    <div class="flex items-end justify-between gap-4">
        <flux:heading size="xl" level="1">Invitations</flux:heading>
        @if ($this->permissions->canCreateInvitation)
            <flux:button variant="primary" :href="route('teams.invitations.create', $teamModel)" wire:navigate data-test="invite-member-button">
                Invite member
            </flux:button>
        @endif
    </div>

    <flux:table class="mt-6">
        <flux:table.columns>
            <flux:table.column sticky class="bg-white dark:bg-zinc-900">Email</flux:table.column>
            <flux:table.column>Role</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($invitations as $invitation)
                <flux:table.row :key="$invitation['code']" data-test="invitation-row">
                        <flux:table.cell sticky class="bg-white group-hover:bg-zinc-50 dark:bg-zinc-900 dark:group-hover:bg-zinc-800 relative">
                        <x-table-row-link :href="route('teams.invitations.show', ['team' => $teamModel, 'invitation' => $invitation['code']])" wire:navigate :first="true" aria-label="View {{ $invitation['email'] }}" />
                        {{ $invitation['email'] }}
                    </flux:table.cell>

                    <flux:table.cell class="relative">
                        <x-table-row-link :href="route('teams.invitations.show', ['team' => $teamModel, 'invitation' => $invitation['code']])" wire:navigate />
                        <flux:badge color="zinc" size="sm" inset="top bottom">{{ $invitation['role_label'] }}</flux:badge>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</section>
