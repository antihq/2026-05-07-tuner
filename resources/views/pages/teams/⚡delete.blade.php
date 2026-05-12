<?php

use App\Models\Team;
use App\Models\User;
use App\Support\TeamPermissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Delete team')] class extends Component
{
    public Team $teamModel;

    public string $deleteTeamName = '';

    public function mount(Team $team): void
    {
        $this->teamModel = $team;
    }

    public function deleteTeam(): void
    {
        Gate::authorize('delete', $this->teamModel);

        $validated = $this->validate([
            'deleteTeamName' => ['required', 'string'],
        ]);

        if ($validated['deleteTeamName'] !== $this->teamModel->name) {
            $this->addError('deleteTeamName', 'The team name does not match.');

            return;
        }

        $user = Auth::user();

        $fallbackTeam = $user->isCurrentTeam($this->teamModel)
            ? $user->fallbackTeam($this->teamModel)
            : null;

        DB::transaction(function () use ($user) {
            User::where('current_team_id', $this->teamModel->id)
                ->where('id', '!=', $user->id)
                ->each(fn (User $affectedUser) => $affectedUser->switchTeam($affectedUser->personalTeam()));

            $this->teamModel->invitations()->delete();
            $this->teamModel->memberships()->delete();
            $this->teamModel->delete();
        });

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        $this->redirectRoute('teams.index', navigate: true);
    }

    #[Computed]
    public function permissions(): TeamPermissions
    {
        return Auth::user()->toTeamPermissions($this->teamModel);
    }

    public function render()
    {
        return $this->view();
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Delete team</flux:heading>

    @if ($this->permissions->canDeleteTeam && ! $teamModel->is_personal)
        <form wire:submit="deleteTeam" class="mt-6 space-y-8">
            <flux:field>
                <flux:label>Type "{{ $teamModel->name }}" to confirm</flux:label>
                <flux:input wire:model="deleteTeamName" type="text" required class="max-w-lg" data-test="delete-team-name" />
                <flux:error name="deleteTeamName" />
            </flux:field>

            <flux:button variant="danger" type="submit" data-test="delete-team-button">
                Delete team
            </flux:button>
        </form>
    @endif
</section>
