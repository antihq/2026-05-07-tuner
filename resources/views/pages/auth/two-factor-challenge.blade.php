<x-layouts::guest title="Two-factor authentication">
    <section class="w-full">
        <div class="mx-auto max-w-md">
            <div
                class="relative w-full h-auto"
                x-cloak
                x-data="{
                    showRecoveryInput: @js($errors->has('recovery_code')),
                    code: '',
                    recovery_code: '',
                    toggleInput() {
                        this.showRecoveryInput = !this.showRecoveryInput;

                        this.code = '';
                        this.recovery_code = '';

                        $dispatch('clear-2fa-auth-code');

                        $nextTick(() => {
                            this.showRecoveryInput
                                ? this.$refs.recovery_code?.focus()
                                : $dispatch('focus-2fa-auth-code');
                        });
                    },
                }"
            >
                <div x-show="!showRecoveryInput">
                    <flux:heading size="xl" level="1">Authentication code</flux:heading>
                    <flux:text class="mt-1">Enter the 6-digit code from your authenticator app.</flux:text>
                </div>

                <div x-show="showRecoveryInput">
                    <flux:heading size="xl" level="1">Recovery code</flux:heading>
                    <flux:text class="mt-1">Enter one of your saved recovery codes.</flux:text>
                </div>

                <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-6 space-y-8">
                    @csrf

                    <div x-show="!showRecoveryInput">
                        <flux:field>
                            <flux:label>Code</flux:label>
                            <flux:otp
                                x-model="code"
                                length="6"
                                name="code"
                             />
                            <flux:error name="code" />
                        </flux:field>
                    </div>

                    <div x-show="showRecoveryInput">
                        <flux:field>
                            <flux:label>Recovery code</flux:label>
                            <flux:input
                                type="text"
                                name="recovery_code"
                                x-ref="recovery_code"
                                x-bind:required="showRecoveryInput"
                                autocomplete="one-time-code"
                                x-model="recovery_code"
                            />
                            <flux:error name="recovery_code" />
                        </flux:field>
                    </div>

                    <flux:button variant="primary" type="submit">
                        Verify
                    </flux:button>

                    <flux:separator />

                    <div x-show="showRecoveryInput">
                        <flux:button type="button" @click="toggleInput()">
                            Use an authenticator code instead
                        </flux:button>
                    </div>

                    <div x-show="!showRecoveryInput">
                        <flux:button type="button" @click="toggleInput()">
                            Use a recovery code instead
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-layouts::guest>
