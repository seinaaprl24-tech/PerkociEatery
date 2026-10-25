@props(['type' => 'error'])

@php
    $variant = match ($type) {
        'success' => [
            'container' => 'bg-brand/10 text-brand',
            'icon' => 'check_circle',
        ],
        default => [
            'container' => 'bg-danger/10 text-danger',
            'icon' => 'error',
        ],
    };
@endphp

<div role="alert" {{ $attributes->merge(['class' => 'flex items-start gap-2 rounded-xl px-4 py-3 text-xs font-medium '.$variant['container']]) }}>
    <span class="ms mt-px text-[16px] leading-none" aria-hidden="true">{{ $variant['icon'] }}</span>
    <div class="flex-1 leading-relaxed">{{ $slot }}</div>
</div>
