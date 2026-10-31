{{-- Admin – Meja & QR Code: data nyata dari /api/meja dan /api/pesanan. --}}
@extends('layouts.staff')

@section('title', $role['tables']['title'].' — Perkoci Eatery')

@section('staff_role', 'admin')
@section('active_nav', 'tables')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['tables']['title'] }}</p>
    </div>
@endsection

@section('content')
    <x-ui.page-header :title="$role['tables']['title']"
        subtitle="Kelola meja dan link/QR pesan untuk customer.">
        <button type="button" class="btn-primary btn-sm" data-add-table>
            <span class="ms text-[16px] leading-none" aria-hidden="true">add</span>
            Tambah Meja
        </button>
    </x-ui.page-header>

    <p data-flash class="hidden" role="status"></p>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card data-stat="total" label="Total Meja" value="0" icon="table_restaurant" tone="brand"
            hint="terdaftar di sistem" />
        <x-ui.stat-card data-stat="terisi" label="Terisi" value="0" icon="groups" tone="lime"
            hint="punya pesanan aktif" />
        <x-ui.stat-card data-stat="kosong" label="Kosong" value="0" icon="event_seat" tone="butter"
            hint="siap dipakai" />
    </div>

    <x-ui.section class="mt-4" :title="$role['tables']['title']"
        description="Ikon QR menampilkan kode QR untuk ditempel di meja. Meja yang sudah punya riwayat pesanan tidak bisa dihapus.">
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3" data-tables></div>

        <div data-tables-empty class="hidden">
            <x-ui.empty-state icon="table_restaurant" title="Belum ada meja"
                description="Tambahkan meja pertama dengan tombol Tambah Meja." />
        </div>
    </x-ui.section>

    {{-- Dialog tambah / ubah meja --}}
    <dialog data-table-modal class="modal" aria-labelledby="table-modal-title">
        <form data-table-form novalidate class="p-5">
            <div class="flex items-start justify-between gap-3">
                <h2 id="table-modal-title" class="font-display text-[17px] font-bold leading-tight text-cocoa" data-modal-title>
                    Tambah Meja
                </h2>

                <button type="button" class="btn-icon -mr-1 -mt-1" data-modal-close aria-label="Tutup">
                    <span class="ms text-[20px] leading-none" aria-hidden="true">close</span>
                </button>
            </div>

            <p class="field-error hidden" data-form-error role="alert"></p>

            <div class="mt-4">
                <label class="field-label" for="table-no">Nomor Meja</label>
                <input id="table-no" name="no_meja" type="text" class="field" maxlength="20" autocomplete="off"
                    placeholder="Contoh: 11">
                <p class="field-hint">Kode QR dibuat otomatis oleh sistem.</p>
                <p class="field-error hidden" data-error="no_meja" role="alert"></p>
            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="button" class="btn-ghost btn-sm" data-modal-close>Batal</button>
                <button type="submit" class="btn-primary btn-sm" data-submit>Simpan</button>
            </div>
        </form>
    </dialog>

    {{-- Dialog QR --}}
    <dialog data-qr-modal class="modal" aria-labelledby="qr-modal-title">
        <div class="p-5 text-center">
            <div class="flex items-start justify-between gap-3 text-left">
                <h2 id="qr-modal-title" class="font-display text-[17px] font-bold leading-tight text-cocoa" data-qr-title>
                    QR Meja
                </h2>

                <button type="button" class="btn-icon -mr-1 -mt-1" data-qr-close aria-label="Tutup">
                    <span class="ms text-[20px] leading-none" aria-hidden="true">close</span>
                </button>
            </div>

            <img data-qr-image alt="Kode QR meja" width="220" height="220"
                class="mx-auto mt-4 h-[220px] w-[220px] rounded-xl bg-white ring-1 ring-cocoa/10">

            <p class="mt-3 break-all text-[11px] leading-relaxed text-cocoa/50" data-qr-link></p>

            <p class="mt-2 text-[10.5px] leading-relaxed text-cocoa/40">
                Gambar QR dibuat oleh layanan qrserver.com dan butuh koneksi internet.
            </p>

            <div class="mt-4 flex flex-wrap justify-center gap-2">
                <button type="button" class="btn-secondary btn-sm" data-qr-copy>Salin Link</button>
                <button type="button" class="btn-primary btn-sm" data-qr-print>
                    <span class="ms text-[16px] leading-none" aria-hidden="true">print</span>
                    Cetak
                </button>
            </div>
        </div>
    </dialog>
@endsection

@push('scripts')
    @include('partials.admin-data')

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var origin = window.location.origin;
            var flashEl = document.querySelector('[data-flash]');
            var gridEl = document.querySelector('[data-tables]');
            var emptyEl = document.querySelector('[data-tables-empty]');
            var modal = document.querySelector('[data-table-modal]');
            var form = document.querySelector('[data-table-form]');
            var titleEl = document.querySelector('[data-modal-title]');
            var formError = document.querySelector('[data-form-error]');
            var submitBtn = document.querySelector('[data-submit]');
            var qrModal = document.querySelector('[data-qr-modal]');
            var qrTitle = document.querySelector('[data-qr-title]');
            var qrImage = document.querySelector('[data-qr-image]');
            var qrLinkEl = document.querySelector('[data-qr-link]');

            var tables = [];
            var occupiedIds = {};
            var editing = null;
            var qrCurrent = null;
            var busy = false;

            function orderLink(table) {
                return origin + '/menu?meja=' + encodeURIComponent(table.qr_code);
            }

            function qrSource(table) {
                return 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data='
                    + encodeURIComponent(orderLink(table));
            }

            function openForm(table) {
                editing = table || null;

                form.reset();
                Admin.clearFieldErrors(form);
                formError.classList.add('hidden');

                titleEl.textContent = table ? 'Ubah Meja' : 'Tambah Meja';
                form.elements.no_meja.value = table ? table.no_meja : '';

                modal.showModal();
                form.elements.no_meja.focus();
            }

            function openQr(table) {
                qrCurrent = table;
                qrTitle.textContent = 'QR Meja ' + table.no_meja;
                qrImage.src = qrSource(table);
                qrLinkEl.textContent = orderLink(table);
                qrModal.showModal();
            }

            function removeTable(table) {
                if (!window.confirm('Hapus Meja ' + table.no_meja + '? Tindakan ini tidak bisa dibatalkan.')) {
                    return;
                }

                Admin.hideFlash(flashEl);

                StaffApi.request('DELETE', '/meja/' + table.id_meja)
                    .then(function (response) {
                        Admin.flash(flashEl, 'success', response.message || 'Meja dihapus.');

                        return refresh();
                    })
                    .catch(function (error) {
                        var message = error.status >= 500
                            ? 'Meja tidak bisa dihapus. Kemungkinan sudah punya riwayat pesanan.'
                            : error.message;

                        Admin.flash(flashEl, 'error', message);
                    });
            }

            function card(table) {
                var occupied = !!occupiedIds[table.id_meja];
                var styles = occupied
                    ? 'border-brand/20 bg-brand/10 text-brand'
                    : 'border-cocoa/10 bg-white text-cocoa/45';

                var item = Admin.make('div', 'flex items-center gap-2 rounded-xl border px-3 py-3 ' + styles);

                item.appendChild(Admin.make('span',
                    'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/70 font-display text-xs font-bold',
                    table.no_meja));

                var info = Admin.make('div', 'min-w-0 flex-1');
                info.appendChild(Admin.make('p', 'truncate text-xs font-bold', 'Meja ' + table.no_meja));
                info.appendChild(Admin.make('p', 'mt-0.5 truncate text-[10px] opacity-70',
                    (occupied ? 'Terisi' : 'Kosong') + ' · ' + table.qr_code));
                item.appendChild(info);

                item.appendChild(Admin.iconButton('qr_code_2', 'btn-icon h-8 w-8', 'Lihat QR Meja ' + table.no_meja, function () {
                    openQr(table);
                }));
                item.appendChild(Admin.iconButton('edit', 'btn-icon h-8 w-8', 'Ubah Meja ' + table.no_meja, function () {
                    openForm(table);
                }));
                item.appendChild(Admin.iconButton('delete', 'btn-icon h-8 w-8 hover:bg-danger/10 hover:text-danger',
                    'Hapus Meja ' + table.no_meja, function () {
                        removeTable(table);
                    }));

                return item;
            }

            function render() {
                var occupied = tables.filter(function (table) {
                    return occupiedIds[table.id_meja];
                }).length;

                Admin.setStat('total', tables.length);
                Admin.setStat('terisi', occupied);
                Admin.setStat('kosong', tables.length - occupied);

                Admin.clear(gridEl);
                tables.forEach(function (table) {
                    gridEl.appendChild(card(table));
                });

                emptyEl.classList.toggle('hidden', tables.length > 0);
            }

            function refresh() {
                return Promise.all([
                    Admin.loadAll('/meja'),
                    StaffApi.request('GET', '/pesanan?status=menunggu,diproses&per_page=100')
                ])
                    .then(function (result) {
                        tables = result[0];
                        occupiedIds = {};

                        (result[1].data || []).forEach(function (order) {
                            occupiedIds[order.id_meja] = true;
                        });

                        render();
                    })
                    .catch(function (error) {
                        Admin.flash(flashEl, 'error', 'Gagal memuat meja: ' + error.message);
                    });
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                if (busy) {
                    return;
                }

                var wasEditing = !!editing;
                var body = { no_meja: form.elements.no_meja.value.trim() };

                busy = true;
                submitBtn.disabled = true;
                Admin.clearFieldErrors(form);
                formError.classList.add('hidden');
                Admin.hideFlash(flashEl);

                var request = wasEditing
                    ? StaffApi.request('PUT', '/meja/' + editing.id_meja, body)
                    : StaffApi.request('POST', '/meja', body);

                request
                    .then(function () {
                        modal.close();
                        Admin.flash(flashEl, 'success', wasEditing ? 'Meja diperbarui.' : 'Meja ditambahkan.');

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

            document.querySelector('[data-add-table]').addEventListener('click', function () {
                openForm(null);
            });

            document.querySelectorAll('[data-modal-close]').forEach(function (node) {
                node.addEventListener('click', function () {
                    modal.close();
                });
            });

            document.querySelector('[data-qr-close]').addEventListener('click', function () {
                qrModal.close();
            });

            document.querySelector('[data-qr-copy]').addEventListener('click', function () {
                if (!qrCurrent) {
                    return;
                }

                Admin.copyText(orderLink(qrCurrent), function () {
                    Admin.flash(flashEl, 'success', 'Link Meja ' + qrCurrent.no_meja + ' disalin.');
                    qrModal.close();
                }, function () {
                    Admin.flash(flashEl, 'success', 'Link Meja ' + qrCurrent.no_meja + ': ' + orderLink(qrCurrent));
                    qrModal.close();
                });
            });

            document.querySelector('[data-qr-print]').addEventListener('click', function () {
                if (!qrCurrent) {
                    return;
                }

                var popup = window.open('', '_blank', 'width=420,height=560');

                if (!popup) {
                    Admin.flash(flashEl, 'error', 'Pop-up diblokir browser. Izinkan pop-up untuk mencetak QR.');
                    qrModal.close();

                    return;
                }

                var doc = popup.document;
                doc.title = 'QR Meja ' + qrCurrent.no_meja;
                doc.body.style.cssText = 'font-family: sans-serif; text-align: center; padding: 24px;';

                var heading = doc.createElement('h2');
                heading.textContent = 'Meja ' + qrCurrent.no_meja;
                doc.body.appendChild(heading);

                var image = doc.createElement('img');
                image.width = 300;
                image.height = 300;
                image.onload = function () {
                    popup.focus();
                    popup.print();
                };
                image.src = qrSource(qrCurrent);
                doc.body.appendChild(image);

                var note = doc.createElement('p');
                note.textContent = 'Scan untuk memesan';
                doc.body.appendChild(note);
            });

            Admin.closeOnBackdrop(modal);
            Admin.closeOnBackdrop(qrModal);
            refresh();
        });
    </script>
@endpush
