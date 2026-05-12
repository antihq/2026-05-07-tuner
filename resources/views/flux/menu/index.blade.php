@blaze(fold: true)

@php
$classes = Flux::classes()
    ->add('[:where(&)]:w-max p-1')
    ->add('rounded-xl shadow-lg')
    ->add('ring-1 ring-zinc-950/10 dark:ring-white/10')
    ->add('bg-white/75 backdrop-blur-xl dark:bg-zinc-800/75')
    ->add('focus:outline-hidden overflow-y-auto')
    ;
@endphp

<ui-menu
    {{ $attributes->class($classes) }}
    popover="manual"
    data-flux-menu
>
    {{ $slot }}
</ui-menu>
