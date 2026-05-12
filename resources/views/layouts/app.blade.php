<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900 antialiased text-zinc-950 dark:text-white">
        <flux:sidebar sticky collapsible="mobile" class="bg-white dark:bg-zinc-900 border-r border-zinc-950/5 dark:border-white/5">
            <livewire:team-switcher />

            <div class="-mx-4">
                <flux:separator />
            </div>

            <flux:sidebar.nav>
                <flux:sidebar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" :accent="false" wire:navigate>
                    Dashboard
                </flux:sidebar.item>
                <flux:sidebar.item :href="route('projects.index')" :current="request()->routeIs('projects.*')" :accent="false" wire:navigate>
                    Projects
                </flux:sidebar.item>
                <flux:sidebar.item :href="route('channels.index')" :current="request()->routeIs('channels.*')" :accent="false" wire:navigate>
                    Channels
                </flux:sidebar.item>
                <flux:sidebar.item :href="route('events.index')" :current="request()->routeIs('events.*')" :accent="false" wire:navigate>
                    Events
                </flux:sidebar.item>
            </flux:navbar>

            <flux:sidebar.spacer />

            <div class="-mx-4">
                <flux:separator />
            </div>

            <flux:dropdown>
                <button class="relative flex min-w-0 items-center gap-3 rounded-lg w-full px-2 py-2 text-start text-zinc-950 dark:text-white hover:text-zinc-950 dark:hover:text-white dark:hover:bg-white/5 hover:bg-zinc-950/5">
                    <flux:avatar name="{{ Auth::user()->name }}" color="auto" color:seed="{{ Auth::user()->id }}" class="size-10" size="lg" square />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm/5 font-medium text-zinc-950 dark:text-white">{{ Auth::user()->name }}</span>
                            <span class="block truncate text-xs/5 font-normal text-zinc-500 dark:text-zinc-400">{{ Auth::user()->email }}</span>
                        </span>
                        <flux:icon icon="chevron-up" variant="micro" class="size-5 sm:size-4 text-zinc-500 dark:text-zinc-400" />
                </button>

                <flux:menu class="min-w-64">
                    <flux:menu.item href="{{ route('account.show') }}" wire:navigate>
                        Account
                    </flux:menu.item>
                    <flux:menu.item href="{{ route('appearance.edit') }}" wire:navigate>
                        Appearance
                    </flux:menu.item>
                    <flux:menu.item href="{{ route('password.edit') }}" wire:navigate>
                        Password
                    </flux:menu.item>
                    <flux:menu.item href="{{ route('authenticator.show') }}" wire:navigate>
                        Authenticator
                    </flux:menu.item>
                    <flux:menu.item href="{{ route('teams.index') }}" wire:navigate>
                        Teams
                    </flux:menu.item>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:menu.item type="submit">
                            Sign out
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
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
