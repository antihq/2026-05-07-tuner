@blaze
@props([])

<strong {{ $attributes->merge(['class' => 'font-medium text-zinc-950 dark:text-white']) }}>{{ $slot }}</strong>