<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-white dark:bg-zinc-900 antialiased text-zinc-950 dark:text-white text-base/6 sm:text-sm/6">
        <header>
            <nav class="flex items-end flex-wrap py-5">
                <div class="lg:w-64 lg:text-right px-4 gap-x-3 text-zinc-500 dark:text-zinc-400">
                    <a href="{{ route('home') }}" wire:navigate>
                        {{ config('app.name') }}
                    </a>
                    (<flux:link :href="route('dashboard', ['current_team' => Auth::user()->currentTeam])" wire:navigate :accent="false">{{ Auth::user()->currentTeam->name }}</flux:link>)
                    <flux:button size="xs" variant="filled" :href="route('teams.switch')" wire:navigate class="lowercase">switch team</flux:button>
                </div>

                <div class="w-full lg:flex-1 flex-wrap flex px-4 gap-x-3 md:justify-between">
                    <div class="flex gap-x-3">
                        @if (Auth::user()->currentTeam)
                            <flux:link :href="route('dashboard', ['current_team' => Auth::user()->currentTeam])" class="lowercase" wire:navigate :accent="false" :variant="request()->routeIs('dashboard') ? null : 'ghost'">dashboard</flux:link>
                            <flux:link :href="route('projects.index', ['current_team' => Auth::user()->currentTeam])" class="lowercase" wire:navigate :accent="false" :variant="request()->routeIs('projects.*') ? null : 'ghost'">projects</flux:link>
                            <flux:link :href="route('channels.index', ['current_team' => Auth::user()->currentTeam])" class="lowercase" wire:navigate :accent="false" :variant="request()->routeIs('channels.*') ? null : 'ghost'">channels</flux:link>
                            <flux:link :href="route('events.index', ['current_team' => Auth::user()->currentTeam])" class="lowercase" wire:navigate :accent="false" :variant="request()->routeIs('events.*') ? null : 'ghost'">events</flux:link>
                            <flux:link :href="route('teams.settings', Auth::user()->currentTeam)" class="lowercase" wire:navigate :accent="false" :variant="request()->routeIs('teams.settings') ? null : 'ghost'">settings</flux:link>
                        @endif
                    </div>

                    <div aria-hidden="true" class="flex-1"></div>

                    <div>
                        logged in as <flux:link :href="route('settings')" class="lowercase" wire:navigate :accent="false">{{ Auth::user()->email }}</flux:link>
                        <form method="POST" action="{{ route('logout') }}" class="inline-flex">
                            @csrf
                            <flux:button size="xs" variant="filled" type="submit" class="lowercase">logout</flux:button>
                        </form>
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
