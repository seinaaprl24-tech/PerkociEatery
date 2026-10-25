{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
{{-- TODO(figma): Manager – Laporan Keuangan --}}
@extends('layouts.staff')

@section('title', $role['finance_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'manager')
@section('active_nav', 'finance')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['finance_page']['title'] }}</p>
    </div>
@endsection

@section('content')
    @php
        $page = $role['finance_page'];
        $income = collect($page['income']);
        $expenses = collect($page['expenses']);
        $totalIncome = $income->sum('amount');
        $totalExpenses = $expenses->sum('amount');
        $net = $totalIncome - $totalExpenses;
    @endphp

    <x-ui.preview-banner current="manager" />

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']">
        <x-ui.badge tone="butter" icon="science">Data Contoh</x-ui.badge>
    </x-ui.page-header>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card label="Total Pemasukan" :value="\App\Support\Perkoci::rupiah($totalIncome)" icon="south_west" tone="lime" hint="dihitung dari baris" />
        <x-ui.stat-card label="Total Pengeluaran" :value="\App\Support\Perkoci::rupiah($totalExpenses)" icon="north_east" tone="danger" hint="dihitung dari baris" />
        <x-ui.stat-card label="Saldo Bersih" :value="\App\Support\Perkoci::rupiah($net)" icon="account_balance_wallet" tone="brand" hint="pemasukan - pengeluaran" />
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <x-ui.section :title="'Rincian Pemasukan'" description="Contoh pemasukan pada periode ini." flush>
            <x-slot:actions>
                <x-ui.badge tone="lime">{{ $income->count() }} baris</x-ui.badge>
            </x-slot:actions>

            <x-ui.table class="rounded-none border-0" :head="['Sumber', 'Nominal']">
                @foreach ($page['income'] as $row)
                    <tr>
                        <td class="font-semibold text-cocoa">{{ $row['label'] }}</td>
                        <td class="whitespace-nowrap font-semibold text-cocoa">{{ \App\Support\Perkoci::rupiah($row['amount']) }}</td>
                    </tr>
                @endforeach
                <tr class="bg-sand/60">
                    <td class="font-bold text-cocoa">Total Pemasukan</td>
                    <td class="whitespace-nowrap font-bold text-cocoa">{{ \App\Support\Perkoci::rupiah($totalIncome) }}</td>
                </tr>
            </x-ui.table>
        </x-ui.section>

        <x-ui.section title="Rincian Pengeluaran" description="Contoh pengeluaran pada periode ini." flush>
            <x-slot:actions>
                <x-ui.badge tone="danger">{{ $expenses->count() }} baris</x-ui.badge>
            </x-slot:actions>

            <x-ui.table class="rounded-none border-0" :head="['Keperluan', 'Nominal']">
                @foreach ($page['expenses'] as $row)
                    <tr>
                        <td class="font-semibold text-cocoa">{{ $row['label'] }}</td>
                        <td class="whitespace-nowrap font-semibold text-cocoa">{{ \App\Support\Perkoci::rupiah($row['amount']) }}</td>
                    </tr>
                @endforeach
                <tr class="bg-sand/60">
                    <td class="font-bold text-cocoa">Total Pengeluaran</td>
                    <td class="whitespace-nowrap font-bold text-cocoa">{{ \App\Support\Perkoci::rupiah($totalExpenses) }}</td>
                </tr>
            </x-ui.table>
        </x-ui.section>
    </div>

    {{-- TODO(figma): Manager – Laporan Keuangan --}}
    <x-ui.design-note class="mt-4" title="Menunggu frame Figma: Manager – Laporan Keuangan">
        Pengelompokan pemasukan/pengeluaran dan kartu saldo menunggu referensi Figma.
    </x-ui.design-note>
@endsection
