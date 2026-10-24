{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
@extends('layouts.staff')

@section('title', $role['title'].' — Perkoci Eatery')

@section('staff_role', 'admin')
@section('active_nav', 'dashboard')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['title'] }}</p>
    </div>
@endsection

@section('content')
    <x-ui.preview-banner current="admin" />

    <x-ui.page-header :title="$role['title']" :subtitle="$role['subtitle']">
        <x-ui.badge tone="butter" icon="science">Data Contoh</x-ui.badge>

        <button type="button" class="btn-primary btn-sm"
            data-toast-message="Form tambah menu belum tersedia pada mode preview.">
            <span class="ms text-[16px] leading-none" aria-hidden="true">add</span>
            Tambah Menu
        </button>
    </x-ui.page-header>

    {{-- Ringkasan angka --}}
    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($role['stats'] as $stat)
            <x-ui.stat-card :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :tone="$stat['tone']"
                :hint="$stat['hint'] ?? null" />
        @endforeach
    </div>

    {{-- Kelola menu --}}
    <x-ui.section class="mt-4" :title="$role['menu']['title']" :description="$role['menu']['description']" flush>
        <x-slot:actions>
            <x-ui.badge tone="neutral">{{ count($role['menu']['rows']) }} contoh</x-ui.badge>

            <button type="button" class="btn-secondary btn-sm" data-toast-message="Pencarian menu belum tersedia pada mode preview.">
                <span class="ms text-[16px] leading-none" aria-hidden="true">search</span>
                Cari
            </button>
        </x-slot:actions>

        <x-ui.table class="rounded-none border-0" :head="['Menu', 'Kategori', 'Harga', 'Status', 'Aksi']">
            @foreach ($role['menu']['rows'] as $row)
                <tr>
                    @php $menuItem = \App\Support\Perkoci::menuBySlug($row['slug'] ?? ''); @endphp

                    <td>
                        <div class="flex items-center gap-2.5">
                            <span class="h-9 w-9 shrink-0 overflow-hidden rounded-lg bg-sand">
                                @if ($menuItem)
                                    <x-menu-thumb :item="$menuItem" size="text-[16px]" width="72" height="72" />
                                @endif
                            </span>
                            <span class="font-semibold text-cocoa">{{ $row['name'] }}</span>
                        </div>
                    </td>
                    <td class="whitespace-nowrap">{{ $row['category'] }}</td>
                    <td class="whitespace-nowrap font-semibold text-cocoa">
                        {{ \App\Support\Perkoci::rupiah($row['price']) }}
                        <span class="text-[10px] font-medium text-cocoa/40">/ {{ $row['unit'] ?? 'porsi' }}</span>
                    </td>
                    <td>
                        @if ($row['available'])
                            <x-ui.badge tone="lime">Tersedia</x-ui.badge>
                        @else
                            <x-ui.badge tone="neutral">Nonaktif</x-ui.badge>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-1">
                            <button type="button" class="btn-icon h-8 w-8"
                                data-toast-message="Edit menu belum tersedia pada mode preview."
                                aria-label="Edit {{ $row['name'] }}">
                                <span class="ms text-[16px] leading-none" aria-hidden="true">edit</span>
                            </button>

                            <button type="button" class="btn-icon h-8 w-8 hover:bg-danger/10 hover:text-danger"
                                data-toast-message="Hapus menu belum tersedia pada mode preview."
                                aria-label="Hapus {{ $row['name'] }}">
                                <span class="ms text-[16px] leading-none" aria-hidden="true">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </x-ui.section>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        {{-- Stok menipis --}}
        <x-ui.section :title="$role['low_stock']['title']" :description="$role['low_stock']['description']">
            <ul class="space-y-2.5">
                @foreach ($role['low_stock']['rows'] as $row)
                    @php
                        $tone = $row['tone'] === 'danger' ? 'bg-danger' : 'bg-butter';
                    @endphp

                    <li class="flex items-center gap-3 rounded-xl border border-cocoa/10 px-3.5 py-3">
                        <span class="h-2 w-2 shrink-0 rounded-full {{ $tone }}"></span>
                        <p class="min-w-0 flex-1 truncate text-xs font-semibold text-cocoa">{{ $row['name'] }}</p>
                        <p class="shrink-0 text-[11px] font-bold text-cocoa/45">{{ $row['left'] }}</p>

                        <button type="button" class="btn-secondary btn-sm shrink-0"
                            data-toast-message="Restock belum tersedia pada mode preview.">
                            Restock
                        </button>
                    </li>
                @endforeach
            </ul>
        </x-ui.section>

        {{-- Pengguna internal --}}
        <x-ui.section :title="$role['users']['title']" :description="$role['users']['description']">
            <ul class="divide-y divide-cocoa/10">
                @foreach ($role['users']['rows'] as $row)
                    <li class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sand text-[10px] font-extrabold text-cocoa/55">
                            {{ strtoupper(substr($row['name'], 0, 1)) }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold text-cocoa">{{ $row['name'] }}</p>
                            <p class="mt-0.5 text-[11px] text-cocoa/45">{{ $row['role'] }}</p>
                        </div>

                        <x-ui.badge :tone="$row['status']['tone']">{{ $row['status']['label'] }}</x-ui.badge>
                    </li>
                @endforeach
            </ul>
        </x-ui.section>
    </div>

    {{-- Meja & QR --}}
    <x-ui.section class="mt-4" :title="$role['tables']['title']" :description="$role['tables']['description']">
        <x-slot:actions>
            <x-ui.badge tone="neutral">{{ count($role['tables']['items']) }} meja</x-ui.badge>
        </x-slot:actions>

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

        {{-- TODO(figma): Dashboard Admin – kartu meja & QR --}}
        <x-ui.design-note class="mt-4">
            Ukuran kartu, aksi QR, dan status meja final menunggu frame Figma.
        </x-ui.design-note>
    </x-ui.section>

    {{-- TODO(figma): Dashboard Admin - frame utama --}}
    <x-ui.design-note class="mt-4" title="Catatan Tahap 2 — Dashboard Admin">
        {{ $preview['figma_note'] }}
    </x-ui.design-note>
@endsection
