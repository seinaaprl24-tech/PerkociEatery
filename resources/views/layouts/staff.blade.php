@extends('layouts.app')

@section('body')
    @php
        $staff = config('perkoci.staff');
        $roleKey = trim($__env->yieldContent('staff_role')) ?: 'kasir';
        $activeKey = trim($__env->yieldContent('active_nav')) ?: 'dashboard';
        $roleLabel = collect($staff['roles'])->firstWhere('key', $roleKey)['label'] ?? ucfirst($roleKey);
        $navItems = array_values(array_filter(
            $staff['nav'],
            fn ($item) => in_array($roleKey, $item['roles'] ?? [], true),
        ));
        $user = $staff['user'];
    @endphp

    <div class="min-h-screen bg-cream">

        {{-- Backdrop drawer (mobile) --}}
        <div data-drawer-backdrop class="fixed inset-0 z-40 hidden bg-cocoa/40 backdrop-blur-sm lg:hidden"
            aria-hidden="true"></div>

        <div class="flex min-h-screen">

            {{-- ============================ SIDEBAR ============================ --}}
            <aside data-drawer-panel
                class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-cocoa/10 bg-cream shadow-card transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:z-10 lg:h-screen lg:w-60 lg:shrink-0 lg:translate-x-0 lg:shadow-none">

                {{-- Brand --}}
                <div class="flex items-center gap-3 border-b border-cocoa/10 px-4 py-4">
                    <a href="{{ url('/') }}" class="flex min-w-0 flex-1 items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white shadow-soft ring-1 ring-cocoa/10">
                            <x-logo class="h-11 w-11" alt="" />
                        </span>

                        <span class="min-w-0">
                            <span class="block truncate font-display text-[16px] font-bold leading-tight text-cocoa">
                                {{ config('perkoci.brand.name') }}
                            </span>
                            <span class="mt-0.5 block truncate text-[9.5px] font-bold uppercase tracking-[0.12em] text-cocoa/45">
                                {{ $roleLabel }} Panel
                            </span>
                        </span>
                    </a>

                    <button type="button" data-drawer-close class="btn-icon lg:hidden" aria-label="Tutup menu">
                        <span class="ms text-[20px] leading-none" aria-hidden="true">close</span>
                    </button>
                </div>

                {{-- Menu --}}
                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Navigasi {{ $roleLabel }}">
                    @foreach ($navItems as $item)
                        @php
                            $isActive = $item['key'] === $activeKey;
                            $routeName = $item['key'] === 'dashboard'
                                ? 'preview.'.$roleKey
                                : 'preview.'.$roleKey.'.'.$item['key'];
                            $href = \Illuminate\Support\Facades\Route::has($routeName)
                                ? route($routeName)
                                : ($item['href'] ?? '#');
                        @endphp

                        <a href="{{ $href }}" @if ($isActive) aria-current="page" @endif
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $isActive ? 'bg-brand text-white shadow-soft' : 'text-cocoa/70 hover:bg-sand' }}">
                            <span class="ms text-[20px] leading-none {{ $isActive ? 'ms-fill' : '' }}" aria-hidden="true">{{ $item['icon'] }}</span>
                            <span class="flex-1 truncate">{{ $item['label'] }}</span>

                            @if (! empty($item['badge']))
                                <span class="flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[10px] font-extrabold leading-none {{ $isActive ? 'bg-white/20 text-white' : 'bg-danger text-white' }}">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                {{-- Pengguna + slot footer --}}
                <div class="border-t border-cocoa/10 p-3">
                    <div class="flex items-center gap-2.5 rounded-xl bg-sand/70 px-2.5 py-2">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand text-[11px] font-extrabold tracking-wide text-white">
                            {{ $user['initials'] }}
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-xs font-bold text-cocoa">{{ $user['name'] }}</span>
                            <span class="mt-0.5 block truncate text-[10px] text-cocoa/45">{{ $user['shift'] }}</span>
                        </span>
                    </div>

                    @yield('sidebar_footer')
                </div>
            </aside>

            {{-- ====================== AREA KONTEN KANAN ======================= --}}
            <div class="flex min-w-0 flex-1 flex-col">

                {{-- Topbar --}}
                <header class="sticky top-0 z-30 border-b border-cocoa/10 bg-cream/95 backdrop-blur">
                    <div class="flex items-center gap-3 px-4 py-3 lg:px-7 lg:py-3.5">
                        <button type="button" data-drawer-toggle class="btn-icon -ml-1 lg:hidden" aria-label="Buka menu">
                            <span class="ms text-[22px] leading-none" aria-hidden="true">menu</span>
                        </button>

                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            @yield('topbar_left')
                        </div>

                        <div class="flex shrink-0 items-center gap-2.5">
                            @yield('topbar_right')

                            <span class="badge badge-brand hidden sm:inline-flex">{{ $roleLabel }}</span>

                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand text-[10px] font-extrabold text-white"
                                aria-label="{{ $user['name'] }}">
                                {{ $user['initials'] }}
                            </span>
                        </div>
                    </div>
                </header>

                {{-- Konten --}}
                <main class="flex-1 px-4 py-5 lg:px-7 lg:py-6">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>
@endsection
