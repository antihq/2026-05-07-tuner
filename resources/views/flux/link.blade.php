@blaze(fold: true)

@props([
    'as' => null,
    'external' => null,
    'accent' => false,
    'variant' => null,
    'strong' => false,
])

@php
$classes = Flux::classes()
    ->add('inline')
    ->add(match ($variant) {
        'ghost' => 'no-underline hover:underline',
        'subtle' => 'no-underline',
        default => 'underline',
    })
    ->add('[[data-color]>&]:text-inherit [[data-color]>&]:decoration-current/20 dark:[[data-color]>&]:decoration-current/50 [[data-color]>&]:hover:decoration-current')
    ->add(match ($variant) {
        'subtle' => 'text-zinc-500 dark:text-white/70 hover:text-zinc-800 dark:hover:text-white',
        default => match ($accent) {
            true => 'text-[var(--color-accent-content)] decoration-[color-mix(in_oklab,var(--color-accent-content),transparent_80%)]',
            false => 'text-zinc-950 dark:text-white decoration-zinc-950/50 hover:decoration-zinc-950 dark:decoration-white/50 dark:hover:decoration-white',
        },
    })
    ;
@endphp
{{-- NOTE: It's important that this file has NO newline at the end of the file. --}}
<?php if ($as !== 'button') : ?><a {{ $attributes->class($classes) }} data-flux-link <?php if ($external) : ?>target="_blank"<?php endif; ?>>{{ $slot }}</a><?php else : ?><button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }} data-flux-link>{{ $slot }}</button><?php endif; ?>