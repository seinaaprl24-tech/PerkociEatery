{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
{{-- TODO(figma): Admin – Pengguna --}}
@extends('layouts.staff')

@section('title', 'Pengguna — Perkoci Eatery')

@section('staff_role', 'admin')
@section('active_nav', 'users')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">Pengguna</p>
    </div>
@endsection

@section('content')
    @php
        $rows = collect($role['users']['rows']);
        $active = $rows->where('status.label', 'Aktif')->count();
        $inactive = $rows->where('status.label', 'Nonaktif')->count();
    @endphp

    <x-ui.preview-banner current="admin" />

    <x-ui.page-header title="Pengguna" subtitle="Contoh akun staff internal dan statusnya.">
        <x-ui.badge tone="butter" icon="science">Data Contoh</x-ui.badge>

        <button type="button" class="btn-primary btn-sm"
            data-toast-message="Form tambah pengguna belum tersedia pada mode preview.">
            <span class="ms text-[16px] leading-none" aria-hidden="true">person_add</span>
            Tambah Pengguna
        </button>
    </x-ui.page-header>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card label="Total Pengguna" :value="$rows->count()" icon="group" tone="brand" hint="data contoh" />
        <x-ui.stat-card label="Aktif" :value="$active" icon="check_circle" tone="lime" hint="dapat bertugas" />
        <x-ui.stat-card label="Nonaktif" :value="$inactive" icon="block" tone="danger" hint="akses ditahan" />
    </div>

    <x-ui.section class="mt-4" :title="$role['users']['title']" :description="$role['users']['description']" flush>
        <x-slot:actions>
            <x-ui.badge tone="neutral">{{ $rows->count() }} akun</x-ui.badge>
        </x-slot:actions>

        <x-ui.table class="rounded-none border-0" :head="['Nama', 'Peran', 'Status', 'Aksi']">
            @foreach ($role['users']['rows'] as $row)
                <tr>
                    <td>
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sand text-[10px] font-extrabold text-cocoa/55">
                                {{ strtoupper(substr($row['name'], 0, 1)) }}
                            </span>
                            <span class="font-semibold text-cocoa">{{ $row['name'] }}</span>
                        </div>
                    </td>
                    <td class="whitespace-nowrap">{{ $row['role'] }}</td>
                    <td><x-ui.badge :tone="$row['status']['tone']">{{ $row['status']['label'] }}</x-ui.badge></td>
                    <td>
                        <button type="button" class="btn-secondary btn-sm"
                            data-toast-message="Aksi pengguna belum tersedia pada mode preview.">
                            Kelola
                        </button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </x-ui.section>

    {{-- TODO(figma): Admin – Pengguna --}}
    <x-ui.design-note class="mt-4" title="Menunggu frame Figma: Admin – Pengguna">
        Kolom akun, status, dan aksi pengelolaan menunggu referensi Figma.
    </x-ui.design-note>
@endsection
