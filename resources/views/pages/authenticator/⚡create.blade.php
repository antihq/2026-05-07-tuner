<?php

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Enable authenticator')] class extends Component {
    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $requiresConfirmation;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    public function mount(): void
    {
        if (Auth::user()->hasEnabledTwoFactorAuthentication()) {
            $this->redirectRoute('authenticator.show', navigate: true);

            return;
        }

        $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');

        $enableTwoFactorAuthentication = app(EnableTwoFactorAuthentication::class);
        $enableTwoFactorAuthentication(Auth::user());

        $this->loadSetupData();
    }

    private function loadSetupData(): void
    {
        $user = Auth::user()?->fresh();

        try {
            if (! $user || ! $user->two_factor_secret) {
                throw new Exception('Two-factor setup secret is not available.');
            }

            $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(Auth::user(), $this->code);

        $this->redirectRoute('authenticator.show', navigate: true);
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl" level="1">Enable authenticator</flux:heading>

    <div class="mt-6">
        @error('setupData')
            <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" />
        @enderror

        <flux:heading level="2">
            Step 1 — Add your account
        </flux:heading>
        <flux:text class="mt-1">Scan the QR code below, or enter the setup key manually in your authenticator app.</flux:text>

        <div class="mt-6 space-y-8">
            <div>
                {!! $qrCodeSvg !!}
            </div>

            <flux:input
                :value="$manualSetupKey"
                readonly
                variant="filled"
                copyable
                icon="key"
                label="Manual setup key"
                class="max-w-lg"
                input:class="font-mono"
            />
        </div>

        @if ($requiresConfirmation)
            <flux:heading level="2" class="mt-8">
                Step 2 — Confirm setup
            </flux:heading>
            <flux:text class="mt-1">Enter the 6-digit code from your authenticator app to complete setup.</flux:text>

            <div class="mt-6 space-y-8">
                <flux:field>
                    <flux:label>Authentication code</flux:label>
                    <flux:otp
                        name="code"
                        wire:model="code"
                        length="6"
                    />
                    <flux:error name="code" />
                </flux:field>

                <flux:button
                    variant="primary"
                    wire:click="confirmTwoFactor"
                    x-bind:disabled="$wire.code.length < 6"
                >
                    Confirm
                </flux:button>
            </div>
        @else
            <flux:button
                variant="primary"
                class="mt-8"
                :disabled="$errors->has('setupData')"
                :href="route('authenticator.show')"
                wire:navigate
            >
                Enable
            </flux:button>
        @endif
    </div>
</section>
