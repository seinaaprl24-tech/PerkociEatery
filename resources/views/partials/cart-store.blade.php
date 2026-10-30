{{-- Penyimpan keranjang customer (localStorage). Di-include oleh halaman menu & keranjang. --}}
<script>
    window.PerkociCart = (function () {
        var KEY = 'perkoci_cart_v1';
        var memory = [];

        function read() {
            try {
                var raw = window.localStorage.getItem(KEY);

                if (raw === null) {
                    return memory.slice();
                }

                var data = JSON.parse(raw);
                var rows = data && data.items instanceof Array ? data.items : [];
                var out = [];

                for (var i = 0; i < rows.length; i++) {
                    var id = parseInt(rows[i].id, 10);
                    var qty = parseInt(rows[i].qty, 10);

                    if (id > 0 && qty > 0) {
                        out.push({ id: id, qty: qty });
                    }
                }

                return out;
            } catch (e) {
                return memory.slice();
            }
        }

        function write(items) {
            memory = items.slice();

            try {
                window.localStorage.setItem(KEY, JSON.stringify({ items: items }));
            } catch (e) {
                /* penyimpanan ditolak (mis. mode privat): pakai memori saja */
            }
        }

        function find(items, id) {
            for (var i = 0; i < items.length; i++) {
                if (items[i].id === id) {
                    return items[i];
                }
            }

            return null;
        }

        function limitOf(max) {
            return max > 0 ? max : 99;
        }

        return {
            read: read,

            replace: function (items) {
                write(items);
            },

            qty: function (id) {
                var row = find(read(), id);

                return row ? row.qty : 0;
            },

            /* Tambah 1. Mengembalikan false bila sudah mencapai batas stok. */
            add: function (id, max) {
                var items = read();
                var row = find(items, id);
                var limit = limitOf(max);

                if (row) {
                    if (row.qty >= limit) {
                        return false;
                    }

                    row.qty += 1;
                } else {
                    if (limit < 1) {
                        return false;
                    }

                    items.push({ id: id, qty: 1 });
                }

                write(items);

                return true;
            },

            setQty: function (id, qty, max) {
                var items = read();
                var row = find(items, id);

                if (!row) {
                    return;
                }

                row.qty = Math.max(1, Math.min(qty, limitOf(max)));
                write(items);
            },

            remove: function (id) {
                write(read().filter(function (row) {
                    return row.id !== id;
                }));
            },

            clear: function () {
                write([]);
            }
        };
    })();
</script>
