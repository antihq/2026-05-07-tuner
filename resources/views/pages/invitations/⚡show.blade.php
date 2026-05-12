<?php

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    public TeamInvitation $invitationModel;

    public function mount(TeamInvitation $invitation): void
    {
        $this->invitationModel = $invitation;
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
        <x-description.term>Email</x-description.term>
        <x-description.details>{{ $invitationModel->email }}</x-description.details>

        <x-description.term>Role</x-description.term>
        <x-description.details>
            <flux:badge color="zinc" size="sm" inset="top bottom">{{ $invitationModel->role->label() }}</flux:badge>
        </x-description.details>

        <x-description.term>Invited by</x-description.term>
        <x-description.details>{{ $invitationModel->inviter?->name ?? '—' }}</x-description.details>

        <x-description.term>Expires</x-description.term>
        <x-description.details class="tabular-nums">{{ $invitationModel->expires_at?->format('Y-m-d H:i') ?? '—' }}</x-description.details>
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
