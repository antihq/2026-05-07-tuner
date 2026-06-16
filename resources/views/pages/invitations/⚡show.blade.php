<?php

use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public TeamInvitation $invitationModel;

    public function mount(TeamInvitation $invitation): void
    {
        $this->invitationModel = $invitation;
    }

    #[Computed]
    public function teamMemberCount(): int
    {
        return $this->invitationModel->team->members()->count();
    }

    #[Computed]
    public function teamOwnerName(): ?string
    {
        return $this->invitationModel->team->owner()?->name;
    }

    public function accept(): void
    {
        $user = Auth::user();

        if ($this->invitationModel->isAccepted()) {
            $this->addError('invitation', 'This invitation has already been accepted.');

            return;
        }

        if ($this->invitationModel->isExpired()) {
            $this->addError('invitation', 'This invitation has expired.');

            return;
        }

        if (Str::lower($this->invitationModel->email) !== Str::lower($user->email)) {
            $this->addError('invitation', 'This invitation was sent to a different email address.');

            return;
        }

        DB::transaction(function () use ($user) {
            $team = $this->invitationModel->team;

            $team->memberships()->firstOrCreate(
                ['user_id' => $user->id],
                ['role' => $this->invitationModel->role],
            );

            $this->invitationModel->update(['accepted_at' => now()]);

            $user->switchTeam($team);
        });

        $this->redirectRoute('dashboard');
    }

    public function render()
    {
        return $this->view()->title($this->invitationModel->team->name);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">{{ $invitationModel->team->name }}</flux:heading>

    <x-description.list class="mt-2.5">
        <x-description.term>Invitation code</x-description.term>
        <x-description.details class="font-mono text-sm">{{ $invitationModel->code }}</x-description.details>

        <x-description.term>Sent to</x-description.term>
        <x-description.details>{{ $invitationModel->email }}</x-description.details>

        <x-description.term>Your email</x-description.term>
        <x-description.details>{{ Auth::user()->email }}</x-description.details>

        <x-description.term>Role</x-description.term>
        <x-description.details>
            <flux:badge color="zinc" size="sm" inset="top bottom">{{ $invitationModel->role->label() }}</flux:badge>
        </x-description.details>

        <x-description.term>Invited by</x-description.term>
        <x-description.details>{{ $invitationModel->inviter?->name ?? '—' }}</x-description.details>

        <x-description.term>Team owner</x-description.term>
        <x-description.details>{{ $this->teamOwnerName ?? '—' }}</x-description.details>

        <x-description.term>Team members</x-description.term>
        <x-description.details>{{ $this->teamMemberCount }} {{ str()->plural('member', $this->teamMemberCount) }}</x-description.details>

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

    @if ($invitationModel->isPending())
        <form wire:submit="accept" class="mt-6 space-y-8">
            @error('invitation')
                <flux:text color="red">{{ $message }}</flux:text>
            @enderror

            <flux:button variant="primary" type="submit" data-test="invitation-accept-button">
                Accept invitation
            </flux:button>
        </form>
    @endif
</section>
