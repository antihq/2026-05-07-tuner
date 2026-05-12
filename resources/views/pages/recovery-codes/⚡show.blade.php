<?php

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Recovery codes')] class extends Component {
    #[Locked]
    public array $recoveryCodes = [];

    public function mount(): void
    {
        if (! Auth::user()->hasEnabledTwoFactorAuthentication()) {
            $this->redirectRoute('authenticator.show', navigate: true);

            return;
        }

        $this->loadRecoveryCodes();
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(Auth::user());

        $this->loadRecoveryCodes();
    }

    private function loadRecoveryCodes(): void
    {
        $user = Auth::user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            try {
                $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            } catch (Exception) {
                $this->addError('recoveryCodes', 'Failed to load recovery codes');

                $this->recoveryCodes = [];
            }
        }
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Recovery codes</flux:heading>

    <flux:text class="mt-2">Each code can only be used once.</flux:text>

    <div class="mt-6 space-y-8">
        @error('recoveryCodes')
            <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" />
        @enderror

        @if (filled($recoveryCodes))
            <div
                class="grid grid-cols-2 gap-x-8 gap-y-1 font-mono text-sm"
                role="list"
                aria-label="Recovery codes"
            >
                @foreach($recoveryCodes as $code)
                    <div
                        role="listitem"
                        class="select-text"
                        wire:loading.class="opacity-50 animate-pulse"
                    >
                        {{ $code }}
                    </div>
                @endforeach
            </div>

            <flux:separator />

            <flux:button variant="danger" wire:click="regenerateRecoveryCodes">
                Regenerate codes
            </flux:button>
        @else
            <flux:callout variant="warning" icon="exclamation-triangle" heading="No recovery codes">
                No recovery codes were found. Set up an authenticator to generate a set.
            </flux:callout>
        @endif
    </div>
</section>
