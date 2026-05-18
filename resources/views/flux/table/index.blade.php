@blaze(fold: true)

@props([
    'paginate' => null,
    'bleed' => true,
])

@php
$classes = Flux::classes()
    ->add('[:where(&)]:min-w-full table-fixed border-separate border-spacing-0 isolate')
    ->add('text-zinc-800')
    // We want whitespace-nowrap for the table, but not for modals and dropdowns...
    ->add('whitespace-nowrap [&_dialog]:whitespace-normal [&_[popover]]:whitespace-normal')
    ;

$containerClasses = Flux::classes()
    ->add('flex flex-col')
    ->add($bleed ? 'max-sm:-mx-6' : '')
    ->add($attributes->pluck('container:class'))
    ;
@endphp

<div class="{{ $containerClasses }}">
    {{ $header ?? '' }}

    <ui-table-scroll-area class="overflow-auto">
        <table {{ $attributes->class($classes) }} @if($bleed) data-bleed @endif data-flux-table>
            {{ $slot }}
        </table>
    </ui-table-scroll-area>

    {{ $footer ?? '' }}

    <?php if ($paginate): ?>
        <?php $paginationAttributes = Flux::attributesAfter('pagination:', $attributes, ['paginator' => $paginate, 'class' => 'shrink-0 max-lg:px-6']); ?>
        <flux:pagination :attributes="$paginationAttributes" />
    <?php endif; ?>
</div>
