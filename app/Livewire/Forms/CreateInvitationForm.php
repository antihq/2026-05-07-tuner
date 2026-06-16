<?php

namespace App\Livewire\Forms;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Notifications\Teams\TeamInvitation as TeamInvitationNotification;
use App\Rules\UniqueTeamInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CreateInvitationForm extends Form
{
    public ?Team $team;

    public string $email = '';

    public string $role = 'member';

    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', new UniqueTeamInvitation($this->team)],
            'role' => ['required', 'string', Rule::enum(TeamRole::class)],
        ];
    }

    public function save(): void
    {
        Gate::authorize('inviteMember', $this->team);

        $validated = $this->validate();

        $invitation = $this->team->invitations()->create([
            'email' => $validated['email'],
            'role' => TeamRole::from($validated['role']),
            'invited_by' => Auth::id(),
            'expires_at' => now()->addDays(3),
        ]);

        Notification::route('mail', $invitation->email)
            ->notify(new TeamInvitationNotification($invitation));

        $this->reset('email', 'role');
    }
}
