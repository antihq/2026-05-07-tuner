<x-layouts::auth title="Two-factor authentication">
    <section class="max-w-2xl">
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
                <flux:heading level="1" class="lowercase">authentication code</flux:heading>
                <p class="mt-1 lowercase">Enter the 6-digit code from your authenticator app.</p>
            </div>

            <div x-show="showRecoveryInput">
                <flux:heading level="1" class="lowercase">recovery code</flux:heading>
                <p class="mt-1 lowercase">Enter one of your saved recovery codes.</p>
            </div>

            <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-2">
                @csrf

                <div x-show="!showRecoveryInput">
                    <flux:field class="max-w-sm">
                        <flux:label class="lowercase">Code</flux:label>
                        <flux:otp
                            x-model="code"
                            length="6"
                            name="code"
                         />
                        <flux:error name="code" />
                    </flux:field>
                </div>

                <div x-show="showRecoveryInput">
                    <flux:field class="max-w-sm">
                        <flux:label class="lowercase">Recovery code</flux:label>
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

                <div class="mt-4">
                    <flux:button type="submit" variant="primary" color="lime" class="lowercase">
                        Verify
                    </flux:button>
                </div>

                <div x-show="showRecoveryInput" class="mt-8">
                    <flux:button type="button" @click="toggleInput()" class="lowercase">
                        Use an authenticator code instead
                    </flux:button>
                </div>

                <div x-show="!showRecoveryInput" class="mt-8">
                    <flux:button type="button" @click="toggleInput()" class="lowercase">
                        Use a recovery code instead
                    </flux:button>
                </div>
            </form>
        </div>
    </section>
</x-layouts::auth>
