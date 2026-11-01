{{-- Dashboard Manager: data nyata dari /api/laporan/* dan /api/produk. --}}
@extends('layouts.staff')

@section('title', $role['title'].' — Perkoci Eatery')

@section('staff_role', 'manager')
@section('active_nav', 'dashboard')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['title'] }}</p>
    </div>
@endsection

@section('content')
    <x-ui.page-header :title="$role['title']" :subtitle="$role['subtitle']">
        <x-ui.badge tone="lime" icon="calendar_today"><span data-today>Hari ini</span></x-ui.badge>
        <a href="{{ route('manager.reports') }}" class="btn-secondary btn-sm">
            <span class="ms text-[16px] leading-none" aria-hidden="true">bar_chart</span>
            Laporan Lengkap
        </a>
    </x-ui.page-header>

    <p data-flash class="hidden" role="status"></p>

    {{-- Ringkasan angka --}}
    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card data-stat="today" label="Pemasukan Hari Ini" value="Rp 0" icon="payments" tone="brand"
            hint="dari pembayaran lunas" />
        <x-ui.stat-card data-stat="done" label="Pesanan Selesai" value="0" icon="task_alt" tone="lime"
            hint="hari ini" />
        <x-ui.stat-card data-stat="process" label="Sedang Diproses" value="0" icon="hourglass_top" tone="butter"
            hint="hari ini" />
        <x-ui.stat-card data-stat="month" label="Pemasukan Bulan Ini" value="Rp 0" icon="calendar_month" tone="cocoa"
            hint="dari pembayaran lunas" />
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        {{-- Grafik 7 hari --}}
        <x-ui.section class="xl:col-span-2" title="Penjualan 7 Hari Terakhir"
            description="Total penjualan per hari dari pesanan yang sudah dibayar.">
            <div class="flex items-baseline justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-cocoa/45">Total 7 Hari</p>
                    <p class="mt-1 font-display text-[22px] font-bold leading-none text-cocoa" data-week-total>Rp 0</p>
                </div>
                <p class="text-[11px] text-cocoa/40">Skala relatif</p>
            </div>

            <div class="mt-5 flex items-end justify-between gap-1.5 sm:gap-3" data-bars></div>
        </x-ui.section>

        {{-- Perlu perhatian --}}
        <x-ui.section title="Perlu Perhatian" description="Hal yang sebaiknya dicek hari ini.">
            <ul class="space-y-2.5" data-attention></ul>
        </x-ui.section>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        {{-- Transaksi terbaru --}}
        <x-ui.section class="xl:col-span-2" title="Transaksi Terbaru"
            description="Pesanan terakhir yang sudah dibayar (7 hari terakhir)." flush>
            <x-slot:actions>
                <x-ui.badge tone="neutral"><span data-row-count>0</span> transaksi</x-ui.badge>
            </x-slot:actions>

            <x-ui.table data-orders-table class="rounded-none border-0"
                :head="['Pesanan', 'Meja', 'Menu', 'Total', 'Metode', 'Waktu']">
                <tr data-placeholder>
                    <td colspan="6" class="px-4 py-10 text-center text-sm text-cocoa/45">Memuat transaksi…</td>
                </tr>
            </x-ui.table>
        </x-ui.section>

        {{-- Menu terlaris --}}
        <x-ui.section title="Menu Terlaris" description="Lima menu paling banyak terjual (semua waktu).">
            <ul class="space-y-4" data-top></ul>

            <div data-top-empty class="hidden">
                <x-ui.empty-state icon="restaurant_menu" title="Belum ada penjualan"
                    description="Menu terlaris tampil setelah ada pesanan yang dibayar." />
            </div>
        </x-ui.section>
    </div>
@endsection

@push('scripts')
    @include('partials.manager-data')

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var flashEl = document.querySelector('[data-flash]');
            var todayEl = document.querySelector('[data-today]');
            var barsEl = document.querySelector('[data-bars]');
            var weekTotalEl = document.querySelector('[data-week-total]');
            var attentionEl = document.querySelector('[data-attention]');
            var ordersBody = document.querySelector('[data-orders-table] tbody');
            var rowCountEl = document.querySelector('[data-row-count]');
            var topEl = document.querySelector('[data-top]');
            var topEmpty = document.querySelector('[data-top-empty]');

            todayEl.textContent = new Date().toLocaleDateString('id-ID', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
            });

            function attention(tone, icon, text) {
                var styles = tone === 'danger'
                    ? 'bg-danger/10 text-danger'
                    : (tone === 'butter' ? 'bg-butter/60 text-olive' : 'bg-cocoa/10 text-cocoa');

                var item = Manager.make('li', 'flex items-start gap-2.5');
                var iconWrap = Manager.make('span', 'flex h-8 w-8 shrink-0 items-center justify-center rounded-full ' + styles);
                var glyph = Manager.make('span', 'ms text-[16px] leading-none', icon);
                glyph.setAttribute('aria-hidden', 'true');
                iconWrap.appendChild(glyph);
                item.appendChild(iconWrap);
                item.appendChild(Manager.make('p', 'min-w-0 flex-1 pt-1.5 text-xs leading-snug text-cocoa/65', text));

                return item;
            }

            function renderAttention(products, processing) {
                var out = products.filter(function (product) {
                    return product.status === 'aktif' && Number((product.stok || {}).jumlah_stok) <= 0;
                });

                var low = products.filter(function (product) {
                    var stok = product.stok || {};
                    var amount = Number(stok.jumlah_stok) || 0;
                    var limit = Number(stok.min_stok) > 0 ? Number(stok.min_stok) : 5;

                    return product.status === 'aktif' && amount > 0 && amount <= limit;
                });

                Manager.clear(attentionEl);

                if (out.length > 0) {
                    attentionEl.appendChild(attention('danger', 'block', out.length + ' produk stoknya habis dan tidak bisa dipesan.'));
                }

                if (low.length > 0) {
                    attentionEl.appendChild(attention('butter', 'warning', low.length + ' produk stoknya menipis.'));
                }

                if (processing > 0) {
                    attentionEl.appendChild(attention('butter', 'hourglass_top', processing + ' pesanan sedang diproses hari ini.'));
                }

                if (attentionEl.children.length === 0) {
                    attentionEl.appendChild(attention('neutral', 'check_circle', 'Tidak ada hal yang perlu perhatian khusus saat ini.'));
                }
            }

            function renderOrders(orders) {
                var recent = orders.slice().sort(function (a, b) {
                    return b.id_pesanan - a.id_pesanan;
                }).slice(0, 6);

                rowCountEl.textContent = recent.length;
                Manager.clear(ordersBody);

                if (recent.length === 0) {
                    var empty = document.createElement('tr');
                    var cell = Manager.make('td', 'px-4 py-10 text-center text-sm text-cocoa/45', 'Belum ada transaksi dalam 7 hari terakhir.');
                    cell.colSpan = 6;
                    empty.appendChild(cell);
                    ordersBody.appendChild(empty);

                    return;
                }

                recent.forEach(function (order) {
                    var tr = document.createElement('tr');

                    tr.appendChild(Manager.make('td', 'font-semibold text-cocoa', order.nomor_antrean));
                    tr.appendChild(Manager.make('td', 'whitespace-nowrap', Manager.tableLabel(order)));
                    tr.appendChild(Manager.make('td', 'min-w-[220px] text-cocoa/70', Manager.menuSummary(order)));
                    tr.appendChild(Manager.make('td', 'whitespace-nowrap font-semibold text-cocoa', Manager.rupiah(order.total_harga)));

                    var methodCell = Manager.make('td');
                    methodCell.appendChild(Manager.badge('neutral', Manager.methodName(order)));
                    tr.appendChild(methodCell);

                    tr.appendChild(Manager.make('td', 'whitespace-nowrap', Manager.timeLabel(order.tanggal_pesan)));
                    ordersBody.appendChild(tr);
                });
            }

            function renderTop(rows) {
                Manager.clear(topEl);
                topEmpty.classList.toggle('hidden', rows.length > 0);

                var max = rows.reduce(function (highest, row) {
                    return Math.max(highest, Number(row.total_terjual) || 0);
                }, 0);

                rows.forEach(function (row, index) {
                    var sold = Number(row.total_terjual) || 0;
                    var item = Manager.make('li');

                    var head = Manager.make('div', 'flex items-baseline justify-between gap-3');
                    var name = Manager.make('p', 'min-w-0 truncate text-xs font-semibold text-cocoa');
                    name.appendChild(Manager.make('span', 'mr-1 text-cocoa/30', (index + 1) + '.'));
                    name.appendChild(document.createTextNode(row.nama_produk));
                    head.appendChild(name);
                    head.appendChild(Manager.make('p', 'shrink-0 text-[11px] font-bold text-cocoa/50', sold + ' terjual'));
                    item.appendChild(head);

                    var track = Manager.make('div', 'mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-sand');
                    var fill = Manager.make('div', 'h-full rounded-full bg-brand');
                    fill.style.width = (max > 0 ? Math.round(sold / max * 100) : 0) + '%';
                    track.appendChild(fill);
                    item.appendChild(track);

                    item.appendChild(Manager.make('p', 'mt-1 text-[10px] text-cocoa/40', Manager.rupiah(row.total_pendapatan)));
                    topEl.appendChild(item);
                });
            }

            var week = Manager.presetRange('7');

            Promise.all([
                StaffApi.request('GET', '/laporan/daily-stats'),
                StaffApi.request('GET', '/laporan/monthly-income'),
                StaffApi.request('GET', '/laporan/top-products'),
                Manager.loadSales(week.start, week.end),
                Manager.loadAll('/produk')
            ])
                .then(function (result) {
                    var daily = result[0];
                    var monthly = result[1];
                    var top = result[2].data || [];
                    var sales = result[3];
                    var products = result[4];
                    var orders = sales.data || [];

                    Manager.setStat('today', Manager.rupiah(daily.total_pemasukan));
                    Manager.setStat('done', daily.jumlah_pesanan_selesai);
                    Manager.setStat('process', daily.jumlah_pesanan_diproses);
                    Manager.setStat('month', Manager.rupiah(monthly.total_pemasukan));

                    weekTotalEl.textContent = Manager.rupiah(sales.total_pendapatan);
                    Manager.renderBars(barsEl, Manager.groupByDay(orders, week.start, week.end));

                    renderAttention(products, Number(daily.jumlah_pesanan_diproses) || 0);
                    renderOrders(orders);
                    renderTop(top);
                })
                .catch(function (error) {
                    Manager.flash(flashEl, 'error', 'Gagal memuat data: ' + error.message);
                });
        });
    </script>
@endpush
