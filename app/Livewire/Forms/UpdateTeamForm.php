<?php

namespace App\Livewire\Forms;

use App\Models\Team;
use App\Rules\TeamName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Form;

class UpdateTeamForm extends Form
{
    public ?Team $team;

    public string $name = '';

    public function setTeam(Team $team): void
    {
        $this->team = $team;
        $this->name = $team->name;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new TeamName],
        ];
    }

    public function save(): Team
    {
        Gate::authorize('update', $this->team);

        $this->validate();

        $team = DB::transaction(function () {
            $team = Team::whereKey($this->team->id)->lockForUpdate()->firstOrFail();

            $team->update(['name' => $this->name]);

            return $team;
        });

        return $team;
    }
}
