{{-- Admin – Stok Produk: stok per menu dari /api/produk. Tidak ada stok bahan baku. --}}
@extends('layouts.staff')

@section('title', 'Stok Produk — Perkoci Eatery')

@section('staff_role', 'admin')
@section('active_nav', 'inventory')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">Stok Produk</p>
    </div>
@endsection

@section('content')
    <x-ui.page-header title="Stok Produk"
        subtitle="Jumlah stok tiap menu. Stok berkurang otomatis saat ada pesanan dan kembali jika pesanan dibatalkan." />

    <p data-flash class="hidden" role="status"></p>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card data-stat="total" label="Total Produk" value="0" icon="inventory_2" tone="brand"
            hint="semua menu" />
        <x-ui.stat-card data-stat="menipis" label="Stok Menipis" value="0" icon="warning" tone="butter"
            hint="di bawah batas minimum" />
        <x-ui.stat-card data-stat="habis" label="Stok Habis" value="0" icon="block" tone="danger"
            hint="tidak bisa dipesan" />
    </div>

    <x-ui.section class="mt-4" title="Daftar Stok"
        description="Produk dengan stok paling sedikit ada di atas. Produk yang stoknya habis otomatis tidak bisa dipesan customer."
        flush>
        <x-slot:actions>
            <x-ui.badge tone="neutral"><span data-row-count>0</span> produk</x-ui.badge>
        </x-slot:actions>

        <div class="flex flex-wrap gap-2 border-b border-cocoa/10 px-5 py-3">
            <button type="button" data-filter="all" class="chip chip-active" aria-pressed="true">
                Semua <span class="ml-1 text-[10px] opacity-70" data-count="all">0</span>
            </button>
            <button type="button" data-filter="menipis" class="chip" aria-pressed="false">
                Menipis <span class="ml-1 text-[10px] opacity-70" data-count="menipis">0</span>
            </button>
            <button type="button" data-filter="habis" class="chip" aria-pressed="false">
                Habis <span class="ml-1 text-[10px] opacity-70" data-count="habis">0</span>
            </button>
            <button type="button" data-filter="aman" class="chip" aria-pressed="false">
                Aman <span class="ml-1 text-[10px] opacity-70" data-count="aman">0</span>
            </button>
        </div>

        <x-ui.table data-stock-table class="rounded-none border-0"
            :head="['Produk', 'Kategori', 'Stok', 'Minimum', 'Kondisi', 'Diperbarui', 'Aksi']">
            <tr data-placeholder>
                <td colspan="7" class="px-4 py-10 text-center text-sm text-cocoa/45">Memuat stok…</td>
            </tr>
        </x-ui.table>
    </x-ui.section>

    {{-- Dialog restock / atur stok --}}
    <dialog data-stock-modal class="modal" aria-labelledby="stock-modal-title">
        <form data-stock-form novalidate class="p-5">
            <div class="flex items-start justify-between gap-3">
                <h2 id="stock-modal-title" class="font-display text-[17px] font-bold leading-tight text-cocoa" data-modal-title>
                    Restock
                </h2>

                <button type="button" class="btn-icon -mr-1 -mt-1" data-modal-close aria-label="Tutup">
                    <span class="ms text-[20px] leading-none" aria-hidden="true">close</span>
                </button>
            </div>

            <p class="mt-1 text-[11.5px] text-cocoa/50" data-current></p>

            <p class="field-error hidden" data-form-error role="alert"></p>

            <div class="mt-4 space-y-3.5">
                <div data-group-restock>
                    <label class="field-label" for="stock-tambah">Jumlah yang ditambahkan</label>
                    <input id="stock-tambah" name="tambah" type="number" min="1" step="1" inputmode="numeric"
                        class="field" placeholder="Contoh: 20">
                    <p class="field-hint">Ditambahkan ke stok yang ada sekarang.</p>
                    <p class="field-error hidden" data-error="tambah" role="alert"></p>
                </div>

                <div data-group-set class="hidden space-y-3.5">
                    <div>
                        <label class="field-label" for="stock-jumlah">Jumlah stok</label>
                        <input id="stock-jumlah" name="jumlah_stok" type="number" min="0" step="1" inputmode="numeric"
                            class="field">
                        <p class="field-hint">Mengganti jumlah stok menjadi angka ini (untuk koreksi hasil hitung).</p>
                        <p class="field-error hidden" data-error="jumlah_stok" role="alert"></p>
                    </div>

                    <div>
                        <label class="field-label" for="stock-min">Stok minimum</label>
                        <input id="stock-min" name="min_stok" type="number" min="0" step="1" inputmode="numeric"
                            class="field">
                        <p class="field-hint">Produk ditandai menipis jika stoknya sama dengan atau di bawah angka ini. Isi 0 untuk memakai batas 5.</p>
                        <p class="field-error hidden" data-error="min_stok" role="alert"></p>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="button" class="btn-ghost btn-sm" data-modal-close>Batal</button>
                <button type="submit" class="btn-primary btn-sm" data-submit>Simpan</button>
            </div>
        </form>
    </dialog>
@endsection

@push('scripts')
    @include('partials.admin-data')

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var CONDITION = {
                habis: { label: 'Habis', tone: 'danger' },
                menipis: { label: 'Menipis', tone: 'butter' },
                aman: { label: 'Aman', tone: 'lime' }
            };

            var flashEl = document.querySelector('[data-flash]');
            var tableWrap = document.querySelector('[data-stock-table]');
            var tbody = tableWrap.querySelector('tbody');
            var rowCountEl = document.querySelector('[data-row-count]');
            var modal = document.querySelector('[data-stock-modal]');
            var form = document.querySelector('[data-stock-form]');
            var titleEl = document.querySelector('[data-modal-title]');
            var currentEl = document.querySelector('[data-current]');
            var formError = document.querySelector('[data-form-error]');
            var groupRestock = document.querySelector('[data-group-restock]');
            var groupSet = document.querySelector('[data-group-set]');
            var submitBtn = document.querySelector('[data-submit]');
            var chips = Array.prototype.slice.call(document.querySelectorAll('[data-filter]'));

            var products = [];
            var filter = 'all';
            var editing = null;
            var mode = 'restock';
            var busy = false;

            function stokOf(product) {
                var stok = product.stok || {};

                return {
                    jumlah: Number(stok.jumlah_stok) || 0,
                    min: Number(stok.min_stok) || 0,
                    updated: stok.updated_at || null
                };
            }

            function conditionOf(product) {
                var stok = stokOf(product);

                if (stok.jumlah <= 0) {
                    return 'habis';
                }

                var limit = stok.min > 0 ? stok.min : 5;

                return stok.jumlah <= limit ? 'menipis' : 'aman';
            }

            function thumb(product) {
                var wrap = Admin.make('span', 'flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-sand');

                if (product.foto) {
                    var image = document.createElement('img');
                    image.src = '/storage/' + product.foto;
                    image.alt = '';
                    image.className = 'h-full w-full object-cover';
                    image.loading = 'lazy';
                    wrap.appendChild(image);
                } else {
                    wrap.appendChild(Admin.make('span', 'ms text-[16px] leading-none text-cocoa/40', 'restaurant_menu'));
                }

                return wrap;
            }

            function openModal(product, nextMode) {
                var stok = stokOf(product);

                editing = product;
                mode = nextMode;

                form.reset();
                Admin.clearFieldErrors(form);
                formError.classList.add('hidden');

                titleEl.textContent = (mode === 'restock' ? 'Restock — ' : 'Atur Stok — ') + product.nama_produk;
                currentEl.textContent = 'Stok saat ini ' + stok.jumlah + ' · minimum ' + stok.min;
                groupRestock.classList.toggle('hidden', mode !== 'restock');
                groupSet.classList.toggle('hidden', mode !== 'set');

                if (mode === 'set') {
                    form.elements.jumlah_stok.value = stok.jumlah;
                    form.elements.min_stok.value = stok.min;
                }

                modal.showModal();
                (mode === 'restock' ? form.elements.tambah : form.elements.jumlah_stok).focus();
            }

            function row(product) {
                var tr = document.createElement('tr');
                var stok = stokOf(product);
                var condition = CONDITION[conditionOf(product)];

                var nameCell = Admin.make('td');
                var nameWrap = Admin.make('div', 'flex items-center gap-2.5');
                nameWrap.appendChild(thumb(product));
                nameWrap.appendChild(Admin.make('span', 'font-semibold text-cocoa', product.nama_produk));
                nameCell.appendChild(nameWrap);
                tr.appendChild(nameCell);

                tr.appendChild(Admin.make('td', 'whitespace-nowrap', product.kategori || '-'));
                tr.appendChild(Admin.make('td', 'whitespace-nowrap font-display text-sm font-bold text-cocoa', stok.jumlah));
                tr.appendChild(Admin.make('td', 'whitespace-nowrap', stok.min > 0 ? stok.min : '5 (bawaan)'));

                var conditionCell = Admin.make('td');
                conditionCell.appendChild(Admin.badge(condition.tone, condition.label));
                tr.appendChild(conditionCell);

                tr.appendChild(Admin.make('td', 'whitespace-nowrap',
                    stok.updated ? Admin.dateLabel(String(stok.updated).replace(' ', 'T')) : '-'));

                var actionCell = Admin.make('td');
                var actions = Admin.make('div', 'flex flex-wrap items-center gap-1.5');

                actions.appendChild(Admin.button('Restock', 'btn-primary btn-sm', function () {
                    openModal(product, 'restock');
                }));
                actions.appendChild(Admin.button('Atur', 'btn-secondary btn-sm', function () {
                    openModal(product, 'set');
                }));

                actionCell.appendChild(actions);
                tr.appendChild(actionCell);

                return tr;
            }

            function render() {
                var counts = { all: products.length, habis: 0, menipis: 0, aman: 0 };

                products.forEach(function (product) {
                    counts[conditionOf(product)]++;
                });

                document.querySelectorAll('[data-count]').forEach(function (node) {
                    node.textContent = counts[node.getAttribute('data-count')] || 0;
                });

                Admin.setStat('total', products.length);
                Admin.setStat('menipis', counts.menipis);
                Admin.setStat('habis', counts.habis);

                var rows = products
                    .filter(function (product) {
                        return filter === 'all' || conditionOf(product) === filter;
                    })
                    .sort(function (a, b) {
                        var diff = stokOf(a).jumlah - stokOf(b).jumlah;

                        return diff !== 0 ? diff : String(a.nama_produk).localeCompare(String(b.nama_produk));
                    });

                rowCountEl.textContent = rows.length;
                Admin.clear(tbody);

                if (rows.length === 0) {
                    var empty = document.createElement('tr');
                    var cell = Admin.make('td', 'px-4 py-10 text-center text-sm text-cocoa/45', 'Tidak ada produk pada kondisi ini.');
                    cell.colSpan = 7;
                    empty.appendChild(cell);
                    tbody.appendChild(empty);

                    return;
                }

                rows.forEach(function (product) {
                    tbody.appendChild(row(product));
                });
            }

            function refresh() {
                return Admin.loadAll('/produk')
                    .then(function (list) {
                        products = list;
                        render();
                    })
                    .catch(function (error) {
                        Admin.flash(flashEl, 'error', 'Gagal memuat stok: ' + error.message);
                    });
            }

            function numberOrNull(raw) {
                return raw === '' ? null : Number(raw);
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                if (busy || !editing) {
                    return;
                }

                var body;
                var problems = {};

                if (mode === 'restock') {
                    var add = numberOrNull(form.elements.tambah.value);

                    if (add === null || !Number.isInteger(add) || add < 1) {
                        problems.tambah = ['Isi jumlah yang ditambahkan (bilangan bulat, minimal 1).'];
                    }

                    body = { tambah: add };
                } else {
                    var total = numberOrNull(form.elements.jumlah_stok.value);
                    var min = numberOrNull(form.elements.min_stok.value);

                    if (total === null || !Number.isInteger(total) || total < 0) {
                        problems.jumlah_stok = ['Isi jumlah stok (bilangan bulat, minimal 0).'];
                    }

                    if (min === null || !Number.isInteger(min) || min < 0) {
                        problems.min_stok = ['Isi stok minimum (bilangan bulat, minimal 0).'];
                    }

                    body = { jumlah_stok: total, min_stok: min };
                }

                Admin.clearFieldErrors(form);
                formError.classList.add('hidden');

                if (Object.keys(problems).length > 0) {
                    Admin.showFieldErrors(form, problems);

                    return;
                }

                var name = editing.nama_produk;
                var wasRestock = mode === 'restock';

                busy = true;
                submitBtn.disabled = true;
                Admin.hideFlash(flashEl);

                StaffApi.request('PATCH', '/produk/' + editing.id_produk + '/stok', body)
                    .then(function (response) {
                        var stok = response.data && response.data.stok ? response.data.stok : null;

                        modal.close();
                        Admin.flash(flashEl, 'success',
                            (wasRestock ? 'Stok ' : 'Stok ') + name + ' diperbarui'
                            + (stok ? ': sekarang ' + stok.jumlah_stok + '.' : '.'));

                        return refresh();
                    })
                    .catch(function (error) {
                        if (error.status === 422 && error.data && error.data.errors) {
                            Admin.showFieldErrors(form, error.data.errors);
                        } else {
                            formError.textContent = error.message;
                            formError.classList.remove('hidden');
                        }
                    })
                    .then(function () {
                        busy = false;
                        submitBtn.disabled = false;
                    });
            });

            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    filter = chip.getAttribute('data-filter');

                    chips.forEach(function (other) {
                        var active = other === chip;

                        other.classList.toggle('chip-active', active);
                        other.setAttribute('aria-pressed', active ? 'true' : 'false');
                    });

                    render();
                });
            });

            document.querySelectorAll('[data-modal-close]').forEach(function (node) {
                node.addEventListener('click', function () {
                    modal.close();
                });
            });

            Admin.closeOnBackdrop(modal);
            refresh();
        });
    </script>
@endpush
