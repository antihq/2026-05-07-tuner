@blaze(fold: true)

@php $srOnly = $srOnly ??= $attributes->pluck('sr-only'); @endphp

@props([
    'srOnly' => null,
])

@php
$classes = Flux::classes()
    ->add('text-base/6 text-zinc-950 sm:text-sm/6 dark:text-white')
    ->add($srOnly ? 'sr-only' : '')
    ;
@endphp

<ui-description {{ $attributes->class($classes) }} data-flux-description>
    {{ $slot }}
</ui-description>
