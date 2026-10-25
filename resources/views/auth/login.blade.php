@extends('layouts.auth')

@section('title', 'Masuk — Portal Internal Perkoci Eatery')

@section('content')
    {{-- TODO(figma): Login – komposisi form --}}
    <span class="pill bg-sand text-cocoa/55">Portal Internal</span>

    <h1 class="mt-4 font-display text-[28px] font-bold leading-tight text-cocoa">Masuk ke akun kamu</h1>

    <p class="mt-2 text-sm leading-relaxed text-cocoa/55">
        Gunakan akun yang diberikan manager untuk mengakses dashboard kasir, admin, atau manager.
    </p>

    <div class="mt-7">
        <x-auth.login-form :preview="true" />
    </div>

    <p class="mt-6 flex items-start gap-2 rounded-xl bg-butter/40 px-3.5 py-3 text-[11px] leading-relaxed text-cocoa/60">
        <span class="ms mt-px text-[15px] leading-none text-olive" aria-hidden="true">info</span>
        <span>
            <strong class="font-bold text-cocoa/80">Mode preview.</strong>
            Autentikasi belum terhubung — halaman ini hanya menampilkan UI. Mengirim form tidak akan membuat sesi
            login atau mengubah data apa pun.
        </span>
    </p>

    {{-- Tautan preview dashboard (tanpa autentikasi) --}}
    {{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
    <div class="mt-6 rounded-2xl border border-dashed border-cocoa/20 bg-white/60 p-4">
        <div class="flex items-center justify-between gap-2">
            <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-cocoa/45">Preview Dashboard</p>
            <span class="pill-sm bg-butter text-cocoa">Sementara</span>
        </div>
        <p class="mt-1 text-[11px] leading-relaxed text-cocoa/45">
            Tautan ini hanya membuka kerangka tampilan, tanpa login dan tanpa sesi.
        </p>

        <div class="mt-3 space-y-1.5">
            @foreach (config('preview.roles', []) as $key => $role)
                <a href="{{ url('/preview/'.$key) }}"
                    class="flex items-center justify-between gap-3 rounded-xl bg-sand/80 px-3.5 py-2.5 text-xs font-bold text-cocoa transition hover:bg-cocoa/10">
                    <span class="flex min-w-0 items-center gap-2">
                        <span class="ms text-[16px] leading-none text-cocoa/40" aria-hidden="true">dashboard</span>
                        <span class="truncate">{{ $role['title'] }}</span>
                    </span>
                    <span class="ms text-[16px] leading-none text-cocoa/30" aria-hidden="true">arrow_forward</span>
                </a>
            @endforeach
        </div>
    </div>

    <p class="mt-8 text-center text-[11px] font-medium leading-relaxed text-cocoa/35">
        © {{ date('Y') }} {{ config('perkoci.brand.name') }}<br>
        {{ config('perkoci.brand.footer') }}
    </p>
@endsection
