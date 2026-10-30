{{-- Kasir – Daftar Pesanan: data nyata dari /api/pesanan. --}}
@extends('layouts.staff')

@section('title', $role['orders_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'kasir')
@section('active_nav', 'orders')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['orders_page']['title'] }}</p>
    </div>
@endsection

@section('content')
    @php
        $page = $role['orders_page'];

        $filters = [
            ['key' => 'all', 'label' => 'Semua'],
            ['key' => 'menunggu', 'label' => 'Menunggu'],
            ['key' => 'diproses', 'label' => 'Diproses'],
            ['key' => 'selesai', 'label' => 'Selesai'],
            ['key' => 'batal', 'label' => 'Batal'],
        ];

        $requested = request('status', 'all');
        $activeFilter = in_array($requested, ['menunggu', 'diproses', 'selesai', 'batal'], true) ? $requested : 'all';
    @endphp

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']">
        <x-ui.badge tone="danger" icon="notifications_active"><span data-new-count>0</span> pesanan menunggu</x-ui.badge>
    </x-ui.page-header>

    <p data-flash class="hidden" role="status"></p>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card data-stat="total" label="Total Pesanan" value="0" icon="receipt_long" tone="brand"
            hint="100 pesanan terbaru" />
        <x-ui.stat-card data-stat="nilai" label="Nilai Pesanan" value="Rp 0" icon="payments" tone="lime"
            hint="tanpa pesanan batal" />
        <x-ui.stat-card data-stat="baru" label="Pesanan Menunggu" value="0" icon="schedule" tone="danger"
            hint="perlu ditindaklanjuti" />
    </div>

    <x-ui.section class="mt-4" :title="$page['title']" :description="$page['subtitle']" flush>
        <x-slot:actions>
            <x-ui.badge tone="neutral"><span data-row-count>0</span> baris</x-ui.badge>
        </x-slot:actions>

        <div class="flex flex-wrap gap-2 border-b border-cocoa/10 px-5 py-3">
            @foreach ($filters as $filter)
                <a href="{{ route('kasir.orders', ['status' => $filter['key']]) }}"
                    class="chip {{ $activeFilter === $filter['key'] ? 'chip-active' : '' }}"
                    aria-pressed="{{ $activeFilter === $filter['key'] ? 'true' : 'false' }}">
                    {{ $filter['label'] }}
                    <span class="ml-1 text-[10px] opacity-70" data-count="{{ $filter['key'] }}">0</span>
                </a>
            @endforeach
        </div>

        <div data-orders-empty class="hidden">
            <x-ui.empty-state icon="receipt_long" title="Tidak ada pesanan"
                description="Belum ada pesanan dengan status ini." />
        </div>

        <x-ui.table data-orders-table class="rounded-none border-0"
            :head="['Pesanan', 'Meja', 'Menu', 'Item', 'Waktu', 'Total', 'Status', 'Aksi']">
            <tr data-placeholder>
                <td colspan="8" class="px-4 py-10 text-center text-sm text-cocoa/45">Memuat pesanan…</td>
            </tr>
        </x-ui.table>
    </x-ui.section>
@endsection

@push('scripts')
    @include('partials.kasir-data')

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var filter = @json($activeFilter);
            var payUrl = @json(route('kasir.payments'));
            var flashEl = document.querySelector('[data-flash]');
            var tableWrap = document.querySelector('[data-orders-table]');
            var tbody = tableWrap.querySelector('tbody');
            var emptyEl = document.querySelector('[data-orders-empty]');
            var rowCountEl = document.querySelector('[data-row-count]');
            var newCountEl = document.querySelector('[data-new-count]');
            var busy = false;

            function changeStatus(order, status, trigger, successMessage) {
                if (busy) {
                    return;
                }

                busy = true;
                trigger.disabled = true;
                Kasir.hideFlash(flashEl);

                Kasir.setStatus(order.id_pesanan, status)
                    .then(function () {
                        Kasir.flash(flashEl, 'success', successMessage);

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

            function cell(className, text) {
                return Kasir.make('td', className, text);
            }

            function row(order) {
                var tr = document.createElement('tr');

                tr.appendChild(cell('font-semibold text-cocoa', order.nomor_antrean));
                tr.appendChild(cell('whitespace-nowrap', Kasir.tableLabel(order)));
                tr.appendChild(cell('min-w-[220px] text-cocoa/70', Kasir.menuSummary(order)));
                tr.appendChild(cell('whitespace-nowrap', Kasir.itemCount(order) + ' item'));
                tr.appendChild(cell('whitespace-nowrap', Kasir.ago(order.tanggal_pesan)));
                tr.appendChild(cell('whitespace-nowrap font-semibold text-cocoa', Kasir.rupiah(order.total_harga)));

                var statusCell = cell('');
                var meta = Kasir.statusMeta(order.status_pesanan);
                statusCell.appendChild(Kasir.badge(meta.tone, meta.label));
                tr.appendChild(statusCell);

                var actionCell = cell('');
                var actions = Kasir.make('div', 'flex flex-wrap items-center gap-1.5');

                if (order.status_pesanan === 'menunggu') {
                    var processBtn = Kasir.button('Proses', 'btn-secondary btn-sm', function () {
                        changeStatus(order, 'diproses', processBtn, 'Pesanan ' + order.nomor_antrean + ' mulai diproses.');
                    });
                    actions.appendChild(processBtn);
                }

                if (Kasir.isActive(order)) {
                    actions.appendChild(Kasir.link('Bayar', 'btn-primary btn-sm', payUrl + '?pesanan=' + order.id_pesanan));

                    var cancelBtn = Kasir.button('Batalkan', 'btn-danger btn-sm', function () {
                        if (window.confirm('Batalkan pesanan ' + order.nomor_antrean + '? Stok akan dikembalikan.')) {
                            changeStatus(order, 'batal', cancelBtn, 'Pesanan ' + order.nomor_antrean + ' dibatalkan.');
                        }
                    });
                    actions.appendChild(cancelBtn);
                } else {
                    actions.appendChild(Kasir.make('span', 'text-[11px] text-cocoa/35', '—'));
                }

                actionCell.appendChild(actions);
                tr.appendChild(actionCell);

                return tr;
            }

            function render(orders) {
                var counts = { all: orders.length, menunggu: 0, diproses: 0, selesai: 0, batal: 0 };
                var value = 0;

                orders.forEach(function (order) {
                    if (counts[order.status_pesanan] !== undefined) {
                        counts[order.status_pesanan]++;
                    }

                    if (order.status_pesanan !== 'batal') {
                        value += Number(order.total_harga) || 0;
                    }
                });

                document.querySelectorAll('[data-count]').forEach(function (node) {
                    node.textContent = counts[node.getAttribute('data-count')] || 0;
                });

                Kasir.setStat('total', orders.length);
                Kasir.setStat('nilai', Kasir.rupiah(value));
                Kasir.setStat('baru', counts.menunggu);
                newCountEl.textContent = counts.menunggu;

                var rows = filter === 'all' ? orders : orders.filter(function (order) {
                    return order.status_pesanan === filter;
                });

                rowCountEl.textContent = rows.length;
                Kasir.clear(tbody);
                rows.forEach(function (order) {
                    tbody.appendChild(row(order));
                });

                emptyEl.classList.toggle('hidden', rows.length > 0);
                tableWrap.classList.toggle('hidden', rows.length === 0);
            }

            function refresh() {
                return Kasir.loadOrders()
                    .then(render)
                    .catch(function (error) {
                        Kasir.flash(flashEl, 'error', 'Gagal memuat pesanan: ' + error.message);
                    });
            }

            refresh();
            Kasir.every(refresh, 20000);
        });
    </script>
@endpush
