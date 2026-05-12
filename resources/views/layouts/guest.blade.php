<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900 antialiased text-zinc-950 dark:text-white">
        <flux:header class="border-b border-zinc-950/5 dark:border-white/5" container>
            <flux:spacer />

            <flux:navbar class="-mr-2.5 -mb-px">
                @guest
                    @if (Route::has('login'))
                        <flux:navbar.item :href="route('login')" :accent="false" wire:navigate>
                            Sign in
                        </flux:navbar.item>
                    @endif

                    @if (Route::has('register'))
                        <flux:navbar.item :href="route('register')" :accent="false" wire:navigate>
                            Create account
                        </flux:navbar.item>
                    @endif
                @endguest

                @auth
                    <flux:navbar.item :href="route('dashboard')" :accent="false" wire:navigate>
                        Dashboard
                    </flux:navbar.item>
                @endauth
            </flux:navbar>
        </flux:header>

        <flux:main container>
            {{ $slot }}
        </flux:main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
