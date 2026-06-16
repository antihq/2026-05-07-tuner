<x-layouts::auth title="Create account">
    <section class="max-w-2xl">
        <flux:heading level="1" class="lowercase">create account</flux:heading>

        @if (session('status'))
            <p class="mt-1">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="mt-2">
            @csrf

            <flux:field class="max-w-sm">
                <flux:label class="lowercase">Name</flux:label>
                <flux:input
                    name="name"
                    :value="old('name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                />
                <flux:error name="name" />
            </flux:field>

            <flux:field class="mt-2 max-w-sm">
                <flux:label class="lowercase">Email address</flux:label>
                <flux:input
                    name="email"
                    :value="old('email')"
                    type="email"
                    required
                    autocomplete="email"
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
                <flux:button type="submit" variant="primary" color="lime" data-test="register-user-button" class="lowercase">
                    Create account
                </flux:button>
            </div>
        </form>
    </section>
</x-layouts::auth>
