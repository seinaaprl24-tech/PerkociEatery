@extends('layouts.customer')

@section('title', 'Perkoci Eatery - Selamat Datang')

@section('header')@endsection

@section('bottomnav')@endsection

@section('content')
    @php
        $cover = config('perkoci.cover');
        $table = config('perkoci.table');
    @endphp

    <div class="relative min-h-screen overflow-hidden bg-cream">
        {{-- Gradasi hijau brand ke krem --}}
        <div class="pointer-events-none absolute inset-x-0 top-0 h-[600px] bg-[linear-gradient(to_bottom,#195905_0%,#195905_8%,#2e7d12_42%,#f9f8f3_100%)]"></div>

        {{-- Lingkaran dekoratif samar (kanan atas) --}}
        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -right-10 top-36 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute right-20 top-72 h-2 w-2 rounded-full bg-lime/60"></div>

        <div class="relative z-10 mx-auto flex min-h-screen w-full max-w-[390px] flex-col px-6 pt-8">

            {{-- Status sesi meja --}}
            <div class="flex justify-center">
                <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-[10px] font-bold uppercase tracking-[0.18em] text-cocoa shadow-soft ring-1 ring-cocoa/5">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>
                    {{ $table['session_label'] }}
                </span>
            </div>

            {{-- Logo bulat + badge daun --}}
            <div class="relative mx-auto mt-10 h-[200px] w-[200px]">
                <div class="flex h-full w-full items-center justify-center rounded-full bg-white shadow-[0_22px_45px_-22px_rgba(0,0,0,.45)]">
                    <x-logo class="h-[172px] w-[172px]" alt="Logo Perkoci Eatery" />
                </div>

                <div class="absolute -bottom-1 -right-1 flex h-[58px] w-[58px] items-center justify-center rounded-full bg-brand shadow-soft ring-4 ring-white">
                    <span class="ms ms-fill ms-wght text-[26px] leading-none text-lime" aria-hidden="true">eco</span>
                </div>
            </div>

            {{-- Judul --}}
            <h1 class="mt-10 text-center font-display text-[31px] font-bold leading-[1.2]">
                <span class="block text-cocoa">{{ $cover['greeting'] }}</span>
                <span class="block text-brand">{{ $cover['title'] }}</span>
            </h1>

            <p class="mt-3 text-center text-[10px] font-bold uppercase tracking-[0.3em] text-olive">
                {{ $cover['subtitle'] }}
            </p>

            {{-- Kartu info meja --}}
            <div class="mt-8 rounded-[26px] bg-white px-5 py-5 shadow-[0_22px_45px_-30px_rgba(61,43,31,.55)] ring-1 ring-cocoa/5">
                <span class="inline-flex items-center gap-2 rounded-full bg-butter px-3.5 py-2 text-[12px] font-bold text-cocoa">
                    <span class="ms ms-fill text-[15px] leading-none text-brand" aria-hidden="true">verified</span>
                    {{ $table['location'] }}
                </span>

                <p class="mt-3 flex items-center gap-2 text-[11.5px] font-medium text-cocoa/55">
                    <span class="ms text-[16px] leading-none text-brand" aria-hidden="true">qr_code_2</span>
                    {{ $table['connected_note'] }}
                </p>
            </div>

            {{-- Progress --}}
            <div class="mt-7">
                <div class="h-1.5 w-full overflow-hidden rounded-full bg-cocoa/10">
                    <div class="h-full rounded-full bg-brand transition-all duration-700" style="width: {{ $cover['progress'] }}%"></div>
                </div>

                <div class="mt-3 flex items-center justify-center gap-2">
                    <p class="text-[11px] font-medium text-cocoa/55">
                        {{ $cover['progress_label'] }}
                    </p>
                    <span class="flex items-end gap-1" aria-hidden="true">
                        <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-brand-dark" style="animation-delay: .1s"></span>
                        <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-brand-dark" style="animation-delay: .2s"></span>
                        <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-brand-soft" style="animation-delay: .3s"></span>
                    </span>
                </div>
            </div>

            {{-- TODO(figma): Cover – frame lanjutan --}}
            <div class="mt-6 text-center">
                <a href="{{ url($cover['redirect_to']) }}"
                    class="text-xs font-bold text-brand underline decoration-brand/30 underline-offset-4 transition hover:decoration-brand">
                    Masuk ke menu sekarang
                </a>
            </div>

            {{-- Footer --}}
            <footer class="mt-auto pt-10 pb-6 text-center text-[11px] font-medium tracking-wide text-cocoa/35">
                {{ config('perkoci.brand.footer') }}
            </footer>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var target = @json(config('perkoci.cover.redirect_to'));
            var delay = @json(config('perkoci.cover.redirect_delay') * 1000);

            window.setTimeout(function () {
                window.location.replace(target);
            }, delay);
        })();
    </script>
@endpush
