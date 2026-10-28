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
        <x-auth.login-form :preview="false" />
    </div>

    <p class="mt-8 text-center text-[11px] font-medium leading-relaxed text-cocoa/35">
        © {{ date('Y') }} {{ config('perkoci.brand.name') }}<br>
        {{ config('perkoci.brand.footer') }}
    </p>
@endsection
