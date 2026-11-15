@extends('layouts.customer')

@section('title', 'Status Pesanan — Perkoci Eatery')

@section('content')
    <div class="px-4 pt-4">

        {{-- ==================== STATE KOSONG (Belum ada pesanan) ==================== --}}
        <div id="empty-state" class="card hidden p-8 text-center">
            <span class="ms text-[48px] text-cocoa/30">receipt_long</span>
            <h2 class="mt-3 font-display text-[18px] font-bold text-cocoa">Belum Ada Pesanan Aktif</h2>
            <p class="mt-1 text-[12px] text-cocoa/55">Kamu belum memiliki pesanan. Silakan pilih menu di katalog.</p>
            <a href="{{ url('/menu') }}" class="pill mt-5 inline-block bg-brand px-5 py-2.5 text-[12px] font-bold text-white">
                Ke Katalog Menu
            </a>
        </div>

        {{-- ==================== WRAPPER DATA PESANAN ==================== --}}
        <div id="order-wrapper" class="hidden">

            {{-- Judul halaman & lokasi meja --}}
            <div class="flex items-center justify-between gap-3">
                <h1 class="font-display text-[26px] font-bold leading-none text-brand">Status Pesanan</h1>
                <span id="display-location" class="pill shrink-0 bg-lime px-3 py-1.5 text-[10px] text-cocoa">
                    Meja --
                </span>
            </div>

            {{-- ==================== NOMOR ANTREAN ==================== --}}
            <section class="card mt-4 overflow-hidden" aria-labelledby="queue-label">
                <div class="px-5 pt-6 text-center">
                    <p id="queue-label" class="text-[10px] font-bold uppercase tracking-[0.22em] text-cocoa/40">
                        NOMOR ANTREAN
                    </p>

                    <p id="display-antrean" class="mt-1.5 font-display text-[36px] font-bold leading-none tracking-tight text-brand">
                        ---
                    </p>
                </div>

                {{-- Meja & pemesan --}}
                <div class="mx-4 mt-5 flex items-start gap-2.5 rounded-2xl bg-sand px-4 py-3">
                    <span class="ms mt-px shrink-0 text-[16px] leading-none text-brand">location_on</span>

                    <div class="min-w-0 text-[11.5px] leading-relaxed">
                        <p id="display-meja-detail" class="font-bold text-cocoa">Meja --</p>
                        <p class="text-cocoa/55">
                            <span id="display-customer">-</span> <span class="text-cocoa/30">•</span> Order ID <span id="display-order-id">-</span>
                        </p>
                    </div>
                </div>

                {{-- Status terkini --}}
                <div id="status-banner" class="mt-4 bg-brand px-5 py-4 text-center transition-colors">
                    <p class="text-[9px] font-bold uppercase tracking-[0.22em] text-white/55">
                        STATUS TERKINI
                    </p>
                    <p id="display-status-text" class="mt-1 font-display text-[16px] font-bold leading-snug text-white">
                        Memuat status pesanan...
                    </p>
                </div>
            </section>

            {{-- ===================== PROGRES SAJIAN (Timeline Jujur) =================== --}}
            <section class="card mt-3 p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="font-display text-[16px] font-bold leading-tight text-cocoa">Progres Sajian</h2>

                    <span class="pill shrink-0 bg-sand text-[9px] text-cocoa/55">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand/50"></span>
                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-brand"></span>
                        </span>
                        Pembaruan otomatis
                    </span>
                </div>

                <ol class="mt-5">
                    {{-- 1. Menunggu --}}
                    <li id="step-menunggu" class="relative flex gap-3 pb-5 transition-opacity opacity-40">
                        <div class="relative flex w-11 shrink-0 justify-center">
                            <span class="absolute left-1/2 top-9 -bottom-5 w-[2px] -translate-x-1/2 bg-cocoa/10 step-line"></span>
                            <span class="step-icon relative z-10 flex h-9 w-9 items-center justify-center rounded-full bg-sand text-cocoa/30 transition">
                                <span class="ms text-[16px] leading-none">receipt_long</span>
                            </span>
                        </div>
                        <div class="min-w-0 flex-1 pt-2">
                            <div class="flex items-baseline justify-between gap-2">
                                <p class="step-title text-[13px] font-bold leading-snug text-cocoa/40">Pesanan Diterima</p>
                                <p id="time-created" class="shrink-0 text-[10px] font-semibold text-cocoa/35">-</p>
                            </div>
                            <p class="mt-0.5 text-[11px] leading-relaxed text-cocoa/40">Pesanan tercatat dan diteruskan ke kasir/dapur.</p>
                        </div>
                    </li>

                    {{-- 2. Diproses --}}
                    <li id="step-diproses" class="relative flex gap-3 pb-5 transition-opacity opacity-40">
                        <div class="relative flex w-11 shrink-0 justify-center">
                            <span class="absolute left-1/2 top-9 -bottom-5 w-[2px] -translate-x-1/2 bg-cocoa/10 step-line"></span>
                            <span class="step-icon relative z-10 flex h-9 w-9 items-center justify-center rounded-full bg-sand text-cocoa/30 transition">
                                <span class="ms text-[16px] leading-none">skillet</span>
                            </span>
                        </div>
                        <div class="min-w-0 flex-1 pt-2">
                            <p class="step-title text-[13px] font-bold leading-snug text-cocoa/40">Sedang Diracik / Dimasak</p>
                            <p class="mt-0.5 text-[11px] leading-relaxed text-cocoa/40">Hidangan sedang dipersiapkan oleh tim dapur.</p>
                        </div>
                    </li>

                    {{-- 3. Selesai / Dibatalkan --}}
                    <li id="step-final" class="relative flex gap-3 transition-opacity opacity-40">
                        <div class="relative flex w-11 shrink-0 justify-center">
                            <span class="step-icon relative z-10 flex h-9 w-9 items-center justify-center rounded-full bg-sand text-cocoa/30 transition">
                                <span id="final-step-ms-icon" class="ms text-[16px] leading-none">task_alt</span>
                            </span>
                        </div>
                        <div class="min-w-0 flex-1 pt-2">
                            <div class="flex items-baseline justify-between gap-2">
                                <p id="step-final-title" class="step-title text-[13px] font-bold leading-snug text-cocoa/40">Selesai & Lunas</p>
                                <p id="time-updated" class="shrink-0 text-[10px] font-semibold text-cocoa/35">-</p>
                            </div>
                            <p id="step-final-desc" class="mt-0.5 text-[11px] leading-relaxed text-cocoa/40">Pesanan telah disajikan dan selesai.</p>
                        </div>
                    </li>
                </ol>
            </section>

            {{-- =================== METODE PEMBAYARAN ================== --}}
            <section class="card mt-3 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="font-display text-[16px] font-bold leading-tight text-cocoa">Pembayaran Non-Tunai</h2>
                        <p class="mt-1 text-[12px] font-semibold text-cocoa/55">QRIS / Kasir</p>
                    </div>

                    <span id="payment-status-pill" class="pill shrink-0 bg-butter text-cocoa">
                        <span class="ms text-[12px] leading-none">schedule</span>
                        <span id="payment-status-text">Menunggu Pembayaran</span>
                    </span>
                </div>

                <div class="mt-4 rounded-2xl bg-sand p-4">
                    <div class="flex items-center gap-4">
                        {{-- Pola QR --}}
                        <div class="grid h-[96px] w-[96px] shrink-0 place-items-center rounded-xl bg-white p-1.5 shadow-soft">
                            <svg viewBox="0 0 21 21" class="h-full w-full text-cocoa" shape-rendering="crispEdges" role="img">
                                <path fill="currentColor" d="M0 0h7v7H0zM2 2v3h3V2zM14 0h7v7h-7zM16 2v3h3V2zM0 14h7v7H0zM2 16v3h3v-3zM9 0h3v3H9zM9 5h2v4H9zM4 9h5v2H4zM12 9h4v2h-4zM18 9h3v5h-3zM9 13h4v2H9zM15 13h2v4h-2zM9 17h3v4H9zM17 17h4v4h-4z"/>
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <p class="text-[11px] leading-relaxed text-cocoa/55">Tunjukkan kode ini ke kasir jika membayar tunai/QRIS manual:</p>
                            <p id="display-payment-code" class="mt-2 font-display text-[15px] font-bold tracking-[0.1em] text-cocoa">
                                PERKOCI
                            </p>
                            <p class="mt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-cocoa/40">
                                TOTAL PEMBAYARAN
                            </p>
                            <p id="display-total-price" class="font-display text-[19px] font-bold leading-tight text-brand">
                                Rp 0
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ==================== RINCIAN ITEM ====================== --}}
            <details class="card group mt-3 overflow-hidden">
                <summary class="no-tap-highlight flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 [&::-webkit-details-marker]:hidden">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand">
                            <span class="ms text-[17px] leading-none">receipt_long</span>
                        </span>

                        <div class="min-w-0">
                            <p class="font-display text-[15px] font-bold leading-tight text-cocoa">
                                Ringkasan Pesanan (<span id="display-menu-count">0</span> Menu)
                            </p>
                            <p class="mt-0.5 text-[10.5px] text-cocoa/45">
                                <span id="display-item-count">0</span> item <span class="text-cocoa/25">•</span>
                                <span id="display-total-price-summary">Rp 0</span>
                            </p>
                        </div>
                    </div>

                    <span class="ms shrink-0 text-[22px] leading-none text-cocoa/35 transition-transform duration-200 group-open:rotate-180">expand_more</span>
                </summary>

                <div class="border-t border-cocoa/10 px-5 py-4">
                    <ul id="display-item-list" class="space-y-3">
                        {{-- Diisi dinamis oleh JavaScript --}}
                    </ul>

                    <dl class="mt-4 space-y-2 rounded-xl bg-sand px-4 py-3 text-[12px]">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-cocoa/55">Subtotal</dt>
                            <dd id="display-subtotal" class="shrink-0 font-semibold text-cocoa">Rp 0</dd>
                        </div>
                    </dl>

                    <div class="mt-2 flex items-center justify-between border-t border-dashed border-cocoa/15 px-1 pt-2.5">
                        <span class="text-[12.5px] font-bold text-cocoa">Total Tagihan</span>
                        <span id="display-grand-total" class="font-display text-[16px] font-bold text-brand">Rp 0</span>
                    </div>
                </div>
            </details>

            

            <div data-action-feedback role="status" aria-live="polite" class="mt-2.5 hidden rounded-xl bg-brand/10 px-4 py-2.5 text-center text-[11px] font-semibold text-brand"></div>

            {{-- ====================== PENUTUP ========================= --}}
            <div class="mt-3 rounded-2xl bg-sand px-5 py-5 text-center">
                <span class="ms text-[24px] leading-none text-brand/70">self_improvement</span>
                <p class="mt-1.5 text-[11.5px] leading-relaxed text-cocoa/55">Terima kasih telah memesan! Selamat menikmati sajian khas Perkoci Eatery.</p>
            </div>

            <p class="mt-3 text-center text-[9px] leading-relaxed text-cocoa/30">Halaman ini otomatis mengambil data terbaru setiap 10 detik.</p>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Helper format Rupiah
            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number || 0);
            }

            // Helper escape HTML (nama menu berasal dari database)
            function esc(str) {
                return String(str == null ? '' : str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            // Helper format jam (ISO -> "14:20 WIB")
            function formatJam(iso) {
                if (!iso) return '-';
                var d = new Date(iso);
                if (isNaN(d.getTime())) return '-';
                return d.toLocaleTimeString('id-ID', {
                    hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta'
                }).replace('.', ':') + ' WIB';
            }

            // Baca info order dari localStorage
            var lastOrder = window.PerkociOrder ? window.PerkociOrder.get() : null;
            if (!lastOrder) {
                try {
                    var raw = localStorage.getItem('perkoci_last_order');
                    if (raw) lastOrder = JSON.parse(raw);
                } catch(e){}
            }

            // Data tersimpan bisa memakai nama field berbeda (id/kode atau order_id/kode_akses)
            var orderId = lastOrder ? (lastOrder.id || lastOrder.order_id || lastOrder.id_pesanan) : null;
            var kodeAkses = lastOrder ? (lastOrder.kode || lastOrder.kode_akses) : null;

            if (!orderId || !kodeAkses) {
                document.getElementById('empty-state').classList.remove('hidden');
                return;
            }

            document.getElementById('order-wrapper').classList.remove('hidden');

            // Ubah response PesananResource ke bentuk yang dipakai updateView()
            function normalize(d) {
                var namaMeja = 'Meja --';
                if (d.meja) {
                    var no = d.meja.no_meja || d.meja.nama_meja || '';
                    if (no !== '') {
                        namaMeja = /^meja/i.test(String(no)) ? String(no) : 'Meja ' + no;
                    }
                }

                var detail = d.detail_pesanan || d.items || [];

                return {
                    id: d.id_pesanan || d.id || '-',
                    nomor_antrean: d.nomor_antrean || '---',
                    nama_pemesan: d.nama_pemesan || '-',
                    meja: namaMeja,
                    status: d.status_pesanan === 'batal' ? 'dibatalkan' : d.status_pesanan,
                    created_at: formatJam(d.tanggal_pesan || d.created_at),
                    updated_at: formatJam(d.updated_at),
                    total: parseFloat(d.total_harga) || 0,
                    items: detail.map(function (it) {
                        return {
                            jumlah: it.jumlah,
                            harga_satuan: parseFloat(it.harga) || 0,
                            nama_menu: (it.produk && it.produk.nama_produk) || it.nama_produk || 'Menu'
                        };
                    })
                };
            }

            function updateView(data) {
                // Info dasar
                document.getElementById('display-location').textContent = data.meja;
                document.getElementById('display-meja-detail').textContent = data.meja;
                document.getElementById('display-antrean').textContent = data.nomor_antrean;
                document.getElementById('display-customer').textContent = data.nama_pemesan;
                document.getElementById('display-order-id').textContent = data.id;
                document.getElementById('display-payment-code').textContent = data.nomor_antrean !== '---' ? data.nomor_antrean : 'ORD-' + data.id;

                // Waktu
                document.getElementById('time-created').textContent = data.created_at || '-';
                document.getElementById('time-updated').textContent = data.updated_at || '-';

                // Total harga
                var total = data.total || 0;
                document.getElementById('display-total-price').textContent = formatRupiah(total);
                document.getElementById('display-total-price-summary').textContent = formatRupiah(total);
                document.getElementById('display-subtotal').textContent = formatRupiah(total);
                document.getElementById('display-grand-total').textContent = formatRupiah(total);

                // Rincian Item Menu
                var items = data.items || [];
                var itemCount = 0;
                var itemListHtml = '';

                items.forEach(function(item) {
                    var qty = item.jumlah || item.qty || 1;
                    var harga = item.harga_satuan || item.price || 0;
                    var nama = item.nama_menu || item.name || 'Menu';
                    itemCount += parseInt(qty, 10);

                    itemListHtml += `
                        <li class="flex items-center gap-3">
                            <div class="h-11 w-11 shrink-0 overflow-hidden rounded-xl bg-sand flex items-center justify-center">
                                <span class="ms text-[20px] text-cocoa/40">restaurant</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[12.5px] font-bold leading-snug text-cocoa">${esc(nama)}</p>
                                <p class="truncate text-[10.5px] text-cocoa/50">${formatRupiah(harga)} / porsi</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="font-display text-[12.5px] font-bold text-cocoa">${formatRupiah(harga * qty)}</p>
                                <p class="text-[10px] text-cocoa/40">${qty}x</p>
                            </div>
                        </li>
                    `;
                });

                document.getElementById('display-menu-count').textContent = items.length;
                document.getElementById('display-item-count').textContent = itemCount;
                document.getElementById('display-item-list').innerHTML = itemListHtml;

                // Reset Class Timeline
                var resetStep = function(id) {
                    var li = document.getElementById(id);
                    li.className = (id === 'step-final' ? 'relative flex gap-3' : 'relative flex gap-3 pb-5') + ' transition-opacity opacity-40';
                    var icon = li.querySelector('.step-icon');
                    icon.className = 'step-icon relative z-10 flex h-9 w-9 items-center justify-center rounded-full bg-sand text-cocoa/30 transition';
                    var title = li.querySelector('.step-title');
                    if (title) title.className = 'step-title text-[13px] font-bold leading-snug text-cocoa/40';
                };

                var activateStep = function(id, isDone) {
                    var li = document.getElementById(id);
                    li.classList.remove('opacity-40');
                    var icon = li.querySelector('.step-icon');
                    var title = li.querySelector('.step-title');

                    if (isDone) {
                        icon.className = 'step-icon relative z-10 flex h-9 w-9 items-center justify-center rounded-full bg-brand text-white transition';
                        if (title) title.className = 'step-title text-[13px] font-bold leading-snug text-cocoa';
                    } else {
                        icon.className = 'step-icon relative z-10 flex h-11 w-11 items-center justify-center rounded-full bg-brand text-white shadow-soft ring-4 ring-lime transition';
                        if (title) title.className = 'step-title text-[13px] font-bold leading-snug text-brand';
                    }
                };

                resetStep('step-menunggu');
                resetStep('step-diproses');
                resetStep('step-final');

                // Kembalikan teks langkah akhir ke bawaan (kalau sebelumnya dibatalkan)
                document.getElementById('step-final-title').textContent = 'Selesai & Lunas';
                document.getElementById('step-final-desc').textContent = 'Pesanan telah disajikan dan selesai.';
                document.getElementById('final-step-ms-icon').textContent = 'task_alt';

                var banner = document.getElementById('status-banner');
                var statusText = document.getElementById('display-status-text');
                var payPill = document.getElementById('payment-status-pill');
                var payText = document.getElementById('payment-status-text');

                // Pemetaan Status DB ke Timeline
                if (data.status === 'menunggu') {
                    banner.className = 'mt-4 bg-brand px-5 py-4 text-center transition-colors';
                    statusText.textContent = 'Pesanan Menunggu Konfirmasi';
                    activateStep('step-menunggu', false);

                    payPill.className = 'pill shrink-0 bg-butter text-cocoa';
                    payText.textContent = 'Belum Lunas';
                }
                else if (data.status === 'diproses') {
                    banner.className = 'mt-4 bg-brand px-5 py-4 text-center transition-colors';
                    statusText.textContent = 'Sedang Diracik oleh Barista / Dapur';
                    activateStep('step-menunggu', true);
                    activateStep('step-diproses', false);

                    payPill.className = 'pill shrink-0 bg-butter text-cocoa';
                    payText.textContent = 'Dalam Diproses';
                }
                else if (data.status === 'selesai') {
                    banner.className = 'mt-4 bg-brand px-5 py-4 text-center transition-colors';
                    statusText.textContent = 'Pesanan Selesai & Lunas';
                    activateStep('step-menunggu', true);
                    activateStep('step-diproses', true);
                    activateStep('step-final', true);

                    payPill.className = 'pill shrink-0 bg-lime text-cocoa';
                    payText.textContent = 'Selesai & Lunas';
                }
                else if (data.status === 'dibatalkan') {
                    banner.className = 'mt-4 bg-red-600 px-5 py-4 text-center transition-colors';
                    statusText.textContent = 'Pesanan Dibatalkan';

                    document.getElementById('step-final-title').textContent = 'Pesanan Dibatalkan';
                    document.getElementById('step-final-desc').textContent = 'Pesanan ini dibatalkan oleh pihak kasir/restoran.';
                    document.getElementById('final-step-ms-icon').textContent = 'cancel';

                    var liFinal = document.getElementById('step-final');
                    liFinal.classList.remove('opacity-40');
                    var iconFinal = liFinal.querySelector('.step-icon');
                    iconFinal.className = 'step-icon relative z-10 flex h-11 w-11 items-center justify-center rounded-full bg-red-600 text-white transition';

                    payPill.className = 'pill shrink-0 bg-red-100 text-red-700';
                    payText.textContent = 'Dibatalkan';
                }
            }

            function fetchStatus() {
                fetch('/api/pesanan/' + encodeURIComponent(orderId) +
                      '?kode=' + encodeURIComponent(kodeAkses), {
                    headers: { 'Accept': 'application/json' }
                })
                .then(function (res) {
                    if (res.status === 404) throw new Error('NOT_FOUND');
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function (res) {
                    updateView(normalize(res.data || res));
                })
                .catch(function (err) {
                    console.error('Gagal memperbarui status:', err);
                    document.getElementById('display-status-text').textContent =
                        err.message === 'NOT_FOUND'
                            ? 'Pesanan tidak ditemukan'
                            : 'Gagal memuat status, mencoba lagi...';
                });
            }

            // Jalankan saat load & set polling 10s
            fetchStatus();
            setInterval(fetchStatus, 10000);

            // Handler tombol panggil pelayan & struk
            var buttons = document.querySelectorAll('[data-action]');
            var feedback = document.querySelector('[data-action-feedback]');

            if (buttons.length && feedback) {
                Array.prototype.forEach.call(buttons, function (button) {
                    var labelEl = button.querySelector('[data-label-text]');
                    var hintEl = button.querySelector('[data-hint]');
                    var iconEl = button.querySelector('[data-icon]');

                    button.addEventListener('click', function () {
                        if (button.disabled) return;

                        button.disabled = true;
                        labelEl.textContent = button.getAttribute('data-done');
                        hintEl.textContent = 'Permintaan terkirim';
                        iconEl.textContent = 'check_circle';
                        iconEl.classList.add('ms-fill');

                        var locationText = document.getElementById('display-location').textContent;
                        feedback.textContent = button.getAttribute('data-done') + ' — ' + locationText;
                        feedback.classList.remove('hidden');

                        window.setTimeout(function () {
                            button.disabled = false;
                            labelEl.textContent = button.getAttribute('data-label');
                            hintEl.textContent = button.getAttribute('data-default-hint');
                            iconEl.textContent = button.getAttribute('data-default-icon');
                            iconEl.classList.remove('ms-fill');
                            feedback.classList.add('hidden');
                        }, 5000);
                    });
                });
            }
        });
    </script>
@endpush
