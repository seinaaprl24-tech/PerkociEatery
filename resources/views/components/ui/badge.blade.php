@props(['tone' => 'neutral', 'icon' => null])

@php
    $tones = [
        'brand' => 'badge-brand',
        'lime' => 'badge-lime',
        'butter' => 'badge-butter',
        'danger' => 'badge-danger',
        'neutral' => 'badge-neutral',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'badge '.($tones[$tone] ?? $tones['neutral'])]) }}>
    @if ($icon)
        <span class="ms text-[12px] leading-none" aria-hidden="true">{{ $icon }}</span>
    @endif

    {{ $slot }}
</span>
