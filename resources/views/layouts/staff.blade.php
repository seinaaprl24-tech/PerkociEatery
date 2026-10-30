@extends('layouts.app')

@section('body')
    @php
        use Illuminate\Support\Facades\Route;
        use Illuminate\Support\Str;

        $staff = config('perkoci.staff');
        $roleKey = trim($__env->yieldContent('staff_role')) ?: 'kasir';
        $activeKey = trim($__env->yieldContent('active_nav')) ?: 'dashboard';
        $roleLabel = collect($staff['roles'])->firstWhere('key', $roleKey)['label'] ?? ucfirst($roleKey);
        $navItems = array_values(array_filter(
            $staff['nav'],
            fn ($item) => in_array($roleKey, $item['roles'] ?? [], true),
        ));

        // Pengguna yang sedang login (bukan data contoh lagi).
        $authUser = auth()->user();
        $displayName = $authUser?->nama ?: ($authUser?->username ?: 'Pengguna');
        $user = [
            'name' => $displayName,
            'initials' => Str::of($displayName)
                ->explode(' ')
                ->filter()
                ->take(2)
                ->map(fn ($word) => Str::upper(Str::substr($word, 0, 1)))
                ->implode(''),
            'shift' => $authUser ? '@'.$authUser->username : '',
        ];
    @endphp

    {{-- Token API untuk JavaScript dashboard (dibuat saat login, dihapus saat logout) --}}
    @push('head')
        <meta name="api-token" content="{{ session('api_token') }}">
    @endpush

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
                            $routeName = $roleKey.'.'.$item['key'];
                            $href = Route::has($routeName)
                                ? route($routeName)
                                : ($item['href'] ?? '#');
                        @endphp

                        <a href="{{ $href }}" @if ($isActive) aria-current="page" @endif
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $isActive ? 'bg-brand text-white shadow-soft' : 'text-cocoa/70 hover:bg-sand' }}">
                            <span class="ms text-[20px] leading-none {{ $isActive ? 'ms-fill' : '' }}" aria-hidden="true">{{ $item['icon'] }}</span>
                            <span class="flex-1 truncate">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                {{-- Pengguna + tombol keluar --}}
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

                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="btn-secondary btn-sm w-full">
                            <span class="ms text-[16px] leading-none" aria-hidden="true">logout</span>
                            Keluar
                        </button>
                    </form>

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

    {{-- Helper pemanggil API untuk halaman staff: StaffApi.request('GET', '/pesanan') --}}
    @push('scripts')
        <script>
            window.StaffApi = (function () {
                var meta = document.querySelector('meta[name="api-token"]');
                var token = meta ? meta.getAttribute('content') : '';

                function toast(message) {
                    if (window.perkociToast) {
                        window.perkociToast(message);
                    }
                }

                function firstError(data) {
                    if (data && data.errors) {
                        for (var key in data.errors) {
                            if (data.errors[key] && data.errors[key][0]) {
                                return data.errors[key][0];
                            }
                        }
                    }

                    return (data && data.message) || 'Terjadi kesalahan. Coba lagi.';
                }

                function unauthorized() {
                    var storeKey = 'staff_reload_at';
                    var last = 0;

                    try {
                        last = Number(window.sessionStorage.getItem(storeKey) || 0);
                    } catch (e) { /* abaikan */ }

                    if (Date.now() - last > 10000) {
                        try {
                            window.sessionStorage.setItem(storeKey, String(Date.now()));
                        } catch (e) { /* abaikan */ }

                        window.location.reload();
                    } else {
                        window.location.href = '/login?expired=1';
                    }
                }

                /* path diawali "/" tanpa "/api", contoh: '/pesanan' */
                function request(method, path, body) {
                    var options = {
                        method: method,
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token
                        }
                    };

                    if (body !== undefined && body !== null) {
                        options.headers['Content-Type'] = 'application/json';
                        options.body = JSON.stringify(body);
                    }

                    return fetch('/api' + path, options).then(function (response) {
                        return response.json()
                            .catch(function () { return {}; })
                            .then(function (data) {
                                if (response.status === 401) {
                                    unauthorized();
                                    throw new Error('Sesi berakhir.');
                                }

                                if (response.status === 403) {
                                    toast('Kamu tidak punya akses untuk aksi ini.');
                                    throw new Error('Tidak punya akses.');
                                }

                                if (!response.ok) {
                                    var error = new Error(firstError(data));
                                    error.status = response.status;
                                    error.data = data;
                                    throw error;
                                }

                                return data;
                            });
                    });
                }

                return { request: request };
            })();
        </script>
    @endpush
@endsection
