<?php

namespace App\Livewire\Forms;

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Form;

class DeleteAccountForm extends Form
{
    public string $password = '';

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'current_password'],
        ];
    }

    public function delete(Logout $logout): void
    {
        $this->validate();

        tap(Auth::user(), $logout(...))->delete();
    }
}
