<x-layouts::guest title="Forgot password">
    <section class="w-full mx-auto max-w-md">
        <flux:heading size="xl" level="1">Reset password</flux:heading>
        <flux:text class="mt-1">Enter your email. If an account exists, you'll receive a reset link.</flux:text>

        @if (session('status'))
            <flux:text color="green" class="mt-4 font-medium">{{ session('status') }}</flux:text>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-8">
            @csrf
         <flux:field>
                <flux:label>Email address</flux:label>
                    <flux:input
                        name="email"
                        type="email"
                        required
                        autofocus
                    />
                <flux:error name="email" />
            </flux:field>
         <flux:button variant="primary" type="submit" data-test="email-password-reset-link-button">
                Send reset link
            </flux:button>
        </form>
    </section>
</x-layouts::guest>
