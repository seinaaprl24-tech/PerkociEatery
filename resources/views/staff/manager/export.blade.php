{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
{{-- TODO(figma): Manager – Export/Cetak Laporan --}}
@extends('layouts.staff')

@section('title', $role['export_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'manager')
@section('active_nav', 'export')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['export_page']['title'] }}</p>
    </div>
@endsection

@section('content')
    @php $page = $role['export_page']; @endphp

    <x-ui.preview-banner current="manager" />

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']">
        <x-ui.badge tone="butter" icon="science">Data Contoh</x-ui.badge>
    </x-ui.page-header>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        @foreach ($page['formats'] as $format)
            <x-ui.section :title="$format['label']" :description="$format['description']">
                <button type="button" class="btn-secondary w-full"
                    data-toast-message="Export {{ $format['label'] }} belum tersedia pada mode preview.">
                    <span class="ms text-[18px] leading-none" aria-hidden="true">{{ $format['icon'] }}</span>
                    Pilih {{ $format['label'] }}
                </button>
            </x-ui.section>
        @endforeach
    </div>

    <x-ui.section class="mt-4" title="Riwayat Export" description="Belum ada file contoh yang diexport." flush>
        <x-slot:actions>
            <x-ui.badge tone="neutral">{{ count($page['history']) }} file</x-ui.badge>
        </x-slot:actions>

        @if (count($page['history']) === 0)
            <x-ui.empty-state icon="folder_open" title="Belum ada riwayat export"
                description="Riwayat file akan tampil di sini setelah proses export tersedia." />
        @else
            <x-ui.table class="rounded-none border-0" :head="['Nama File', 'Format', 'Tanggal']">
                @foreach ($page['history'] as $row)
                    <tr>
                        <td class="font-semibold text-cocoa">{{ $row['name'] }}</td>
                        <td>{{ $row['format'] }}</td>
                        <td class="whitespace-nowrap">{{ $row['date'] }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </x-ui.section>

    {{-- TODO(figma): Manager – Export/Cetak Laporan --}}
    <x-ui.design-note class="mt-4" title="Menunggu frame Figma: Manager – Export/Cetak Laporan">
        Pilihan format, riwayat file, dan alur cetak menunggu referensi Figma.
    </x-ui.design-note>
@endsection
