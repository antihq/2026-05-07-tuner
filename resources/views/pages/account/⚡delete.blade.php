<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Delete account')] class extends Component
{
    use PasswordValidationRules;

    public string $password = '';

    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    <div>
        <flux:heading size="xl" level="1">Delete account</flux:heading>

        @if ($this->showDeleteUser)
            <form wire:submit="deleteUser" class="mt-6 space-y-8 max-w-xl">
                <flux:field>
                    <flux:label>Password</flux:label>
                    <flux:input wire:model="password" type="password" required viewable />
                    <flux:error name="password" />
                </flux:field>

                <flux:button variant="danger" type="submit" data-test="delete-user-button">
                    Delete account
                </flux:button>
            </form>
        @else
            <flux:text color="amber" class="mt-6">
                Email verification required to delete your account.
            </flux:text>
        @endif
    </div>
</section>
