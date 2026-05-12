<x-layouts::guest title="Sign in">
    <section class="w-full">
        <div class="mx-auto max-w-md">
            <flux:heading size="xl" level="1">Sign in to your account</flux:heading>

            @if (session('status'))
                <flux:text color="green" class="mt-4 font-medium">{{ session('status') }}</flux:text>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-8">
                @csrf

                <flux:field>
                    <flux:label>Email address</flux:label>
                    <flux:input
                        name="email"
                        :value="old('email')"
                        type="email"
                        required
                        autofocus
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
                        autocomplete="current-password"
                        viewable
                    />
                    <flux:error name="password" />
                    @if (Route::has('password.request'))
                        <flux:text class="mt-3">
                            <flux:link :href="route('password.request')" :accent="false" wire:navigate>Reset password</flux:link>
                        </flux:text>
                    @endif
                </flux:field>

                <flux:checkbox name="remember" label="Remember me" :checked="old('remember')" />

                <flux:button variant="primary" type="submit" data-test="login-button">
                    Sign in
                </flux:button>
            </form>

        </div>
    </section>
</x-layouts::guest>
