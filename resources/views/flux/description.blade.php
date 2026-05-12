@blaze(fold: true)

@php $srOnly = $srOnly ??= $attributes->pluck('sr-only'); @endphp

@props([
    'srOnly' => null,
])

@php
$classes = Flux::classes()
    ->add('text-base/6 sm:text-sm/6 text-zinc-500 dark:text-zinc-400')
    ->add($srOnly ? 'sr-only' : '')
    ;
@endphp

<ui-description {{ $attributes->class($classes) }} data-flux-description>
    {{ $slot }}
</ui-description>
