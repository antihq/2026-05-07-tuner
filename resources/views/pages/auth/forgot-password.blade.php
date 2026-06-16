<x-layouts::auth title="Forgot password">
    <section class="max-w-2xl">
        <flux:heading level="1" class="lowercase">reset password</flux:heading>
        <p class="mt-1">Enter your email. If an account exists, you'll receive a reset link.</p>

        @if (session('status'))
            <p class="mt-1">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-2">
            @csrf

            <flux:field class="max-w-sm">
                <flux:label class="lowercase">Email address</flux:label>
                <flux:input
                    name="email"
                    type="email"
                    required
                    autofocus
                />
                <flux:error name="email" />
            </flux:field>

            <div class="mt-4">
                <flux:button type="submit" variant="primary" color="lime" data-test="email-password-reset-link-button" class="lowercase">
                    Send reset link
                </flux:button>
            </div>
        </form>
    </section>
</x-layouts::auth>
