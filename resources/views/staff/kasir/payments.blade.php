{{-- Kasir – Pembayaran & Cetak Struk: data nyata dari /api/pesanan dan /api/pembayaran. --}}
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

@push('head')
    {{-- Saat mencetak, hanya struk yang tampil. --}}
    <style>
        @@media print {
            body * { visibility: hidden; }
            [data-receipt], [data-receipt] * { visibility: visible; }
            [data-receipt] { position: absolute; left: 0; top: 0; width: 100%; border: 0; }
        }
    </style>
@endpush

@section('content')
    @php
        $page = $role['payment_page'];

        $methods = [
            ['value' => 'tunai', 'label' => 'Tunai', 'meta' => 'Uang tunai di kasir'],
            ['value' => 'qris', 'label' => 'QRIS', 'meta' => 'Scan kode QRIS'],
            ['value' => 'e-wallet', 'label' => 'E-Wallet', 'meta' => 'Transfer / dompet digital'],
        ];
    @endphp

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']" />

    <p data-flash class="hidden" role="status"></p>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card data-stat="total" label="Total Tagihan" value="Rp 0" icon="receipt_long" tone="brand"
            hint="dari pesanan terpilih" />
        <x-ui.stat-card data-stat="cash" label="Uang Diterima" value="Rp 0" icon="payments" tone="lime"
            hint="nominal yang diisi kasir" />
        <x-ui.stat-card data-stat="change" label="Kembalian" value="Rp 0" icon="price_check" tone="butter"
            hint="diterima - total" />
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        {{-- Struk --}}
        <x-ui.section title="Pratinjau Struk" description="Isi struk mengikuti pesanan yang dipilih.">
            <div data-receipt class="rounded-xl border border-dashed border-cocoa/25 bg-white px-4 py-4 text-[11px]">
                <div class="text-center">
                    <p class="font-display text-[14px] font-bold text-cocoa">{{ config('perkoci.brand.name') }}</p>
                    <p class="mt-1 text-[10px] leading-relaxed text-cocoa/45" data-receipt-meta>Belum ada pesanan dipilih</p>
                </div>

                <div class="my-3 border-t border-dashed border-cocoa/20"></div>

                <ul class="space-y-1.5" data-receipt-items></ul>

                <div class="my-3 border-t border-dashed border-cocoa/20"></div>

                <dl class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <dt class="text-cocoa/55">Total</dt>
                        <dd class="font-display text-[14px] font-bold text-cocoa" data-receipt-total>Rp 0</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-cocoa/55" data-receipt-method>Tunai</dt>
                        <dd class="font-semibold text-cocoa" data-receipt-cash>Rp 0</dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-dashed border-cocoa/20 pt-1.5">
                        <dt class="font-bold text-cocoa">Kembalian</dt>
                        <dd class="font-display text-[14px] font-bold text-brand" data-receipt-change>Rp 0</dd>
                    </div>
                </dl>

                <p class="mt-3 text-center text-[9.5px] leading-relaxed text-cocoa/35" data-receipt-note>
                    Pilih pesanan di antrean untuk mulai pembayaran.
                </p>
            </div>
        </x-ui.section>

        <div class="space-y-4">
            {{-- Pembayaran --}}
            <x-ui.section title="Pembayaran" description="Konfirmasi pembayaran untuk pesanan yang dipilih.">
                <div data-summary-empty>
                    <x-ui.empty-state icon="payments" title="Belum ada pesanan dipilih"
                        description="Pilih pesanan dari antrean pembayaran." />
                </div>

                <dl class="hidden space-y-2.5" data-summary>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-cocoa/50">Nomor pesanan</dt>
                        <dd class="text-xs font-bold text-cocoa" data-sum-number>-</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-cocoa/50">Meja</dt>
                        <dd class="text-xs font-semibold text-cocoa" data-sum-table>-</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-cocoa/50">Jumlah item</dt>
                        <dd class="text-xs font-semibold text-cocoa" data-sum-items>-</dd>
                    </div>
                </dl>

                <form data-pay-form class="mt-4 hidden space-y-3" novalidate>
                    <div>
                        <p class="field-label">Metode Pembayaran</p>

                        <div class="grid gap-2 sm:grid-cols-3">
                            @foreach ($methods as $method)
                                <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-cocoa/10 bg-sand/70 px-3 py-2.5 transition has-[:checked]:border-brand/45 has-[:checked]:bg-brand/[0.06]">
                                    <input type="radio" name="metode" value="{{ $method['value'] }}"
                                        @checked($loop->first) class="mt-0.5 h-[16px] w-[16px] shrink-0 accent-brand">

                                    <span class="min-w-0">
                                        <span class="block text-[12.5px] font-semibold text-cocoa">{{ $method['label'] }}</span>
                                        <span class="mt-0.5 block text-[10.5px] text-cocoa/50">{{ $method['meta'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="field-label" for="pay-cash">Uang Diterima</label>
                        <input id="pay-cash" data-pay-cash type="number" min="0" step="500" inputmode="numeric"
                            class="field" placeholder="Masukkan nominal">
                        <div class="mt-2 flex flex-wrap gap-1.5" data-quick-cash></div>
                    </div>

                    <button type="submit" class="btn-primary" data-pay-submit>Konfirmasi Pembayaran</button>
                </form>

                <div class="mt-4 hidden flex-wrap gap-2" data-after-pay>
                    <button type="button" class="btn-secondary" data-print>
                        <span class="ms text-[16px] leading-none" aria-hidden="true">print</span>
                        Cetak Struk
                    </button>
                    <button type="button" class="btn-ghost" data-next>Pesanan Berikutnya</button>
                </div>
            </x-ui.section>

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
            var cashInput = q('[data-pay-cash]');
            var quickEl = q('[data-quick-cash]');
            var submitBtn = q('[data-pay-submit]');
            var afterPay = q('[data-after-pay]');
            var queueEl = q('[data-queue]');
            var queueEmpty = q('[data-queue-empty]');
            var historyEl = q('[data-history]');
            var historyEmpty = q('[data-history-empty]');
            var summaryEl = q('[data-summary]');
            var summaryEmpty = q('[data-summary-empty]');
            var receiptMeta = q('[data-receipt-meta]');
            var receiptItems = q('[data-receipt-items]');
            var receiptTotal = q('[data-receipt-total]');
            var receiptMethod = q('[data-receipt-method]');
            var receiptCash = q('[data-receipt-cash]');
            var receiptChange = q('[data-receipt-change]');
            var receiptNote = q('[data-receipt-note]');

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
                        renderMoney();
                    }));
                });
            }

            function renderMoney() {
                var order = paid ? paid.order : selected;
                var total = totalOf(order);
                var received = paid ? paid.cash : cash();
                var change = paid ? paid.change : Math.max(0, received - total);

                Kasir.setStat('total', Kasir.rupiah(total));
                Kasir.setStat('cash', Kasir.rupiah(received));
                Kasir.setStat('change', Kasir.rupiah(change));

                receiptTotal.textContent = Kasir.rupiah(total);
                receiptCash.textContent = Kasir.rupiah(received);
                receiptChange.textContent = Kasir.rupiah(change);
                receiptMethod.textContent = paid ? (paid.method || 'Tunai') : (METHOD_LABEL[method()] || 'Tunai');
            }

            function renderReceipt() {
                var order = paid ? paid.order : selected;

                Kasir.clear(receiptItems);

                if (!order) {
                    receiptMeta.textContent = 'Belum ada pesanan dipilih';
                    receiptNote.textContent = 'Pilih pesanan di antrean untuk mulai pembayaran.';

                    return;
                }

                receiptMeta.textContent = order.nomor_antrean + ' · ' + Kasir.tableLabel(order) + ' · '
                    + new Date(order.tanggal_pesan).toLocaleString('id-ID');

                Kasir.lines(order).forEach(function (line) {
                    var item = Kasir.make('li', 'flex items-start justify-between gap-2');
                    var name = Kasir.make('span', 'min-w-0 text-cocoa/70');
                    name.appendChild(Kasir.make('span', 'font-semibold text-cocoa', (line.jumlah || 0) + '× '));
                    name.appendChild(document.createTextNode(line.nama_produk || (line.produk && line.produk.nama_produk) || '-'));
                    item.appendChild(name);
                    item.appendChild(Kasir.make('span', 'shrink-0 font-semibold text-cocoa', Kasir.rupiah(line.subtotal)));
                    receiptItems.appendChild(item);
                });

                receiptNote.textContent = paid
                    ? 'LUNAS — terima kasih sudah memesan.'
                    : 'Pratinjau struk (belum dibayar).';
            }

            function renderSummary() {
                var order = paid ? paid.order : selected;

                summaryEmpty.classList.toggle('hidden', !!order);
                summaryEl.classList.toggle('hidden', !order);

                if (order) {
                    q('[data-sum-number]').textContent = order.nomor_antrean;
                    q('[data-sum-table]').textContent = Kasir.tableLabel(order);
                    q('[data-sum-items]').textContent = Kasir.itemCount(order) + ' item';
                }

                form.classList.toggle('hidden', !selected || !!paid);
                afterPay.classList.toggle('hidden', !paid);
                afterPay.classList.toggle('flex', !!paid);
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
                        order.nomor_antrean + ' · ' + Kasir.tableLabel(order)));
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
                renderMoney();
            });

            cashInput.addEventListener('input', renderMoney);

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                if (busy || !selected) {
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

            q('[data-print]').addEventListener('click', function () {
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
