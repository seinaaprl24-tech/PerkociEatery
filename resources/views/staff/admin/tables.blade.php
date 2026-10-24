{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
{{-- TODO(figma): Admin – Meja & QR Code --}}
@extends('layouts.staff')

@section('title', $role['tables']['title'].' — Perkoci Eatery')

@section('staff_role', 'admin')
@section('active_nav', 'tables')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['tables']['title'] }}</p>
    </div>
@endsection

@section('content')
    @php
        $tables = collect($role['tables']['items']);
        $occupied = $tables->where('state', 'Terisi')->count();
        $available = $tables->where('state', 'Kosong')->count();
    @endphp

    <x-ui.preview-banner current="admin" />

    <x-ui.page-header :title="$role['tables']['title']" :subtitle="$role['tables']['description']">
        <x-ui.badge tone="butter" icon="science">Data Contoh</x-ui.badge>

        <button type="button" class="btn-primary btn-sm"
            data-toast-message="Cetak QR meja belum tersedia pada mode preview.">
            <span class="ms text-[16px] leading-none" aria-hidden="true">print</span>
            Cetak QR
        </button>
    </x-ui.page-header>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card label="Total Meja" :value="$tables->count()" icon="table_restaurant" tone="brand" hint="data contoh" />
        <x-ui.stat-card label="Terisi" :value="$occupied" icon="groups" tone="lime" hint="sedang dipakai" />
        <x-ui.stat-card label="Kosong" :value="$available" icon="event_seat" tone="butter" hint="siap dipakai" />
    </div>

    <x-ui.section class="mt-4" :title="$role['tables']['title']" :description="$role['tables']['description']">
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($role['tables']['items'] as $table)
                @php
                    $styles = match ($table['state']) {
                        'Terisi' => 'border-brand/20 bg-brand/10 text-brand',
                        'Reservasi' => 'border-butter bg-butter/40 text-olive',
                        default => 'border-cocoa/10 bg-white text-cocoa/45',
                    };
                @endphp

                <div class="flex items-center gap-3 rounded-xl border px-3.5 py-3 {{ $styles }}">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/70 font-display text-xs font-bold">
                        {{ $table['code'] }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-bold">Meja {{ $table['code'] }}</p>
                        <p class="mt-0.5 truncate text-[10px] opacity-70">{{ $table['area'] }}</p>
                    </div>

                    <span class="shrink-0 text-[9px] font-bold uppercase tracking-[0.06em]">{{ $table['state'] }}</span>
                </div>
            @endforeach
        </div>
    </x-ui.section>

    {{-- TODO(figma): Admin – Meja & QR Code --}}
    <x-ui.design-note class="mt-4" title="Menunggu frame Figma: Admin – Meja & QR Code">
        Ukuran kartu meja, area, dan aksi QR menunggu referensi Figma.
    </x-ui.design-note>
@endsection
