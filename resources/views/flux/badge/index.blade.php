@blaze(fold: true, memo: true, unsafe: ['icon:trailing', 'icon:variant'])

@php $iconTrailing ??= $attributes->pluck('icon:trailing'); @endphp
@php $iconVariant ??= $attributes->pluck('icon:variant'); @endphp

@props([
    'iconVariant' => 'micro',
    'iconTrailing' => null,
    'variant' => null,
    'rounded' => null,
    'color' => null,
    'inset' => null,
    'size' => null,
    'icon' => null,
    'label' => null,
])

@php
// Backwards compatibility for 'pill' variant...
if ($variant === 'pill') {
    $rounded = true;
    $variant = null;
}

$insetClasses = Flux::applyInset($inset, top: '-mt-1', right: '-me-2', bottom: '-mb-1', left: '-ms-2');

// When using the outline icon variant, we need to size it down to match the default icon sizes...
$iconClasses = Flux::classes()
    ->add($size === 'sm' ? 'size-3' : ($iconVariant === 'outline' ? 'size-4' : ''));

$classes = Flux::classes()
    ->add('inline-flex items-center font-medium whitespace-nowrap')
    ->add('[&:is(button)]:disabled:opacity-50 [&:is(button)]:disabled:cursor-default [&:is(button)]:disabled:pointer-events-none')
    ->add($insetClasses)
    ->add('[print-color-adjust:exact]')
    ->add(match ($size) {
        'lg' => 'text-sm/5 py-1.5 gap-x-1.5',
        default => 'text-sm/5 sm:text-xs/5 py-0.5 gap-x-1.5',
        'sm' => 'text-xs/5 py-0.5 gap-x-1',
    })
    ->add(match ($rounded) {
        true => 'rounded-full px-2',
        default => 'px-1.5',
    })
    /**
     * We can't compile classes for each color because of variants color to color and Tailwind's JIT compiler.
     * We instead need to write out each one by hand. Sorry...
     */
    ->add($variant === 'solid' ? match ($color) {
        default => 'text-white dark:text-white bg-zinc-600 dark:bg-zinc-600 [&:is(button)]:hover:bg-zinc-700 dark:[button]:hover:bg-zinc-500',
        'red' => 'text-white dark:text-white bg-red-500 dark:bg-red-600 [&:is(button)]:hover:bg-red-600 dark:[button]:hover:bg-red-500',
        'orange' => 'text-white dark:text-white bg-orange-500 dark:bg-orange-600 [&:is(button)]:hover:bg-orange-600 dark:[button]:hover:bg-orange-500',
        'amber' => 'text-white dark:text-zinc-950 bg-amber-500 dark:bg-amber-500 [&:is(button)]:hover:bg-amber-600 dark:[button]:hover:bg-amber-400',
        'yellow' => 'text-white dark:text-zinc-950 bg-yellow-500 dark:bg-yellow-400 [&:is(button)]:hover:bg-yellow-600 dark:[button]:hover:bg-yellow-300',
        'lime' => 'text-white dark:text-white bg-lime-500 dark:bg-lime-600 [&:is(button)]:hover:bg-lime-600 dark:[button]:hover:bg-lime-500',
        'green' => 'text-white dark:text-white bg-green-500 dark:bg-green-600 [&:is(button)]:hover:bg-green-600 dark:[button]:hover:bg-green-500',
        'emerald' => 'text-white dark:text-white bg-emerald-500 dark:bg-emerald-600 [&:is(button)]:hover:bg-emerald-600 dark:[button]:hover:bg-emerald-500',
        'teal' => 'text-white dark:text-white bg-teal-500 dark:bg-teal-600 [&:is(button)]:hover:bg-teal-600 dark:[button]:hover:bg-teal-500',
        'cyan' => 'text-white dark:text-white bg-cyan-500 dark:bg-cyan-600 [&:is(button)]:hover:bg-cyan-600 dark:[button]:hover:bg-cyan-500',
        'sky' => 'text-white dark:text-white bg-sky-500 dark:bg-sky-600 [&:is(button)]:hover:bg-sky-600 dark:[button]:hover:bg-sky-500',
        'blue' => 'text-white dark:text-white bg-blue-500 dark:bg-blue-600 [&:is(button)]:hover:bg-blue-600 dark:[button]:hover:bg-blue-500',
        'indigo' => 'text-white dark:text-white bg-indigo-500 dark:bg-indigo-600 [&:is(button)]:hover:bg-indigo-600 dark:[button]:hover:bg-indigo-500',
        'violet' => 'text-white dark:text-white bg-violet-500 dark:bg-violet-600 [&:is(button)]:hover:bg-violet-600 dark:[button]:hover:bg-violet-500',
        'purple' => 'text-white dark:text-white bg-purple-500 dark:bg-purple-600 [&:is(button)]:hover:bg-purple-600 dark:[button]:hover:bg-purple-500',
        'fuchsia' => 'text-white dark:text-white bg-fuchsia-500 dark:bg-fuchsia-600 [&:is(button)]:hover:bg-fuchsia-600 dark:[button]:hover:bg-fuchsia-500',
        'pink' => 'text-white dark:text-white bg-pink-500 dark:bg-pink-600 [&:is(button)]:hover:bg-pink-600 dark:[button]:hover:bg-pink-500',
        'rose' => 'text-white dark:text-white bg-rose-500 dark:bg-rose-600 [&:is(button)]:hover:bg-rose-600 dark:[button]:hover:bg-rose-500',
    } :  match ($color) {
        default => 'text-zinc-700 [&_button]:text-zinc-700! dark:text-zinc-400 dark:[&_button]:text-zinc-400! bg-zinc-600/10 dark:bg-white/5 [&:is(button)]:hover:bg-zinc-600/20 dark:[button]:hover:bg-white/10',
        'red' => 'text-red-700 [&_button]:text-red-700! dark:text-red-400 dark:[&_button]:text-red-400! bg-red-500/15 dark:bg-red-500/10 [&:is(button)]:hover:bg-red-500/25 dark:[button]:hover:bg-red-500/20',
        'orange' => 'text-orange-700 [&_button]:text-orange-700! dark:text-orange-400 dark:[&_button]:text-orange-400! bg-orange-500/15 dark:bg-orange-500/10 [&:is(button)]:hover:bg-orange-500/25 dark:[button]:hover:bg-orange-500/20',
        'amber' => 'text-amber-700 [&_button]:text-amber-700! dark:text-amber-400 dark:[&_button]:text-amber-400! bg-amber-400/20 dark:bg-amber-400/10 [&:is(button)]:hover:bg-amber-400/30 dark:[button]:hover:bg-amber-400/15',
        'yellow' => 'text-yellow-700 [&_button]:text-yellow-700! dark:text-yellow-300 dark:[&_button]:text-yellow-300! bg-yellow-400/20 dark:bg-yellow-400/10 [&:is(button)]:hover:bg-yellow-400/30 dark:[button]:hover:bg-yellow-400/15',
        'lime' => 'text-lime-700 [&_button]:text-lime-700! dark:text-lime-300 dark:[&_button]:text-lime-300! bg-lime-400/20 dark:bg-lime-400/10 [&:is(button)]:hover:bg-lime-400/30 dark:[button]:hover:bg-lime-400/15',
        'green' => 'text-green-700 [&_button]:text-green-700! dark:text-green-400 dark:[&_button]:text-green-400! bg-green-500/15 dark:bg-green-500/10 [&:is(button)]:hover:bg-green-500/25 dark:[button]:hover:bg-green-500/20',
        'emerald' => 'text-emerald-700 [&_button]:text-emerald-700! dark:text-emerald-400 dark:[&_button]:text-emerald-400! bg-emerald-500/15 dark:bg-emerald-500/10 [&:is(button)]:hover:bg-emerald-500/25 dark:[button]:hover:bg-emerald-500/20',
        'teal' => 'text-teal-700 [&_button]:text-teal-700! dark:text-teal-300 dark:[&_button]:text-teal-300! bg-teal-500/15 dark:bg-teal-500/10 [&:is(button)]:hover:bg-teal-500/25 dark:[button]:hover:bg-teal-500/20',
        'cyan' => 'text-cyan-700 [&_button]:text-cyan-700! dark:text-cyan-300 dark:[&_button]:text-cyan-300! bg-cyan-400/20 dark:bg-cyan-400/10 [&:is(button)]:hover:bg-cyan-400/30 dark:[button]:hover:bg-cyan-400/15',
        'sky' => 'text-sky-700 [&_button]:text-sky-700! dark:text-sky-300 dark:[&_button]:text-sky-300! bg-sky-500/15 dark:bg-sky-500/10 [&:is(button)]:hover:bg-sky-500/25 dark:[button]:hover:bg-sky-500/20',
        'blue' => 'text-blue-700 [&_button]:text-blue-700! dark:text-blue-400 dark:[&_button]:text-blue-400! bg-blue-500/15 dark:bg-blue-500/15 [&:is(button)]:hover:bg-blue-500/25 dark:[button]:hover:bg-blue-500/25',
        'indigo' => 'text-indigo-700 [&_button]:text-indigo-700! dark:text-indigo-400 dark:[&_button]:text-indigo-400! bg-indigo-500/15 dark:bg-indigo-500/15 [&:is(button)]:hover:bg-indigo-500/25 dark:[button]:hover:bg-indigo-500/20',
        'violet' => 'text-violet-700 [&_button]:text-violet-700! dark:text-violet-400 dark:[&_button]:text-violet-400! bg-violet-500/15 dark:bg-violet-500/15 [&:is(button)]:hover:bg-violet-500/25 dark:[button]:hover:bg-violet-500/20',
        'purple' => 'text-purple-700 [&_button]:text-purple-700! dark:text-purple-400 dark:[&_button]:text-purple-400! bg-purple-500/15 dark:bg-purple-500/15 [&:is(button)]:hover:bg-purple-500/25 dark:[button]:hover:bg-purple-500/20',
        'fuchsia' => 'text-fuchsia-700 [&_button]:text-fuchsia-700! dark:text-fuchsia-400 dark:[&_button]:text-fuchsia-400! bg-fuchsia-400/15 dark:bg-fuchsia-400/10 [&:is(button)]:hover:bg-fuchsia-400/25 dark:[button]:hover:bg-fuchsia-400/20',
        'pink' => 'text-pink-700 [&_button]:text-pink-700! dark:text-pink-400 dark:[&_button]:text-pink-400! bg-pink-400/15 dark:bg-pink-400/10 [&:is(button)]:hover:bg-pink-400/25 dark:[button]:hover:bg-pink-400/20',
        'rose' => 'text-rose-700 [&_button]:text-rose-700! dark:text-rose-400 dark:[&_button]:text-rose-400! bg-rose-400/15 dark:bg-rose-400/10 [&:is(button)]:hover:bg-rose-400/25 dark:[button]:hover:bg-rose-400/20',
    });
@endphp

<flux:button-or-div :attributes="$attributes->class($classes)" data-flux-badge>
    <?php if (is_string($icon) && $icon !== ''): ?>
        <flux:icon :$icon :variant="$iconVariant" :class="$iconClasses" data-flux-badge-icon />
    <?php else: ?>
        {{ $icon }}
    <?php endif; ?>

    {{ $slot->isEmpty() ? $label : $slot }}

    <?php if ($iconTrailing): ?>
        <div class="ps-1 flex items-center" data-flux-badge-icon:trailing>
            <?php if (is_string($iconTrailing)): ?>
                <flux:icon :icon="$iconTrailing" :variant="$iconVariant" :class="$iconClasses" />
            <?php else: ?>
                {{ $iconTrailing }}
            <?php endif; ?>
        </div>
    <?php endif; ?>
</flux:button-or-div>
