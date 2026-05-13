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
