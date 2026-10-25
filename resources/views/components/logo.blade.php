@props(['alt' => null])

@php
    /*
     | Logo resmi Perkoci Eatery.
     |
     | Sumber utama: config('perkoci.brand.logo') — salinan PNG yang sudah
     | dipangkas area transparannya (tanpa mengubah warna/bentuk logo).
     | Bila file itu belum ada, komponen mencoba file asli yang diletakkan
     | di public/images/ (termasuk nama ganda "logo-perkoci.png.jpeg").
     | Tidak ada logo pengganti yang digambar di sini.
     */
    $candidates = array_filter([
        config('perkoci.brand.logo'),
        'images/logo-perkoci.jpeg',
        'images/logo-perkoci.png',
        'images/logo-perkoci.png.jpeg',
    ]);

    $logoPath = null;

    foreach ($candidates as $candidate) {
        if (is_file(public_path($candidate))) {
            $logoPath = $candidate;
            break;
        }
    }
@endphp

@if ($logoPath)
    <img src="{{ asset($logoPath) }}" alt="{{ $alt ?? config('perkoci.brand.name') }}"
        {{ $attributes->merge(['class' => 'object-contain']) }} draggable="false" decoding="async">
@endif
