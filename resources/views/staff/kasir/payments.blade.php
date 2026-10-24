{{-- TEMPORARY – hapus saat auth/backend siap, jangan dipublikasikan ke produksi. --}}
{{-- TODO(figma): Kasir – Pembayaran & Cetak Struk --}}
@extends('layouts.staff')

@section('title', $role['payment_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'kasir')
@section('active_nav', 'payments')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['payment_page']['title'] }}</p>
    </div>
@endsection

@section('content')
    @php
        $page = $role['payment_page'];
        $receipt = $role['receipt'];
        $total = collect($receipt['items'])->sum(fn ($item) => $item['price'] * $item['qty']);
        $cashReceived = $page['cash_received'];
        $change = $cashReceived - $total;
    @endphp

    <x-ui.preview-banner current="kasir" />

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']">
        <x-ui.badge tone="butter" icon="science">Data Contoh</x-ui.badge>
        <x-ui.badge tone="neutral" icon="payments">Tunai</x-ui.badge>
    </x-ui.page-header>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card label="Total Tagihan" :value="\App\Support\Perkoci::rupiah($total)" icon="receipt_long" tone="brand" hint="dihitung dari item" />
        <x-ui.stat-card label="Tunai Diterima" :value="\App\Support\Perkoci::rupiah($cashReceived)" icon="payments" tone="lime" hint="nominal contoh" />
        <x-ui.stat-card label="Kembalian" :value="\App\Support\Perkoci::rupiah($change)" icon="price_check" tone="butter" hint="tunai - total" />
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <x-ui.section :title="$receipt['title']" :description="$receipt['description']">
            <div class="rounded-xl border border-dashed border-cocoa/25 bg-white px-4 py-4 text-[11px]">
                <div class="text-center">
                    <p class="font-display text-[14px] font-bold text-cocoa">{{ config('perkoci.brand.name') }}</p>
                    <p class="mt-1 text-[10px] leading-relaxed text-cocoa/45">
                        {{ implode(' · ', $receipt['meta']) }}
                    </p>
                </div>

                <div class="my-3 border-t border-dashed border-cocoa/20"></div>

                <x-ui.table :head="['Item', 'Qty', 'Harga', 'Subtotal']" class="rounded-none border-0">
                    @foreach ($receipt['items'] as $item)
                        <tr>
                            <td class="font-semibold text-cocoa">{{ $item['name'] }}</td>
                            <td class="whitespace-nowrap">{{ $item['qty'] }}×</td>
                            <td class="whitespace-nowrap">{{ \App\Support\Perkoci::rupiah($item['price']) }}</td>
                            <td class="whitespace-nowrap font-semibold text-cocoa">
                                {{ \App\Support\Perkoci::rupiah($item['price'] * $item['qty']) }}
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>

                <div class="my-3 border-t border-dashed border-cocoa/20"></div>

                <dl class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <dt class="text-cocoa/55">Total</dt>
                        <dd class="font-display text-[14px] font-bold text-cocoa">{{ \App\Support\Perkoci::rupiah($total) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-cocoa/55">Tunai</dt>
                        <dd class="font-semibold text-cocoa">{{ \App\Support\Perkoci::rupiah($cashReceived) }}</dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-dashed border-cocoa/20 pt-1.5">
                        <dt class="font-bold text-cocoa">Kembalian</dt>
                        <dd class="font-display text-[14px] font-bold text-brand">{{ \App\Support\Perkoci::rupiah($change) }}</dd>
                    </div>
                </dl>

                <p class="mt-3 text-center text-[9.5px] leading-relaxed text-cocoa/35">{{ $receipt['note'] }}</p>
            </div>
        </x-ui.section>

        <div class="space-y-4">
            <x-ui.section title="Ringkasan Pesanan" description="Contoh pesanan yang sedang dibayar.">
                <dl class="space-y-2.5">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-cocoa/50">Nomor pesanan</dt>
                        <dd class="text-xs font-bold text-cocoa">#PK-9821</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-cocoa/50">Meja</dt>
                        <dd class="text-xs font-semibold text-cocoa">Meja 08</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-cocoa/50">Jumlah item</dt>
                        <dd class="text-xs font-semibold text-cocoa">{{ collect($receipt['items'])->sum('qty') }} item</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-cocoa/50">Metode</dt>
                        <dd><x-ui.badge tone="lime">Tunai</x-ui.badge></dd>
                    </div>
                </dl>

                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" class="btn-primary"
                        data-toast-message="Pembayaran belum terhubung pada mode preview.">
                        Konfirmasi Pembayaran
                    </button>
                    <button type="button" class="btn-secondary"
                        data-toast-message="Cetak struk belum terhubung pada mode preview.">
                        <span class="ms text-[16px] leading-none" aria-hidden="true">print</span>
                        Cetak Struk
                    </button>
                </div>
            </x-ui.section>

            <x-ui.section title="Antrean Pembayaran" description="Contoh pesanan lain yang menunggu pembayaran.">
                <ul class="space-y-2.5">
                    @foreach ($role['payments']['rows'] as $row)
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-cocoa/10 px-3.5 py-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-cocoa">{{ $row['id'] }} · {{ $row['table'] }}</p>
                                <p class="mt-0.5 text-[11px] text-cocoa/45">{{ $row['method'] }}</p>
                            </div>
                            <p class="shrink-0 font-display text-sm font-bold text-cocoa">{{ \App\Support\Perkoci::rupiah($row['total']) }}</p>
                        </li>
                    @endforeach
                </ul>
            </x-ui.section>
        </div>
    </div>

    {{-- TODO(figma): Kasir – Pembayaran & Cetak Struk --}}
    <x-ui.design-note class="mt-4" title="Menunggu frame Figma: Kasir – Pembayaran & Cetak Struk">
        Tata letak ringkasan pembayaran, nominal tunai, kembalian, dan preview struk menunggu Figma.
    </x-ui.design-note>
@endsection
