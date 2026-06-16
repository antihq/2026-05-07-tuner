<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-white dark:bg-zinc-900 antialiased text-zinc-950 dark:text-white text-base/6 sm:text-sm/6">
        <header>
            <nav class="flex items-end flex-wrap py-5">
                <div class="lg:w-64 lg:text-right px-4 gap-x-3 text-zinc-500 dark:text-zinc-400">
                    <a href="{{ route('home') }}" class="text-zinc-500 dark:text-zinc-400" wire:navigate>
                        {{ config('app.name') }}
                    </a>
                </div>

                <div class="w-full lg:flex-1 flex-wrap flex px-4 gap-x-3 md:justify-between">
                    <div class="flex gap-x-3">
                        <flux:link :href="route('home')" class="lowercase" wire:navigate :accent="false" :variant="request()->routeIs('home') ? null : 'ghost'">home</flux:link>
                    </div>

                    <div aria-hidden="true" class="flex-1"></div>

                    <div class="flex gap-x-3">
                        @guest
                            @if (Route::has('login'))
                                <flux:link :href="route('login')" class="lowercase" wire:navigate :accent="false">Sign in</flux:link>
                            @endif

                            @if (Route::has('register'))
                                <flux:link :href="route('register')" class="lowercase" wire:navigate :accent="false">Create account</flux:link>
                            @endif
                        @endguest

                        @auth
                            <flux:link :href="route('dashboard')" class="lowercase" wire:navigate :accent="false">dashboard</flux:link>
                        @endauth
                    </div>
                </div>
            </nav>
        </header>

        <main class="lg:pl-64">
            <div class="p-4 pt-0">
                <div class="w-full max-w-6xl">
                    {{ $slot }}
                </div>
            </div>
        </main>

        @persist('toast')
            <flux:toast.group position="bottom center">
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
