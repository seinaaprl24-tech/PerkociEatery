{{-- Admin – Kelola Menu: data nyata dari /api/produk. --}}
@extends('layouts.staff')

@section('title', $role['menu_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'admin')
@section('active_nav', 'menu')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['menu_page']['title'] }}</p>
    </div>
@endsection

@section('content')
    <x-ui.page-header :title="$role['menu_page']['title']"
        subtitle="Kelola daftar menu dari database. Tambah, ubah, atau hapus produk langsung dari halaman ini.">
        <x-ui.badge tone="lime" icon="database">Data Produk</x-ui.badge>

        <button type="button" class="btn-primary btn-sm" data-add-menu>
            <span class="ms text-[16px] leading-none" aria-hidden="true">add</span>
            Tambah Menu
        </button>
    </x-ui.page-header>

    <p data-flash class="hidden" role="status"></p>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card data-stat="total" label="Total Menu" value="0" icon="restaurant_menu" tone="brand"
            hint="produk di database" />
        <x-ui.stat-card data-stat="minuman" label="Minuman" value="0" icon="local_cafe" tone="lime"
            hint="kategori minuman" />
        <x-ui.stat-card data-stat="makanan" label="Makanan" value="0" icon="restaurant" tone="butter"
            hint="kategori makanan" />
    </div>

    <x-ui.section class="mt-4" :title="$role['menu_page']['title']"
        description="Nama, kategori, harga, deskripsi, dan status produk diambil langsung dari tabel produk." flush>
        <x-slot:actions>
            <x-ui.badge tone="neutral"><span data-row-count>0</span> menu</x-ui.badge>

            <div class="flex items-center gap-2">
                <input type="search" class="field h-9 w-44 text-xs" data-search placeholder="Cari menu…"
                    aria-label="Cari menu" autocomplete="off">

                <button type="button" class="btn-secondary btn-sm" data-search-button>
                    <span class="ms text-[16px] leading-none" aria-hidden="true">search</span>
                    Cari
                </button>
            </div>
        </x-slot:actions>

        <x-ui.table data-menu-table class="rounded-none border-0" :head="['Menu', 'Kategori', 'Harga', 'Status', 'Aksi']">
            <tr data-placeholder>
                <td colspan="5" class="px-4 py-10 text-center text-sm text-cocoa/45">Memuat menu…</td>
            </tr>
        </x-ui.table>
    </x-ui.section>

    <dialog data-menu-modal class="modal" aria-labelledby="menu-modal-title">
        <form data-menu-form novalidate class="p-5">
            <div class="flex items-start justify-between gap-3">
                <h2 id="menu-modal-title" class="font-display text-[17px] font-bold leading-tight text-cocoa"
                    data-modal-title>
                    Tambah Menu
                </h2>

                <button type="button" class="btn-icon -mr-1 -mt-1" data-modal-close aria-label="Tutup">
                    <span class="ms text-[20px] leading-none" aria-hidden="true">close</span>
                </button>
            </div>

            <p class="field-error hidden" data-form-error role="alert"></p>

            <div class="mt-4 space-y-3.5">
                <div>
                    <label class="field-label" for="menu-nama">Nama Produk</label>
                    <input id="menu-nama" name="nama_produk" type="text" class="field" maxlength="255"
                        autocomplete="off">
                    <p class="field-error hidden" data-error="nama_produk" role="alert"></p>
                </div>

                <div>
                    <label class="field-label" for="menu-kategori">Kategori</label>
                    <input id="menu-kategori" name="kategori" type="text" class="field" maxlength="255"
                        list="menu-categories" autocomplete="off">
                    <datalist id="menu-categories" data-categories></datalist>
                    <p class="field-error hidden" data-error="kategori" role="alert"></p>
                </div>

                <div>
                    <label class="field-label" for="menu-harga">Harga</label>
                    <input id="menu-harga" name="harga" type="number" class="field" min="0" step="1"
                        inputmode="numeric" placeholder="Contoh: 18000">
                    <p class="field-error hidden" data-error="harga" role="alert"></p>
                </div>

                <div>
                    <label class="field-label" for="menu-deskripsi">Deskripsi</label>
                    <textarea id="menu-deskripsi" name="deskripsi" class="field min-h-[84px]" rows="3"
                        placeholder="Opsional"></textarea>
                    <p class="field-error hidden" data-error="deskripsi" role="alert"></p>
                </div>

                <div>
                    <label class="field-label" for="menu-status">Status</label>
                    <select id="menu-status" name="status" class="field">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                    <p class="field-error hidden" data-error="status" role="alert"></p>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="button" class="btn-ghost btn-sm" data-modal-close>Batal</button>
                <button type="submit" class="btn-primary btn-sm" data-submit>Simpan</button>
            </div>
        </form>
    </dialog>

    <dialog data-photo-modal class="modal" aria-labelledby="photo-modal-title">
        <form data-photo-form novalidate class="p-5">
            <div class="flex items-start justify-between gap-3">
                <h2 id="photo-modal-title" class="font-display text-[17px] font-bold leading-tight text-cocoa"
                    data-photo-title>
                    Edit Foto
                </h2>

                <button type="button" class="btn-icon -mr-1 -mt-1" data-photo-close aria-label="Tutup">
                    <span class="ms text-[20px] leading-none" aria-hidden="true">close</span>
                </button>
            </div>

            <p class="field-error hidden" data-photo-error role="alert"></p>

            <div class="mt-4 space-y-3.5">
                <div class="flex justify-center">
                    <div data-photo-preview
                        class="flex h-40 w-40 items-center justify-center overflow-hidden rounded-2xl bg-sand ring-1 ring-cocoa/10">
                    </div>
                </div>

                <div>
                    <label class="field-label" for="menu-foto">Pilih Foto Baru</label>
                    <input id="menu-foto" name="foto" type="file" class="field"
                        accept="image/jpeg,image/png,image/webp">
                    <p class="field-hint">Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB. Foto baru akan menggantikan
                        foto lama.</p>
                    <p class="field-error hidden" data-error="foto" role="alert"></p>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="button" class="btn-ghost btn-sm" data-photo-close>Batal</button>
                <button type="submit" class="btn-primary btn-sm" data-photo-submit>Simpan Foto</button>
            </div>
        </form>
    </dialog>
@endsection

@push('scripts')
    @include('partials.admin-data')

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var baseUrl = @json(url('/'));
            var flashEl = document.querySelector('[data-flash]');
            var tableWrap = document.querySelector('[data-menu-table]');
            var tbody = tableWrap.querySelector('tbody');
            var rowCountEl = document.querySelector('[data-row-count]');
            var searchInput = document.querySelector('[data-search]');
            var searchButton = document.querySelector('[data-search-button]');
            var categoryList = document.querySelector('[data-categories]');
            var modal = document.querySelector('[data-menu-modal]');
            var form = document.querySelector('[data-menu-form]');
            var titleEl = document.querySelector('[data-modal-title]');
            var formError = document.querySelector('[data-form-error]');
            var submitBtn = document.querySelector('[data-submit]');
            var photoModal = document.querySelector('[data-photo-modal]');
            var photoForm = document.querySelector('[data-photo-form]');
            var photoTitle = document.querySelector('[data-photo-title]');
            var photoPreview = document.querySelector('[data-photo-preview]');
            var photoInput = document.querySelector('[data-photo-form] [name="foto"]');
            var photoError = document.querySelector('[data-photo-error]');
            var photoSubmit = document.querySelector('[data-photo-submit]');

            var products = [];
            var editing = null;
            var photoTarget = null;
            var previewUrl = null;
            var keyword = '';
            var busy = false;

            function isMinuman(kategori) {
                var value = String(kategori || '').toLowerCase();
                var keywords = ['kopi', 'coffee', 'teh', 'tea', 'minum', 'jus', 'juice', 'susu', 'drink', 'beverage'];

                for (var i = 0; i < keywords.length; i++) {
                    if (value.indexOf(keywords[i]) !== -1) {
                        return true;
                    }
                }

                return false;
            }

            function imageUrl(path) {
                if (!path) {
                    return '';
                }

                if (/^https?:\/\//i.test(path)) {
                    return path;
                }

                return baseUrl + '/' + path.replace(/^\/+/, '');
            }

            function placeholder(product) {
                var node = Admin.make('div', 'flex h-full w-full items-center justify-center bg-brand/10 text-brand');
                node.setAttribute('role', 'img');
                node.setAttribute('aria-label', product.nama_produk + ' - foto belum tersedia');
                node.appendChild(Admin.icon(isMinuman(product.kategori) ? 'local_cafe' : 'restaurant'));

                return node;
            }

            function thumbnail(product) {
                var thumb = Admin.make('span', 'h-9 w-9 shrink-0 overflow-hidden rounded-lg bg-sand');

                if (product.image) {
                    var image = document.createElement('img');
                    image.src = imageUrl(product.image);
                    image.alt = product.nama_produk;
                    image.width = 72;
                    image.height = 72;
                    image.loading = 'lazy';
                    image.className = 'h-full w-full object-cover';
                    image.addEventListener('error', function () {
                        image.remove();
                        thumb.appendChild(placeholder(product));
                    });
                    thumb.appendChild(image);
                } else {
                    thumb.appendChild(placeholder(product));
                }

                return thumb;
            }

            function photoPlaceholder(product) {
                var node = Admin.make('div', 'flex h-full w-full flex-col items-center justify-center gap-1 bg-brand/10 text-brand');
                var icon = Admin.icon(isMinuman(product.kategori) ? 'local_cafe' : 'restaurant');
                icon.className = 'ms text-[32px] leading-none';
                node.appendChild(icon);
                node.appendChild(Admin.make('span', 'text-[10px] font-semibold text-cocoa/45', 'Belum ada foto'));

                return node;
            }

            function renderPhotoPreview(src, product) {
                Admin.clear(photoPreview);

                if (src) {
                    var image = document.createElement('img');
                    image.src = src;
                    image.alt = 'Foto ' + (product ? product.nama_produk : 'produk');
                    image.className = 'h-full w-full object-cover';
                    image.addEventListener('error', function () {
                        image.remove();
                        photoPreview.appendChild(photoPlaceholder(product));
                    });
                    photoPreview.appendChild(image);
                } else {
                    photoPreview.appendChild(photoPlaceholder(product));
                }
            }

            function clearPreviewUrl() {
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = null;
                }
            }

            function openPhoto(product) {
                photoTarget = product;

                photoForm.reset();
                Admin.clearFieldErrors(photoForm);
                photoError.classList.add('hidden');
                clearPreviewUrl();

                photoTitle.textContent = 'Edit Foto — ' + product.nama_produk;
                renderPhotoPreview(product.image ? imageUrl(product.image) : '', product);
                photoSubmit.disabled = true;

                photoModal.showModal();
            }

            function openForm(product) {
                editing = product || null;

                form.reset();
                Admin.clearFieldErrors(form);
                formError.classList.add('hidden');

                titleEl.textContent = product ? 'Ubah Menu' : 'Tambah Menu';
                form.elements.nama_produk.value = product ? product.nama_produk : '';
                form.elements.kategori.value = product ? product.kategori : '';
                form.elements.harga.value = product ? product.harga : '';
                form.elements.deskripsi.value = product ? (product.deskripsi || '') : '';
                form.elements.status.value = product ? product.status : 'aktif';

                modal.showModal();
                form.elements.nama_produk.focus();
            }

            function renderCategories() {
                Admin.clear(categoryList);

                var seen = {};

                products.forEach(function (product) {
                    if (!product.kategori || seen[product.kategori]) {
                        return;
                    }

                    seen[product.kategori] = true;

                    var option = document.createElement('option');
                    option.value = product.kategori;
                    categoryList.appendChild(option);
                });
            }

            function row(product) {
                var tr = document.createElement('tr');

                var menuCell = Admin.make('td');
                var menuWrap = Admin.make('div', 'flex items-center gap-2.5');
                menuWrap.appendChild(thumbnail(product));

                var text = Admin.make('div', 'min-w-0');
                text.appendChild(Admin.make('p', 'font-semibold text-cocoa', product.nama_produk));

                var description = Admin.make('p', 'mt-0.5 max-w-[260px] truncate text-[11px] text-cocoa/45',
                    product.deskripsi || 'Tanpa deskripsi');

                if (product.deskripsi) {
                    description.title = product.deskripsi;
                }

                text.appendChild(description);
                menuWrap.appendChild(text);
                menuCell.appendChild(menuWrap);
                tr.appendChild(menuCell);

                tr.appendChild(Admin.make('td', 'whitespace-nowrap', product.kategori || '-'));

                tr.appendChild(Admin.make('td', 'whitespace-nowrap font-semibold text-cocoa', Admin.rupiah(product.harga)));

                var statusCell = Admin.make('td');
                var active = product.status === 'aktif';
                statusCell.appendChild(Admin.badge(active ? 'lime' : 'neutral', active ? 'Aktif' : 'Nonaktif'));
                tr.appendChild(statusCell);

                var actionCell = Admin.make('td');
                var actions = Admin.make('div', 'flex items-center gap-1');

                actions.appendChild(Admin.iconButton('photo_camera', 'btn-icon h-8 w-8',
                    'Edit foto ' + product.nama_produk, function () {
                        openPhoto(product);
                    }));

                actions.appendChild(Admin.iconButton('edit', 'btn-icon h-8 w-8', 'Ubah ' + product.nama_produk, function () {
                    openForm(product);
                }));

                actions.appendChild(Admin.iconButton('delete', 'btn-icon h-8 w-8 hover:bg-danger/10 hover:text-danger',
                    'Hapus ' + product.nama_produk, function () {
                        removeProduct(product);
                    }));

                actionCell.appendChild(actions);
                tr.appendChild(actionCell);

                return tr;
            }

            function filteredProducts() {
                if (keyword === '') {
                    return products;
                }

                var query = keyword.toLowerCase();

                return products.filter(function (product) {
                    return [product.nama_produk, product.kategori, product.deskripsi]
                        .join(' ')
                        .toLowerCase()
                        .indexOf(query) !== -1;
                });
            }

            function render() {
                var list = filteredProducts();
                var drinks = products.filter(function (product) {
                    return isMinuman(product.kategori);
                }).length;

                Admin.setStat('total', products.length);
                Admin.setStat('minuman', drinks);
                Admin.setStat('makanan', products.length - drinks);
                rowCountEl.textContent = list.length;

                Admin.clear(tbody);

                if (list.length === 0) {
                    var empty = document.createElement('tr');
                    var cell = Admin.make('td', 'px-4 py-10 text-center text-sm text-cocoa/45',
                        products.length === 0 ? 'Belum ada menu.' : 'Menu tidak ditemukan.');
                    cell.colSpan = 5;
                    empty.appendChild(cell);
                    tbody.appendChild(empty);

                    return;
                }

                list.forEach(function (product) {
                    tbody.appendChild(row(product));
                });
            }

            function refresh() {
                return Admin.loadAll('/produk')
                    .then(function (list) {
                        products = list;
                        renderCategories();
                        render();
                    })
                    .catch(function (error) {
                        Admin.flash(flashEl, 'error', 'Gagal memuat menu: ' + error.message);
                    });
            }

            function removeProduct(product) {
                if (!window.confirm('Hapus menu "' + product.nama_produk + '"? Tindakan ini tidak bisa dibatalkan.')) {
                    return;
                }

                Admin.hideFlash(flashEl);

                StaffApi.request('DELETE', '/produk/' + product.id_produk)
                    .then(function (response) {
                        Admin.flash(flashEl, 'success', response.message || 'Menu dihapus.');

                        return refresh();
                    })
                    .catch(function (error) {
                        Admin.flash(flashEl, 'error', error.message);
                    });
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                if (busy) {
                    return;
                }

                var wasEditing = !!editing;
                var price = form.elements.harga.value;

                var body = {
                    nama_produk: form.elements.nama_produk.value.trim(),
                    kategori: form.elements.kategori.value.trim(),
                    harga: price === '' ? null : Number(price),
                    deskripsi: form.elements.deskripsi.value.trim() || null,
                    status: form.elements.status.value
                };

                busy = true;
                submitBtn.disabled = true;
                Admin.clearFieldErrors(form);
                formError.classList.add('hidden');
                Admin.hideFlash(flashEl);

                var request = wasEditing
                    ? StaffApi.request('PUT', '/produk/' + editing.id_produk, body)
                    : StaffApi.request('POST', '/produk', body);

                request
                    .then(function () {
                        modal.close();
                        Admin.flash(flashEl, 'success', wasEditing ? 'Menu diperbarui.' : 'Menu ditambahkan.');

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

            photoInput.addEventListener('change', function () {
                Admin.clearFieldErrors(photoForm);
                photoError.classList.add('hidden');
                clearPreviewUrl();

                var file = photoInput.files && photoInput.files[0];
                var currentImage = photoTarget && photoTarget.image ? imageUrl(photoTarget.image) : '';

                if (!file) {
                    renderPhotoPreview(currentImage, photoTarget);
                    photoSubmit.disabled = true;

                    return;
                }

                var allowed = ['image/jpeg', 'image/png', 'image/webp'];

                if (allowed.indexOf(file.type) === -1) {
                    photoError.textContent = 'Format foto harus JPG, JPEG, PNG, atau WEBP.';
                    photoError.classList.remove('hidden');
                    renderPhotoPreview(currentImage, photoTarget);
                    photoSubmit.disabled = true;

                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    photoError.textContent = 'Ukuran foto maksimal 2 MB.';
                    photoError.classList.remove('hidden');
                    renderPhotoPreview(currentImage, photoTarget);
                    photoSubmit.disabled = true;

                    return;
                }

                previewUrl = URL.createObjectURL(file);
                renderPhotoPreview(previewUrl, photoTarget);
                photoSubmit.disabled = false;
            });

            photoForm.addEventListener('submit', function (event) {
                event.preventDefault();

                if (busy || !photoTarget) {
                    return;
                }

                var file = photoInput.files && photoInput.files[0];

                if (!file) {
                    photoError.textContent = 'Pilih foto terlebih dahulu.';
                    photoError.classList.remove('hidden');

                    return;
                }

                var body = new FormData();
                body.append('foto', file);
                body.append('_method', 'PUT');

                busy = true;
                photoSubmit.disabled = true;
                Admin.clearFieldErrors(photoForm);
                photoError.classList.add('hidden');
                Admin.hideFlash(flashEl);

                StaffApi.request('POST', '/produk/' + photoTarget.id_produk, body)
                    .then(function (response) {
                        photoModal.close();
                        Admin.flash(flashEl, 'success', response.message || 'Foto produk diperbarui.');

                        return refresh();
                    })
                    .catch(function (error) {
                        if (error.status === 422 && error.data && error.data.errors) {
                            Admin.showFieldErrors(photoForm, error.data.errors);

                            var message = error.data.errors.foto && error.data.errors.foto[0];

                            if (message) {
                                photoError.textContent = message;
                                photoError.classList.remove('hidden');
                            }
                        } else {
                            photoError.textContent = error.message;
                            photoError.classList.remove('hidden');
                        }
                    })
                    .then(function () {
                        busy = false;
                        photoSubmit.disabled = !(photoInput.files && photoInput.files.length);
                    });
            });

            function applySearch() {
                keyword = searchInput.value.trim();
                render();
            }

            document.querySelector('[data-add-menu]').addEventListener('click', function () {
                openForm(null);
            });

            searchInput.addEventListener('input', applySearch);
            searchButton.addEventListener('click', applySearch);

            document.querySelectorAll('[data-modal-close]').forEach(function (node) {
                node.addEventListener('click', function () {
                    modal.close();
                });
            });

            document.querySelectorAll('[data-photo-close]').forEach(function (node) {
                node.addEventListener('click', function () {
                    photoModal.close();
                });
            });

            photoModal.addEventListener('close', function () {
                clearPreviewUrl();
                photoTarget = null;
            });

            Admin.closeOnBackdrop(modal);
            Admin.closeOnBackdrop(photoModal);
            refresh();
        });
    </script>
@endpush
