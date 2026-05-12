<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Notifications\Teams\TeamInvitation as TeamInvitationNotification;
use App\Rules\UniqueTeamInvitation;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public Team $team;

    public string $inviteEmail = '';

    public string $inviteRole = 'member';

    public function mount(Team $team): void
    {
        $this->team = $team;
    }

    public function createInvitation(): void
    {
        Gate::authorize('inviteMember', $this->team);

        $validated = $this->validate([
            'inviteEmail' => ['required', 'string', 'email', 'max:255', new UniqueTeamInvitation($this->team)],
            'inviteRole' => ['required', 'string', Rule::enum(TeamRole::class)],
        ]);

        $invitation = $this->team->invitations()->create([
            'email' => $validated['inviteEmail'],
            'role' => TeamRole::from($validated['inviteRole']),
            'invited_by' => Auth::id(),
            'expires_at' => now()->addDays(3),
        ]);

        Notification::route('mail', $invitation->email)
            ->notify(new TeamInvitationNotification($invitation));

        $this->reset('inviteEmail', 'inviteRole');

        Flux::toast(variant: 'success', text: 'Invitation sent.');

        $this->redirectRoute('teams.invitations', ['team' => $this->team->slug], navigate: true);
    }

    #[Computed]
    public function availableRoles(): array
    {
        return TeamRole::assignable();
    }

    public function render()
    {
        return $this->view()->title('Invite team member — ' . $this->team->name);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Invite team member</flux:heading>

    <form wire:submit="createInvitation" class="mt-6 space-y-8">
        <flux:field>
            <flux:label>Email address</flux:label>
            <flux:input wire:model="inviteEmail" type="email" required autofocus autocomplete="email" class="max-w-lg" data-test="invite-email" />
            <flux:error name="inviteEmail" />
        </flux:field>

        <flux:field>
            <flux:label>Role</flux:label>
            <flux:select wire:model="inviteRole" class="max-w-lg" data-test="invite-role">
                @foreach ($this->availableRoles as $role)
                    <flux:select.option value="{{ $role['value'] }}">{{ $role['label'] }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="inviteRole" />
        </flux:field>

        <flux:button variant="primary" type="submit" data-test="invite-submit">
            Send invitation
        </flux:button>
    </form>
</section>
