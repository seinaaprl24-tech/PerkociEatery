{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
@props(['current' => null])

<div {{ $attributes->merge(['class' => 'mb-5 flex flex-col gap-3 rounded-2xl border border-dashed border-cocoa/25 bg-butter/30 px-4 py-3 lg:flex-row lg:items-center lg:justify-between']) }}>
    <div class="flex items-start gap-2.5">
        <span class="ms mt-0.5 text-[18px] leading-none text-olive" aria-hidden="true">construction</span>

        <div class="min-w-0">
            <p class="text-xs font-bold text-cocoa">Mode Preview — autentikasi belum terhubung</p>
            <p class="mt-0.5 text-[11px] leading-relaxed text-cocoa/60">{{ config('preview.notice') }}</p>
        </div>
    </div>

    <nav aria-label="Pilih preview peran" class="flex flex-wrap items-center gap-1.5">
        @foreach (config('preview.roles', []) as $key => $role)
            <a href="{{ url('/preview/'.$key) }}" aria-pressed="{{ $current === $key ? 'true' : 'false' }}"
                class="no-tap-highlight rounded-full px-3 py-1.5 text-[11px] font-bold transition {{ $current === $key ? 'bg-brand text-white' : 'bg-white/70 text-cocoa/60 hover:bg-white' }}">
                {{ $role['label'] }}
            </a>
        @endforeach

        <a href="{{ url('/login') }}"
            class="no-tap-highlight rounded-full bg-white/70 px-3 py-1.5 text-[11px] font-bold text-cocoa/60 transition hover:bg-white">
            Login
        </a>
    </nav>
</div>
