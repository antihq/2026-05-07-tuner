@blaze(fold: true, safe: ['key'])

@props([
    'key' => null,
    'sticky' => false,
])

@php
$classes = Flux::classes()
    ->add('group')
    ->add($sticky ? 'last:sticky last:bottom-0 last:z-20' : '')
    ->add('has-[[data-row-link]]:hover:bg-zinc-950/[2.5%] has-[[data-row-link]]:dark:hover:bg-white/[2.5%]')
    ->add('has-[[data-row-link]:focus-visible]:outline-2 has-[[data-row-link]:focus-visible]:-outline-offset-2 has-[[data-row-link]:focus-visible]:outline-blue-500')
    ->add('dark:has-[[data-row-link]:focus-visible]:bg-white/[2.5%]')
    ;
@endphp

<tr @if ($key) wire:key="table-{{ $key }}" @endif {{ $attributes->class($classes) }} data-flux-row>
    {{ $slot }}
</tr>
