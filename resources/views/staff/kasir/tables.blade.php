{{-- Kasir – Meja & QR Code: data nyata dari /api/meja dan /api/pesanan. --}}
@extends('layouts.staff')

@section('title', 'Meja & QR Code — Perkoci Eatery')

@section('staff_role', 'kasir')
@section('active_nav', 'tables')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">Meja & QR Code</p>
    </div>
@endsection

@section('content')
    <x-ui.page-header title="Meja & QR Code" subtitle="Status meja dan link QR untuk customer." />

    <p data-flash class="hidden" role="status"></p>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card data-stat="total" label="Total Meja" value="0" icon="table_restaurant" tone="brand"
            hint="terdaftar di sistem" />
        <x-ui.stat-card data-stat="terisi" label="Terisi" value="0" icon="groups" tone="lime"
            hint="punya pesanan aktif" />
        <x-ui.stat-card data-stat="kosong" label="Kosong" value="0" icon="event_seat" tone="butter"
            hint="siap dipakai" />
    </div>

    <x-ui.section class="mt-4" title="Daftar Meja"
        description="Tombol QR menyalin link pesan untuk meja tersebut. Perubahan data meja dilakukan admin.">
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3" data-tables></div>

        <div data-tables-empty class="hidden">
            <x-ui.empty-state icon="table_restaurant" title="Belum ada meja"
                description="Meja ditambahkan oleh admin." />
        </div>
    </x-ui.section>
@endsection

@push('scripts')
    @include('partials.kasir-data')

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var flashEl = document.querySelector('[data-flash]');
            var gridEl = document.querySelector('[data-tables]');
            var emptyEl = document.querySelector('[data-tables-empty]');
            var origin = window.location.origin;

            function copyLink(table) {
                var url = origin + '/menu?meja=' + encodeURIComponent(table.qr_code);

                function done() {
                    Kasir.flash(flashEl, 'success', 'Link Meja ' + table.no_meja + ' disalin: ' + url);
                }

                function fallback() {
                    Kasir.flash(flashEl, 'success', 'Link Meja ' + table.no_meja + ': ' + url);
                }

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(done, fallback);
                } else {
                    fallback();
                }
            }

            function card(table, occupied) {
                var styles = occupied
                    ? 'border-brand/20 bg-brand/10 text-brand'
                    : 'border-cocoa/10 bg-white text-cocoa/45';

                var item = Kasir.make('div', 'flex items-center gap-3 rounded-xl border px-3.5 py-3 ' + styles);

                item.appendChild(Kasir.make('span',
                    'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/70 font-display text-xs font-bold',
                    table.no_meja));

                var info = Kasir.make('div', 'min-w-0 flex-1');
                info.appendChild(Kasir.make('p', 'truncate text-xs font-bold', 'Meja ' + table.no_meja));
                info.appendChild(Kasir.make('p', 'mt-0.5 truncate text-[10px] opacity-70', occupied ? 'Terisi' : 'Kosong'));
                item.appendChild(info);

                var qrBtn = Kasir.button('', 'btn-icon h-8 w-8', function () {
                    copyLink(table);
                });
                qrBtn.setAttribute('aria-label', 'Salin link QR Meja ' + table.no_meja);
                var qrIcon = Kasir.make('span', 'ms text-[16px] leading-none', 'qr_code_2');
                qrIcon.setAttribute('aria-hidden', 'true');
                qrBtn.appendChild(qrIcon);
                item.appendChild(qrBtn);

                return item;
            }

            function render(orders, tables) {
                var occupiedIds = {};

                orders.filter(Kasir.isActive).forEach(function (order) {
                    occupiedIds[order.id_meja] = true;
                });

                var occupied = tables.filter(function (table) {
                    return occupiedIds[table.id_meja];
                }).length;

                Kasir.setStat('total', tables.length);
                Kasir.setStat('terisi', occupied);
                Kasir.setStat('kosong', tables.length - occupied);

                Kasir.clear(gridEl);
                tables.forEach(function (table) {
                    gridEl.appendChild(card(table, !!occupiedIds[table.id_meja]));
                });

                emptyEl.classList.toggle('hidden', tables.length > 0);
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
