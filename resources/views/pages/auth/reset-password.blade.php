<x-layouts::auth title="Reset password">
    <section class="max-w-2xl">
        <flux:heading level="1" class="lowercase">reset password</flux:heading>
        <p class="mt-1">Choose a new password. This reset link expires after use.</p>

        @if (session('status'))
            <p class="mt-1">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="mt-2">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <flux:field class="max-w-sm">
                <flux:label class="lowercase">Email address</flux:label>
                <flux:input
                    name="email"
                    value="{{ request('email') }}"
                    type="email"
                    required
                    autocomplete="email"
                    readonly
                />
                <flux:error name="email" />
            </flux:field>

            <flux:field class="mt-2 max-w-sm">
                <flux:label class="lowercase">Password</flux:label>
                <flux:input
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                />
                <flux:error name="password" />
            </flux:field>

            <flux:field class="mt-2 max-w-sm">
                <flux:label class="lowercase">Confirm password</flux:label>
                <flux:input
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                />
                <flux:error name="password_confirmation" />
            </flux:field>

            <div class="mt-4">
                <flux:button type="submit" variant="primary" color="lime" data-test="reset-password-button" class="lowercase">
                    Reset password
                </flux:button>
            </div>
        </form>
    </section>
</x-layouts::auth>
