@blaze

@props([
    'first' => false,
])

<a
    {{ $attributes->merge(['class' => 'absolute inset-0 focus:outline-hidden']) }}
    data-row-link
    tabindex="{{ $first ? 0 : -1 }}"
></a>
