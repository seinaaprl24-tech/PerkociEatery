{{-- Kasir – Pembayaran & Cetak Struk: data nyata dari /api/pesanan dan /api/pembayaran. --}}
@extends('layouts.staff')

@php
    /* ==== Ukuran struk (ubah di sini bila perlu) ==== */
    $STRUK_LEBAR_MM = 58;
    $STRUK_TINGGI_MIN_MM = 80;
    $STRUK_PADDING_MM = 3;

    /* Identitas kedai: alamat/telepon hanya ditampilkan jika tersedia di config. */
    $perkociBrand = (array) config('perkoci.brand', []);
    $brandAddress = $perkociBrand['address'] ?? $perkociBrand['alamat']
        ?? config('perkoci.address') ?? config('perkoci.alamat');
    $brandPhone = $perkociBrand['phone'] ?? $perkociBrand['telepon'] ?? $perkociBrand['telp']
        ?? config('perkoci.phone') ?? config('perkoci.telepon') ?? config('perkoci.telp');

    $brandAddress = is_string($brandAddress) ? trim($brandAddress) : $brandAddress;
    $brandPhone = is_string($brandPhone) ? trim($brandPhone) : $brandPhone;

    $brandAddress = ($brandAddress === '' || $brandAddress === '-') ? null : $brandAddress;
    $brandPhone = ($brandPhone === '' || $brandPhone === '-') ? null : $brandPhone;
@endphp

@section('title', $role['payment_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'kasir')
@section('active_nav', 'payments')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['payment_page']['title'] }}</p>
    </div>
@endsection

@push('head')
    {{-- Gaya khusus halaman ini memakai CSS biasa, jadi tampilan tetap rapi walau build Tailwind belum diperbarui. --}}
    <style>
        .pay-stack > * + * { margin-top: 20px; }

        /* Kotak TOTAL: label di kiri, angka di kanan */
        .pay-total {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 28px;
            border-radius: 16px;
            background: var(--color-cocoa, #3b2a20);
            color: #fff;
        }
        .pay-total-label {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .7);
        }
        .pay-total-value {
            font-size: 40px;
            font-weight: 700;
            line-height: 1;
            text-align: right;
        }

        /* Ubin metode pembayaran */
        .pay-tiles {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 10px;
        }
        .pay-tile {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 16px 8px;
            border: 2px solid rgba(59, 42, 32, .12);
            border-radius: 16px;
            background: #fff;
            text-align: center;
            cursor: pointer;
            transition: background .15s, border-color .15s;
        }
        .pay-tile:hover { background: #f5f1ea; }
        .pay-tile:has(input:checked) {
            border-color: var(--color-brand, #1f5c1a);
            background: rgba(31, 92, 26, .1);
        }
        .pay-tile:has(input:focus-visible) { outline: 2px solid rgba(31, 92, 26, .45); outline-offset: 2px; }

        /* Uang diterima + kembalian */
        .pay-money {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            align-items: stretch;
        }
        .pay-box { padding: 14px 16px; border-radius: 16px; }
        .pay-box-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        /* Tombol nominal cepat */
        .pay-chips { display: flex; flex-wrap: wrap; gap: 8px; }

        @media (max-width: 640px) {
            .pay-money { grid-template-columns: 1fr; }
            .pay-total { padding: 16px 18px; }
            .pay-total-value { font-size: 32px; }
        }
    </style>

    {{-- Saat mencetak, hanya struk yang tampil. Ukuran kertas diatur lewat JS sebelum window.print(). --}}
    <style>
        @@media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            body * { visibility: hidden; }

            [data-receipt], [data-receipt] * { visibility: visible; }

            [data-receipt] {
                position: absolute;
                left: 0;
                top: 0;
                width: {{ $STRUK_LEBAR_MM }}mm !important;
                max-width: {{ $STRUK_LEBAR_MM }}mm !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: {{ $STRUK_PADDING_MM }}mm !important;
                border: 0 !important;
                border-radius: 0 !important;
                background: #fff !important;
                box-shadow: none !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            [data-receipt] * {
                color: #000 !important;
                border-color: #555 !important;
            }

            /* Struk dipindah ke <body> saat cetak agar sisa halaman tidak ikut menjadi halaman kosong. */
            html.receipt-printing body > *:not([data-receipt]) {
                display: none !important;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $page = $role['payment_page'];

        $methods = [
            ['value' => 'tunai', 'label' => 'Tunai', 'meta' => 'Uang tunai di kasir', 'icon' => 'payments'],
            ['value' => 'qris', 'label' => 'QRIS', 'meta' => 'Scan kode QRIS', 'icon' => 'qr_code_2'],
            ['value' => 'e-wallet', 'label' => 'E-Wallet', 'meta' => 'Transfer / dompet digital', 'icon' => 'account_balance_wallet'],
        ];
    @endphp

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']" />

    <p data-flash class="hidden" role="status"></p>

    {{-- Kiri (2/3): pembayaran. Kanan (1/3): pratinjau struk. --}}
    <div class="mt-5 grid gap-4 xl:grid-cols-3 xl:items-start">
        {{-- ================= KIRI: Pembayaran ================= --}}
        <section class="rounded-2xl border border-cocoa/10 bg-white p-5 sm:p-7 xl:col-span-2" aria-label="Pembayaran">
            <div data-summary-empty>
                <x-ui.empty-state icon="payments" title="Belum ada pesanan dipilih"
                    description="Pilih pesanan dari antrean pembayaran." />
            </div>

            <div class="pay-stack hidden" data-summary>
                {{-- 1. Nomor pesanan + lencana status --}}
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="min-w-0 break-words font-display text-[28px] font-bold leading-tight text-cocoa" data-sum-number>-</h2>
                    <span class="badge badge-butter shrink-0 whitespace-nowrap" data-sum-status>Menunggu Pembayaran</span>
                </div>

                {{-- 2. Meja • nama pemesan --}}
                <p style="margin-top: 6px" class="text-[14px] text-cocoa/60">
                    <span data-sum-table>-</span><span data-sum-customer></span>
                </p>

                {{-- 3. Daftar item --}}
                <div class="rounded-2xl border border-cocoa/10 bg-sand/40" style="padding: 4px 20px">
                    <ul class="divide-y divide-cocoa/10" data-sum-lines></ul>

                    <div class="flex items-center justify-between gap-4 border-t border-cocoa/15 py-3">
                        <p class="text-[13px] font-semibold text-cocoa/60">
                            Subtotal · <span data-sum-items>-</span>
                        </p>
                        <p class="text-[15px] font-bold text-cocoa" data-sum-subtotal>Rp 0</p>
                    </div>
                </div>

                {{-- 4. Kotak TOTAL: label di samping angka --}}
                <div data-stat="total" class="pay-total">
                    <p class="pay-total-label">Total</p>
                    <p class="pay-total-value font-display">Rp 0</p>
                </div>
            </div>

            <form data-pay-form id="pay-form" class="pay-stack hidden" style="margin-top: 24px" novalidate>
                {{-- 5. Metode pembayaran --}}
                <div data-pay-controls>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-cocoa/50">Metode Pembayaran</p>

                    <div class="pay-tiles">
                        @foreach ($methods as $method)
                            <label class="pay-tile">
                                <input type="radio" name="metode" value="{{ $method['value'] }}"
                                    @checked($loop->first) class="sr-only">

                                <span class="ms text-[30px] leading-none text-cocoa" aria-hidden="true">{{ $method['icon'] }}</span>
                                <span class="text-[14px] font-bold text-cocoa">{{ $method['label'] }}</span>
                                <span class="text-[10.5px] leading-snug text-cocoa/50">{{ $method['meta'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- 6. Uang diterima + kembalian (Total sudah ada di kotak cokelat di atas) --}}
                <div class="pay-money">
                    <div data-stat="cash" class="pay-box bg-sand">
                        <label for="pay-cash" class="pay-box-label text-cocoa/50">Uang Diterima</label>
                        {{-- Target pengisian script (.font-display); nilai yang tampil ada di input di bawahnya. --}}
                        <span class="font-display sr-only" aria-hidden="true">Rp 0</span>
                        <input id="pay-cash" data-pay-cash type="number" min="0" step="500" inputmode="numeric"
                            placeholder="0" style="margin-top: 6px"
                            class="w-full rounded-xl border border-cocoa/15 bg-white px-3 py-2 text-[22px] font-bold leading-tight text-cocoa outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/30 read-only:bg-white/60 read-only:text-cocoa/70">
                    </div>

                    <div data-stat="change" class="pay-box bg-brand text-white">
                        <p class="pay-box-label text-white/75">Kembalian</p>
                        <p class="font-display text-[26px] font-bold leading-tight" style="margin-top: 10px">Rp 0</p>
                    </div>
                </div>

                {{-- 7 & 8. Nominal cepat + konfirmasi --}}
                <div class="pay-stack" data-pay-controls>
                    <div class="pay-chips" data-quick-cash></div>

                    <button type="submit" class="btn-primary w-full justify-center py-4 text-[15px]" data-pay-submit>
                        Konfirmasi Pembayaran
                    </button>
                </div>
            </form>

            <div class="mt-4 hidden" data-after-pay>
                <button type="button" class="btn-secondary w-full justify-center py-4 text-[15px]" data-next>
                    Pesanan Berikutnya
                </button>
            </div>
        </section>

        {{-- ================= KANAN: Pratinjau Struk ================= --}}
        <x-ui.section title="Pratinjau Struk" description="Isi struk mengikuti pesanan yang dipilih.">
            <div class="flex justify-center overflow-x-auto py-1" data-receipt-holder>
                <div data-receipt
                    data-receipt-width-mm="{{ $STRUK_LEBAR_MM }}"
                    data-receipt-min-height-mm="{{ $STRUK_TINGGI_MIN_MM }}"
                    class="rounded-xl border border-dashed border-cocoa/25 bg-white text-cocoa"
                    style="width: {{ $STRUK_LEBAR_MM }}mm; box-sizing: border-box; margin: 0 auto; padding: {{ $STRUK_PADDING_MM }}mm; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace; font-size: 10px; line-height: 1.45;">

                    {{-- Kepala struk --}}
                    <div class="text-center">
                        <p class="text-[12px] font-bold uppercase tracking-wide">{{ config('perkoci.brand.name') }}</p>

                        @if ($brandAddress)
                            <p class="mt-0.5 text-[9px] leading-snug">{{ $brandAddress }}</p>
                        @endif

                        @if ($brandPhone)
                            <p class="mt-0.5 text-[9px] leading-snug">Telp. {{ $brandPhone }}</p>
                        @endif
                    </div>

                    <div class="my-2 border-t border-dashed border-cocoa/30"></div>

                    <p class="text-center text-[9.5px] leading-snug text-cocoa/55" data-receipt-empty>
                        Belum ada pesanan dipilih.
                    </p>

                    <div class="hidden" data-receipt-body>
                        {{-- Info pesanan --}}
                        <dl class="space-y-0.5">
                            <div class="flex items-start justify-between gap-2">
                                <dt class="shrink-0 text-cocoa/60">Antrean</dt>
                                <dd class="min-w-0 break-words text-right font-semibold" data-receipt-order>-</dd>
                            </div>
                            <div class="flex items-start justify-between gap-2">
                                <dt class="shrink-0 text-cocoa/60">Tanggal</dt>
                                <dd class="min-w-0 text-right" data-receipt-datetime>-</dd>
                            </div>
                            <div class="flex items-start justify-between gap-2">
                                <dt class="shrink-0 text-cocoa/60">Meja</dt>
                                <dd class="min-w-0 text-right" data-receipt-table>-</dd>
                            </div>
                            <div class="hidden items-start justify-between gap-2" data-receipt-customer-row>
                                <dt class="shrink-0 text-cocoa/60">Pemesan</dt>
                                <dd class="min-w-0 break-words text-right" data-receipt-customer>-</dd>
                            </div>
                            <div class="flex items-start justify-between gap-2">
                                <dt class="shrink-0 text-cocoa/60">Kasir</dt>
                                <dd class="min-w-0 break-words text-right">{{ auth()->user()?->nama ?? '-' }}</dd>
                            </div>
                        </dl>

                        <div class="my-2 border-t border-dashed border-cocoa/30"></div>

                        {{-- Daftar item --}}
                        <ul class="space-y-1.5" data-receipt-items></ul>

                        <div class="my-2 border-t border-dashed border-cocoa/30"></div>

                        {{-- Subtotal & total --}}
                        <dl class="space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <dt class="text-cocoa/60">SUBTOTAL</dt>
                                <dd class="font-semibold" data-receipt-subtotal>Rp 0</dd>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <dt class="text-[11px] font-bold">TOTAL</dt>
                                <dd class="text-[12px] font-bold" data-receipt-total>Rp 0</dd>
                            </div>
                        </dl>

                        <div class="my-2 border-t border-dashed border-cocoa/30"></div>

                        {{-- Pembayaran --}}
                        <dl class="space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <dt class="text-cocoa/60">PEMBAYARAN</dt>
                                <dd class="font-semibold" data-receipt-method>Tunai</dd>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <dt class="text-cocoa/60" data-receipt-received-label>Uang diterima</dt>
                                <dd class="font-semibold" data-receipt-cash>Rp 0</dd>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <dt class="text-cocoa/60">Kembalian</dt>
                                <dd class="font-semibold" data-receipt-change>Rp 0</dd>
                            </div>
                        </dl>

                        <div class="my-2 border-t border-dashed border-cocoa/30"></div>

                        {{-- Penutup --}}
                        <div class="text-center">
                            <p class="text-[11px] font-bold uppercase tracking-wide" data-receipt-status>Pratinjau (belum dibayar)</p>
                            <p class="mt-0.5 text-[9.5px] text-cocoa/60">Terima kasih</p>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" data-print disabled
                class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-cocoa px-4 py-3.5 text-[14px] font-semibold text-white transition hover:bg-cocoa/90 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-cocoa">
                <span class="ms text-[18px] leading-none" aria-hidden="true">print</span>
                Cetak Struk
            </button>
        </x-ui.section>
    </div>

    {{-- ================= BAWAH: Antrean & Riwayat ================= --}}
    <div class="mt-4 grid gap-4 lg:grid-cols-2 lg:items-start">
        {{-- Antrean pembayaran --}}
        <x-ui.section title="Antrean Pembayaran" description="Pesanan aktif yang belum dibayar.">
            <ul class="space-y-2.5" data-queue></ul>

            <div data-queue-empty class="hidden">
                <x-ui.empty-state icon="receipt_long" title="Tidak ada antrean pembayaran"
                    description="Semua pesanan aktif sudah dibayar." />
            </div>
        </x-ui.section>

        {{-- Riwayat --}}
        <x-ui.section title="Riwayat Pembayaran" description="10 pembayaran terakhir.">
            <ul class="space-y-2.5" data-history></ul>

            <div data-history-empty class="hidden">
                <x-ui.empty-state icon="history" title="Belum ada pembayaran"
                    description="Pembayaran yang sudah diterima akan tampil di sini." />
            </div>
        </x-ui.section>
    </div>
@endsection

@push('scripts')
    @include('partials.kasir-data')

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            function q(selector) {
                return document.querySelector(selector);
            }

            var METHOD_LABEL = { 'tunai': 'Tunai', 'qris': 'QRIS', 'e-wallet': 'E-Wallet' };

            var flashEl = q('[data-flash]');
            var form = q('[data-pay-form]');
            var payControls = document.querySelectorAll('[data-pay-controls]');
            var cashInput = q('[data-pay-cash]');
            var quickEl = q('[data-quick-cash]');
            var submitBtn = q('[data-pay-submit]');
            var afterPay = q('[data-after-pay]');
            var printBtn = q('[data-print]');
            var queueEl = q('[data-queue]');
            var queueEmpty = q('[data-queue-empty]');
            var historyEl = q('[data-history]');
            var historyEmpty = q('[data-history-empty]');
            var summaryEl = q('[data-summary]');
            var summaryEmpty = q('[data-summary-empty]');
            var sumStatus = q('[data-sum-status]');
            var sumCustomer = q('[data-sum-customer]');
            var sumLines = q('[data-sum-lines]');
            var sumSubtotal = q('[data-sum-subtotal]');
            var totalMirror = q('[data-total-mirror]'); /* opsional: bisa tidak ada */

            var receiptEl = q('[data-receipt]');
            var receiptEmpty = q('[data-receipt-empty]');
            var receiptBody = q('[data-receipt-body]');
            var receiptOrder = q('[data-receipt-order]');
            var receiptDatetime = q('[data-receipt-datetime]');
            var receiptTable = q('[data-receipt-table]');
            var receiptCustomerRow = q('[data-receipt-customer-row]');
            var receiptCustomer = q('[data-receipt-customer]');
            var receiptItems = q('[data-receipt-items]');
            var receiptSubtotal = q('[data-receipt-subtotal]');
            var receiptTotal = q('[data-receipt-total]');
            var receiptMethod = q('[data-receipt-method]');
            var receiptReceivedLabel = q('[data-receipt-received-label]');
            var receiptCash = q('[data-receipt-cash]');
            var receiptChange = q('[data-receipt-change]');
            var receiptStatus = q('[data-receipt-status]');

            var STRUK_LEBAR_MM = parseFloat(receiptEl.getAttribute('data-receipt-width-mm')) || 58;
            var STRUK_TINGGI_MIN_MM = parseFloat(receiptEl.getAttribute('data-receipt-min-height-mm')) || 80;
            var pageStyle = null;
            var printHome = null;

            var orders = [];
            var history = [];
            var selected = null;
            var paid = null;
            var busy = false;
            var wanted = new URLSearchParams(window.location.search).get('pesanan');

            function method() {
                var checked = form.querySelector('input[name="metode"]:checked');

                return checked ? checked.value : 'tunai';
            }

            function totalOf(order) {
                return order ? Number(order.total_harga) || 0 : 0;
            }

            function cash() {
                return Number(cashInput.value) || 0;
            }

            function activeOrders() {
                return orders.filter(Kasir.isActive).sort(function (a, b) {
                    return a.id_pesanan - b.id_pesanan;
                });
            }

            function syncMethod() {
                /* Setelah dibayar, isian uang dikunci dan menampilkan nominal yang diterima. */
                if (paid) {
                    cashInput.readOnly = true;

                    return;
                }

                if (method() === 'tunai') {
                    cashInput.readOnly = false;
                } else {
                    cashInput.value = totalOf(selected);
                    cashInput.readOnly = true;
                }
            }

            function renderQuick() {
                Kasir.clear(quickEl);

                var total = totalOf(selected);

                if (!selected || paid || method() !== 'tunai' || total <= 0) {
                    return;
                }

                var candidates = [
                    total,
                    Math.ceil(total / 5000) * 5000,
                    Math.ceil(total / 10000) * 10000,
                    50000,
                    100000,
                    200000
                ];
                var seen = {};
                var options = [];

                candidates.forEach(function (value) {
                    if (value >= total && !seen[value]) {
                        seen[value] = true;
                        options.push(value);
                    }
                });

                options.slice(0, 4).forEach(function (value, index) {
                    quickEl.appendChild(Kasir.button(index === 0 ? 'Uang pas' : Kasir.rupiah(value), 'chip', function () {
                        cashInput.value = value;
                        renderReceipt();
                        renderMoney();
                    }));
                });
            }

            function lineSubtotal(line) {
                var subtotal = Number(line.subtotal);

                if (!isFinite(subtotal)) {
                    subtotal = (parseInt(line.jumlah, 10) || 0) * (Number(line.harga_satuan) || 0);
                }

                return subtotal;
            }

            function subtotalOf(order) {
                return Kasir.lines(order).reduce(function (sum, line) {
                    return sum + lineSubtotal(line);
                }, 0);
            }

            /* Nilai yang dipakai struk: tunai memakai uang diterima, non-tunai dianggap dibayar pas. */
            function receiptState() {
                var order = paid ? paid.order : selected;
                var total = totalOf(order);
                var methodLabel = paid
                    ? (paid.method || METHOD_LABEL[method()] || 'Tunai')
                    : (METHOD_LABEL[method()] || 'Tunai');
                var isCash = String(methodLabel).toLowerCase().indexOf('tunai') !== -1;
                var received;
                var change;

                if (paid) {
                    received = Number(paid.cash) || 0;
                    change = Number(paid.change) || 0;
                } else if (isCash) {
                    received = cash();
                    change = Math.max(0, received - total);
                } else {
                    received = total;
                    change = 0;
                }

                return {
                    order: order,
                    total: total,
                    methodLabel: methodLabel,
                    isCash: isCash,
                    received: received,
                    change: change
                };
            }

            function formatDateTime(value) {
                var date = new Date(value);

                if (isNaN(date.getTime())) {
                    return '-';
                }

                return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' })
                    + ' ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            }

            function renderMoney() {
                var state = receiptState();

                Kasir.setStat('total', Kasir.rupiah(state.total));
                Kasir.setStat('cash', Kasir.rupiah(state.received));
                Kasir.setStat('change', Kasir.rupiah(state.change));

                if (totalMirror) {
                    totalMirror.textContent = Kasir.rupiah(state.total);
                }
            }

            function renderReceiptItems(order) {
                Kasir.clear(receiptItems);

                Kasir.lines(order).forEach(function (line) {
                    var qty = parseInt(line.jumlah, 10) || 0;
                    var unitPrice = Number(line.harga_satuan);

                    if (!isFinite(unitPrice)) {
                        unitPrice = qty > 0 ? (Number(line.subtotal) || 0) / qty : 0;
                    }

                    var item = Kasir.make('li', 'space-y-0.5');
                    item.appendChild(Kasir.make(
                        'p',
                        'break-words font-semibold leading-snug',
                        line.nama_produk || (line.produk && line.produk.nama_produk) || '-'
                    ));

                    var detail = Kasir.make('div', 'flex items-start justify-between gap-2');
                    detail.appendChild(Kasir.make('span', 'shrink-0 text-cocoa/70', qty + ' x ' + Kasir.rupiah(unitPrice)));
                    detail.appendChild(Kasir.make('span', 'shrink-0 font-semibold', Kasir.rupiah(line.subtotal)));
                    item.appendChild(detail);

                    receiptItems.appendChild(item);
                });
            }

            function renderReceipt() {
                var state = receiptState();
                var order = state.order;
                var customer = order ? Kasir.customer(order) : '';

                receiptEmpty.classList.toggle('hidden', !!order);
                receiptBody.classList.toggle('hidden', !order);

                if (!order) {
                    Kasir.clear(receiptItems);

                    return;
                }

                receiptOrder.textContent = order.nomor_antrean || '-';
                receiptDatetime.textContent = formatDateTime(order.tanggal_pesan);
                receiptTable.textContent = Kasir.tableLabel(order);

                receiptCustomerRow.classList.toggle('hidden', !customer);
                receiptCustomerRow.classList.toggle('flex', !!customer);
                receiptCustomer.textContent = customer || '-';

                renderReceiptItems(order);

                receiptSubtotal.textContent = Kasir.rupiah(subtotalOf(order));
                receiptTotal.textContent = Kasir.rupiah(state.total);
                receiptMethod.textContent = state.methodLabel;
                receiptReceivedLabel.textContent = state.isCash ? 'Uang diterima' : 'Dibayar';
                receiptCash.textContent = Kasir.rupiah(state.received);
                receiptChange.textContent = Kasir.rupiah(state.change);
                receiptStatus.textContent = paid ? 'LUNAS' : 'Pratinjau (belum dibayar)';
                receiptStatus.classList.toggle('text-brand', !!paid);
            }

            /* ==== Cetak: ukuran kertas mengikuti tinggi struk ==== */
            function pxToMm(px) {
                return px * 25.4 / 96;
            }

            function receiptHeightMm() {
                var height = pxToMm(receiptEl.getBoundingClientRect().height) + 6;

                return Math.max(STRUK_TINGGI_MIN_MM, Math.ceil(height));
            }

            function ensurePageStyle() {
                if (!pageStyle) {
                    pageStyle = document.createElement('style');
                    pageStyle.setAttribute('data-receipt-page', '');
                    document.head.appendChild(pageStyle);
                }

                return pageStyle;
            }

            function applyPageSize() {
                /* @@page hanya dipakai saat mencetak, jadi aman ditulis lewat JS. */
                ensurePageStyle().textContent = '@@page { size: '
                    + STRUK_LEBAR_MM + 'mm ' + receiptHeightMm() + 'mm; margin: 0; }';
            }

            function mountReceipt() {
                if (printHome || !receiptEl.parentNode) {
                    return;
                }

                printHome = { parent: receiptEl.parentNode, next: receiptEl.nextSibling };
                document.body.appendChild(receiptEl);
                document.documentElement.classList.add('receipt-printing');
            }

            function unmountReceipt() {
                if (!printHome) {
                    return;
                }

                var home = printHome;
                printHome = null;

                document.documentElement.classList.remove('receipt-printing');

                if (home.parent && home.parent.isConnected) {
                    var before = home.next && home.next.parentNode === home.parent ? home.next : null;
                    home.parent.insertBefore(receiptEl, before);
                }
            }

            function preparePrint() {
                renderReceipt();
                mountReceipt();
                applyPageSize();
            }

            window.addEventListener('beforeprint', preparePrint);
            window.addEventListener('afterprint', unmountReceipt);

            function renderSummaryLines(order) {
                Kasir.clear(sumLines);

                Kasir.lines(order).forEach(function (line) {
                    var qty = parseInt(line.jumlah, 10) || 0;
                    var name = line.nama_produk || (line.produk && line.produk.nama_produk) || '-';
                    var item = Kasir.make('li', 'flex items-start justify-between gap-4 py-2.5');

                    item.appendChild(Kasir.make('span', 'min-w-0 break-words text-[14px] font-semibold text-cocoa', qty + '× ' + name));
                    item.appendChild(Kasir.make('span', 'shrink-0 text-[14px] font-semibold text-cocoa', Kasir.rupiah(lineSubtotal(line))));

                    sumLines.appendChild(item);
                });
            }

            function renderSummary() {
                var order = paid ? paid.order : selected;
                var showControls = !!selected && !paid;

                summaryEmpty.classList.toggle('hidden', !!order);
                summaryEl.classList.toggle('hidden', !order);

                if (order) {
                    var customer = Kasir.customer(order);

                    q('[data-sum-number]').textContent = order.nomor_antrean;
                    q('[data-sum-table]').textContent = Kasir.tableLabel(order);
                    sumCustomer.textContent = customer ? ' • ' + customer : '';
                    q('[data-sum-items]').textContent = Kasir.itemCount(order) + ' item';
                    sumSubtotal.textContent = Kasir.rupiah(subtotalOf(order));
                    renderSummaryLines(order);

                    sumStatus.textContent = paid ? 'Lunas' : 'Menunggu Pembayaran';
                    sumStatus.className = paid
                        ? 'badge badge-lime shrink-0 whitespace-nowrap'
                        : 'badge badge-butter shrink-0 whitespace-nowrap';
                }

                /* Kotak Uang/Kembalian tetap tampil setelah dibayar; hanya kontrol input yang disembunyikan. */
                form.classList.toggle('hidden', !selected);

                Array.prototype.forEach.call(payControls, function (node) {
                    node.classList.toggle('hidden', !showControls);
                });

                afterPay.classList.toggle('hidden', !paid);
                afterPay.classList.toggle('flex', !!paid);

                printBtn.disabled = !paid;
            }

            function renderQueue() {
                var active = activeOrders();

                Kasir.clear(queueEl);

                active.forEach(function (order) {
                    var isSelected = selected && !paid && selected.id_pesanan === order.id_pesanan;
                    var item = Kasir.make('li');
                    var pick = Kasir.make('button',
                        'flex w-full items-center justify-between gap-3 rounded-xl border px-3.5 py-3 text-left transition '
                        + (isSelected ? 'border-brand/45 bg-brand/[0.06]' : 'border-cocoa/10 hover:bg-sand'));
                    pick.type = 'button';

                    var left = Kasir.make('div', 'min-w-0');
                    left.appendChild(Kasir.make('p', 'text-xs font-bold text-cocoa',
                        order.nomor_antrean + ' · ' + Kasir.tableLabel(order)
                        + (Kasir.customer(order) ? ' · ' + Kasir.customer(order) : '')));
                    left.appendChild(Kasir.make('p', 'mt-0.5 text-[11px] text-cocoa/45',
                        Kasir.itemCount(order) + ' item · ' + Kasir.ago(order.tanggal_pesan)));
                    pick.appendChild(left);
                    pick.appendChild(Kasir.make('p', 'shrink-0 font-display text-sm font-bold text-cocoa',
                        Kasir.rupiah(order.total_harga)));

                    pick.addEventListener('click', function () {
                        choose(order);
                    });

                    item.appendChild(pick);
                    queueEl.appendChild(item);
                });

                queueEmpty.classList.toggle('hidden', active.length > 0);
            }

            function renderHistory() {
                Kasir.clear(historyEl);

                history.forEach(function (payment) {
                    var order = payment.pesanan || {};
                    var item = Kasir.make('li', 'flex items-center justify-between gap-3 rounded-xl border border-cocoa/10 px-3.5 py-3');

                    var left = Kasir.make('div', 'min-w-0');
                    left.appendChild(Kasir.make('p', 'text-xs font-bold text-cocoa', order.nomor_antrean || ('Pesanan #' + payment.id_pesanan)));
                    left.appendChild(Kasir.make('p', 'mt-0.5 text-[11px] text-cocoa/45',
                        (payment.metode_pembayaran || '-') + ' · ' + Kasir.ago(payment.tanggal_pembayaran)));
                    item.appendChild(left);

                    var total = order.total_harga !== undefined
                        ? order.total_harga
                        : (Number(payment.jumlah_bayar) || 0) - (Number(payment.kembalian) || 0);
                    item.appendChild(Kasir.make('p', 'shrink-0 font-display text-sm font-bold text-cocoa', Kasir.rupiah(total)));

                    historyEl.appendChild(item);
                });

                historyEmpty.classList.toggle('hidden', history.length > 0);
            }

            function renderAll() {
                syncMethod();
                renderQueue();
                renderSummary();
                renderReceipt();
                renderQuick();
                renderMoney();
            }

            function choose(order) {
                if (busy) {
                    return;
                }

                paid = null;
                selected = order;
                cashInput.value = method() === 'tunai' ? '' : totalOf(order);
                Kasir.hideFlash(flashEl);
                renderAll();
            }

            function reselect() {
                if (paid) {
                    return;
                }

                var active = activeOrders();
                var current = null;

                if (selected) {
                    current = active.filter(function (order) {
                        return order.id_pesanan === selected.id_pesanan;
                    })[0] || null;
                }

                if (!current && wanted) {
                    current = active.filter(function (order) {
                        return String(order.id_pesanan) === String(wanted);
                    })[0] || null;
                    wanted = null;
                }

                if (!current && !selected) {
                    current = active[0] || null;
                }

                var currentId = current ? current.id_pesanan : null;
                var selectedId = selected ? selected.id_pesanan : null;

                if (currentId !== selectedId) {
                    selected = current;
                    cashInput.value = selected && method() !== 'tunai' ? totalOf(selected) : '';
                } else if (current) {
                    /* pesanan sama: perbarui datanya, biarkan isian uang apa adanya */
                    selected = current;
                }
            }

            function refresh() {
                return Promise.all([
                    Kasir.loadOrders(),
                    StaffApi.request('GET', '/pembayaran').then(function (response) {
                        return response.data || [];
                    })
                ])
                    .then(function (result) {
                        orders = result[0];
                        history = result[1];
                        reselect();
                        renderHistory();
                        renderAll();
                    })
                    .catch(function (error) {
                        Kasir.flash(flashEl, 'error', 'Gagal memuat data: ' + error.message);
                    });
            }

            form.addEventListener('change', function () {
                syncMethod();
                renderQuick();
                renderReceipt();
                renderMoney();
            });

            cashInput.addEventListener('input', function () {
                renderReceipt();
                renderMoney();
            });

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                if (busy || !selected || paid) {
                    return;
                }

                var total = totalOf(selected);
                var received = cash();

                Kasir.hideFlash(flashEl);

                if (received < total) {
                    Kasir.flash(flashEl, 'error', 'Uang diterima kurang dari total tagihan.');

                    return;
                }

                busy = true;
                submitBtn.disabled = true;

                StaffApi.request('POST', '/pembayaran', {
                    pesanan_id: selected.id_pesanan,
                    jumlah_bayar: received,
                    metode_pembayaran: method()
                })
                    .then(function (response) {
                        var data = response.data || {};

                        paid = {
                            order: selected,
                            cash: Number(data.jumlah_bayar) || received,
                            change: data.kembalian !== undefined ? Number(data.kembalian) : received - total,
                            method: data.metode_pembayaran || METHOD_LABEL[method()]
                        };

                        cashInput.value = paid.cash;

                        Kasir.flash(flashEl, 'success', 'Pembayaran berhasil. Kembalian ' + Kasir.rupiah(paid.change) + '.');

                        return refresh();
                    })
                    .catch(function (error) {
                        Kasir.flash(flashEl, 'error', error.message);
                    })
                    .then(function () {
                        busy = false;
                        submitBtn.disabled = false;
                        renderAll();
                    });
            });

            printBtn.addEventListener('click', function () {
                if (!paid) {
                    return;
                }

                preparePrint();
                window.print();
            });

            q('[data-next]').addEventListener('click', function () {
                paid = null;
                selected = null;
                cashInput.value = '';
                Kasir.hideFlash(flashEl);
                reselect();
                renderAll();
            });

            refresh();
            Kasir.every(refresh, 20000);
        });
    </script>
@endpush
