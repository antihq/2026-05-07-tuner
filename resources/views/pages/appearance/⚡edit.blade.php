<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Appearance')] class extends Component
{
}; ?>

<section class="w-full" x-data>
    <flux:heading size="xl" level="1">Appearance</flux:heading>

    <x-description.list class="mt-2.5">
        <x-description.term>Selected theme</x-description.term>
        <x-description.details>
            <span x-text="{ light: 'Light', dark: 'Dark', system: 'System' }[$flux.appearance]"></span>
        </x-description.details>

        <x-description.term>System preference</x-description.term>
        <x-description.details>
            <span x-text="window.matchMedia('(prefers-color-scheme: dark)').matches ? 'Dark' : 'Light'"></span>
        </x-description.details>

        <x-description.term>Resolved appearance</x-description.term>
        <x-description.details>
            <span x-text="document.documentElement.classList.contains('dark') ? 'Dark' : 'Light'"></span>
        </x-description.details>
    </x-description.list>

    <flux:separator class="mt-8" />

    <flux:field class="mt-4">
        <flux:label>Theme</flux:label>
        <flux:description>System follows the operating system preference.</flux:description>
        <flux:radio.group variant="segmented" x-model="$flux.appearance" class="mt-2 max-w-lg">
            <flux:radio value="light" icon="sun">Light</flux:radio>
            <flux:radio value="dark" icon="moon">Dark</flux:radio>
            <flux:radio value="system" icon="computer-desktop">System</flux:radio>
        </flux:radio.group>
    </flux:field>
</section>
