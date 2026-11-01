{{-- Dashboard Kasir: data nyata dari API (/api/pesanan, /api/meja). --}}
@extends('layouts.staff')

@section('title', $role['title'].' — Perkoci Eatery')

@section('staff_role', 'kasir')
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
    </x-ui.page-header>

    <p data-flash class="hidden" role="status"></p>

    {{-- Ringkasan angka --}}
    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card data-stat="aktif" label="Pesanan Aktif" value="0" icon="receipt_long" tone="brand"
            hint="menunggu + diproses" />
        <x-ui.stat-card data-stat="selesai" label="Selesai Hari Ini" value="0" icon="task_alt" tone="lime"
            hint="sudah dibayar" />
        <x-ui.stat-card data-stat="meja" label="Meja Terisi" value="0 / 0" icon="table_restaurant" tone="butter"
            hint="punya pesanan aktif" />
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        {{-- Pesanan masuk --}}
        <x-ui.section class="xl:col-span-2" title="Pesanan Masuk"
            description="Pesanan yang perlu ditindaklanjuti. Diperbarui otomatis tiap 20 detik.">
            <x-slot:actions>
                <x-ui.badge tone="danger"><span data-queue-count>0</span> aktif</x-ui.badge>
            </x-slot:actions>

            <ul class="space-y-2.5" data-queue></ul>

            <div data-queue-empty class="hidden">
                <x-ui.empty-state icon="receipt_long" title="Belum ada pesanan aktif"
                    description="Pesanan baru dari customer akan muncul di sini." />
            </div>
        </x-ui.section>

        <div class="space-y-4">
            {{-- Menunggu pembayaran --}}
            <x-ui.section title="Menunggu Pembayaran" description="Pesanan aktif yang belum dibayar.">
                <ul class="space-y-2.5" data-pay-list></ul>

                <div data-pay-empty class="hidden">
                    <x-ui.empty-state icon="payments" title="Tidak ada antrean pembayaran"
                        description="Semua pesanan aktif sudah dibayar." />
                </div>
            </x-ui.section>

            {{-- Aksi cepat --}}
            <x-ui.section title="Aksi Cepat" description="Pintasan ke halaman kerja kasir.">
                <ul class="space-y-2">
                    @foreach ([
                        ['label' => 'Daftar Pesanan', 'icon' => 'receipt_long', 'route' => 'kasir.orders'],
                        ['label' => 'Terima Pembayaran', 'icon' => 'payments', 'route' => 'kasir.payments'],
                        ['label' => 'Status Meja', 'icon' => 'table_restaurant', 'route' => 'kasir.tables'],
                    ] as $action)
                        <li>
                            <a href="{{ route($action['route']) }}"
                                class="flex w-full items-center gap-3 rounded-xl border border-cocoa/10 bg-cream/40 px-3.5 py-3 text-left text-xs font-bold text-cocoa transition hover:bg-sand">
                                <span class="ms text-[18px] leading-none text-brand" aria-hidden="true">{{ $action['icon'] }}</span>
                                <span class="flex-1">{{ $action['label'] }}</span>
                                <span class="ms text-[16px] leading-none text-cocoa/25" aria-hidden="true">chevron_right</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </x-ui.section>
        </div>
    </div>

    {{-- Status meja --}}
    <x-ui.section class="mt-4" title="Status Meja" description="Meja dengan pesanan aktif ditandai terisi.">
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-3 text-[10px] font-semibold text-cocoa/45">
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-brand"></span>Terisi</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-cocoa/20"></span>Kosong</span>
            </div>
        </x-slot:actions>

        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-6" data-tables></div>
    </x-ui.section>
@endsection

@push('scripts')
    @include('partials.kasir-data')

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var flashEl = document.querySelector('[data-flash]');
            var queueEl = document.querySelector('[data-queue]');
            var queueEmpty = document.querySelector('[data-queue-empty]');
            var queueCount = document.querySelector('[data-queue-count]');
            var payEl = document.querySelector('[data-pay-list]');
            var payEmpty = document.querySelector('[data-pay-empty]');
            var tablesEl = document.querySelector('[data-tables]');
            var todayEl = document.querySelector('[data-today]');
            var payUrl = @json(route('kasir.payments'));
            var busy = false;

            if (todayEl) {
                todayEl.textContent = new Date().toLocaleDateString('id-ID', {
                    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
                });
            }

            function processOrder(order, trigger) {
                if (busy) {
                    return;
                }

                busy = true;
                trigger.disabled = true;
                Kasir.hideFlash(flashEl);

                Kasir.setStatus(order.id_pesanan, 'diproses')
                    .then(function () {
                        Kasir.flash(flashEl, 'success', 'Pesanan ' + order.nomor_antrean + ' mulai diproses.');

                        return refresh();
                    })
                    .catch(function (error) {
                        Kasir.flash(flashEl, 'error', error.message);
                    })
                    .then(function () {
                        busy = false;
                        trigger.disabled = false;
                    });
            }

            function queueRow(order) {
                var item = Kasir.make('li', 'flex flex-wrap items-center gap-3 rounded-xl border border-cocoa/10 bg-cream/40 px-3.5 py-3');

                var iconWrap = Kasir.make('span', 'flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sand text-cocoa/50');
                iconWrap.appendChild(Kasir.icon('table_restaurant'));
                item.appendChild(iconWrap);

                var info = Kasir.make('div', 'min-w-0 flex-1');
                var title = Kasir.make('p', 'truncate text-xs font-bold text-cocoa', Kasir.tableLabel(order));
                title.appendChild(Kasir.make('span', 'ml-1 font-medium text-cocoa/40', '· ' + order.nomor_antrean));
                info.appendChild(title);
                info.appendChild(Kasir.make('p', 'mt-0.5 truncate text-[11px] text-cocoa/50', Kasir.menuSummary(order)));
                info.appendChild(Kasir.make('p', 'mt-0.5 text-[10px] text-cocoa/35',
                    Kasir.itemCount(order) + ' item · ' + Kasir.ago(order.tanggal_pesan)));
                item.appendChild(info);

                item.appendChild(Kasir.make('p', 'shrink-0 font-display text-sm font-bold text-cocoa', Kasir.rupiah(order.total_harga)));

                var meta = Kasir.statusMeta(order.status_pesanan);
                item.appendChild(Kasir.badge(meta.tone, meta.label));

                if (order.status_pesanan === 'menunggu') {
                    var processBtn = Kasir.button('Proses', 'btn-secondary btn-sm shrink-0', function () {
                        processOrder(order, processBtn);
                    });
                    item.appendChild(processBtn);
                } else {
                    item.appendChild(Kasir.link('Bayar', 'btn-primary btn-sm shrink-0', payUrl + '?pesanan=' + order.id_pesanan));
                }

                return item;
            }

            function payRow(order) {
                var item = Kasir.make('li', 'rounded-xl border border-cocoa/10 px-3.5 py-3');

                var head = Kasir.make('div', 'flex items-center justify-between gap-2');
                head.appendChild(Kasir.make('p', 'text-xs font-bold text-cocoa', order.nomor_antrean));
                head.appendChild(Kasir.badge('neutral', Kasir.tableLabel(order)));
                item.appendChild(head);

                var foot = Kasir.make('div', 'mt-2 flex items-center justify-between gap-3');
                foot.appendChild(Kasir.make('p', 'font-display text-sm font-bold text-cocoa', Kasir.rupiah(order.total_harga)));
                foot.appendChild(Kasir.link('Terima', 'btn-primary btn-sm', payUrl + '?pesanan=' + order.id_pesanan));
                item.appendChild(foot);

                return item;
            }

            function render(orders, tables) {
                var active = orders.filter(Kasir.isActive).sort(function (a, b) {
                    return a.id_pesanan - b.id_pesanan;
                });

                var occupiedIds = {};
                active.forEach(function (order) {
                    occupiedIds[order.id_meja] = true;
                });

                var occupied = tables.filter(function (table) {
                    return occupiedIds[table.id_meja];
                }).length;

                Kasir.setStat('aktif', active.length);
                Kasir.setStat('selesai', orders.filter(function (order) {
                    return order.status_pesanan === 'selesai' && Kasir.isToday(order.tanggal_pesan);
                }).length);
                Kasir.setStat('meja', occupied + ' / ' + tables.length);

                queueCount.textContent = active.length;

                Kasir.clear(queueEl);
                active.forEach(function (order) {
                    queueEl.appendChild(queueRow(order));
                });
                queueEmpty.classList.toggle('hidden', active.length > 0);

                Kasir.clear(payEl);
                active.slice(0, 6).forEach(function (order) {
                    payEl.appendChild(payRow(order));
                });
                payEmpty.classList.toggle('hidden', active.length > 0);

                Kasir.clear(tablesEl);
                tables.forEach(function (table) {
                    var isOccupied = !!occupiedIds[table.id_meja];
                    var styles = isOccupied
                        ? 'border-brand/20 bg-brand/10 text-brand'
                        : 'border-cocoa/10 bg-white text-cocoa/40';

                    var cell = Kasir.make('div', 'rounded-xl border px-2 py-3 text-center ' + styles);
                    cell.appendChild(Kasir.make('p', 'font-display text-sm font-bold leading-none', table.no_meja));
                    cell.appendChild(Kasir.make('p', 'mt-1 text-[9px] font-bold uppercase tracking-[0.06em]',
                        isOccupied ? 'Terisi' : 'Kosong'));
                    tablesEl.appendChild(cell);
                });
            }

            function refresh() {
                return Promise.all([Kasir.loadOrders(), Kasir.loadTables()])
                    .then(function (result) {
                        render(result[0], result[1]);
                    })
                    .catch(function (error) {
                        Kasir.flash(flashEl, 'error', 'Gagal memuat data: ' + error.message);
                    });
            }

            refresh();
            Kasir.every(refresh, 20000);
        });
    </script>
@endpush
