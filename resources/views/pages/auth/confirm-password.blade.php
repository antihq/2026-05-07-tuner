<x-layouts::auth title="Confirm password">
    <section class="max-w-2xl">
        <flux:heading level="1" class="lowercase">confirm password</flux:heading>

        @if (session('status'))
            <p class="mt-1">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('password.confirm.store') }}" class="mt-2">
            @csrf

            <flux:field class="max-w-sm">
                <flux:label class="lowercase">Password</flux:label>
                <flux:input
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <flux:error name="password" />
            </flux:field>

            <div class="mt-4">
                <flux:button type="submit" variant="primary" color="lime" data-test="confirm-password-button" class="lowercase">
                    Confirm
                </flux:button>
            </div>
        </form>
    </section>
</x-layouts::auth>
