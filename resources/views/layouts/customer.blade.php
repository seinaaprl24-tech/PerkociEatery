@extends('layouts.app')

@section('body')
    @php
        $table = config('perkoci.table');
        $cartTotals = \App\Support\Perkoci::cartTotals();
        $backHref = $__env->yieldContent('back_href', url('/menu'));

        // Kalau halaman anak mendefinisikan section "bottomnav", nav tersebut
        // dipakai apa adanya (section kosong = tanpa bottom nav).
        $customNav = View::hasSection('bottomnav') ? $__env->yieldContent('bottomnav') : null;
        $hasBottomNav = $customNav === null ? true : (trim(strip_tags($customNav)) !== '');
    @endphp

    <div class="relative mx-auto flex min-h-screen w-full max-w-[430px] flex-col bg-cream shadow-[0_0_70px_-30px_rgba(61,43,31,.35)]">

        {{-- ============================ HEADER ============================ --}}
        @if (View::hasSection('header'))
            {{-- Halaman anak mengatur penuh header (section kosong = tanpa header) --}}
            @yield('header')
        @else
            <header class="sticky top-0 z-30 border-b border-cocoa/10 bg-cream/95 backdrop-blur">
                @if (View::hasSection('header_title'))
                    {{-- Varian "detail": tombol back + judul + pill meja --}}
                    <div class="flex items-center gap-3 px-4 py-3">
                        <a href="{{ $backHref }}"
                            class="no-tap-highlight flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sand text-cocoa transition hover:bg-cocoa/10"
                            aria-label="Kembali">
                            <span class="ms text-[18px] leading-none">arrow_back</span>
                        </a>

                        <p class="min-w-0 flex-1 truncate font-display text-[15px] font-bold leading-tight text-cocoa">
                            @yield('header_title')
                        </p>

                        <span class="pill shrink-0 bg-butter text-cocoa">
                            <span class="ms ms-wght text-[12px] leading-none">table_restaurant</span>
                            {{ $table['location'] }}
                        </span>
                    </div>
                @else
                    {{-- Varian default --}}
                    <div class="flex items-center gap-3 px-4 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-display text-[17px] font-bold leading-tight text-cocoa">
                                {{ config('perkoci.brand.name') }}
                            </p>
                            <p class="mt-0.5 flex items-center gap-1 truncate text-[11px] font-medium text-cocoa/55">
                                <span class="ms text-[14px] leading-none text-olive">table_restaurant</span>
                                {{ $table['location'] }}
                            </p>
                        </div>

                        <a href="{{ url('/') }}"
                            class="no-tap-highlight flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white shadow-soft ring-1 ring-cocoa/10"
                            aria-label="Perkoci Eatery">
                            <x-logo class="h-10 w-10" alt="" />
                        </a>
                    </div>
                @endif
            </header>
        @endif

        {{-- ============================ CONTENT =========================== --}}
        <main class="flex-1 {{ $hasBottomNav ? 'pb-[calc(160px+env(safe-area-inset-bottom))]' : '' }}">
            @yield('content')
        </main>

        {{-- ========================== BOTTOM NAV ========================== --}}
        @if ($customNav !== null)
            {!! $customNav !!}
        @else
            <nav class="fixed bottom-0 left-1/2 z-40 w-full max-w-[430px] -translate-x-1/2 border-t border-cocoa/10 bg-white/95 shadow-bar backdrop-blur">
                <div class="flex items-stretch justify-around px-2 pb-[env(safe-area-inset-bottom)]">
                    @foreach (config('perkoci.nav', []) as $item)
                        @php
                            $isActive = request()->routeIs($item['route'] ?? '__none__');
                            $badge = $item['key'] === 'keranjang' ? $cartTotals['count'] : ($item['badge'] ?? null);
                        @endphp
                        <a href="{{ url($item['href']) }}"
                            @if ($isActive) aria-current="page" @endif
                            class="no-tap-highlight relative flex flex-1 flex-col items-center gap-1 py-2.5 text-[10px] font-bold transition {{ $isActive ? 'text-brand' : 'text-cocoa/45' }}">

                            <span class="ms text-[22px] leading-none {{ $isActive ? 'ms-fill' : '' }}">{{ $item['icon'] }}</span>
                            <span>{{ $item['label'] }}</span>

                            @if (! empty($badge))
                                <span class="absolute left-1/2 top-1.5 ml-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-danger px-1 text-[9px] font-extrabold leading-none text-white">
                                    {{ $badge }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </nav>
        @endif
    </div>
@endsection
