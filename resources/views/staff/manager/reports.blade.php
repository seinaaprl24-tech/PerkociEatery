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

  {{-- Pilih periode & metode --}}
    <style>
        .periode-clean > div:first-child h2,
        .periode-clean > div:first-child h3,
        .periode-clean > div:first-child p:first-child {
            font-size: 25px !important;
            font-weight: 700 !important;
        }
        .periode-clean > div:first-child p:last-child {
            font-size: 13px !important;
        }
        .periode-clean .chip {
            border-radius: 10px !important;
            padding: 9px 18px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            line-height: 1.2 !important;
        }
        @media (max-width: 768px) {
            .periode-clean .periode-methods-group {
            margin-left: 56px !important;
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

    <x-ui.section class="mt-5 periode-clean" title="Periode" description="Pilih rentang tanggal dan metode pembayaran.">
        <div class="flex flex-wrap items-center gap-y-3">
            <div class="flex flex-wrap items-center gap-2" data-presets>
                <span class="mr-1 text-[11px] font-extrabold uppercase tracking-[0.06em] text-cocoa">Periode:</span>
                <button type="button" class="chip" data-preset="today" aria-pressed="false">Hari Ini</button>
                <button type="button" class="chip chip-active" data-preset="7" aria-pressed="true">7 Hari</button>
                <button type="button" class="chip" data-preset="30" aria-pressed="false">30 Hari</button>
                <button type="button" class="chip" data-preset="month" aria-pressed="false">Bulan Ini</button>
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
                <label class="field-label" style="font-size: 11px; font-weight: 700; letter-spacing: 0.06em;" for="period-start">Dari</label>
                <input id="period-start" type="date" class="field" data-start>
            </div>
            <div class="periode-date-box">
                <label class="field-label" style="font-size: 11px; font-weight: 700; letter-spacing: 0.06em;" for="period-end">Sampai</label>
                <input id="period-end" type="date" class="field" data-end>
            </div>
            <button type="button" class="btn-primary periode-apply" data-apply>Terapkan</button>
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

    <div class="mt-4 grid gap-4 xl:grid-cols-3 items-stretch">
        <x-ui.section class="xl:col-span-2 flex flex-col" title="Penjualan Harian"
            description="Arahkan kursor ke titik untuk melihat nominal dan jumlah pesanan.">
            <div class="flex items-baseline justify-between gap-3 border-b border-cocoa/10 pb-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-cocoa/45">Total Periode</p>
                <p class="font-display text-[18px] font-bold leading-none text-cocoa" data-chart-total>Rp 0</p>
            </div>

            <div class="mt-4 flex-1 flex items-center" data-bars></div>
        </x-ui.section>

        <x-ui.section class="flex flex-col" title="Menu Terlaris" description="Persentase penjualan tiap menu pada periode ini.">
            <div class="mt-2 flex flex-1 flex-col items-center justify-between gap-4" data-donut-wrap>
                <div class="flex w-full justify-center py-1" data-donut></div>
                <ul class="w-full divide-y divide-cocoa/10 overflow-y-auto pr-1" style="max-height: 225px;" data-donut-legend></ul>
            </div>

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
            var methodButtons = Array.prototype.slice.call(document.querySelectorAll('[data-method]'));
            var periodLabel = document.querySelector('[data-period-label]');
            var barsEl = document.querySelector('[data-bars]');
            var chartTotal = document.querySelector('[data-chart-total]');
            var donutWrap = document.querySelector('[data-donut-wrap]');
            var donutEl = document.querySelector('[data-donut]');
            var donutLegendEl = document.querySelector('[data-donut-legend]');
            var topEmpty = document.querySelector('[data-top-empty]');
            var recapBody = document.querySelector('[data-recap-table] tbody');
            var PRESET_LABEL = { today: 'Hari Ini', '7': '7 Hari', '30': '30 Hari', month: 'Bulan Ini' };
            var METHOD_LABEL = { all: '', tunai: ' · Tunai', qris: ' · QRIS' };

            var PRODUCT_COLORS = [
                '#234218',
                '#d97706',
                '#6b8e23',
                '#7c4a21',
                '#0f766e',
                '#b91c1c',
                '#4338ca',
                '#9333ea',
                '#0369a1',
                '#be185d'
            ];

            var busy = false;
            var selectedMethod = 'all';
            var lastSales = null;
            var currentRange = { start: '', end: '', label: '7 Hari' };

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

            function svgNode(tag, attrs) {
                var el = document.createElementNS('http://www.w3.org/2000/svg', tag);
                if (attrs) {
                    Object.keys(attrs).forEach(function (key) {
                        el.setAttribute(key, attrs[key]);
                    });
                }
                return el;
            }

            function collectAllMenu(orders) {
                var map = {};
                (orders || []).forEach(function (order) {
                    var items = Manager.lines(order) || [];
                    items.forEach(function (line) {
                        var key = line.id_produk || ((line.produk && line.produk.nama_produk) || line.nama_produk || 'menu');
                        var name = (line.produk && line.produk.nama_produk) || line.nama_produk || ('Produk #' + key);
                        if (!map[key]) {
                            map[key] = { name: name, sold: 0, revenue: 0 };
                        }
                        map[key].sold += parseInt(line.jumlah, 10) || 0;
                        map[key].revenue += Number(line.subtotal) || 0;
                    });
                });

                return Object.keys(map)
                    .map(function (key) { return map[key]; })
                    .sort(function (a, b) { return b.sold - a.sold; });
            }

            function renderTopDonut(orders) {
                var rows = collectAllMenu(orders);

                Manager.clear(donutEl);
                Manager.clear(donutLegendEl);

                var hasData = rows.length > 0;
                if (donutWrap) { donutWrap.classList.toggle('hidden', !hasData); }
                if (topEmpty) { topEmpty.classList.toggle('hidden', hasData); }

                if (!hasData) {
                    return;
                }

                var totalSold = rows.reduce(function (sum, row) {
                    return sum + (Number(row.sold) || 0);
                }, 0);

                var size = 200, cx = 100, cy = 100, r = 68, strokeW = 32;
                var circ = 2 * Math.PI * r;
                var svg = svgNode('svg', {
                    viewBox: '0 0 ' + size + ' ' + size,
                    style: 'width:100%;max-width:210px;height:auto;aspect-ratio:1/1;display:block;'
                });

                svg.appendChild(svgNode('circle', {
                    cx: cx, cy: cy, r: r, fill: 'none', stroke: '#ece6da', 'stroke-width': strokeW
                }));

                var offset = 0;
                rows.forEach(function (row, idx) {
                    var color = PRODUCT_COLORS[idx % PRODUCT_COLORS.length];
                    var sold = Number(row.sold) || 0;
                    var len = totalSold > 0 ? (sold / totalSold) * circ : 0;

                    var seg = svgNode('circle', {
                        cx: cx, cy: cy, r: r, fill: 'none',
                        stroke: color, 'stroke-width': strokeW,
                        'stroke-dasharray': len + ' ' + (circ - len),
                        'stroke-dashoffset': -offset,
                        transform: 'rotate(-90 ' + cx + ' ' + cy + ')'
                    });
                    var title = svgNode('title');
                    title.textContent = row.name + ': ' + sold + ' terjual (' + Manager.rupiah(row.revenue) + ')';
                    seg.appendChild(title);
                    svg.appendChild(seg);
                    offset += len;

                    var pct = totalSold > 0 ? Math.round((sold / totalSold) * 100) : 0;
                    var li = Manager.make('li', 'flex items-center justify-between gap-2.5 py-2');

                    var left = Manager.make('div', 'flex min-w-0 items-center gap-2');
                    var dot = Manager.make('span', 'h-3 w-3 shrink-0 rounded-full');
                    dot.style.backgroundColor = color;
                    left.appendChild(dot);

                    var nameWrap = Manager.make('div', 'min-w-0');
                    var nameEl = Manager.make('p', 'truncate text-xs font-bold', row.name);
                    nameEl.style.color = color;
                    nameWrap.appendChild(nameEl);
                    nameWrap.appendChild(Manager.make('p', 'text-[10px] text-cocoa/50', sold + ' terjual · ' + Manager.rupiah(row.revenue)));
                    left.appendChild(nameWrap);

                    li.appendChild(left);
                    li.appendChild(Manager.make('span', 'shrink-0 text-xs font-extrabold text-cocoa', pct + '%'));
                    donutLegendEl.appendChild(li);
                });

                var countTxt = svgNode('text', {
                    x: cx, y: cy + 2, 'text-anchor': 'middle',
                    fill: '#3b2a20', 'font-size': '22', 'font-weight': '800'
                });
                countTxt.textContent = totalSold;
                svg.appendChild(countTxt);

                var subTxt = svgNode('text', {
                    x: cx, y: cy + 18, 'text-anchor': 'middle',
                    fill: '#3b2a20', 'fill-opacity': '0.55', 'font-size': '10', 'font-weight': '700'
                });
                subTxt.textContent = 'TERJUAL';
                svg.appendChild(subTxt);

                donutEl.appendChild(svg);
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

                var revenue = selectedMethod === 'all'
                    ? (Number(lastSales.total_pendapatan) || 0)
                    : filteredOrders.reduce(function (sum, order) { return sum + (Number(order.total_harga) || 0); }, 0);

                var days = Manager.groupByDay(filteredOrders, currentRange.start, currentRange.end);

                Manager.setStat('revenue', Manager.rupiah(revenue));
                Manager.setStat('orders', transactions);
                Manager.setStat('average', Manager.rupiah(transactions > 0 ? Math.round(revenue / transactions) : 0));
                Manager.setStat('sold', Manager.totalSold(filteredOrders));

                chartTotal.textContent = Manager.rupiah(revenue);
                Manager.renderBars(barsEl, days);
                renderTopDonut(filteredOrders);
                renderRecap(days, transactions, revenue);
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

            usePreset('7');
        });
    </script>
@endpush
