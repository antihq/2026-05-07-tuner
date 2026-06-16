<flux:menu class="min-w-64">
    <flux:menu.item href="{{ route('settings') }}" wire:navigate>
        Settings
    </flux:menu.item>
    <flux:menu.item href="{{ route('teams.switch') }}" wire:navigate>
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
