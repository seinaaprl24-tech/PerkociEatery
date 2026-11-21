{{-- Manager – Laporan Keuangan: pemasukan nyata dari /api/laporan/penjualan + placeholder pengeluaran. --}}
@extends('layouts.staff')

@section('title', $role['finance_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'manager')
@section('active_nav', 'finance')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['finance_page']['title'] }}</p>
    </div>
@endsection

@section('content')
    @php $page = $role['finance_page']; @endphp

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']">
        <x-ui.badge tone="neutral" icon="calendar_month"><span data-period-label>Bulan Ini</span></x-ui.badge>
    </x-ui.page-header>

    <p data-flash class="hidden" role="status"></p>

    {{-- Pilih periode & metode --}}
    <style>
        .periode-clean .chip {
            border-radius: 10px !important;
            padding: 9px 18px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            line-height: 1.2 !important;
        }
        .periode-clean .chip.chip-active {
            font-weight: 700 !important;
        }
        .periode-clean .periode-methods-group {
            margin-left: 28px;
        }
        @media (max-width: 768px) {
            .periode-clean .periode-methods-group {
                margin-left: 0;
            }
        }
        .periode-clean .periode-date-box {
            width: 185px;
            max-width: 100%;
        }
        .periode-clean .field {
            width: 100% !important;
            height: 44px !important;
            border-radius: 10px !important;
            padding: 0 14px !important;
            font-size: 13.5px !important;
            font-weight: 500 !important;
        }
        .periode-clean .periode-apply {
            height: 44px !important;
            padding: 0 28px !important;
            border-radius: 10px !important;
            font-size: 13.5px !important;
            font-weight: 700 !important;
        }
    </style>

    <x-ui.section class="mt-5 periode-clean" title="Periode" description="Pilih rentang tanggal dan metode pembayaran. Maksimal 120 hari.">
        <div class="flex flex-wrap items-center gap-y-3">
            <div class="flex flex-wrap items-center gap-2" data-presets>
                <span class="mr-1 text-[11px] font-extrabold uppercase tracking-[0.06em] text-cocoa">Periode:</span>
                <button type="button" class="chip" data-preset="today" aria-pressed="false">Hari Ini</button>
                <button type="button" class="chip" data-preset="7" aria-pressed="false">7 Hari</button>
                <button type="button" class="chip" data-preset="30" aria-pressed="false">30 Hari</button>
                <button type="button" class="chip chip-active" data-preset="month" aria-pressed="true">Bulan Ini</button>
            </div>

            <div class="flex flex-wrap items-center gap-2 periode-methods-group" data-methods>
                <span class="mr-1 text-[11px] font-extrabold uppercase tracking-[0.06em] text-cocoa">Metode:</span>
                <button type="button" class="chip chip-active" data-method="all" aria-pressed="true">Semua</button>
                <button type="button" class="chip" data-method="tunai" aria-pressed="false">Tunai</button>
                <button type="button" class="chip" data-method="qris" aria-pressed="false">QRIS</button>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-end gap-3.5 border-t border-cocoa/10 pt-4">
            <div class="periode-date-box">
                <label class="field-label" style="font-size: 11px; font-weight: 700; letter-spacing: 0.06em;" for="finance-start">Dari</label>
                <input id="finance-start" type="date" class="field" data-start>
            </div>
            <div class="periode-date-box">
                <label class="field-label" style="font-size: 11px; font-weight: 700; letter-spacing: 0.06em;" for="finance-end">Sampai</label>
                <input id="finance-end" type="date" class="field" data-end>
            </div>
            <button type="button" class="btn-primary periode-apply" data-apply>Terapkan</button>
        </div>
    </x-ui.section>

    <div class="mt-4 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card data-stat="income" label="Total Pemasukan" value="Rp 0" icon="south_west" tone="lime"
            hint="pembayaran lunas, tanpa kembalian" />
        <x-ui.stat-card data-stat="orders" label="Jumlah Transaksi" value="0" icon="receipt_long" tone="brand"
            hint="pada periode ini" />
        <x-ui.stat-card data-stat="average" label="Rata-rata / Transaksi" value="Rp 0" icon="trending_up" tone="butter"
            hint="pemasukan / transaksi" />
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <x-ui.section title="Pemasukan per Metode Pembayaran" description="Nominal berdasarkan cara pembayaran." flush>
            <x-ui.table data-method-table class="rounded-none border-0" :head="['Metode', 'Transaksi', 'Nominal']">
                <tr data-placeholder>
                    <td colspan="3" class="px-4 py-10 text-center text-sm text-cocoa/45">Memuat…</td>
                </tr>
            </x-ui.table>
        </x-ui.section>

        <x-ui.section title="Pemasukan per Hari" description="Total pemasukan setiap tanggal." flush>
            <x-ui.table data-daily-table class="rounded-none border-0" :head="['Tanggal', 'Transaksi', 'Nominal']">
                <tr data-placeholder>
                    <td colspan="3" class="px-4 py-10 text-center text-sm text-cocoa/45">Memuat…</td>
                </tr>
            </x-ui.table>
        </x-ui.section>
    </div>

    <x-ui.section class="mt-4" title="Pengeluaran"
        description="Pencatatan pengeluaran (bahan, gaji, operasional) belum tersedia.">
        <x-ui.empty-state icon="receipt_long" title="Belum ada pencatatan pengeluaran"
            description="Saldo bersih (pemasukan dikurangi pengeluaran) akan tampil setelah fitur pengeluaran dibuat." />
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
            var methodButtons = Array.prototype.slice.call(document.querySelectorAll('[data-method]'));
            var periodLabel = document.querySelector('[data-period-label]');
            var methodBody = document.querySelector('[data-method-table] tbody');
            var dailyBody = document.querySelector('[data-daily-table] tbody');
            var PRESET_LABEL = { today: 'Hari Ini', '7': '7 Hari', '30': '30 Hari', month: 'Bulan Ini' };
            var METHOD_LABEL = { all: '', tunai: ' · Tunai', qris: ' · QRIS' };

            var busy = false;
            var selectedMethod = 'all';
            var lastSales = null;
            var currentRange = { start: '', end: '', label: 'Bulan Ini' };

            function markPreset(name) {
                presetButtons.forEach(function (button) {
                    var active = button.getAttribute('data-preset') === name;

                    button.classList.toggle('chip-active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            }

            function markMethod(method) {
                selectedMethod = method;
                methodButtons.forEach(function (button) {
                    var active = button.getAttribute('data-method') === method;

                    button.classList.toggle('chip-active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            }

            function renderMethods(methods, totalOrders, totalIncome) {
                Manager.clear(methodBody);

                if (methods.length === 0) {
                    var empty = document.createElement('tr');
                    var cell = Manager.make('td', 'px-4 py-8 text-center text-sm text-cocoa/45', 'Belum ada pembayaran pada periode ini.');
                    cell.colSpan = 3;
                    empty.appendChild(cell);
                    methodBody.appendChild(empty);

                    return;
                }

                methods.forEach(function (row) {
                    var tr = document.createElement('tr');

                    tr.appendChild(Manager.make('td', 'font-semibold text-cocoa', row.name));
                    tr.appendChild(Manager.make('td', '', row.count));
                    tr.appendChild(Manager.make('td', 'whitespace-nowrap font-semibold text-cocoa', Manager.rupiah(row.amount)));
                    methodBody.appendChild(tr);
                });

                var total = document.createElement('tr');
                total.className = 'bg-sand/60';
                total.appendChild(Manager.make('td', 'font-bold text-cocoa', 'Total'));
                total.appendChild(Manager.make('td', 'font-bold text-cocoa', totalOrders));
                total.appendChild(Manager.make('td', 'whitespace-nowrap font-bold text-cocoa', Manager.rupiah(totalIncome)));
                methodBody.appendChild(total);
            }

            function renderDaily(days) {
                Manager.clear(dailyBody);

                var active = days.filter(function (day) { return day.orders > 0; });

                if (active.length === 0) {
                    var empty = document.createElement('tr');
                    var cell = Manager.make('td', 'px-4 py-8 text-center text-sm text-cocoa/45', 'Belum ada pemasukan pada periode ini.');
                    cell.colSpan = 3;
                    empty.appendChild(cell);
                    dailyBody.appendChild(empty);

                    return;
                }

                active.forEach(function (day) {
                    var tr = document.createElement('tr');

                    tr.appendChild(Manager.make('td', 'font-semibold text-cocoa', Manager.longDay(day.date)));
                    tr.appendChild(Manager.make('td', '', day.orders));
                    tr.appendChild(Manager.make('td', 'whitespace-nowrap font-semibold text-cocoa', Manager.rupiah(day.revenue)));
                    dailyBody.appendChild(tr);
                });
            }

            function renderFiltered() {
                if (!lastSales) {
                    return;
                }

                periodLabel.textContent = currentRange.label + (METHOD_LABEL[selectedMethod] || '');

                var allOrders = lastSales.data || [];
                var filteredOrders = allOrders.filter(function (order) {
                    if (selectedMethod === 'all') {
                        return true;
                    }
                    return Manager.methodName(order).toLowerCase().indexOf(selectedMethod) !== -1;
                });

                var transactions = selectedMethod === 'all'
                    ? (Number(lastSales.total_transaksi) || filteredOrders.length)
                    : filteredOrders.length;

                var income = selectedMethod === 'all'
                    ? (Number(lastSales.total_pendapatan) || 0)
                    : filteredOrders.reduce(function (sum, order) { return sum + (Number(order.total_harga) || 0); }, 0);

                var days = Manager.groupByDay(filteredOrders, currentRange.start, currentRange.end);

                Manager.setStat('income', Manager.rupiah(income));
                Manager.setStat('orders', transactions);
                Manager.setStat('average', Manager.rupiah(transactions > 0 ? Math.round(income / transactions) : 0));

                renderMethods(Manager.byMethod(filteredOrders), transactions, income);
                renderDaily(days);
            }

            function load(start, end, label) {
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
                currentRange = { start: start, end: end, label: label };
                periodLabel.textContent = label + (METHOD_LABEL[selectedMethod] || '');

                Manager.loadSales(start, end)
                    .then(function (sales) {
                        lastSales = sales;
                        renderFiltered();
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
                load(range.start, range.end, PRESET_LABEL[name]);
            }

            presetButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    usePreset(button.getAttribute('data-preset'));
                });
            });

            methodButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    markMethod(button.getAttribute('data-method'));
                    renderFiltered();
                });
            });

            document.querySelector('[data-apply]').addEventListener('click', function () {
                markPreset('');
                load(startInput.value, endInput.value, Manager.longDay(startInput.value) + ' – ' + Manager.longDay(endInput.value));
            });

            usePreset('month');
        });
    </script>
@endpush
