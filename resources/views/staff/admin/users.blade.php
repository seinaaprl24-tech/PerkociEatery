{{-- Admin – Pengguna: data nyata dari /api/users. --}}
@extends('layouts.staff')

@section('title', 'Pengguna — Perkoci Eatery')

@section('staff_role', 'admin')
@section('active_nav', 'users')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">Pengguna</p>
    </div>
@endsection

@section('content')
    <x-ui.page-header title="Pengguna" subtitle="Kelola akun staff internal: admin, kasir, dan manager.">
        <button type="button" class="btn-primary btn-sm" data-add-user>
            <span class="ms text-[16px] leading-none" aria-hidden="true">person_add</span>
            Tambah Pengguna
        </button>
    </x-ui.page-header>

    <p data-flash class="hidden" role="status"></p>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card data-stat="total" label="Total Pengguna" value="0" icon="group" tone="brand"
            hint="semua akun staff" />
        <x-ui.stat-card data-stat="admin" label="Admin" value="0" icon="admin_panel_settings" tone="lime"
            hint="akses penuh" />
        <x-ui.stat-card data-stat="staf" label="Kasir & Manager" value="0" icon="badge" tone="butter"
            hint="akses sesuai peran" />
    </div>

    <x-ui.section class="mt-4" title="Daftar Akun"
        description="Akun yang bisa login ke dashboard. Password disimpan terenkripsi dan tidak bisa dilihat." flush>
        <x-slot:actions>
            <x-ui.badge tone="neutral"><span data-row-count>0</span> akun</x-ui.badge>
        </x-slot:actions>

        <x-ui.table data-users-table class="rounded-none border-0" :head="['Nama', 'Peran', 'Dibuat', 'Aksi']">
            <tr data-placeholder>
                <td colspan="4" class="px-4 py-10 text-center text-sm text-cocoa/45">Memuat pengguna…</td>
            </tr>
        </x-ui.table>
    </x-ui.section>

    {{-- Dialog tambah / ubah --}}
    <dialog data-user-modal class="modal" aria-labelledby="user-modal-title">
        <form data-user-form novalidate class="p-5">
            <div class="flex items-start justify-between gap-3">
                <h2 id="user-modal-title" class="font-display text-[17px] font-bold leading-tight text-cocoa" data-modal-title>
                    Tambah Pengguna
                </h2>

                <button type="button" class="btn-icon -mr-1 -mt-1" data-modal-close aria-label="Tutup">
                    <span class="ms text-[20px] leading-none" aria-hidden="true">close</span>
                </button>
            </div>

            <p class="field-error hidden" data-form-error role="alert"></p>

            <div class="mt-4 space-y-3.5">
                <div>
                    <label class="field-label" for="user-nama">Nama</label>
                    <input id="user-nama" name="nama" type="text" class="field" maxlength="255" autocomplete="off">
                    <p class="field-error hidden" data-error="nama" role="alert"></p>
                </div>

                <div>
                    <label class="field-label" for="user-username">Username</label>
                    <input id="user-username" name="username" type="text" class="field" maxlength="255"
                        autocomplete="off" autocapitalize="none" spellcheck="false">
                    <p class="field-error hidden" data-error="username" role="alert"></p>
                </div>

                <div>
                    <label class="field-label" for="user-role">Peran</label>
                    <select id="user-role" name="role" class="field">
                        <option value="admin">Admin</option>
                        <option value="kasir">Kasir</option>
                        <option value="manager">Manager</option>
                    </select>
                    <p class="field-hint" data-role-hint></p>
                    <p class="field-error hidden" data-error="role" role="alert"></p>
                </div>

                <div>
                    <label class="field-label" for="user-password">Password</label>
                    <input id="user-password" name="password" type="password" class="field" autocomplete="new-password">
                    <p class="field-hint" data-password-hint>Minimal 6 karakter.</p>
                    <p class="field-error hidden" data-error="password" role="alert"></p>
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
            var meId = @json(auth()->id());
            var ROLES = {
                admin: { label: 'Admin', tone: 'brand' },
                kasir: { label: 'Kasir', tone: 'lime' },
                manager: { label: 'Manager', tone: 'butter' }
            };

            var flashEl = document.querySelector('[data-flash]');
            var tableWrap = document.querySelector('[data-users-table]');
            var tbody = tableWrap.querySelector('tbody');
            var rowCountEl = document.querySelector('[data-row-count]');
            var modal = document.querySelector('[data-user-modal]');
            var form = document.querySelector('[data-user-form]');
            var titleEl = document.querySelector('[data-modal-title]');
            var formError = document.querySelector('[data-form-error]');
            var passwordHint = document.querySelector('[data-password-hint]');
            var roleHint = document.querySelector('[data-role-hint]');
            var submitBtn = document.querySelector('[data-submit]');

            var users = [];
            var editing = null;
            var busy = false;

            function roleMeta(role) {
                return ROLES[role] || { label: role || '-', tone: 'neutral' };
            }

            function openModal(user) {
                editing = user || null;

                form.reset();
                Admin.clearFieldErrors(form);
                formError.classList.add('hidden');

                var isSelf = !!(user && user.id_user === meId);

                titleEl.textContent = user ? 'Ubah Pengguna' : 'Tambah Pengguna';
                form.elements.nama.value = user ? user.nama : '';
                form.elements.username.value = user ? user.username : '';
                form.elements.role.value = user ? user.role : 'kasir';
                form.elements.role.disabled = isSelf;
                roleHint.textContent = isSelf ? 'Peran akun yang sedang kamu pakai tidak bisa diubah.' : '';
                passwordHint.textContent = user
                    ? 'Kosongkan jika tidak ingin mengganti password.'
                    : 'Minimal 6 karakter.';

                modal.showModal();
                form.elements.nama.focus();
            }

            function row(user) {
                var tr = document.createElement('tr');
                var isSelf = user.id_user === meId;
                var meta = roleMeta(user.role);

                var nameCell = Admin.make('td');
                var nameWrap = Admin.make('div', 'flex items-center gap-2.5');
                nameWrap.appendChild(Admin.make('span',
                    'flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sand text-[10px] font-extrabold text-cocoa/55',
                    (user.nama || '?').charAt(0).toUpperCase()));

                var nameText = Admin.make('div', 'min-w-0');
                var nameLine = Admin.make('p', 'font-semibold text-cocoa', user.nama);

                if (isSelf) {
                    nameLine.appendChild(Admin.make('span', 'ml-1.5 text-[10px] font-bold text-brand', '(kamu)'));
                }

                nameText.appendChild(nameLine);
                nameText.appendChild(Admin.make('p', 'text-[11px] text-cocoa/45', '@' + user.username));
                nameWrap.appendChild(nameText);
                nameCell.appendChild(nameWrap);
                tr.appendChild(nameCell);

                var roleCell = Admin.make('td');
                roleCell.appendChild(Admin.badge(meta.tone, meta.label));
                tr.appendChild(roleCell);

                tr.appendChild(Admin.make('td', 'whitespace-nowrap', Admin.dateLabel(user.created_at)));

                var actionCell = Admin.make('td');
                var actions = Admin.make('div', 'flex items-center gap-1');

                actions.appendChild(Admin.iconButton('edit', 'btn-icon h-8 w-8', 'Ubah ' + user.nama, function () {
                    openModal(user);
                }));

                var deleteBtn = Admin.iconButton('delete', 'btn-icon h-8 w-8 hover:bg-danger/10 hover:text-danger',
                    'Hapus ' + user.nama, function () {
                        removeUser(user);
                    });

                if (isSelf) {
                    deleteBtn.disabled = true;
                    deleteBtn.title = 'Akun yang sedang dipakai tidak bisa dihapus';
                }

                actions.appendChild(deleteBtn);
                actionCell.appendChild(actions);
                tr.appendChild(actionCell);

                return tr;
            }

            function render() {
                var admins = users.filter(function (user) { return user.role === 'admin'; }).length;

                Admin.setStat('total', users.length);
                Admin.setStat('admin', admins);
                Admin.setStat('staf', users.length - admins);
                rowCountEl.textContent = users.length;

                Admin.clear(tbody);

                if (users.length === 0) {
                    var empty = document.createElement('tr');
                    empty.appendChild(Admin.make('td', 'px-4 py-10 text-center text-sm text-cocoa/45', 'Belum ada pengguna.'));
                    empty.firstChild.colSpan = 4;
                    tbody.appendChild(empty);

                    return;
                }

                users.forEach(function (user) {
                    tbody.appendChild(row(user));
                });
            }

            function refresh() {
                return Admin.loadAll('/users')
                    .then(function (list) {
                        users = list;
                        render();
                    })
                    .catch(function (error) {
                        Admin.flash(flashEl, 'error', 'Gagal memuat pengguna: ' + error.message);
                    });
            }

            function removeUser(user) {
                if (!window.confirm('Hapus akun "' + user.username + '"? Tindakan ini tidak bisa dibatalkan.')) {
                    return;
                }

                Admin.hideFlash(flashEl);

                StaffApi.request('DELETE', '/users/' + user.id_user)
                    .then(function (response) {
                        Admin.flash(flashEl, 'success', response.message || 'Pengguna dihapus.');

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
                var body = {
                    nama: form.elements.nama.value.trim(),
                    username: form.elements.username.value.trim()
                };
                var password = form.elements.password.value;

                if (!wasEditing || password !== '') {
                    body.password = password;
                }

                if (!form.elements.role.disabled) {
                    body.role = form.elements.role.value;
                }

                busy = true;
                submitBtn.disabled = true;
                Admin.clearFieldErrors(form);
                formError.classList.add('hidden');
                Admin.hideFlash(flashEl);

                var request = wasEditing
                    ? StaffApi.request('PUT', '/users/' + editing.id_user, body)
                    : StaffApi.request('POST', '/users', body);

                request
                    .then(function () {
                        modal.close();
                        Admin.flash(flashEl, 'success', wasEditing ? 'Pengguna diperbarui.' : 'Pengguna ditambahkan.');

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

            document.querySelector('[data-add-user]').addEventListener('click', function () {
                openModal(null);
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
