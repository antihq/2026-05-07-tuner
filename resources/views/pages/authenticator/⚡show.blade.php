<?php

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Authenticator')] class extends Component
{
    public bool $canManageTwoFactor;

    public bool $twoFactorEnabled;

    public function mount(): void
    {
        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();

        if ($this->canManageTwoFactor) {
            $user = Auth::user();

            if (Fortify::confirmsTwoFactorAuthentication() && is_null($user->two_factor_confirmed_at)) {
                app(\Laravel\Fortify\Actions\DisableTwoFactorAuthentication::class)($user);
            }

            $this->twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication();

            if (! $this->twoFactorEnabled) {
                $this->redirectRoute('authenticator.create', navigate: true);

                return;
            }
        }
    }

    #[Computed]
    public function twoFactorStatus(): string
    {
        $user = Auth::user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_confirmed_at) {
            return 'Enabled on ' . $user->two_factor_confirmed_at->format('M j, Y');
        }

        return 'Enabled';
    }

    #[Computed]
    public function recoveryCodesRemaining(): int
    {
        $user = Auth::user();

        if (! $user->hasEnabledTwoFactorAuthentication() || ! $user->two_factor_recovery_codes) {
            return 0;
        }

        try {
            $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);

            return count($codes);
        } catch (Throwable) {
            return 0;
        }
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Authenticator</flux:heading>

    <x-description.list class="mt-2.5">
        <x-description.term>Status</x-description.term>
        <x-description.details>{{ $this->twoFactorStatus }}</x-description.details>

        <x-description.term>Recovery codes remaining</x-description.term>
        <x-description.details>
            <flux:link :accent="false" :href="route('recovery-codes.show')" wire:navigate>
                <span class="{{ $this->recoveryCodesRemaining <= 2 ? 'text-amber-600' : '' }}">
                    {{ $this->recoveryCodesRemaining . ' of 8 codes' }}
                </span>
            </flux:link>
        </x-description.details>
    </x-description.list>

    <flux:separator class="mt-8" />

    <div class="mt-4">
        <flux:button variant="danger" :href="route('authenticator.delete')" wire:navigate>
            Disable
        </flux:button>
    </div>
</section>
