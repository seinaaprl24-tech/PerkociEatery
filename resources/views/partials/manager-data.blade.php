{{-- Helper bersama halaman manager. Bergantung pada window.StaffApi (dari layouts/staff). --}}
<script>
    window.Manager = (function () {
        function rupiah(value) {
            return 'Rp ' + (Number(value) || 0).toLocaleString('id-ID');
        }

        function shortMoney(value) {
            var n = Number(value) || 0;
            if (n >= 1000000) {
                return (n / 1000000).toFixed(1).replace('.', ',').replace(',0', '') + ' jt';
            }
            if (n >= 1000) {
                return Math.round(n / 1000) + ' rb';
            }
            return String(n);
        }

        function make(tag, className, text) {
            var node = document.createElement(tag);
            if (className) { node.className = className; }
            if (text !== undefined && text !== null) { node.textContent = text; }
            return node;
        }

        function svgEl(tag, attrs) {
            var el = document.createElementNS('http://www.w3.org/2000/svg', tag);
            if (attrs) {
                Object.keys(attrs).forEach(function (k) { el.setAttribute(k, attrs[k]); });
            }
            return el;
        }

        function badge(tone, label) {
            return make('span', 'badge badge-' + tone, label);
        }

        function clear(node) {
            while (node && node.firstChild) {
                node.removeChild(node.firstChild);
            }
        }

        function setStat(name, value) {
            var card = document.querySelector('[data-stat="' + name + '"]');
            var target = card ? card.querySelector('.font-display') : null;
            if (target) { target.textContent = value; }
        }

        function flash(node, type, message) {
            if (!node) { return; }
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

        /* ---------- Tanggal ---------- */
        function pad(number) { return number < 10 ? '0' + number : String(number); }
        function ymd(date) { return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()); }
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
            if (name === 'today') { return { start: ymd(today), end: ymd(today) }; }
            if (name === '30') { return { start: ymd(addDays(today, -29)), end: ymd(today) }; }
            if (name === 'month') { return { start: ymd(new Date(today.getFullYear(), today.getMonth(), 1)), end: ymd(today) }; }
            if (name === 'year') { return { start: ymd(new Date(today.getFullYear(), 0, 1)), end: ymd(today) }; }
            if (name === 'last_year') {
                return { start: ymd(new Date(today.getFullYear() - 1, 0, 1)), end: ymd(new Date(today.getFullYear() - 1, 11, 31)) };
            }
            return { start: ymd(addDays(today, -6)), end: ymd(today) };
        }

        function validRange(start, end) {
            if (!start || !end) { return 'Isi tanggal mulai dan tanggal akhir.'; }
            if (parseYmd(end) < parseYmd(start)) { return 'Tanggal akhir tidak boleh sebelum tanggal mulai.'; }
            return null;
        }

        function shortDay(text) { return parseYmd(text).toLocaleDateString('id-ID', { weekday: 'short' }); }
        function shortDate(text) {
            var d = parseYmd(text);
            return pad(d.getDate()) + '/' + pad(d.getMonth() + 1);
        }
        function longDay(text) { return parseYmd(text).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }); }
        function timeLabel(iso) {
            var date = new Date(iso);
            return isNaN(date.getTime()) ? '-' : pad(date.getHours()) + ':' + pad(date.getMinutes());
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

        function lines(order) { return order.detail_pesanan || order.items || []; }
        function itemCount(order) {
            return lines(order).reduce(function (sum, line) { return sum + (parseInt(line.jumlah, 10) || 0); }, 0);
        }
        function menuSummary(order) {
            return lines(order).map(function (line) {
                var name = (line.produk && line.produk.nama_produk) || line.nama_produk || '-';
                return (line.jumlah || 0) + '× ' + name;
            }).join(', ');
        }
        function tableLabel(order) {
            if (order && (String(order.tipe_pesanan || '').toLowerCase() === 'takeaway' || order.is_takeaway === true)) {
                return 'Takeaway';
            }

            return order.meja && order.meja.no_meja ? 'Meja ' + order.meja.no_meja : 'Meja -';
        }
        function methodName(order) {
            var payment = order.pembayaran;
            var method = payment ? (payment.metode_pembayaran || payment.metodePembayaran) : null;
            if (method && typeof method === 'object' && method.nama_metode) { return method.nama_metode; }
            return typeof method === 'string' && method !== '' ? method : 'Tidak diketahui';
        }
        function totalSold(orders) {
            return orders.reduce(function (sum, order) { return sum + itemCount(order); }, 0);
        }

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

        function topMenu(orders, limit) {
            var map = {};
            orders.forEach(function (order) {
                lines(order).forEach(function (line) {
                    var key = line.id_produk;
                    var name = (line.produk && line.produk.nama_produk) || line.nama_produk || ('Produk #' + key);
                    if (!map[key]) { map[key] = { name: name, sold: 0, revenue: 0 }; }
                    map[key].sold += parseInt(line.jumlah, 10) || 0;
                    map[key].revenue += Number(line.subtotal) || 0;
                });
            });

            return Object.keys(map)
                .map(function (key) { return map[key]; })
                .sort(function (a, b) { return b.sold - a.sold; })
                .slice(0, limit || 5);
        }

        function byMethod(orders) {
            var map = {};
            orders.forEach(function (order) {
                var name = methodName(order);
                if (!map[name]) { map[name] = { name: name, count: 0, amount: 0 }; }
                map[name].count += 1;
                map[name].amount += Number(order.total_harga) || 0;
            });
            return Object.keys(map)
                .map(function (key) { return map[key]; })
                .sort(function (a, b) { return b.amount - a.amount; });
        }

        /* ---------- Grafik Garis & Area (Penjualan Harian) ---------- */
        function niceCeiling(maxVal) {
            if (maxVal <= 0) { return 100000; }
            var pow = Math.pow(10, Math.floor(Math.log10(maxVal)));
            var norm = maxVal / pow;
            var step = norm <= 1 ? 1 : (norm <= 2 ? 2 : (norm <= 5 ? 5 : 10));
            return step * pow;
        }

        function renderBars(container, days) {
            clear(container);
            container.className = 'mt-4 flex-1 flex flex-col justify-center w-full overflow-x-auto';

            var W = 680, H = 310;
            var padL = 56, padR = 26, padT = 28, padB = 34;
            var plotW = W - padL - padR;
            var plotH = H - padT - padB;

            var maxRaw = days.reduce(function (m, d) { return Math.max(m, d.revenue); }, 0);
            var maxY = niceCeiling(maxRaw * 1.15);

            var svg = svgEl('svg', {
                viewBox: '0 0 ' + W + ' ' + H,
                style: 'width:100%;height:100%;min-height:260px;display:block;'
            });

            var defs = svgEl('defs');
            var grad = svgEl('linearGradient', { id: 'perkociAreaGrad', x1: '0', y1: '0', x2: '0', y2: '1' });
            grad.appendChild(svgEl('stop', { offset: '0%', 'stop-color': '#264d1b', 'stop-opacity': '0.32' }));
            grad.appendChild(svgEl('stop', { offset: '100%', 'stop-color': '#264d1b', 'stop-opacity': '0.03' }));
            defs.appendChild(grad);
            svg.appendChild(defs);

            for (var i = 0; i <= 4; i++) {
                var val = (maxY / 4) * i;
                var y = padT + plotH - (plotH * (i / 4));
                svg.appendChild(svgEl('line', {
                    x1: padL, y1: y, x2: W - padR, y2: y,
                    stroke: '#3b2a20',
                    'stroke-opacity': i === 0 ? '0.35' : '0.1',
                    'stroke-width': i === 0 ? '1.2' : '1'
                }));
                var lbl = svgEl('text', {
                    x: padL - 10, y: y + 4, 'text-anchor': 'end',
                    fill: '#3b2a20', 'fill-opacity': '0.65',
                    'font-size': '11', 'font-weight': '600'
                });
                lbl.textContent = i === 0 ? '0' : shortMoney(val);
                svg.appendChild(lbl);
            }

            svg.appendChild(svgEl('line', {
                x1: padL, y1: padT - 6, x2: padL, y2: padT + plotH,
                stroke: '#3b2a20', 'stroke-opacity': '0.35', 'stroke-width': '1.2'
            }));

            if (days.length === 0) {
                container.appendChild(svg);
                return;
            }

            var points = days.map(function (d, idx) {
                var x = days.length === 1
                    ? padL + plotW / 2
                    : padL + (plotW * (idx / (days.length - 1)));
                var y = padT + plotH - (maxY > 0 ? (d.revenue / maxY) * plotH : 0);
                return { x: x, y: y, day: d };
            });

            var areaD = 'M ' + points[0].x + ' ' + (padT + plotH);
            points.forEach(function (p) { areaD += ' L ' + p.x + ' ' + p.y; });
            areaD += ' L ' + points[points.length - 1].x + ' ' + (padT + plotH) + ' Z';
            svg.appendChild(svgEl('path', { d: areaD, fill: 'url(#perkociAreaGrad)' }));

            var lineD = points.map(function (p, idx) { return (idx === 0 ? 'M ' : 'L ') + p.x + ' ' + p.y; }).join(' ');
            svg.appendChild(svgEl('path', {
                d: lineD, fill: 'none', stroke: '#234218', 'stroke-width': '2.6',
                'stroke-linecap': 'round', 'stroke-linejoin': 'round'
            }));

            var showPointText = days.length <= 14;
            var labelStep = days.length <= 10 ? 1 : Math.ceil(days.length / 8);

            points.forEach(function (p, idx) {
                var dot = svgEl('circle', {
                    cx: p.x, cy: p.y, r: showPointText ? '4.5' : '3',
                    fill: '#234218', stroke: '#ffffff', 'stroke-width': '1.5'
                });
                var title = svgEl('title');
                title.textContent = longDay(p.day.date) + ': ' + rupiah(p.day.revenue) + ' (' + p.day.orders + ' pesanan)';
                dot.appendChild(title);
                svg.appendChild(dot);

                if (showPointText && p.day.revenue > 0) {
                    var valTxt = svgEl('text', {
                        x: p.x, y: p.y - 10, 'text-anchor': 'middle',
                        fill: '#234218', 'font-size': '11', 'font-weight': '700'
                    });
                    valTxt.textContent = shortMoney(p.day.revenue);
                    svg.appendChild(valTxt);
                }

                if (idx % labelStep === 0 || idx === points.length - 1) {
                    var xTxt = svgEl('text', {
                        x: p.x, y: padT + plotH + 20, 'text-anchor': 'middle',
                        fill: '#3b2a20', 'fill-opacity': '0.75',
                        'font-size': '11', 'font-weight': '600'
                    });
                    xTxt.textContent = days.length <= 7 ? shortDay(p.day.date) : shortDate(p.day.date);
                    svg.appendChild(xTxt);
                }
            });

            container.appendChild(svg);
        }

        /* ---------- Ekspor CSV ---------- */
        function csvCell(value, delimiter) {
            var text = value === null || value === undefined ? '' : String(value);
            if (/^[=+\-@\t\r]/.test(text)) { text = "'" + text; }
            return '"' + text.replace(/"/g, '""') + '"';
        }

        function downloadCsv(filename, rows, delimiter) {
            var sep = delimiter || ';';
            var content = '\uFEFF' + rows.map(function (row) {
                return row.map(function (cell) { return csvCell(cell, sep); }).join(sep);
            }).join('\r\n');

            var blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
            var url = URL.createObjectURL(blob);
            var link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
        }

        return {
            rupiah: rupiah,
            shortMoney: shortMoney,
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
