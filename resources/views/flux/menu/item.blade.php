@blaze(fold: true, unsafe: ['icon:trailing', 'icon:variant'])

@php $iconTrailing ??= $attributes->pluck('icon:trailing'); @endphp
@php $iconVariant ??= $attributes->pluck('icon:variant'); @endphp

@props([
    'iconTrailing' => null,
    'iconVariant' => 'mini',
    'variant' => 'default',
    'suffix' => null,
    'value' => null,
    'icon' => null,
    'kbd' => null,
])

@php
if ($kbd) $suffix = $kbd;

$iconClasses = Flux::classes()
    ->add('me-2')
    // When using the outline icon variant, we need to size it down to match the default icon sizes...
    ->add($iconVariant === 'outline' ? 'size-5' : null)
    ;

$trailingIconClasses = Flux::classes()
    ->add('ms-auto text-zinc-400 [[data-flux-menu-item-icon]:hover_&]:text-current')
    // When using the outline icon variant, we need to size it down to match the default icon sizes...
    ->add($iconVariant === 'outline' ? 'size-5' : null)
    ;

$classes = Flux::classes()
    ->add('flex items-center px-3.5 py-2.5 sm:px-3 sm:py-1.5 w-full focus:outline-hidden')
    ->add('rounded-lg')
    ->add('text-start text-base/6 sm:text-sm/6')
    ->add('[&[disabled]]:opacity-50')
    ->add(match ($variant) {
        'danger' => [
            'text-zinc-950 data-active:bg-red-500 data-active:text-white dark:text-white',
            '**:data-flux-menu-item-icon:text-zinc-500 dark:**:data-flux-menu-item-icon:text-zinc-400 [&[data-active]_[data-flux-menu-item-icon]]:text-white',
        ],
        'default' => [
            'text-zinc-950 data-active:bg-blue-500 data-active:text-white dark:text-white',
            '**:data-flux-menu-item-icon:text-zinc-500 dark:**:data-flux-menu-item-icon:text-zinc-400 [&[data-active]_[data-flux-menu-item-icon]]:text-white',
        ]
    })
    ;

$suffixClasses = Flux::classes()
    ->add('ms-auto text-xs text-zinc-400')
    ;
@endphp

<flux:button-or-link-pure :attributes="$attributes->class($classes)" data-flux-menu-item :data-flux-menu-item-has-icon="!! $icon">
    <?php if (is_string($icon) && $icon !== ''): ?>
        <flux:icon :$icon :variant="$iconVariant" :class="$iconClasses" data-flux-menu-item-icon />
    <?php elseif ($icon): ?>
        {{ $icon }}
    <?php else: ?>
        <div class="w-7 hidden [[data-flux-menu]:has(>[data-flux-menu-item-has-icon])_&]:block"></div>
    <?php endif; ?>

    {{ $slot }}

    <?php if ($suffix): ?>
        <?php if (is_string($suffix)): ?>
            <div class="{{ $suffixClasses }}">
                {{ $suffix }}
            </div>
        <?php else: ?>
            {{ $suffix }}
        <?php endif; ?>
    <?php endif; ?>

    <?php if (is_string($iconTrailing) && $iconTrailing !== ''): ?>
        <flux:icon :icon="$iconTrailing" :variant="$iconVariant" :class="$trailingIconClasses" data-flux-menu-item-icon />
    <?php elseif ($iconTrailing): ?>
        {{ $iconTrailing }}
    <?php endif; ?>

    {{ $submenu ?? '' }}
</flux:button-or-link-pure>
