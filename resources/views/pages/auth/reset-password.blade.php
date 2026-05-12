<x-layouts::guest title="Reset password">
    <section class="w-full">
        <div class="mx-auto max-w-md">
            <flux:heading size="xl" level="1">Reset password</flux:heading>
            <flux:text class="mt-1">Choose a new password. This reset link expires after use.</flux:text>

            @if (session('status'))
                <flux:text color="green" class="mt-4 font-medium">{{ session('status') }}</flux:text>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="mt-4 space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ request()->route('token') }}">

                <flux:field>
                    <flux:label>Email address</flux:label>
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

                <flux:field>
                    <flux:label>Password</flux:label>
                    <flux:input
                        name="password"
                        type="password"
                        required
                        autocomplete="new-password"
                        viewable
                    />
                    <flux:error name="password" />
                    <flux:description>Minimum 8 characters, at least one uppercase letter, at least one lowercase letter, at least one number.</flux:description>
                </flux:field>

                <flux:field>
                    <flux:label>Confirm password</flux:label>
                    <flux:input
                        name="password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        viewable
                    />
                    <flux:error name="password_confirmation" />
                </flux:field>

                <flux:button type="submit" variant="primary" data-test="reset-password-button">
                    Reset password
                </flux:button>
            </form>
        </div>
    </section>
</x-layouts::guest>
