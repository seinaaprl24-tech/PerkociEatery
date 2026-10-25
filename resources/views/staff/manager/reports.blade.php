{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
{{-- TODO(figma): Manager – Laporan Penjualan --}}
@extends('layouts.staff')

@section('title', $role['reports_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'manager')
@section('active_nav', 'reports')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['reports_page']['title'] }}</p>
    </div>
@endsection

@section('content')
    @php
        $page = $role['reports_page'];
        $daily = collect($page['daily']);
        $totalRevenue = $daily->sum('revenue');
        $totalOrders = $daily->sum('orders');
        $average = $totalOrders > 0 ? (int) round($totalRevenue / $totalOrders) : 0;
        $sold = collect($role['top_menu']['rows'])->sum('sold');
        $maxRevenue = max(array_column($page['daily'], 'revenue'));
    @endphp

    <x-ui.preview-banner current="manager" />

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']">
        <x-ui.badge tone="butter" icon="science">Data Contoh</x-ui.badge>
        <x-ui.badge tone="neutral" icon="calendar_month">7 Hari</x-ui.badge>
    </x-ui.page-header>

    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Total Penjualan" :value="\App\Support\Perkoci::rupiah($totalRevenue)" icon="payments" tone="brand" hint="dihitung dari 7 hari" />
        <x-ui.stat-card label="Total Pesanan" :value="$totalOrders" icon="receipt_long" tone="lime" hint="dihitung dari 7 hari" />
        <x-ui.stat-card label="Rata-rata / Pesanan" :value="\App\Support\Perkoci::rupiah($average)" icon="trending_up" tone="butter" hint="total / pesanan" />
        <x-ui.stat-card label="Menu Terjual" :value="$sold" icon="restaurant_menu" tone="cocoa" hint="dari menu terlaris" />
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <x-ui.section class="xl:col-span-2" title="Penjualan Harian" description="Grafik kerangka dari data contoh. Tinggi batang dihitung relatif.">
            <x-slot:actions>
                <x-ui.badge tone="neutral">Kerangka Grafik</x-ui.badge>
            </x-slot:actions>

            <div class="flex items-end justify-between gap-1.5 sm:gap-3">
                @foreach ($page['daily'] as $day)
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                        <div class="flex h-36 w-full items-end">
                            <div class="w-full rounded-t-md bg-gradient-to-t from-brand to-brand/55"
                                style="height: {{ max(6, min(100, (int) round($day['revenue'] / $maxRevenue * 100))) }}%"></div>
                        </div>
                        <span class="text-[10px] font-semibold text-cocoa/40">{{ $day['day'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-baseline justify-between gap-3 border-t border-cocoa/10 pt-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-cocoa/45">Total 7 Hari</p>
                <p class="font-display text-[18px] font-bold leading-none text-cocoa">{{ \App\Support\Perkoci::rupiah($totalRevenue) }}</p>
            </div>
        </x-ui.section>

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
                            <div class="h-full rounded-full bg-brand" style="width: {{ (int) round($row['sold'] / $maxSold * 100) }}%"></div>
                        </div>

                        <p class="mt-1 text-[10px] text-cocoa/40">{{ \App\Support\Perkoci::rupiah($row['revenue']) }}</p>
                    </li>
                @endforeach
            </ul>
        </x-ui.section>
    </div>

    <x-ui.section class="mt-4" title="Rekap Harian" description="Total baris dihitung dari data contoh." flush>
        <x-ui.table class="rounded-none border-0" :head="['Hari', 'Pesanan', 'Penjualan']">
            @foreach ($page['daily'] as $day)
                <tr>
                    <td class="font-semibold text-cocoa">{{ $day['day'] }}</td>
                    <td>{{ $day['orders'] }}</td>
                    <td class="whitespace-nowrap font-semibold text-cocoa">{{ \App\Support\Perkoci::rupiah($day['revenue']) }}</td>
                </tr>
            @endforeach
            <tr class="bg-sand/60">
                <td class="font-bold text-cocoa">Total</td>
                <td class="font-bold text-cocoa">{{ $totalOrders }}</td>
                <td class="whitespace-nowrap font-bold text-cocoa">{{ \App\Support\Perkoci::rupiah($totalRevenue) }}</td>
            </tr>
        </x-ui.table>
    </x-ui.section>

    {{-- TODO(figma): Manager – Laporan Penjualan --}}
    <x-ui.design-note class="mt-4" title="Menunggu frame Figma: Manager – Laporan Penjualan">
        Jenis grafik, rentang tanggal, dan susunan rekap menunggu referensi Figma.
    </x-ui.design-note>
@endsection
