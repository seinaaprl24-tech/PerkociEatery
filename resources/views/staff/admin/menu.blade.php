{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
{{-- TODO(figma): Admin – Kelola Menu --}}
@extends('layouts.staff')

@section('title', $role['menu_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'admin')
@section('active_nav', 'menu')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['menu_page']['title'] }}</p>
    </div>
@endsection

@section('content')
    @php
        $page = $role['menu_page'];
        $rows = collect($page['rows']);
        $drinks = $rows->where('category', 'Minuman')->count();
        $foods = $rows->where('category', 'Makanan')->count();
    @endphp

    <x-ui.preview-banner current="admin" />

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']">
        <x-ui.badge tone="butter" icon="science">Data Contoh</x-ui.badge>

        <button type="button" class="btn-primary btn-sm"
            data-toast-message="Form tambah menu belum tersedia pada mode preview.">
            <span class="ms text-[16px] leading-none" aria-hidden="true">add</span>
            Tambah Menu
        </button>
    </x-ui.page-header>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card label="Total Menu" :value="$rows->count()" icon="restaurant_menu" tone="brand" hint="15 menu asli" />
        <x-ui.stat-card label="Minuman" :value="$drinks" icon="local_cafe" tone="lime" hint="kategori minuman" />
        <x-ui.stat-card label="Makanan" :value="$foods" icon="restaurant" tone="butter" hint="kategori makanan" />
    </div>

    <x-ui.section class="mt-4" :title="$page['title']" :description="$page['subtitle']" flush>
        <x-slot:actions>
            <x-ui.badge tone="neutral">{{ $rows->count() }} menu</x-ui.badge>

            <button type="button" class="btn-secondary btn-sm"
                data-toast-message="Pencarian menu belum tersedia pada mode preview.">
                <span class="ms text-[16px] leading-none" aria-hidden="true">search</span>
                Cari
            </button>
        </x-slot:actions>

        <x-ui.table class="rounded-none border-0" :head="['Menu', 'Kategori', 'Harga', 'Status', 'Aksi']">
            @foreach ($page['rows'] as $row)
                <tr>
                    @php $menuItem = \App\Support\Perkoci::menuBySlug($row['slug']); @endphp

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
                        <span class="text-[10px] font-medium text-cocoa/40">/ {{ $row['unit'] }}</span>
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

    {{-- TODO(figma): Admin – Kelola Menu --}}
    <x-ui.design-note class="mt-4" title="Menunggu frame Figma: Admin – Kelola Menu">
        Form tambah/edit, kolom tabel, dan konfirmasi hapus menunggu referensi Figma.
    </x-ui.design-note>
@endsection
