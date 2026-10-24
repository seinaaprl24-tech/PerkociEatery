{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
{{-- TODO(figma): Admin – Pengaturan --}}
@extends('layouts.staff')

@section('title', $role['settings_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'admin')
@section('active_nav', 'settings')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['settings_page']['title'] }}</p>
    </div>
@endsection

@section('content')
    @php $page = $role['settings_page']; @endphp

    <x-ui.preview-banner current="admin" />

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']">
        <x-ui.badge tone="butter" icon="science">Data Contoh</x-ui.badge>

        <button type="button" class="btn-primary btn-sm"
            data-toast-message="Penyimpanan pengaturan belum tersedia pada mode preview.">
            <span class="ms text-[16px] leading-none" aria-hidden="true">save</span>
            Simpan
        </button>
    </x-ui.page-header>

    <div class="mt-5 grid gap-4 xl:grid-cols-2">
        @foreach ($page['groups'] as $group)
            <x-ui.section :title="$group['title']" description="Contoh data pengaturan.">
                <dl class="divide-y divide-cocoa/10">
                    @foreach ($group['fields'] as $field)
                        <div class="flex flex-wrap items-center justify-between gap-2 py-2.5 first:pt-0 last:pb-0">
                            <dt class="text-xs text-cocoa/50">{{ $field['label'] }}</dt>
                            <dd class="text-xs font-semibold text-cocoa">{{ $field['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-ui.section>
        @endforeach
    </div>

    {{-- TODO(figma): Admin – Pengaturan --}}
    <x-ui.design-note class="mt-4" title="Menunggu frame Figma: Admin – Pengaturan">
        Susunan grup pengaturan dan bentuk form final menunggu referensi Figma.
    </x-ui.design-note>
@endsection
