@extends('layouts.app')

@section('body')
    <div class="flex min-h-screen flex-col lg:flex-row">

        {{-- ======================= PANEL BRAND (DESKTOP) ======================= --}}
        {{-- TODO(figma): Login – panel kiri --}}
        <aside class="relative hidden overflow-hidden bg-brand px-12 py-12 text-white lg:flex lg:w-[46%] lg:flex-col">
            <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-lime/10 blur-2xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-white/5 blur-2xl" aria-hidden="true"></div>

            <a href="{{ url('/') }}" class="relative inline-flex items-center gap-3">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white ring-1 ring-white/30">
                    <x-logo class="h-12 w-12" alt="" />
                </span>

                <span class="min-w-0">
                    <span class="block truncate font-display text-[17px] font-bold leading-tight">{{ config('perkoci.brand.name') }}</span>
                    <span class="mt-0.5 block truncate text-[10px] font-semibold uppercase tracking-[0.16em] text-lime">{{ config('perkoci.brand.tagline') }}</span>
                </span>
            </a>

            <div class="relative mt-auto max-w-md pt-16">
                <span class="pill bg-white/15 text-white ring-1 ring-inset ring-white/25">Portal Internal</span>

                <h1 class="mt-5 text-balance font-display text-[32px] font-bold leading-[1.15]">
                    Satu tempat untuk mengelola layanan Perkoci.
                </h1>

                <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/60">
                    Pantau pesanan masuk, kelola menu, dan siapkan laporan penjualan langsung dari dashboard.
                </p>
            </div>

            <div class="relative mt-10 flex flex-wrap gap-2">
                @foreach (config('perkoci.staff.roles', []) as $role)
                    <span class="pill bg-white/10 text-white/75 ring-1 ring-inset ring-white/15">{{ $role['label'] }}</span>
                @endforeach
            </div>
        </aside>

        {{-- ============================ AREA FORM ============================ --}}
        <main class="flex flex-1 items-center justify-center px-5 py-10 sm:px-8 lg:py-14">
            <div class="w-full max-w-[380px]">
                {{-- Brand ringkas (mobile) --}}
                <a href="{{ url('/') }}" class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white shadow-soft ring-1 ring-cocoa/10">
                        <x-logo class="h-11 w-11" alt="" />
                    </span>

                    <span class="min-w-0">
                        <span class="block truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ config('perkoci.brand.name') }}</span>
                        <span class="mt-0.5 block truncate text-[9px] font-bold uppercase tracking-[0.14em] text-cocoa/45">{{ config('perkoci.brand.tagline') }}</span>
                    </span>
                </a>

                @yield('content')
            </div>
        </main>
    </div>
@endsection
