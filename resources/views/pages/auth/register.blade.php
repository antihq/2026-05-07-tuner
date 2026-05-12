<x-layouts::guest title="Create account">
    <section class="w-full">
        <div class="mx-auto max-w-md">
            <flux:heading size="xl" level="1">Create an account</flux:heading>

            @if (session('status'))
                <flux:text color="green" class="mt-4 font-medium">{{ session('status') }}</flux:text>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-8">
                @csrf

                <flux:field>
                    <flux:label>Name</flux:label>
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

                <flux:field>
                    <flux:label>Email address</flux:label>
                    <flux:input
                        name="email"
                        :value="old('email')"
                        type="email"
                        required
                        autocomplete="email"
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

                <flux:button type="submit" variant="primary" data-test="register-user-button">
                    Create account
                </flux:button>
            </form>

        </div>
    </section>
</x-layouts::guest>
