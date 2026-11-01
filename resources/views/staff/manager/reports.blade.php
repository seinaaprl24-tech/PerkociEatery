{{-- Manager – Laporan Penjualan: data nyata dari /api/laporan/penjualan. --}}
@extends('layouts.staff')

@section('title', $role['reports_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'manager')
@section('active_nav', 'reports')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['reports_page']['title'] }}</p>
    </div>
@endsection

@section('content')
    @php $page = $role['reports_page']; @endphp

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']">
        <x-ui.badge tone="neutral" icon="calendar_month"><span data-period-label>7 Hari</span></x-ui.badge>
    </x-ui.page-header>

    <p data-flash class="hidden" role="status"></p>

    {{-- Pilih periode --}}
    <x-ui.section class="mt-5" title="Periode Laporan" description="Pilih rentang tanggal. Maksimal 120 hari.">
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

    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card data-stat="revenue" label="Total Penjualan" value="Rp 0" icon="payments" tone="brand"
            hint="pesanan yang sudah dibayar" />
        <x-ui.stat-card data-stat="orders" label="Total Pesanan" value="0" icon="receipt_long" tone="lime"
            hint="pada periode ini" />
        <x-ui.stat-card data-stat="average" label="Rata-rata / Pesanan" value="Rp 0" icon="trending_up" tone="butter"
            hint="penjualan / pesanan" />
        <x-ui.stat-card data-stat="sold" label="Menu Terjual" value="0" icon="restaurant_menu" tone="cocoa"
            hint="total porsi / gelas" />
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <x-ui.section class="xl:col-span-2" title="Penjualan Harian"
            description="Arahkan kursor ke batang untuk melihat nominal dan jumlah pesanan.">
            <div class="flex items-baseline justify-between gap-3 border-b border-cocoa/10 pb-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-cocoa/45">Total Periode</p>
                <p class="font-display text-[18px] font-bold leading-none text-cocoa" data-chart-total>Rp 0</p>
            </div>

            <div class="mt-5 flex items-end justify-between gap-1 sm:gap-1.5" data-bars></div>
        </x-ui.section>

        <x-ui.section title="Menu Terlaris" description="Lima menu paling banyak terjual pada periode ini.">
            <ul class="space-y-4" data-top></ul>

            <div data-top-empty class="hidden">
                <x-ui.empty-state icon="restaurant_menu" title="Belum ada penjualan"
                    description="Tidak ada pesanan yang dibayar pada periode ini." />
            </div>
        </x-ui.section>
    </div>

    <x-ui.section class="mt-4" title="Rekap Harian" description="Jumlah pesanan dan penjualan tiap tanggal." flush>
        <x-ui.table data-recap-table class="rounded-none border-0" :head="['Tanggal', 'Pesanan', 'Penjualan']">
            <tr data-placeholder>
                <td colspan="3" class="px-4 py-10 text-center text-sm text-cocoa/45">Memuat laporan…</td>
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
            var periodLabel = document.querySelector('[data-period-label]');
            var barsEl = document.querySelector('[data-bars]');
            var chartTotal = document.querySelector('[data-chart-total]');
            var topEl = document.querySelector('[data-top]');
            var topEmpty = document.querySelector('[data-top-empty]');
            var recapBody = document.querySelector('[data-recap-table] tbody');
            var PRESET_LABEL = { today: 'Hari Ini', '7': '7 Hari', '30': '30 Hari', month: 'Bulan Ini' };
            var busy = false;

            function markPreset(name) {
                presetButtons.forEach(function (button) {
                    var active = button.getAttribute('data-preset') === name;

                    button.classList.toggle('chip-active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            }

            function renderTop(orders) {
                var rows = Manager.topMenu(orders, 5);
                var max = rows.length > 0 ? rows[0].sold : 0;

                Manager.clear(topEl);
                topEmpty.classList.toggle('hidden', rows.length > 0);

                rows.forEach(function (row, index) {
                    var item = Manager.make('li');
                    var head = Manager.make('div', 'flex items-baseline justify-between gap-3');
                    var name = Manager.make('p', 'min-w-0 truncate text-xs font-semibold text-cocoa');

                    name.appendChild(Manager.make('span', 'mr-1 text-cocoa/30', (index + 1) + '.'));
                    name.appendChild(document.createTextNode(row.name));
                    head.appendChild(name);
                    head.appendChild(Manager.make('p', 'shrink-0 text-[11px] font-bold text-cocoa/50', row.sold + ' terjual'));
                    item.appendChild(head);

                    var track = Manager.make('div', 'mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-sand');
                    var fill = Manager.make('div', 'h-full rounded-full bg-brand');
                    fill.style.width = (max > 0 ? Math.round(row.sold / max * 100) : 0) + '%';
                    track.appendChild(fill);
                    item.appendChild(track);

                    item.appendChild(Manager.make('p', 'mt-1 text-[10px] text-cocoa/40', Manager.rupiah(row.revenue)));
                    topEl.appendChild(item);
                });
            }

            function renderRecap(days, totalOrders, totalRevenue) {
                Manager.clear(recapBody);

                days.forEach(function (day) {
                    var tr = document.createElement('tr');

                    tr.appendChild(Manager.make('td', 'font-semibold text-cocoa', Manager.longDay(day.date)));
                    tr.appendChild(Manager.make('td', '', day.orders));
                    tr.appendChild(Manager.make('td', 'whitespace-nowrap font-semibold text-cocoa', Manager.rupiah(day.revenue)));
                    recapBody.appendChild(tr);
                });

                var total = document.createElement('tr');
                total.className = 'bg-sand/60';
                total.appendChild(Manager.make('td', 'font-bold text-cocoa', 'Total'));
                total.appendChild(Manager.make('td', 'font-bold text-cocoa', totalOrders));
                total.appendChild(Manager.make('td', 'whitespace-nowrap font-bold text-cocoa', Manager.rupiah(totalRevenue)));
                recapBody.appendChild(total);
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
                periodLabel.textContent = label;

                Manager.loadSales(start, end)
                    .then(function (sales) {
                        var orders = sales.data || [];
                        var transactions = Number(sales.total_transaksi) || 0;
                        var revenue = Number(sales.total_pendapatan) || 0;
                        var days = Manager.groupByDay(orders, start, end);

                        Manager.setStat('revenue', Manager.rupiah(revenue));
                        Manager.setStat('orders', transactions);
                        Manager.setStat('average', Manager.rupiah(transactions > 0 ? Math.round(revenue / transactions) : 0));
                        Manager.setStat('sold', Manager.totalSold(orders));

                        chartTotal.textContent = Manager.rupiah(revenue);
                        Manager.renderBars(barsEl, days);
                        renderTop(orders);
                        renderRecap(days, transactions, revenue);
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

            document.querySelector('[data-apply]').addEventListener('click', function () {
                markPreset('');
                load(startInput.value, endInput.value, Manager.longDay(startInput.value) + ' – ' + Manager.longDay(endInput.value));
            });

            usePreset('7');
        });
    </script>
@endpush
