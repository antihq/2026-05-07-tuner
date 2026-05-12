@blaze(fold: true)

@php
$classes = Flux::classes([
    'px-3.5 pt-2 pb-1 sm:px-3 w-full',
    'flex items-center',
    'text-start text-sm/5 sm:text-xs/5 font-medium',
    'text-zinc-500 font-medium dark:text-zinc-400',
]);
@endphp

<div {{ $attributes->class($classes) }} data-flux-menu-heading>
    <div class="w-7 hidden [[data-flux-menu]:has(>[data-flux-menu-item-has-icon])_&]:block"></div>

    <div>{{ $slot }}</div>
</div>
