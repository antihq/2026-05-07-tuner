@blaze(fold: true)

@props([
    'variant' => 'block',
])

@php
$classes = Flux::classes()
    ->add('min-w-0') // This is here to allow nested input elements like flux::input.file to truncate properly...
    ->add('[&:not(:has([data-flux-field])):has([data-flux-control][disabled])>[data-flux-label]]:opacity-50') // Dim labels for fields with no nested fields when a control is disabled...
    ->add('[&:not(:has([data-flux-field])):has([data-flux-control][disabled])>[data-flux-description]]:opacity-50') // Dim descriptions for fields with no nested fields when a control is disabled...
    ->add('[&:has(>[data-flux-radio-group][disabled])>[data-flux-label]]:opacity-50') // Special case for radio groups because they are nested fields...
    ->add('[&:has(>[data-flux-checkbox-group][disabled])>[data-flux-label]]:opacity-50') // Special case for checkbox groups because they are nested fields...
    ->add(match ($variant) {
        default => 'block',
        'bare' => '[:where(&)]:block',
        'inline' => [
            'grid gap-x-2 gap-y-0',
            'has-[[data-flux-label]~[data-flux-control]]:grid-cols-[1fr_auto]',
            'has-[[data-flux-control]~[data-flux-label]]:grid-cols-[auto_1fr]',
            '[&>[data-flux-control]~[data-flux-description]]:row-start-2 [&>[data-flux-control]~[data-flux-description]]:col-start-2',
            '[&>[data-flux-control]~[data-flux-error]]:col-span-2 [&>[data-flux-control]~[data-flux-error]]:mt-1', // Position error messages...
            '[&>[data-flux-label]~[data-flux-control]]:row-start-1 [&>[data-flux-label]~[data-flux-control]]:col-start-2',
        ],
    })
    ->add(match ($variant) {
        default => [ // Adjust spacing around label...
            '*:data-flux-label:mb-1 [&>[data-flux-label]:has(+[data-flux-description])]:mb-1',
        ],
        'bare' => '',
        'inline' => '',
    })
    ->add(match ($variant) {
        default => [ // Adjust spacing around description...
            '[&>[data-flux-label]+[data-flux-description]]:mt-0',
            '[&>[data-flux-label]+[data-flux-description]]:mb-1',
            '[&>*:not([data-flux-label])+[data-flux-description]]:mt-1',
        ],
        'bare' => '',
        'inline' => '',
    });
@endphp

<ui-field {{ $attributes->class($classes) }} data-flux-field>
    {{ $slot }}
</ui-field>
