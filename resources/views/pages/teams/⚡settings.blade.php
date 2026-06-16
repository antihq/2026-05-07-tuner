<?php

use App\Enums\TeamRole;
use App\Livewire\Forms\CreateInvitationForm;
use App\Livewire\Forms\DeleteTeamForm;
use App\Livewire\Forms\UpdateMemberRoleForm;
use App\Livewire\Forms\UpdateTeamForm;
use App\Models\Team;
use App\Models\User;
use App\Support\TeamPermissions;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Team $team;

    public UpdateTeamForm $teamForm;

    public DeleteTeamForm $deleteForm;

    public CreateInvitationForm $invitationForm;

    public UpdateMemberRoleForm $memberRoleForm;

    public function mount(): void
    {
        $this->teamForm->setTeam($this->team);
        $this->deleteForm->setTeam($this->team);
        $this->invitationForm->setTeam($this->team);
        $this->memberRoleForm->setTeam($this->team);
    }

    public function updateTeamName(): void
    {
        $team = $this->teamForm->save();

        $this->team = $team;

        Flux::toast(variant: 'success', text: 'Team updated.');

        $this->redirectRoute('teams.settings', ['team' => $team->fresh()->slug], navigate: true);
    }

    public function deleteTeam(): void
    {
        if (! $this->deleteForm->delete()) {
            return;
        }

        $this->redirectRoute('teams.switch', navigate: true);
    }

    public function editMember(int $userId): void
    {
        $member = $this->members->first(fn ($m) => $m->id === $userId);

        if (! $member) {
            return;
        }

        $this->memberRoleForm->setMember($userId, $member->pivot->role->value);
    }

    public function cancelEditMember(): void
    {
        $this->memberRoleForm->reset('memberId', 'role');
    }

    public function updateMemberRole(): void
    {
        $this->memberRoleForm->save();
    }

    public function removeMember(int $userId): void
    {
        Gate::authorize('removeMember', $this->team);

        $this->team->memberships()->where('user_id', $userId)->delete();

        $user = User::find($userId);

        if ($user && $user->isCurrentTeam($this->team)) {
            $user->switchTeam($user->personalTeam());
        }

        Flux::toast(variant: 'success', text: 'Member removed.');
    }

    public function createInvitation(): void
    {
        $this->invitationForm->save();

        Flux::toast(variant: 'success', text: 'Invitation sent.');
    }

    public function cancelInvitation(string $code): void
    {
        Gate::authorize('cancelInvitation', $this->team);

        $invitation = $this->team->invitations()->where('code', $code)->firstOrFail();

        abort_unless($invitation->team_id === $this->team->id, 404);

        $invitation->delete();
    }

    #[Computed]
    public function permissions(): TeamPermissions
    {
        return Auth::user()->toTeamPermissions($this->team);
    }

    #[Computed]
    public function ownerName(): ?string
    {
        return $this->team->owner()?->name;
    }

    #[Computed]
    public function availableRoles(): array
    {
        return TeamRole::assignable();
    }

    #[Computed]
    public function members()
    {
        return $this->team->members()->get();
    }

    #[Computed]
    public function invitations()
    {
        return $this->team->invitations()->whereNull('accepted_at')->get();
    }

    public function render()
    {
        return $this->view()->title($this->team->name);
    }
}; ?>

<section class="max-w-2xl">
    <flux:heading level="1">team settings</flux:heading>

    <form wire:submit="updateTeamName" class="mt-2">
        <flux:field class="max-w-sm">
            <flux:label class="lowercase">Owner</flux:label>
            <flux:input :value="$this->ownerName" type="text" required variant="filled" readonly />
            <flux:error name="teamForm.name" />
        </flux:field>

        <flux:field class="mt-2 max-w-sm">
            <flux:label class="lowercase">Team name</flux:label>
            <flux:input wire:model="teamForm.name" type="text" required data-test="team-name-input" :variant="!$this->permissions->canUpdateTeam ? 'filled' : null" :readonly="!$this->permissions->canUpdateTeam" />
            <flux:error name="teamForm.name" />
        </flux:field>

        <div class="mt-4">
            <flux:button type="submit" variant="primary" color="lime" data-test="team-save-button" class="lowercase" :disabled="!$this->permissions->canUpdateTeam">Update name</flux:button>
        </div>
    </form>

    <div class="flex items-center gap-2 mt-8">
        <flux:heading class="lowercase" level="2">Members</flux:heading>
        <span class="text-zinc-500 dark:text-zinc-400 text-sm/5 sm:text-xs/5">{{ $this->members->count() }}</span>
    </div>

    <ul role="list" class="divide-y divide-zinc-950/5 dark:divide-white/5">
        @foreach ($this->members as $member)
        <li class="py-2" data-test="member-row">
            <div class="flex items-center gap-1.5">
                <p class="font-semibold">{{ $member->name }}</p>
                <flux:badge color="fuchsia">{{ $member->pivot->role->value }}</flux:badge>
            </div>
            <div class="flex flex-wrap gap-x-3 justify-between">
                <p>{{ $member->email }}</p>
                <div class="flex items-center gap-1">
                <flux:button size="xs" variant="filled" wire:click="editMember({{ $member->id }})" data-test="member-edit-button" class="lowercase" :disabled="!$this->permissions->canUpdateMember || $member->pivot->role === \App\Enums\TeamRole::Owner">Edit role</flux:button>
                <flux:button size="xs" variant="filled" wire:click="removeMember({{ $member->id }})" wire:confirm="Remove {{ $member->name }} from this team?" data-test="member-remove-button" :disabled="!$this->permissions->canRemoveMember || $member->pivot->role === \App\Enums\TeamRole::Owner" class="lowercase">
                    Remove
                </flux:button>
                </div>
            </div>
            @if ($this->memberRoleForm->memberId === $member->id)
            <div class="mt-1 p-3 pt-2 border border-zinc-950/10 dark:border-white/10 ">
                <form wire:submit="updateMemberRole">
                    <flux:field>
                        <flux:label class="lowercase">Role</flux:label>
                        <flux:radio.group wire:model="memberRoleForm.role" class="lowercase">
                            @foreach ($this->availableRoles as $role)
                                <flux:radio value="{{ $role['value'] }}" label="{{ $role['label'] }}" description="{{ $role['description'] }}" />
                            @endforeach
                        </flux:radio.group>
                        <flux:error name="memberRoleForm.role" />
                    </flux:field>
                    <div class="mt-4 flex gap-1">
                        <flux:button type="submit" variant="primary" color="lime" class="lowercase" data-test="member-role-save">Save</flux:button>
                        <flux:button type="button" variant="ghost" class="lowercase" wire:click="cancelEditMember">Cancel</flux:button>
                    </div>
                </form>
            </div>
            @endif
        </li>
        @endforeach
    </ul>

    <form wire:submit="createInvitation" class="mt-5">
        <flux:fieldset :disabled="!$this->permissions->canCreateInvitation">
            <flux:legend class="lowercase" level="2">Invite member</flux:legend>

            <flux:field class="max-w-sm">
                <flux:label class="lowercase">Email address</flux:label>
                <flux:input wire:model="invitationForm.email" type="email" required autocomplete="email" data-test="invite-email" />
                <flux:error name="invitationForm.role" />
            </flux:field>

            <flux:field class="mt-2">
                <flux:label class="lowercase">Role</flux:label>
                <flux:radio.group wire:model="invitationForm.role" class="lowercase" data-test="invite-role">
                    @foreach ($this->availableRoles as $role)
                        <flux:radio value="{{ $role['value'] }}" label="{{ $role['label'] }}" description="{{ $role['description'] }}" />
                    @endforeach
                </flux:radio.group>
            </flux:field>
        </flux:fieldset>

        <div class="mt-4">
            <flux:button type="submit" variant="primary" color="lime" data-test="invite-submit" class="lowercase" :disabled="!$this->permissions->canCreateInvitation">Send invitation</flux:button>
        </div>
    </form>

    @if (filled($this->invitations) || $this->permissions->canCreateInvitation)
    <div class="flex items-center gap-2 mt-8">
        <flux:heading class="lowercase" level="2">Pending invitations</flux:heading>
        <span class="text-zinc-500 dark:text-zinc-400 text-sm/5 sm:text-xs/5">{{ $this->invitations->count() }}</span>
    </div>

    @if (filled($this->invitations))
    <ul role="list" class="divide-y divide-zinc-950/5 dark:divide-white/5">
        @foreach ($this->invitations as $invitation)
            <li class="py-2" data-test="invitation-row">
                <p class="font-semibold">{{ $invitation->email }}</p>
                <div class="flex flex-wrap items-center gap-x-3 justify-between">
                    <div>
                        <span class="lowercase">{{ $invitation->role->label() }}</span>
                        <span class="lowercase text-sm/5 sm:text-xs/5 ">@if($invitation->expires_at) @if($invitation->isExpired()) Expired {{ $invitation->expires_at->diffForHumans() }} @else Expires {{ $invitation->expires_at->diffForHumans() }} @endif @endif</span>
                    </div>
                    <flux:button size="xs" variant="filled" wire:click="cancelInvitation('{{ $invitation->code }}')" data-test="invitation-cancel-button" class="lowercase" :disabled="!$this->permissions->canCancelInvitation">
                        Cancel
                    </flux:button>
                </div>
            </li>
        @endforeach
    </ul>
    @endif
    @endif

    <form wire:submit="deleteTeam" class="mt-8">
        <flux:fieldset :disabled="! $this->permissions->canDeleteTeam || $team->is_personal">
            <flux:legend class="lowercase" level="2">Delete team</flux:legend>

            <flux:field class="max-w-sm">
                <flux:label class="lowercase">Type "<span class="normal-case">{{ $team->name }}</span>" to confirm</flux:label>
                <flux:input wire:model="deleteForm.confirmName" type="text" required data-test="delete-team-name" />
                <flux:error name="deleteForm.confirmName" />
            </flux:field>
        </flux:fieldset>

        <flux:button type="submit" variant="danger" class="mt-4 lowercase" data-test="delete-team-button" :disabled="! $this->permissions->canDeleteTeam || $team->is_personal">Delete team</flux:button>
    </form>
</section>
