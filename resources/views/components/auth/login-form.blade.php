@props([
    'action' => route('login.attempt'),
    'emailLabel' => 'Username',
    'emailPlaceholder' => 'Masukkan username',
    'submitLabel' => 'Masuk',
    'error' => null,
    'preview' => false,
])

@php
    // Error dari prop (jika ada) atau dari validasi/login backend.
    $pesanError = $error ?: ($errors->first('username') ?: $errors->first('password'));
@endphp

<form method="POST" action="{{ $action }}" class="space-y-4" novalidate
    @if ($preview) data-preview-auth @endif>
    @csrf

    @if ($pesanError)
        <x-auth.alert type="error">{{ $pesanError }}</x-auth.alert>
    @elseif ($preview)
        <div data-login-alert-wrap class="hidden">
            <x-auth.alert type="error" tabindex="-1" data-login-alert>
                Mode preview: autentikasi belum terhubung.
            </x-auth.alert>
        </div>
    @endif

    <div>
        <x-ui.field id="login-email" name="username" type="text" :label="$emailLabel" :placeholder="$emailPlaceholder"
            icon="person" required autocomplete="username" autocapitalize="none" spellcheck="false"
            :value="old('username')" />

        <p id="login-email-error" class="field-error hidden" role="alert"></p>
    </div>

    <div class="relative">
        <div class="mb-1.5">
            <label for="login-password" class="field-label mb-0">Password</label>
        </div>

        <div class="relative">
            <span class="ms pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[18px] leading-none text-cocoa/40"
                aria-hidden="true">lock</span>

            <input id="login-password" name="password" type="password" required autocomplete="current-password"
                placeholder="Masukkan password" class="field pl-11 pr-12">

            <button type="button" data-password-toggle="login-password" aria-label="Tampilkan password" aria-pressed="false"
                class="absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-cocoa/45 transition hover:bg-cocoa/10 hover:text-cocoa focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40">
                <span class="ms text-[18px] leading-none" aria-hidden="true">visibility</span>
            </button>
        </div>

        <p id="login-password-error" class="field-error hidden" role="alert"></p>

        <a href="#" data-toast-message="Hubungi admin untuk mengatur ulang password"
            class="absolute right-0 top-0 rounded text-[11px] font-semibold text-cocoa/40 transition hover:text-brand focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40">
            Lupa password?
        </a>
    </div>

    <label for="login-remember" class="flex items-center gap-2.5 text-xs font-medium text-cocoa/65">
        <input id="login-remember" type="checkbox" name="remember" value="1"
            class="h-4 w-4 rounded border-cocoa/20 accent-brand focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40 focus-visible:ring-offset-1">
        Ingat saya di perangkat ini
    </label>

    <button type="submit"
        class="btn-primary w-full focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40 focus-visible:ring-offset-2">
        {{ $submitLabel }}
        <span class="ms text-[18px] leading-none" aria-hidden="true">arrow_forward</span>
    </button>
</form>
