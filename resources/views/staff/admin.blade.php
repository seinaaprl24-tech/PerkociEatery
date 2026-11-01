{{-- Dashboard Admin: data nyata dari API (/produk, /meja, /users, /pesanan, /laporan/daily-stats). --}}
@extends('layouts.staff')

@section('title', $role['title'].' — Perkoci Eatery')

@section('staff_role', 'admin')
@section('active_nav', 'dashboard')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['title'] }}</p>
    </div>
@endsection

@section('content')
    <x-ui.page-header :title="$role['title']" :subtitle="$role['subtitle']">
        <a href="{{ route('admin.menu') }}" class="btn-primary btn-sm">
            <span class="ms text-[16px] leading-none" aria-hidden="true">restaurant_menu</span>
            Kelola Menu
        </a>
    </x-ui.page-header>

    <p data-flash class="hidden" role="status"></p>

    {{-- Ringkasan angka --}}
    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card data-stat="menu" label="Total Menu" value="0" icon="restaurant_menu" tone="brand"
            hint="produk terdaftar" />
        <x-ui.stat-card data-stat="meja" label="Total Meja" value="0" icon="table_restaurant" tone="lime"
            hint="meja dengan QR" />
        <x-ui.stat-card data-stat="users" label="Pengguna" value="0" icon="group" tone="butter"
            hint="akun staff" />
        <x-ui.stat-card data-stat="income" label="Pemasukan Hari Ini" value="Rp 0" icon="payments" tone="brand"
            hint="dari pembayaran lunas" />
    </div>

    {{-- Menu terbaru --}}
    <x-ui.section class="mt-4" title="Menu Terbaru" description="Lima produk terakhir yang ditambahkan." flush>
        <x-slot:actions>
            <a href="{{ route('admin.menu') }}" class="btn-secondary btn-sm">Lihat Semua</a>
        </x-slot:actions>

        <x-ui.table data-menu-table class="rounded-none border-0" :head="['Menu', 'Kategori', 'Harga', 'Stok', 'Status']">
            <tr data-placeholder>
                <td colspan="5" class="px-4 py-10 text-center text-sm text-cocoa/45">Memuat menu…</td>
            </tr>
        </x-ui.table>
    </x-ui.section>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        {{-- Pesanan terbaru --}}
        <x-ui.section title="Pesanan Terbaru" description="Lima pesanan terakhir dari semua meja.">
            <ul class="space-y-2.5" data-orders></ul>

            <div data-orders-empty class="hidden">
                <x-ui.empty-state icon="receipt_long" title="Belum ada pesanan"
                    description="Pesanan dari customer akan tampil di sini." />
            </div>
        </x-ui.section>

        {{-- Stok menipis --}}
        <x-ui.section title="Stok Menipis" description="Produk dengan stok di bawah batas minimum.">
            <ul class="space-y-2.5" data-low-stock></ul>

            <div data-low-stock-empty class="hidden">
                <x-ui.empty-state icon="inventory_2" title="Stok aman"
                    description="Tidak ada produk yang stoknya menipis." />
            </div>
        </x-ui.section>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        {{-- Pengguna internal --}}
        <x-ui.section title="Pengguna Internal" description="Akun staff yang bisa login ke dashboard.">
            <x-slot:actions>
                <a href="{{ route('admin.users') }}" class="btn-secondary btn-sm">Kelola</a>
            </x-slot:actions>

            <ul class="divide-y divide-cocoa/10" data-users></ul>
        </x-ui.section>

        {{-- Meja & QR --}}
        <x-ui.section title="Meja & QR" description="Meja dengan pesanan aktif ditandai terisi.">
            <x-slot:actions>
                <a href="{{ route('admin.tables') }}" class="btn-secondary btn-sm">Kelola</a>
            </x-slot:actions>

            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4" data-tables></div>
        </x-ui.section>
    </div>
@endsection

@push('scripts')
    @include('partials.admin-data')

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var ROLE_LABEL = { admin: 'Admin', kasir: 'Kasir', manager: 'Manager' };
            var flashEl = document.querySelector('[data-flash]');
            var menuBody = document.querySelector('[data-menu-table] tbody');
            var ordersEl = document.querySelector('[data-orders]');
            var ordersEmpty = document.querySelector('[data-orders-empty]');
            var lowEl = document.querySelector('[data-low-stock]');
            var lowEmpty = document.querySelector('[data-low-stock-empty]');
            var usersEl = document.querySelector('[data-users]');
            var tablesEl = document.querySelector('[data-tables]');
            var inventoryUrl = @json(route('admin.inventory'));

            function stokOf(product) {
                return product.stok || { jumlah_stok: 0, min_stok: 0 };
            }

            function renderMenu(products) {
                Admin.clear(menuBody);

                products.slice(0, 5).forEach(function (product) {
                    var tr = document.createElement('tr');
                    var stok = stokOf(product);

                    tr.appendChild(Admin.make('td', 'font-semibold text-cocoa', product.nama_produk));
                    tr.appendChild(Admin.make('td', 'whitespace-nowrap', product.kategori || '-'));
                    tr.appendChild(Admin.make('td', 'whitespace-nowrap font-semibold text-cocoa', Admin.rupiah(product.harga)));
                    tr.appendChild(Admin.make('td', 'whitespace-nowrap', stok.jumlah_stok));

                    var statusCell = Admin.make('td');
                    statusCell.appendChild(product.status === 'aktif'
                        ? Admin.badge('lime', 'Tersedia')
                        : Admin.badge('neutral', 'Nonaktif'));
                    tr.appendChild(statusCell);

                    menuBody.appendChild(tr);
                });

                if (products.length === 0) {
                    var empty = document.createElement('tr');
                    var cell = Admin.make('td', 'px-4 py-10 text-center text-sm text-cocoa/45', 'Belum ada menu.');
                    cell.colSpan = 5;
                    empty.appendChild(cell);
                    menuBody.appendChild(empty);
                }
            }

            function renderOrders(orders) {
                Admin.clear(ordersEl);

                orders.slice(0, 5).forEach(function (order) {
                    var meta = Admin.statusMeta(order.status_pesanan);
                    var item = Admin.make('li', 'flex items-center gap-3 rounded-xl border border-cocoa/10 px-3.5 py-3');

                    var info = Admin.make('div', 'min-w-0 flex-1');
                    info.appendChild(Admin.make('p', 'truncate text-xs font-bold text-cocoa',
                        order.nomor_antrean + ' · ' + Admin.tableLabel(order)));
                    info.appendChild(Admin.make('p', 'mt-0.5 text-[11px] text-cocoa/45', Admin.ago(order.tanggal_pesan)));
                    item.appendChild(info);

                    item.appendChild(Admin.make('p', 'shrink-0 font-display text-sm font-bold text-cocoa', Admin.rupiah(order.total_harga)));
                    item.appendChild(Admin.badge(meta.tone, meta.label));

                    ordersEl.appendChild(item);
                });

                ordersEmpty.classList.toggle('hidden', orders.length > 0);
            }

            function renderLowStock(products) {
                Admin.clear(lowEl);

                var low = products
                    .filter(function (product) {
                        var stok = stokOf(product);
                        var limit = stok.min_stok > 0 ? stok.min_stok : 5;

                        return stok.jumlah_stok <= limit;
                    })
                    .sort(function (a, b) {
                        return stokOf(a).jumlah_stok - stokOf(b).jumlah_stok;
                    })
                    .slice(0, 6);

                low.forEach(function (product) {
                    var stok = stokOf(product);
                    var dot = stok.jumlah_stok <= 0 ? 'bg-danger' : 'bg-butter';
                    var item = Admin.make('li', 'flex items-center gap-3 rounded-xl border border-cocoa/10 px-3.5 py-3');

                    item.appendChild(Admin.make('span', 'h-2 w-2 shrink-0 rounded-full ' + dot));
                    item.appendChild(Admin.make('p', 'min-w-0 flex-1 truncate text-xs font-semibold text-cocoa', product.nama_produk));
                    item.appendChild(Admin.make('p', 'shrink-0 text-[11px] font-bold text-cocoa/45', 'sisa ' + stok.jumlah_stok));
                    item.appendChild(Admin.link('Restock', 'btn-secondary btn-sm shrink-0', inventoryUrl));

                    lowEl.appendChild(item);
                });

                lowEmpty.classList.toggle('hidden', low.length > 0);
            }

            function renderUsers(users) {
                Admin.clear(usersEl);

                users.slice(0, 5).forEach(function (user) {
                    var item = Admin.make('li', 'flex items-center gap-3 py-3 first:pt-0 last:pb-0');

                    item.appendChild(Admin.make('span',
                        'flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sand text-[10px] font-extrabold text-cocoa/55',
                        (user.nama || '?').charAt(0).toUpperCase()));

                    var info = Admin.make('div', 'min-w-0 flex-1');
                    info.appendChild(Admin.make('p', 'truncate text-xs font-bold text-cocoa', user.nama));
                    info.appendChild(Admin.make('p', 'mt-0.5 text-[11px] text-cocoa/45', '@' + user.username));
                    item.appendChild(info);

                    item.appendChild(Admin.badge('neutral', ROLE_LABEL[user.role] || user.role));

                    usersEl.appendChild(item);
                });
            }

            function renderTables(tables, orders) {
                var occupiedIds = {};

                orders.forEach(function (order) {
                    if (order.status_pesanan === 'menunggu' || order.status_pesanan === 'diproses') {
                        occupiedIds[order.id_meja] = true;
                    }
                });

                Admin.clear(tablesEl);

                tables.forEach(function (table) {
                    var occupied = !!occupiedIds[table.id_meja];
                    var styles = occupied
                        ? 'border-brand/20 bg-brand/10 text-brand'
                        : 'border-cocoa/10 bg-white text-cocoa/40';

                    var cell = Admin.make('div', 'rounded-xl border px-2 py-3 text-center ' + styles);
                    cell.appendChild(Admin.make('p', 'font-display text-sm font-bold leading-none', table.no_meja));
                    cell.appendChild(Admin.make('p', 'mt-1 text-[9px] font-bold uppercase tracking-[0.06em]',
                        occupied ? 'Terisi' : 'Kosong'));
                    tablesEl.appendChild(cell);
                });
            }

            Promise.all([
                Admin.loadAll('/produk'),
                Admin.loadAll('/meja'),
                StaffApi.request('GET', '/users'),
                StaffApi.request('GET', '/pesanan?per_page=100'),
                StaffApi.request('GET', '/laporan/daily-stats')
            ])
                .then(function (result) {
                    var products = result[0];
                    var tables = result[1];
                    var users = result[2];
                    var orders = result[3].data || [];
                    var income = result[4];

                    Admin.setStat('menu', products.length);
                    Admin.setStat('meja', tables.length);
                    Admin.setStat('users', users.meta && users.meta.total !== undefined ? users.meta.total : (users.data || []).length);
                    Admin.setStat('income', Admin.rupiah(income.total_pemasukan));

                    renderMenu(products);
                    renderOrders(orders);
                    renderLowStock(products);
                    renderUsers(users.data || []);
                    renderTables(tables, orders);
                })
                .catch(function (error) {
                    Admin.flash(flashEl, 'error', 'Gagal memuat data: ' + error.message);
                });
        });
    </script>
@endpush
