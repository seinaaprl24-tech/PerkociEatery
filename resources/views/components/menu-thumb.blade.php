@props([
    'item',
    'size' => 'text-[28px]',
    'width' => 640,
    'height' => 640,
])

@php
    $fallbackIcon = ($item['category'] ?? '') === 'minuman' ? 'local_cafe' : 'restaurant';
@endphp

@if (! empty($item['image']))
    <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" width="{{ $width }}" height="{{ $height }}"
        loading="lazy" decoding="async"
        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
        {{ $attributes->merge(['class' => 'h-full w-full object-cover']) }}>

    <div style="display:none"
        {{ $attributes->merge(['class' => 'flex h-full w-full items-center justify-center bg-brand/10 text-brand']) }}
        role="img" aria-label="{{ $item['name'] }} - foto belum tersedia">
        <span class="ms {{ $size }} leading-none" aria-hidden="true">{{ $fallbackIcon }}</span>
    </div>
@else
    {{-- Placeholder netral: ikon cangkir (minuman) / piring (makanan). --}}
    {{-- TODO: menunggu foto asli di public/images/menu/{slug}.jpg --}}
    <div {{ $attributes->merge(['class' => 'flex h-full w-full items-center justify-center bg-brand/10 text-brand']) }}
        role="img" aria-label="{{ $item['name'] }} - foto belum tersedia">
        <span class="ms {{ $size }} leading-none" aria-hidden="true">{{ $fallbackIcon }}</span>
    </div>
@endif
