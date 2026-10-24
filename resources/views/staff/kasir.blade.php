{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
@extends('layouts.staff')

@section('title', $role['title'].' — Perkoci Eatery')

@section('staff_role', 'kasir')
@section('active_nav', 'dashboard')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['title'] }}</p>
    </div>
@endsection

@section('content')
    <x-ui.preview-banner current="kasir" />

    <x-ui.page-header :title="$role['title']" :subtitle="$role['subtitle']">
        <x-ui.badge tone="butter" icon="science">Data Contoh</x-ui.badge>
        <x-ui.badge tone="lime" icon="schedule">
            {{ $role['shift']['label'] }} · {{ $role['shift']['value'] }}
        </x-ui.badge>
    </x-ui.page-header>

    {{-- Ringkasan angka --}}
    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        @foreach ($role['stats'] as $stat)
            <x-ui.stat-card :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :tone="$stat['tone']"
                :hint="$stat['hint'] ?? null" />
        @endforeach
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        {{-- Pesanan masuk --}}
        <x-ui.section class="xl:col-span-2" :title="$role['queue']['title']" :description="$role['queue']['description']">
            <x-slot:actions>
                <x-ui.badge tone="danger">{{ count($role['queue']['rows']) }} aktif</x-ui.badge>
            </x-slot:actions>

            <ul class="space-y-2.5">
                @foreach ($role['queue']['rows'] as $row)
                    <li class="flex flex-wrap items-center gap-3 rounded-xl border border-cocoa/10 bg-cream/40 px-3.5 py-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sand text-cocoa/50">
                            <span class="ms text-[18px] leading-none" aria-hidden="true">table_restaurant</span>
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold text-cocoa">
                                {{ $row['table'] }}
                                <span class="ml-1 font-medium text-cocoa/40">· {{ $row['id'] }}</span>
                            </p>
                            <p class="mt-0.5 truncate text-[11px] text-cocoa/50">{{ $row['menu'] }}</p>
                            <p class="mt-0.5 text-[10px] text-cocoa/35">{{ $row['items'] }} item · {{ $row['waited'] }} lalu</p>
                        </div>

                        <p class="shrink-0 font-display text-sm font-bold text-cocoa">
                            {{ \App\Support\Perkoci::rupiah($row['total']) }}
                        </p>

                        <x-ui.badge :tone="$row['status']['tone']">{{ $row['status']['label'] }}</x-ui.badge>

                        <button type="button" class="btn-secondary btn-sm shrink-0"
                            data-toast-message="Alur pesanan belum terhubung pada mode preview.">
                            Proses
                        </button>
                    </li>
                @endforeach
            </ul>

            {{-- TODO(figma): Dashboard Kasir – kartu pesanan masuk --}}
            <x-ui.design-note class="mt-4">
                Bentuk kartu pesanan, aksi cepat, dan status final menunggu frame Figma.
            </x-ui.design-note>
        </x-ui.section>

        <div class="space-y-4">
            {{-- Pembayaran --}}
            <x-ui.section :title="$role['payments']['title']" :description="$role['payments']['description']">
                <ul class="space-y-2.5">
                    @foreach ($role['payments']['rows'] as $row)
                        <li class="rounded-xl border border-cocoa/10 px-3.5 py-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-bold text-cocoa">{{ $row['id'] }}</p>
                                <x-ui.badge tone="neutral">{{ $row['method'] }}</x-ui.badge>
                            </div>

                            <p class="mt-1 text-[11px] text-cocoa/45">{{ $row['table'] }}</p>

                            <div class="mt-2 flex items-center justify-between gap-3">
                                <p class="font-display text-sm font-bold text-cocoa">
                                    {{ \App\Support\Perkoci::rupiah($row['total']) }}
                                </p>

                                <button type="button" class="btn-primary btn-sm"
                                    data-toast-message="Pembayaran belum terhubung pada mode preview.">
                                    Terima
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-ui.section>

            {{-- Preview struk pembayaran (contoh) --}}
            <x-ui.section :title="$role['receipt']['title']" :description="$role['receipt']['description']">
                <div class="rounded-xl border border-dashed border-cocoa/25 bg-white px-3.5 py-3.5 text-[11px]">
                    <div class="text-center">
                        <p class="font-display text-[13px] font-bold text-cocoa">{{ config('perkoci.brand.name') }}</p>
                        <p class="mt-0.5 text-[10px] leading-relaxed text-cocoa/45">
                            {{ implode(' · ', $role['receipt']['meta']) }}
                        </p>
                    </div>

                    <div class="my-2.5 border-t border-dashed border-cocoa/20"></div>

                    <ul class="space-y-1.5">
                        @foreach ($role['receipt']['items'] as $item)
                            <li class="flex items-start justify-between gap-2">
                                <span class="min-w-0 text-cocoa/70">
                                    <span class="font-semibold text-cocoa">{{ $item['qty'] }}×</span> {{ $item['name'] }}
                                </span>
                                <span class="shrink-0 font-semibold text-cocoa">
                                    {{ \App\Support\Perkoci::rupiah($item['price'] * $item['qty']) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="my-2.5 border-t border-dashed border-cocoa/20"></div>

                    <dl class="space-y-1">
                        <div class="flex items-center justify-between">
                            <dt class="text-cocoa/55">Total</dt>
                            <dd class="font-display text-[13px] font-bold text-cocoa">
                                {{ \App\Support\Perkoci::rupiah($role['receipt']['total']) }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-cocoa/55">Tunai</dt>
                            <dd class="font-semibold text-cocoa">
                                {{ \App\Support\Perkoci::rupiah($role['receipt']['paid']) }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-dashed border-cocoa/20 pt-1">
                            <dt class="font-bold text-cocoa">Kembalian</dt>
                            <dd class="font-display text-[13px] font-bold text-brand">
                                {{ \App\Support\Perkoci::rupiah($role['receipt']['change']) }}
                            </dd>
                        </div>
                    </dl>

                    <p class="mt-2.5 text-center text-[9.5px] leading-relaxed text-cocoa/35">
                        {{ $role['receipt']['note'] }}
                    </p>
                </div>
            </x-ui.section>

            {{-- Aksi cepat --}}
            <x-ui.section title="Aksi Cepat" description="Tombol contoh untuk kerangka UI.">
                <ul class="space-y-2">
                    @foreach ($role['quick_actions'] as $action)
                        <li>
                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl border border-cocoa/10 bg-cream/40 px-3.5 py-3 text-left text-xs font-bold text-cocoa transition hover:bg-sand"
                                data-toast-message="{{ $action['label'] }} belum tersedia pada mode preview.">
                                <span class="ms text-[18px] leading-none text-brand" aria-hidden="true">{{ $action['icon'] }}</span>
                                <span class="flex-1">{{ $action['label'] }}</span>
                                <span class="ms text-[16px] leading-none text-cocoa/25" aria-hidden="true">chevron_right</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </x-ui.section>
        </div>
    </div>

    {{-- Status meja --}}
    <x-ui.section class="mt-4" :title="$role['tables']['title']" :description="$role['tables']['description']">
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-3 text-[10px] font-semibold text-cocoa/45">
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-brand"></span>Terisi</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-butter"></span>Reservasi</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-cocoa/20"></span>Kosong</span>
            </div>
        </x-slot:actions>

        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-6">
            @foreach ($role['tables']['items'] as $table)
                @php
                    $styles = match ($table['state']) {
                        'Terisi' => 'border-brand/20 bg-brand/10 text-brand',
                        'Reservasi' => 'border-butter bg-butter/40 text-olive',
                        default => 'border-cocoa/10 bg-white text-cocoa/40',
                    };
                @endphp

                <div class="rounded-xl border px-2 py-3 text-center {{ $styles }}">
                    <p class="font-display text-sm font-bold leading-none">{{ $table['code'] }}</p>
                    <p class="mt-1 text-[9px] font-bold uppercase tracking-[0.06em]">{{ $table['state'] }}</p>
                </div>
            @endforeach
        </div>
    </x-ui.section>

    {{-- TODO(figma): Dashboard Kasir - frame utama --}}
    <x-ui.design-note class="mt-4" title="Catatan Tahap 2 — Dashboard Kasir">
        {{ $preview['figma_note'] }}
    </x-ui.design-note>
@endsection
