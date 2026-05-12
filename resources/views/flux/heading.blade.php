@blaze(fold: true)

@props([
    'size' => 'base',
    'accent' => false,
    'level' => null,
])

@php
$classes = Flux::classes()
    ->add('font-semibold')
    ->add(match ($accent) {
        true => 'text-[var(--color-accent-content)]',
        default => '[:where(&)]:text-zinc-950 [:where(&)]:dark:text-white',
    })
    ->add(match ($size) {
        'xl' => 'text-2xl/8 sm:text-xl/8 [&:has(+[data-flux-subheading])]:mb-2 [[data-flux-subheading]+&]:mt-2',
        'lg' => 'text-base/7 sm:text-sm/6 [&:has(+[data-flux-subheading])]:mb-2 [[data-flux-subheading]+&]:mt-2',
        default => 'text-base/7 sm:text-sm/6 [&:has(+[data-flux-subheading])]:mb-2 [[data-flux-subheading]+&]:mt-2',
    })
    ;
@endphp

<?php switch ((int) $level): case(1): ?>
        <h1 {{ $attributes->class($classes) }} data-flux-heading>{{ $slot }}</h1>

        @break
    <?php case(2): ?>
        <h2 {{ $attributes->class($classes) }} data-flux-heading>{{ $slot }}</h2>

        @break
    <?php case(3): ?>
        <h3 {{ $attributes->class($classes) }} data-flux-heading>{{ $slot }}</h3>

        @break
    <?php case(4): ?>
        <h4 {{ $attributes->class($classes) }} data-flux-heading>{{ $slot }}</h4>

        @break
    <?php default: ?>
        <div {{ $attributes->class($classes) }} data-flux-heading>{{ $slot }}</div>
<?php endswitch; ?>
