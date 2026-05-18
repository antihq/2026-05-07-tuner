<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Disable authenticator')] class extends Component
{
    use PasswordValidationRules;

    public string $password = '';

    public function mount(): void
    {
        if (! Auth::user()->hasEnabledTwoFactorAuthentication()) {
            $this->redirectRoute('authenticator.show', navigate: true);

            return;
        }
    }

    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        $disableTwoFactorAuthentication(Auth::user());

        $this->redirectRoute('authenticator.show', navigate: true);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Disable authenticator</flux:heading>

    <flux:text class="mt-1">This will also delete your recovery codes.</flux:text>

    <form wire:submit="disable" class="mt-6 space-y-8 max-w-xl">
        <flux:field>
            <flux:label>Password</flux:label>
            <flux:input wire:model="password" type="password" required viewable />
            <flux:error name="password" />
        </flux:field>

        <flux:button variant="danger" type="submit" data-test="disable-two-factor-button">
            Disable authenticator
        </flux:button>
    </form>
</section>
