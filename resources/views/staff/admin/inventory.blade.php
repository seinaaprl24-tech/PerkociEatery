{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
{{-- TODO(figma): Admin – Kelola Stok --}}
@extends('layouts.staff')

@section('title', $role['inventory_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'admin')
@section('active_nav', 'inventory')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['inventory_page']['title'] }}</p>
    </div>
@endsection

@section('content')
    @php
        $page = $role['inventory_page'];
        $rows = collect($page['rows']);
        $low = $rows->where('status.label', 'Menipis')->count();
        $empty = $rows->where('status.label', 'Habis')->count();
    @endphp

    <x-ui.preview-banner current="admin" />

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']">
        <x-ui.badge tone="butter" icon="science">Data Contoh</x-ui.badge>
    </x-ui.page-header>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card label="Total Bahan" :value="$rows->count()" icon="inventory_2" tone="brand" hint="baris stok contoh" />
        <x-ui.stat-card label="Menipis" :value="$low" icon="warning" tone="butter" hint="di bawah batas" />
        <x-ui.stat-card label="Habis" :value="$empty" icon="error" tone="danger" hint="perlu restock" />
    </div>

    <x-ui.section class="mt-4" :title="$page['title']" :description="$page['subtitle']" flush>
        <x-slot:actions>
            <x-ui.badge tone="neutral">{{ $rows->count() }} bahan</x-ui.badge>
        </x-slot:actions>

        <x-ui.table class="rounded-none border-0" :head="['Bahan', 'Satuan', 'Stok', 'Minimum', 'Status', 'Aksi']">
            @foreach ($page['rows'] as $row)
                <tr>
                    <td class="font-semibold text-cocoa">{{ $row['name'] }}</td>
                    <td class="whitespace-nowrap">{{ $row['unit'] }}</td>
                    <td class="whitespace-nowrap font-semibold text-cocoa">{{ $row['stock'] }}</td>
                    <td class="whitespace-nowrap">{{ $row['minimum'] }}</td>
                    <td><x-ui.badge :tone="$row['status']['tone']">{{ $row['status']['label'] }}</x-ui.badge></td>
                    <td>
                        <button type="button" class="btn-secondary btn-sm"
                            data-toast-message="Restock belum terhubung pada mode preview.">
                            Restock
                        </button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </x-ui.section>

    {{-- TODO(figma): Admin – Kelola Stok --}}
    <x-ui.design-note class="mt-4" title="Menunggu frame Figma: Admin – Kelola Stok">
        Kolom stok, batas minimum, dan aksi restock menunggu referensi Figma.
    </x-ui.design-note>
@endsection
