{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
{{-- TODO(figma): Kasir – Daftar Pesanan --}}
@extends('layouts.staff')

@section('title', $role['orders_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'kasir')
@section('active_nav', 'orders')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['orders_page']['title'] }}</p>
    </div>
@endsection

@section('content')
    @php
        $page = $role['orders_page'];
        $allRows = collect($page['rows']);
        $activeFilter = request('status', 'all');
        $rows = $activeFilter === 'all' ? $allRows : $allRows->where('status.key', $activeFilter);
        $totalValue = $allRows->sum('total');
        $newCount = $allRows->where('status.key', 'baru')->count();
    @endphp

    <x-ui.preview-banner current="kasir" />

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']">
        <x-ui.badge tone="butter" icon="science">Data Contoh</x-ui.badge>
        <x-ui.badge tone="danger" icon="notifications_active">{{ $newCount }} pesanan baru</x-ui.badge>
    </x-ui.page-header>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card label="Total Pesanan" :value="$allRows->count()" icon="receipt_long" tone="brand" hint="data contoh" />
        <x-ui.stat-card label="Nilai Pesanan" :value="\App\Support\Perkoci::rupiah($totalValue)" icon="payments" tone="lime" hint="dari semua baris" />
        <x-ui.stat-card label="Pesanan Baru" :value="$newCount" icon="schedule" tone="danger" hint="perlu ditindaklanjuti" />
    </div>

    <x-ui.section class="mt-4" :title="$page['title']" :description="$page['subtitle']" flush>
        <x-slot:actions>
            <x-ui.badge tone="neutral">{{ $rows->count() }} baris</x-ui.badge>
        </x-slot:actions>

        <div class="flex flex-wrap gap-2 border-b border-cocoa/10 px-5 py-3">
            @foreach ($page['filters'] as $filter)
                <a href="{{ route('preview.kasir.orders', ['status' => $filter['key']]) }}"
                    class="chip {{ $activeFilter === $filter['key'] ? 'chip-active' : '' }}"
                    aria-pressed="{{ $activeFilter === $filter['key'] ? 'true' : 'false' }}">
                    {{ $filter['label'] }}
                    <span class="ml-1 text-[10px] opacity-70">
                        {{ $filter['key'] === 'all' ? $allRows->count() : $allRows->where('status.key', $filter['key'])->count() }}
                    </span>
                </a>
            @endforeach
        </div>

        @if ($rows->isEmpty())
            <x-ui.empty-state icon="receipt_long" title="Tidak ada pesanan"
                description="Tidak ada pesanan contoh dengan status ini." />
        @else
            <x-ui.table class="rounded-none border-0"
                :head="['Pesanan', 'Meja', 'Pelanggan', 'Menu', 'Item', 'Total', 'Status', 'Aksi']">
                @foreach ($rows as $row)
                    <tr>
                        <td class="font-semibold text-cocoa">{{ $row['id'] }}</td>
                        <td class="whitespace-nowrap">{{ $row['table'] }}</td>
                        <td class="whitespace-nowrap">{{ $row['customer'] }}</td>
                        <td class="min-w-[220px] text-cocoa/70">{{ $row['menu'] }}</td>
                        <td class="whitespace-nowrap">{{ $row['items'] }} item</td>
                        <td class="whitespace-nowrap font-semibold text-cocoa">{{ \App\Support\Perkoci::rupiah($row['total']) }}</td>
                        <td><x-ui.badge :tone="$row['status']['tone']">{{ $row['status']['label'] }}</x-ui.badge></td>
                        <td>
                            <button type="button" class="btn-secondary btn-sm"
                                data-toast-message="Detail pesanan belum terhubung pada mode preview.">
                                Detail
                            </button>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </x-ui.section>

    {{-- TODO(figma): Kasir – Daftar Pesanan --}}
    <x-ui.design-note class="mt-4" title="Menunggu frame Figma: Kasir – Daftar Pesanan">
        Susunan kolom, filter status, dan aksi baris menunggu referensi Figma.
    </x-ui.design-note>
@endsection
