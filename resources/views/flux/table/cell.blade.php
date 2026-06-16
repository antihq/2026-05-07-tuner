@blaze(fold: true)

@props([
    'align' => 'start',
    'variant' => null,
    'sticky' => false,
])

@php
$classes = Flux::classes()
    ->add('py-2 px-4 first:ps-2 sm:first:ps-1 last:pe-2 sm:last:pe-1 max-sm:in-data-bleed:first:ps-6 max-sm:in-data-bleed:last:pe-6 text-sm/6')
    ->add(match($align) {
        'center' => 'text-center',
        'end' => 'text-end',
        default => '',
    })
    ->add(match ($variant) {
        'strong' => 'font-medium text-zinc-950 dark:text-white',
        default => 'text-zinc-950 dark:text-white',
    })
    ->add($sticky ? [
        'z-10',
        'first:sticky first:left-0',
        'last:sticky last:right-0',
        'first:after:w-8 first:after:absolute first:after:inset-y-0 first:after:right-0 first:after:translate-x-full first:after:pointer-events-none',
        'last:after:w-8 last:after:absolute last:after:inset-y-0 last:after:left-0 last:after:-translate-x-full last:after:pointer-events-none',
        'in-data-scrolled-right:first:after:inset-shadow-[8px_0px_8px_-8px_rgba(0,0,0,0.05)]',
        'in-data-scrolled-left:last:after:inset-shadow-[-8px_0px_8px_-8px_rgba(0,0,0,0.05)]',
    ]: '')
    ->add('not-in-[tr:first-child]:border-t border-t-zinc-950/5 dark:border-t-white/5')
    ;
@endphp

<td {{ $attributes->class($classes) }} data-flux-cell>
    {{ $slot }}
</td>
