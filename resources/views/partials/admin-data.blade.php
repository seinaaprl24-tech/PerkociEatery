{{-- Helper bersama halaman admin. Bergantung pada window.StaffApi (dari layouts/staff). --}}
<script>
    window.Admin = (function () {
        var STATUS = {
            menunggu: { label: 'Menunggu', tone: 'danger' },
            diproses: { label: 'Diproses', tone: 'butter' },
            selesai: { label: 'Selesai', tone: 'lime' },
            batal: { label: 'Batal', tone: 'neutral' }
        };

        function rupiah(value) {
            return 'Rp ' + (Number(value) || 0).toLocaleString('id-ID');
        }

        function make(tag, className, text) {
            var node = document.createElement(tag);

            if (className) {
                node.className = className;
            }

            if (text !== undefined && text !== null) {
                node.textContent = text;
            }

            return node;
        }

        function icon(name) {
            var node = make('span', 'ms text-[16px] leading-none', name);
            node.setAttribute('aria-hidden', 'true');

            return node;
        }

        function badge(tone, label) {
            return make('span', 'badge badge-' + tone, label);
        }

        function button(label, className, onClick) {
            var node = make('button', className, label);
            node.type = 'button';
            node.addEventListener('click', onClick);

            return node;
        }

        /* Tombol ikon kecil (mis. ubah / hapus) dengan label aksesibilitas. */
        function iconButton(name, className, label, onClick) {
            var node = button('', className, onClick);
            node.setAttribute('aria-label', label);
            node.appendChild(icon(name));

            return node;
        }

        function link(label, className, href) {
            var node = make('a', className, label);
            node.href = href;

            return node;
        }

        function clear(node) {
            while (node.firstChild) {
                node.removeChild(node.firstChild);
            }
        }

        function setStat(name, value) {
            var card = document.querySelector('[data-stat="' + name + '"]');
            var target = card ? card.querySelector('.font-display') : null;

            if (target) {
                target.textContent = value;
            }
        }

        function flash(node, type, message) {
            if (!node) {
                return;
            }

            node.textContent = message;
            node.className = 'mt-4 rounded-xl px-3.5 py-3 text-[12px] font-semibold leading-relaxed '
                + (type === 'error' ? 'bg-danger/10 text-danger' : 'bg-lime/40 text-cocoa');
        }

        function hideFlash(node) {
            if (node) {
                node.textContent = '';
                node.className = 'hidden';
            }
        }

        /* Ambil semua halaman dari endpoint berpagination. */
        function loadAll(path) {
            var all = [];
            var joiner = path.indexOf('?') === -1 ? '?' : '&';

            function next(page) {
                return StaffApi.request('GET', path + joiner + 'page=' + page).then(function (response) {
                    all = all.concat(response.data || []);

                    var last = response.meta && response.meta.last_page ? response.meta.last_page : 1;

                    return page < last ? next(page + 1) : all;
                });
            }

            return next(1);
        }

        /* Kesalahan validasi per kolom (respons 422): <p data-error="nama_kolom"> di dalam form. */
        function clearFieldErrors(form) {
            form.querySelectorAll('[data-error]').forEach(function (node) {
                node.textContent = '';
                node.classList.add('hidden');
            });
        }

        function showFieldErrors(form, errors) {
            clearFieldErrors(form);

            for (var key in errors) {
                var node = form.querySelector('[data-error="' + key + '"]');

                if (node && errors[key] && errors[key][0]) {
                    node.textContent = errors[key][0];
                    node.classList.remove('hidden');
                }
            }
        }

        function copyText(text, onDone, onFail) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(onDone, onFail);
            } else {
                onFail();
            }
        }

        /* Tutup <dialog> bila area gelap di luar kotak ditekan. */
        function closeOnBackdrop(dialog) {
            dialog.addEventListener('click', function (event) {
                if (event.target === dialog) {
                    dialog.close();
                }
            });
        }

        function statusMeta(status) {
            return STATUS[status] || { label: status || '-', tone: 'neutral' };
        }

        function tableLabel(order) {
            return order.meja && order.meja.no_meja ? 'Meja ' + order.meja.no_meja : 'Meja -';
        }

        function ago(iso) {
            var minutes = Math.max(0, Math.round((Date.now() - new Date(iso).getTime()) / 60000));

            if (isNaN(minutes)) {
                return '-';
            }

            if (minutes < 1) {
                return 'baru saja';
            }

            if (minutes < 60) {
                return minutes + ' menit lalu';
            }

            var hours = Math.floor(minutes / 60);

            if (hours < 24) {
                return hours + ' jam lalu';
            }

            return Math.floor(hours / 24) + ' hari lalu';
        }

        function dateLabel(iso) {
            var date = new Date(iso);

            return isNaN(date.getTime())
                ? '-'
                : date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        }

        return {
            rupiah: rupiah,
            make: make,
            icon: icon,
            badge: badge,
            button: button,
            iconButton: iconButton,
            link: link,
            clear: clear,
            setStat: setStat,
            flash: flash,
            hideFlash: hideFlash,
            loadAll: loadAll,
            clearFieldErrors: clearFieldErrors,
            showFieldErrors: showFieldErrors,
            copyText: copyText,
            closeOnBackdrop: closeOnBackdrop,
            statusMeta: statusMeta,
            tableLabel: tableLabel,
            ago: ago,
            dateLabel: dateLabel
        };
    })();
</script>
