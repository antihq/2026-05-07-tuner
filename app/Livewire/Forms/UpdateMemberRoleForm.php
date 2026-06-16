<?php

namespace App\Livewire\Forms;

use App\Enums\TeamRole;
use App\Models\Team;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Form;

class UpdateMemberRoleForm extends Form
{
    public ?Team $team = null;

    public ?int $memberId = null;

    public string $role = '';

    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::enum(TeamRole::class)],
        ];
    }

    public function setMember(int $memberId, string $role): void
    {
        $this->memberId = $memberId;
        $this->role = $role;
    }

    public function save(): void
    {
        Gate::authorize('updateMember', $this->team);

        $validated = $this->validate();

        $this->team->memberships()
            ->where('user_id', $this->memberId)
            ->firstOrFail()
            ->update(['role' => TeamRole::from($validated['role'])]);

        $this->reset('memberId', 'role');
    }
}
