@props([
    'label',
    'value',
    'icon' => null,
    'hint' => null,
    'trend' => null,
    'up' => true,
    'tone' => 'brand',
])

@php
    $tones = [
        'brand' => 'bg-brand/10 text-brand',
        'lime' => 'bg-lime/60 text-brand',
        'butter' => 'bg-butter/70 text-olive',
        'cocoa' => 'bg-cocoa/10 text-cocoa',
        'danger' => 'bg-danger/10 text-danger',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'card flex flex-col gap-3 p-5']) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-cocoa/50">{{ $label }}</p>

        @if ($icon)
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $tones[$tone] ?? $tones['brand'] }}">
                <span class="ms text-[18px] leading-none" aria-hidden="true">{{ $icon }}</span>
            </span>
        @endif
    </div>

    <p class="font-display text-[26px] font-bold leading-none text-cocoa">{{ $value }}</p>

    @if ($hint || $trend)
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
            @if ($trend)
                <span class="inline-flex items-center gap-0.5 text-[11px] font-bold {{ $up ? 'text-brand' : 'text-danger' }}">
                    <span class="ms text-[14px] leading-none" aria-hidden="true">{{ $up ? 'trending_up' : 'trending_down' }}</span>
                    {{ $trend }}
                </span>
            @endif

            @if ($hint)
                <p class="text-[11px] leading-relaxed text-cocoa/45">{{ $hint }}</p>
            @endif
        </div>
    @endif
</div>
