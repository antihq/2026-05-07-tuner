<?php

namespace App\Livewire\Forms;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Form;

class DeleteTeamForm extends Form
{
    public ?Team $team;

    public string $confirmName = '';

    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    public function rules(): array
    {
        return [
            'confirmName' => ['required', 'string'],
        ];
    }

    public function delete(): bool
    {
        Gate::authorize('delete', $this->team);

        $this->validate();

        if ($this->confirmName !== $this->team->name) {
            $this->addError('confirmName', 'The team name does not match.');

            return false;
        }

        $user = Auth::user();

        $fallbackTeam = $user->isCurrentTeam($this->team)
            ? $user->fallbackTeam($this->team)
            : null;

        DB::transaction(function () use ($user) {
            User::where('current_team_id', $this->team->id)
                ->where('id', '!=', $user->id)
                ->each(fn (User $affectedUser) => $affectedUser->switchTeam($affectedUser->personalTeam()));

            $this->team->invitations()->delete();
            $this->team->memberships()->delete();
            $this->team->delete();
        });

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        return true;
    }
}
