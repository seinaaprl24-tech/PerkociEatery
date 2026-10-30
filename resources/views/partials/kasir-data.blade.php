{{-- Helper bersama halaman kasir. Bergantung pada window.StaffApi (dari layouts/staff). --}}
<script>
    window.Kasir = (function () {
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
            var node = make('span', 'ms text-[18px] leading-none', name);
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

        function statusMeta(status) {
            return STATUS[status] || { label: status || '-', tone: 'neutral' };
        }

        function isActive(order) {
            return order.status_pesanan === 'menunggu' || order.status_pesanan === 'diproses';
        }

        function lines(order) {
            return order.detail_pesanan || order.items || [];
        }

        function itemCount(order) {
            return lines(order).reduce(function (sum, line) {
                return sum + (parseInt(line.jumlah, 10) || 0);
            }, 0);
        }

        function menuSummary(order) {
            return lines(order).map(function (line) {
                return (line.jumlah || 0) + '× ' + (line.nama_produk || (line.produk && line.produk.nama_produk) || '-');
            }).join(', ');
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

        function isToday(iso) {
            var date = new Date(iso);
            var now = new Date();

            return date.getFullYear() === now.getFullYear()
                && date.getMonth() === now.getMonth()
                && date.getDate() === now.getDate();
        }

        /* 100 pesanan terbaru (semua status). */
        function loadOrders() {
            return StaffApi.request('GET', '/pesanan?per_page=100').then(function (response) {
                return response.data || [];
            });
        }

        function loadTables() {
            var all = [];

            function next(page) {
                return StaffApi.request('GET', '/meja?page=' + page).then(function (response) {
                    all = all.concat(response.data || []);

                    var last = response.meta && response.meta.last_page ? response.meta.last_page : 1;

                    return page < last ? next(page + 1) : all;
                });
            }

            return next(1);
        }

        function setStatus(id, status) {
            return StaffApi.request('PATCH', '/pesanan/' + id + '/status', { status_pesanan: status });
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

        function every(callback, ms) {
            window.setInterval(function () {
                if (!document.hidden) {
                    callback();
                }
            }, ms);
        }

        return {
            rupiah: rupiah,
            make: make,
            icon: icon,
            badge: badge,
            button: button,
            link: link,
            clear: clear,
            statusMeta: statusMeta,
            isActive: isActive,
            lines: lines,
            itemCount: itemCount,
            menuSummary: menuSummary,
            tableLabel: tableLabel,
            ago: ago,
            isToday: isToday,
            loadOrders: loadOrders,
            loadTables: loadTables,
            setStatus: setStatus,
            setStat: setStat,
            flash: flash,
            hideFlash: hideFlash,
            every: every
        };
    })();
</script>
