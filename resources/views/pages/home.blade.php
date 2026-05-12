<x-layouts::guest title="Welcome">
    <div class="mx-auto max-w-md">
        <flux:badge class="font-mono">{{ config('app.name', 'Laravel Teams Starter Kit') }}</flux:badge>

        <flux:heading level="1" size="xl" class="mt-2">All your Laravel project's events centralized in a single place.</flux:heading>


        <flux:separator class="my-8" />

        <flux:heading level="2" class="mt-8">Sign in to your account</flux:heading>

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
                    placeholder="email@example.com"
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
                        <flux:link :href="route('password.request')" :accent="false" wire:navigate>Forgot your password?</flux:link>
                    </flux:text>
                @endif
            </flux:field>

            <flux:checkbox name="remember" label="Remember me" :checked="old('remember')" />

            <flux:button variant="primary" type="submit" data-test="login-button">
                Sign in
            </flux:button>
        </form>
    </div>
</x-layouts::guest>
