{{-- Helper bersama halaman manager. Bergantung pada window.StaffApi (dari layouts/staff). --}}
<script>
    window.Manager = (function () {
        var MAX_DAYS = 120;

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

        function badge(tone, label) {
            return make('span', 'badge badge-' + tone, label);
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

        /* ---------- Tanggal (zona waktu browser) ---------- */

        function pad(number) {
            return number < 10 ? '0' + number : String(number);
        }

        function ymd(date) {
            return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
        }

        function parseYmd(text) {
            var parts = String(text).split('-');

            return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
        }

        function addDays(date, count) {
            var copy = new Date(date.getTime());
            copy.setDate(copy.getDate() + count);

            return copy;
        }

        function presetRange(name) {
            var today = new Date();
            today.setHours(0, 0, 0, 0);

            if (name === 'today') {
                return { start: ymd(today), end: ymd(today) };
            }

            if (name === '30') {
                return { start: ymd(addDays(today, -29)), end: ymd(today) };
            }

            if (name === 'month') {
                return { start: ymd(new Date(today.getFullYear(), today.getMonth(), 1)), end: ymd(today) };
            }

            return { start: ymd(addDays(today, -6)), end: ymd(today) };
        }

        function validRange(start, end) {
            if (!start || !end) {
                return 'Isi tanggal mulai dan tanggal akhir.';
            }

            if (parseYmd(end) < parseYmd(start)) {
                return 'Tanggal akhir tidak boleh sebelum tanggal mulai.';
            }

            if ((parseYmd(end) - parseYmd(start)) / 86400000 > MAX_DAYS) {
                return 'Rentang maksimal ' + MAX_DAYS + ' hari.';
            }

            return null;
        }

        function shortDay(text) {
            return parseYmd(text).toLocaleDateString('id-ID', { weekday: 'short' });
        }

        function longDay(text) {
            return parseYmd(text).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
        }

        function timeLabel(iso) {
            var date = new Date(iso);

            return isNaN(date.getTime())
                ? '-'
                : pad(date.getHours()) + ':' + pad(date.getMinutes());
        }

        /* ---------- Data ---------- */

        function loadSales(start, end) {
            return StaffApi.request('GET', '/laporan/penjualan?start_date=' + start + '&end_date=' + end);
        }

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
                var name = (line.produk && line.produk.nama_produk) || line.nama_produk || '-';

                return (line.jumlah || 0) + '× ' + name;
            }).join(', ');
        }

        function tableLabel(order) {
            return order.meja && order.meja.no_meja ? 'Meja ' + order.meja.no_meja : 'Meja -';
        }

        function methodName(order) {
            var payment = order.pembayaran;
            var method = payment ? (payment.metode_pembayaran || payment.metodePembayaran) : null;

            if (method && typeof method === 'object' && method.nama_metode) {
                return method.nama_metode;
            }

            return typeof method === 'string' && method !== '' ? method : 'Tidak diketahui';
        }

        function totalSold(orders) {
            return orders.reduce(function (sum, order) {
                return sum + itemCount(order);
            }, 0);
        }

        /* Rekap per hari untuk setiap tanggal dalam rentang (termasuk hari tanpa penjualan). */
        function groupByDay(orders, start, end) {
            var days = [];
            var index = {};
            var cursor = parseYmd(start);
            var last = parseYmd(end);

            while (cursor <= last) {
                var key = ymd(cursor);

                index[key] = { date: key, orders: 0, revenue: 0 };
                days.push(index[key]);
                cursor = addDays(cursor, 1);
            }

            orders.forEach(function (order) {
                var key = ymd(new Date(order.tanggal_pesan));

                if (index[key]) {
                    index[key].orders += 1;
                    index[key].revenue += Number(order.total_harga) || 0;
                }
            });

            return days;
        }

        /* Menu terlaris dari kumpulan pesanan: [{name, sold, revenue}] urut terbanyak. */
        function topMenu(orders, limit) {
            var map = {};

            orders.forEach(function (order) {
                lines(order).forEach(function (line) {
                    var key = line.id_produk;
                    var name = (line.produk && line.produk.nama_produk) || line.nama_produk || ('Produk #' + key);

                    if (!map[key]) {
                        map[key] = { name: name, sold: 0, revenue: 0 };
                    }

                    map[key].sold += parseInt(line.jumlah, 10) || 0;
                    map[key].revenue += Number(line.subtotal) || 0;
                });
            });

            return Object.keys(map)
                .map(function (key) { return map[key]; })
                .sort(function (a, b) { return b.sold - a.sold; })
                .slice(0, limit || 5);
        }

        /* Rekap per metode pembayaran: [{name, count, amount}] urut nominal terbesar. */
        function byMethod(orders) {
            var map = {};

            orders.forEach(function (order) {
                var name = methodName(order);

                if (!map[name]) {
                    map[name] = { name: name, count: 0, amount: 0 };
                }

                map[name].count += 1;
                map[name].amount += Number(order.total_harga) || 0;
            });

            return Object.keys(map)
                .map(function (key) { return map[key]; })
                .sort(function (a, b) { return b.amount - a.amount; });
        }

        /* ---------- Grafik batang ---------- */

        function renderBars(container, days) {
            clear(container);

            var max = days.reduce(function (highest, day) {
                return Math.max(highest, day.revenue);
            }, 0);

            days.forEach(function (day) {
                var column = make('div', 'flex min-w-0 flex-1 flex-col items-center gap-2');
                var track = make('div', 'flex h-36 w-full items-end');
                var percent = max > 0 ? Math.max(3, Math.round(day.revenue / max * 100)) : 3;
                var bar = make('div', 'w-full rounded-t-md bg-gradient-to-t from-brand to-brand/55');

                bar.style.height = percent + '%';
                bar.title = longDay(day.date) + ': ' + rupiah(day.revenue) + ' (' + day.orders + ' pesanan)';
                track.appendChild(bar);
                column.appendChild(track);
                column.appendChild(make('span', 'text-[10px] font-semibold text-cocoa/40', shortDay(day.date)));
                container.appendChild(column);
            });
        }

        /* ---------- Ekspor CSV ---------- */

        function csvCell(value, delimiter) {
            var text = value === null || value === undefined ? '' : String(value);

            /* Cegah rumus tereksekusi di Excel (CSV injection). */
            if (/^[=+\-@\t\r]/.test(text)) {
                text = "'" + text;
            }

            return '"' + text.replace(/"/g, '""') + '"';
        }

        function downloadCsv(filename, rows, delimiter) {
            var sep = delimiter || ';';
            var content = '\uFEFF' + rows.map(function (row) {
                return row.map(function (cell) {
                    return csvCell(cell, sep);
                }).join(sep);
            }).join('\r\n');

            var blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
            var url = URL.createObjectURL(blob);
            var link = document.createElement('a');

            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            window.setTimeout(function () {
                URL.revokeObjectURL(url);
            }, 1000);
        }

        return {
            rupiah: rupiah,
            make: make,
            badge: badge,
            clear: clear,
            setStat: setStat,
            flash: flash,
            hideFlash: hideFlash,
            ymd: ymd,
            parseYmd: parseYmd,
            addDays: addDays,
            presetRange: presetRange,
            validRange: validRange,
            shortDay: shortDay,
            longDay: longDay,
            timeLabel: timeLabel,
            loadSales: loadSales,
            loadAll: loadAll,
            lines: lines,
            itemCount: itemCount,
            menuSummary: menuSummary,
            tableLabel: tableLabel,
            methodName: methodName,
            totalSold: totalSold,
            groupByDay: groupByDay,
            topMenu: topMenu,
            byMethod: byMethod,
            renderBars: renderBars,
            downloadCsv: downloadCsv
        };
    })();
</script>
