<?php

use App\Concerns\PasswordValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Password')] class extends Component
{
    use PasswordValidationRules;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        Flux::toast(variant: 'success', text: 'Password updated.');
    }

    #[Computed]
    public function passwordRulesDescription(): array
    {
        return [
            'Minimum 8 characters',
            'at least one uppercase letter',
            'at least one lowercase letter',
            'at least one number',
        ];
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Password</flux:heading>
    <form wire:submit="updatePassword" class="mt-6 space-y-8 max-w-xl">
        <flux:field>
            <flux:label>Current password</flux:label>
            <flux:input wire:model="current_password" type="password" required autocomplete="current-password" viewable />
            <flux:error name="current_password" />
        </flux:field>

        <flux:field>
            <flux:label>New password</flux:label>
            <flux:input wire:model="password" type="password" required autocomplete="new-password" viewable />
            <flux:error name="password" />
            <flux:description>
                {{ implode(', ', $this->passwordRulesDescription) . '.' }}
            </flux:description>
        </flux:field>

        <flux:field>
            <flux:label>Confirm password</flux:label>
            <flux:input wire:model="password_confirmation" type="password" required autocomplete="new-password" viewable />
            <flux:error name="password_confirmation" />
        </flux:field>

        <flux:button variant="primary" type="submit" data-test="update-password-button">
            Save
        </flux:button>
    </form>
</section>
