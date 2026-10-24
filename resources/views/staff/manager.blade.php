{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
@extends('layouts.staff')

@section('title', $role['title'].' — Perkoci Eatery')

@section('staff_role', 'manager')
@section('active_nav', 'dashboard')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['title'] }}</p>
    </div>
@endsection

@section('content')
    <x-ui.preview-banner current="manager" />

    <x-ui.page-header :title="$role['title']" :subtitle="$role['subtitle']">
        <x-ui.badge tone="butter" icon="science">Data Contoh</x-ui.badge>

        <button type="button" class="btn-secondary btn-sm"
            data-toast-message="Filter tanggal belum tersedia pada mode preview.">
            <span class="ms text-[16px] leading-none" aria-hidden="true">calendar_month</span>
            Hari Ini
        </button>
    </x-ui.page-header>

    {{-- Ringkasan angka --}}
    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($role['stats'] as $stat)
            <x-ui.stat-card :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :tone="$stat['tone']"
                :hint="$stat['hint'] ?? null" :trend="$stat['trend'] ?? null" :up="$stat['trend_up'] ?? true" />
        @endforeach
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        {{-- Grafik (kerangka) --}}
        <x-ui.section class="xl:col-span-2" :title="$role['chart']['title']" :description="$role['chart']['description']">
            <x-slot:actions>
                <x-ui.badge tone="neutral">Kerangka Grafik</x-ui.badge>
            </x-slot:actions>

            <div class="flex items-baseline justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-cocoa/45">Total 7 Hari</p>
                    <p class="mt-1 font-display text-[22px] font-bold leading-none text-cocoa">{{ $role['chart']['total'] }}</p>
                </div>
                <p class="text-[11px] text-cocoa/40">Skala relatif</p>
            </div>

            <div class="mt-5 flex items-end justify-between gap-1.5 sm:gap-3">
                @foreach ($role['chart']['bars'] as $bar)
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                        <div class="flex h-36 w-full items-end">
                            <div class="w-full rounded-t-md bg-gradient-to-t from-brand to-brand/55"
                                style="height: {{ max(6, min(100, (int) $bar['value'])) }}%"></div>
                        </div>
                        <span class="text-[10px] font-semibold text-cocoa/40">{{ $bar['label'] }}</span>
                    </div>
                @endforeach
            </div>

            {{-- TODO(figma): Dashboard Manager – grafik tren penjualan --}}
            <x-ui.design-note class="mt-4">
                Jenis grafik, legend, dan rentang tanggal final menunggu frame Figma.
            </x-ui.design-note>
        </x-ui.section>

        {{-- Perlu perhatian --}}
        <x-ui.section :title="$role['attention']['title']" :description="$role['attention']['description']">
            <ul class="space-y-2.5">
                @foreach ($role['attention']['items'] as $item)
                    @php
                        $tone = match ($item['tone']) {
                            'danger' => 'bg-danger/10 text-danger',
                            'butter' => 'bg-butter/60 text-olive',
                            default => 'bg-cocoa/10 text-cocoa',
                        };
                    @endphp

                    <li class="flex items-start gap-2.5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $tone }}">
                            <span class="ms text-[16px] leading-none" aria-hidden="true">{{ $item['icon'] }}</span>
                        </span>

                        <p class="min-w-0 flex-1 pt-1.5 text-xs leading-snug text-cocoa/65">{{ $item['label'] }}</p>
                    </li>
                @endforeach
            </ul>
        </x-ui.section>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        {{-- Pesanan terbaru --}}
        <x-ui.section class="xl:col-span-2" :title="$role['orders']['title']" :description="$role['orders']['description']" flush>
            <x-slot:actions>
                <x-ui.badge tone="neutral">{{ count($role['orders']['rows']) }} contoh</x-ui.badge>
            </x-slot:actions>

            <x-ui.table class="rounded-none border-0"
                :head="['Pesanan', 'Meja', 'Pelanggan', 'Menu', 'Total', 'Status']">
                @foreach ($role['orders']['rows'] as $row)
                    <tr>
                        <td class="font-semibold text-cocoa">{{ $row['id'] }}</td>
                        <td class="whitespace-nowrap">{{ $row['table'] }}</td>
                        <td class="whitespace-nowrap">{{ $row['customer'] }}</td>
                        <td class="min-w-[220px] text-cocoa/70">{{ $row['menu'] }}</td>
                        <td class="whitespace-nowrap font-semibold text-cocoa">
                            {{ \App\Support\Perkoci::rupiah($row['total']) }}
                        </td>
                        <td>
                            <x-ui.badge :tone="$row['status']['tone']">{{ $row['status']['label'] }}</x-ui.badge>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.section>

        {{-- Menu terlaris --}}
        <x-ui.section :title="$role['top_menu']['title']" :description="$role['top_menu']['description']">
            @php $maxSold = max(array_column($role['top_menu']['rows'], 'sold')); @endphp

            <ul class="space-y-4">
                @foreach ($role['top_menu']['rows'] as $index => $row)
                    <li>
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="min-w-0 truncate text-xs font-semibold text-cocoa">
                                <span class="mr-1 text-cocoa/30">{{ $index + 1 }}.</span>{{ $row['name'] }}
                            </p>
                            <p class="shrink-0 text-[11px] font-bold text-cocoa/50">{{ $row['sold'] }} terjual</p>
                        </div>

                        <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-sand">
                            <div class="h-full rounded-full bg-brand"
                                style="width: {{ (int) round($row['sold'] / $maxSold * 100) }}%"></div>
                        </div>

                        <p class="mt-1 text-[10px] text-cocoa/40">
                            {{ \App\Support\Perkoci::rupiah($row['revenue']) }}
                        </p>
                    </li>
                @endforeach
            </ul>
        </x-ui.section>
    </div>

    {{-- TODO(figma): Dashboard Manager - frame utama --}}
    <x-ui.design-note class="mt-4" title="Catatan Tahap 2 — Dashboard Manager">
        {{ $preview['figma_note'] }}
    </x-ui.design-note>
@endsection
