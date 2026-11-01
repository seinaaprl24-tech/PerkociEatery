{{-- Manager – Export/Cetak Laporan: CSV dibuat di browser dari /api/laporan/penjualan. --}}
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

@push('head')
    {{-- Saat mencetak, hanya pratinjau laporan yang tampil. --}}
    <style>
        @@media print {
            body * { visibility: hidden; }
            [data-print-area], [data-print-area] * { visibility: visible; }
            [data-print-area] { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; border: 0; }
        }
    </style>
@endpush

@section('content')
    @php $page = $role['export_page']; @endphp

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']" />

    <p data-flash class="hidden" role="status"></p>

    {{-- Pilih periode --}}
    <x-ui.section class="mt-5" title="Periode Laporan" description="Pilih rentang tanggal yang akan diexport. Maksimal 120 hari.">
        <div class="flex flex-wrap gap-2" data-presets>
            <button type="button" class="chip" data-preset="today" aria-pressed="false">Hari Ini</button>
            <button type="button" class="chip chip-active" data-preset="7" aria-pressed="true">7 Hari</button>
            <button type="button" class="chip" data-preset="30" aria-pressed="false">30 Hari</button>
            <button type="button" class="chip" data-preset="month" aria-pressed="false">Bulan Ini</button>
        </div>

        <div class="mt-3 flex flex-wrap items-end gap-3">
            <div class="w-full sm:w-44">
                <label class="field-label" for="period-start">Dari</label>
                <input id="period-start" type="date" class="field" data-start>
            </div>
            <div class="w-full sm:w-44">
                <label class="field-label" for="period-end">Sampai</label>
                <input id="period-end" type="date" class="field" data-end>
            </div>
            <button type="button" class="btn-primary btn-sm" data-apply>Terapkan</button>
        </div>
    </x-ui.section>

    {{-- Format --}}
    <div class="mt-4 grid gap-3 sm:grid-cols-3">
        <x-ui.section title="CSV Rincian Transaksi" description="Satu baris per pesanan: tanggal, meja, menu, total, metode.">
            <button type="button" class="btn-secondary w-full" data-export="detail">
                <span class="ms text-[18px] leading-none" aria-hidden="true">download</span>
                Unduh CSV
            </button>
        </x-ui.section>

        <x-ui.section title="CSV Rekap Harian" description="Satu baris per tanggal: jumlah pesanan dan penjualan.">
            <button type="button" class="btn-secondary w-full" data-export="recap">
                <span class="ms text-[18px] leading-none" aria-hidden="true">download</span>
                Unduh CSV
            </button>
        </x-ui.section>

        <x-ui.section title="Cetak / Simpan PDF" description="Cetak pratinjau di bawah. Pilih “Simpan sebagai PDF” untuk membuat PDF.">
            <button type="button" class="btn-secondary w-full" data-export="print">
                <span class="ms text-[18px] leading-none" aria-hidden="true">print</span>
                Cetak
            </button>
        </x-ui.section>
    </div>

    <div class="mt-3 max-w-xs">
        <label class="field-label" for="csv-delimiter">Pemisah kolom CSV</label>
        <select id="csv-delimiter" class="field" data-delimiter>
            <option value=";">Titik koma ( ; ) — untuk Excel Indonesia</option>
            <option value=",">Koma ( , ) — untuk Excel / Google Sheets bahasa Inggris</option>
        </select>
        <p class="field-hint">Jika semua data muncul dalam satu kolom di Excel, coba pemisah yang satunya.</p>
    </div>

    {{-- Pratinjau (juga area cetak) --}}
    <x-ui.section class="mt-4" title="Pratinjau Laporan" description="Data yang akan diexport atau dicetak." data-print-area>
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <div>
                <p class="font-display text-[15px] font-bold text-cocoa">{{ config('perkoci.brand.name') }} — Laporan Penjualan</p>
                <p class="mt-0.5 text-[11px] text-cocoa/50" data-preview-period>—</p>
            </div>
            <p class="text-[11px] text-cocoa/50">
                <span data-preview-count>0</span> pesanan ·
                <span class="font-bold text-cocoa" data-preview-total>Rp 0</span>
            </p>
        </div>

        <h3 class="mt-4 text-xs font-bold uppercase tracking-[0.08em] text-cocoa/45">Rekap Harian</h3>
        <x-ui.table data-recap-table class="mt-2" :head="['Tanggal', 'Pesanan', 'Penjualan']">
            <tr data-placeholder>
                <td colspan="3" class="px-4 py-6 text-center text-sm text-cocoa/45">Memuat laporan…</td>
            </tr>
        </x-ui.table>

        <h3 class="mt-5 text-xs font-bold uppercase tracking-[0.08em] text-cocoa/45">Rincian Transaksi</h3>
        <x-ui.table data-detail-table class="mt-2" :head="['Pesanan', 'Tanggal', 'Meja', 'Menu', 'Total', 'Metode']">
            <tr data-placeholder>
                <td colspan="6" class="px-4 py-6 text-center text-sm text-cocoa/45">Memuat laporan…</td>
            </tr>
        </x-ui.table>
    </x-ui.section>
@endsection

@push('scripts')
    @include('partials.manager-data')

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var flashEl = document.querySelector('[data-flash]');
            var startInput = document.querySelector('[data-start]');
            var endInput = document.querySelector('[data-end]');
            var presetButtons = Array.prototype.slice.call(document.querySelectorAll('[data-preset]'));
            var delimiterEl = document.querySelector('[data-delimiter]');
            var recapBody = document.querySelector('[data-recap-table] tbody');
            var detailBody = document.querySelector('[data-detail-table] tbody');
            var previewPeriod = document.querySelector('[data-preview-period]');
            var previewCount = document.querySelector('[data-preview-count]');
            var previewTotal = document.querySelector('[data-preview-total]');
            var exportButtons = Array.prototype.slice.call(document.querySelectorAll('[data-export]'));

            var current = null; // { start, end, orders, days, revenue }
            var busy = false;

            function markPreset(name) {
                presetButtons.forEach(function (button) {
                    var active = button.getAttribute('data-preset') === name;

                    button.classList.toggle('chip-active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            }

            function dateText(iso) {
                var date = new Date(iso);

                return isNaN(date.getTime())
                    ? '-'
                    : date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
            }

            function row(cells, classes) {
                var tr = document.createElement('tr');

                cells.forEach(function (text, index) {
                    tr.appendChild(Manager.make('td', classes[index] || '', text));
                });

                return tr;
            }

            function render() {
                Manager.clear(recapBody);
                Manager.clear(detailBody);

                current.days.forEach(function (day) {
                    recapBody.appendChild(row(
                        [Manager.longDay(day.date), day.orders, Manager.rupiah(day.revenue)],
                        ['font-semibold text-cocoa', '', 'whitespace-nowrap font-semibold text-cocoa']
                    ));
                });

                if (current.orders.length === 0) {
                    var empty = document.createElement('tr');
                    var cell = Manager.make('td', 'px-4 py-6 text-center text-sm text-cocoa/45', 'Tidak ada pesanan yang dibayar pada periode ini.');
                    cell.colSpan = 6;
                    empty.appendChild(cell);
                    detailBody.appendChild(empty);
                }

                current.orders.forEach(function (order) {
                    detailBody.appendChild(row(
                        [
                            order.nomor_antrean,
                            dateText(order.tanggal_pesan) + ' ' + Manager.timeLabel(order.tanggal_pesan),
                            Manager.tableLabel(order),
                            Manager.menuSummary(order),
                            Manager.rupiah(order.total_harga),
                            Manager.methodName(order)
                        ],
                        ['font-semibold text-cocoa', 'whitespace-nowrap', 'whitespace-nowrap', 'min-w-[180px] text-cocoa/70',
                            'whitespace-nowrap font-semibold text-cocoa', 'whitespace-nowrap']
                    ));
                });

                previewPeriod.textContent = Manager.longDay(current.start) + ' – ' + Manager.longDay(current.end);
                previewCount.textContent = current.orders.length;
                previewTotal.textContent = Manager.rupiah(current.revenue);
            }

            function load(start, end) {
                var problem = Manager.validRange(start, end);

                if (problem) {
                    Manager.flash(flashEl, 'error', problem);

                    return;
                }

                if (busy) {
                    return;
                }

                busy = true;
                Manager.hideFlash(flashEl);

                Manager.loadSales(start, end)
                    .then(function (sales) {
                        var orders = (sales.data || []).slice().sort(function (a, b) {
                            return a.id_pesanan - b.id_pesanan;
                        });

                        current = {
                            start: start,
                            end: end,
                            orders: orders,
                            days: Manager.groupByDay(orders, start, end),
                            revenue: Number(sales.total_pendapatan) || 0
                        };

                        render();
                    })
                    .catch(function (error) {
                        Manager.flash(flashEl, 'error', 'Gagal memuat laporan: ' + error.message);
                    })
                    .then(function () {
                        busy = false;
                    });
            }

            function usePreset(name) {
                var range = Manager.presetRange(name);

                startInput.value = range.start;
                endInput.value = range.end;
                markPreset(name);
                load(range.start, range.end);
            }

            function exportDetail() {
                var rows = [['Nomor Antrean', 'Tanggal', 'Jam', 'Meja', 'Menu', 'Jumlah Item', 'Total', 'Metode', 'Dibayar', 'Kembalian']];

                current.orders.forEach(function (order) {
                    var payment = order.pembayaran || {};

                    rows.push([
                        order.nomor_antrean,
                        dateText(order.tanggal_pesan),
                        Manager.timeLabel(order.tanggal_pesan),
                        Manager.tableLabel(order),
                        Manager.menuSummary(order),
                        Manager.itemCount(order),
                        Number(order.total_harga) || 0,
                        Manager.methodName(order),
                        Number(payment.jumlah_bayar) || 0,
                        Number(payment.kembalian) || 0
                    ]);
                });

                Manager.downloadCsv('transaksi_' + current.start + '_sd_' + current.end + '.csv', rows, delimiterEl.value);
            }

            function exportRecap() {
                var rows = [['Tanggal', 'Jumlah Pesanan', 'Penjualan']];

                current.days.forEach(function (day) {
                    rows.push([day.date, day.orders, day.revenue]);
                });

                rows.push(['Total', current.orders.length, current.revenue]);

                Manager.downloadCsv('rekap_harian_' + current.start + '_sd_' + current.end + '.csv', rows, delimiterEl.value);
            }

            exportButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!current) {
                        Manager.flash(flashEl, 'error', 'Data laporan belum dimuat. Tunggu sebentar lalu coba lagi.');

                        return;
                    }

                    var kind = button.getAttribute('data-export');

                    if (kind === 'detail') {
                        exportDetail();
                    } else if (kind === 'recap') {
                        exportRecap();
                    } else {
                        window.print();
                    }
                });
            });

            presetButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    usePreset(button.getAttribute('data-preset'));
                });
            });

            document.querySelector('[data-apply]').addEventListener('click', function () {
                markPreset('');
                load(startInput.value, endInput.value);
            });

            usePreset('7');
        });
    </script>
@endpush
