@extends('layouts.customer')

@section('title', 'Pesanan Saya — Perkoci Eatery')

@section('content')
    @php
        use App\Support\Perkoci;

        $table = config('perkoci.table');
        $customer = config('perkoci.customer');
        $payments = config('perkoci.payment_methods');
        $menu = Perkoci::menu();
        $catalog = Perkoci::catalog($menu);
    @endphp

    <div class="px-4 pt-4">

        {{-- Judul --}}
        <div class="flex items-center justify-between gap-3">
            <h1 class="font-display text-[26px] font-bold leading-none text-brand">Pesanan Saya</h1>
            <span class="pill shrink-0 bg-lime px-3 py-1.5 text-[10px] text-cocoa" data-cart-count>
                0 Menu Siap
            </span>
        </div>

        {{-- Pesan hasil kirim --}}
        <p data-submit-error role="alert"
            class="mt-4 hidden rounded-xl bg-danger/10 px-3.5 py-3 text-[12px] font-semibold leading-relaxed text-danger"></p>

        <div data-success role="status" class="mt-4 hidden rounded-2xl bg-lime/40 p-4">
            <p class="text-[13.5px] font-bold text-cocoa">Pesanan diterima dapur ✓</p>
            <p class="mt-1 text-[12px] leading-relaxed text-cocoa/70">
                Nomor antrean <strong data-success-queue></strong>
                · Total <strong data-success-total></strong>
            </p>
            <a href="{{ url('/status') }}"
                class="mt-2.5 inline-block text-[12px] font-bold text-brand underline underline-offset-2">
                Lihat Status Pesanan
            </a>
        </div>

        {{-- Panel meja terkunci --}}
        <div class="panel mt-4 p-4">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand/12 text-brand">
                    <span class="ms text-[18px] leading-none">lock</span>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="text-[13.5px] font-bold leading-tight text-cocoa">
                        {{ $table['location'] }}
                    </p>

                    <span class="pill-sm mt-1.5 bg-white text-olive">{{ $table['locked_pill'] }}</span>

                    <p class="mt-2 text-[11.5px] leading-relaxed text-cocoa/55">
                        {{ $table['locked_desc'] }}
                    </p>

                    @if (empty($table['id_meja']))
                        <p class="mt-2 rounded-lg bg-danger/10 px-3 py-2 text-[11.5px] font-semibold leading-relaxed text-danger">
                            Meja belum terdeteksi. Scan QR di meja kamu dulu supaya pesanan bisa dikirim.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Keranjang kosong --}}
        <div data-cart-empty class="hidden py-12 text-center">
            <span class="ms text-[40px] leading-none text-cocoa/20">shopping_basket</span>
            <p class="mt-3 text-sm font-semibold text-cocoa/60">Keranjang masih kosong</p>
            <p class="mt-1 text-xs text-cocoa/40">Pilih menu favoritmu dulu ya.</p>
        </div>

        {{-- Kartu item (dirender JavaScript dari keranjang di browser) --}}
        <div class="mt-4 space-y-3" data-cart-list></div>

        {{-- Tambah menu lain --}}
        <a href="{{ url('/menu') }}"
            class="mt-3 flex w-full items-center justify-center gap-2 rounded-2xl bg-sand px-5 py-3.5 text-[13px] font-bold text-cocoa/70 transition hover:bg-cocoa/10">
            <span class="ms text-[16px] leading-none">add_circle</span>
            Tambah Menu Lainnya
        </a>

        <div data-cart-body class="hidden">

            {{-- Data pemesan --}}
            <div class="card mt-4 p-4">
                <h2 class="text-[14px] font-bold text-cocoa">Data Pemesan</h2>

                <label class="field-label mt-3.5" for="customer-name">
                    {{ $customer['name_label'] }} @if ($customer['name_required'])<span class="text-danger">*</span>@endif
                </label>
                <input id="customer-name" name="customer_name" type="text" class="field"
                    @if ($customer['name_required']) required @endif
                    placeholder="{{ $customer['name_placeholder'] }}" autocomplete="name">

                <label class="field-label mt-3.5" for="customer-phone">
                    {{ $customer['phone_label'] }}
                    <span class="font-medium normal-case tracking-normal text-cocoa/35">(opsional)</span>
                </label>
                <input id="customer-phone" name="customer_phone" type="tel" inputmode="tel" class="field"
                    placeholder="{{ $customer['phone_placeholder'] }}" autocomplete="tel">

                <p class="mt-2.5 flex items-start gap-1.5 text-[11px] leading-relaxed text-cocoa/45">
                    <span class="ms mt-px shrink-0 text-[13px] leading-none text-cocoa/40">info</span>
                    {{ $customer['info'] }}
                </p>
            </div>

            {{-- Metode pembayaran --}}
            <div class="card mt-4 p-4">
                <h2 class="text-[14px] font-bold text-cocoa">Metode Pembayaran</h2>

                <div class="mt-3 space-y-2">
                    @foreach ($payments as $method)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-cocoa/10 bg-sand/70 px-3.5 py-3 transition has-[:checked]:border-brand/45 has-[:checked]:bg-brand/[0.06]">
                            <input type="radio" name="payment_method" value="{{ $method['value'] }}"
                                @checked($method['default']) class="mt-0.5 h-[17px] w-[17px] shrink-0 accent-brand">

                            <span class="min-w-0 flex-1">
                                <span class="block text-[13px] font-semibold text-cocoa">{{ $method['label'] }}</span>
                                <span class="mt-0.5 block text-[11px] text-cocoa/50">{{ $method['meta'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Rincian biaya --}}
            <div class="card mt-4 p-4">
                <h2 class="text-[14px] font-bold text-cocoa">Rincian Biaya</h2>

                <dl class="mt-3 space-y-2.5 text-[12.5px]">
                    <div class="flex items-center justify-between">
                        <dt class="text-cocoa/60">Subtotal</dt>
                        <dd class="font-semibold text-cocoa" data-subtotal>{{ Perkoci::rupiah(0) }}</dd>
                    </div>

                    <div class="flex items-center justify-between border-t border-dashed border-cocoa/15 pt-3">
                        <dt class="text-[13px] font-bold text-cocoa">Total Pembayaran</dt>
                        <dd class="font-display text-[17px] font-bold text-brand" data-total>{{ Perkoci::rupiah(0) }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <div class="h-[150px]"></div>

    {{-- ==================== TOMBOL KIRIM FIXED ===================== --}}
    <div data-submit-bar class="fixed bottom-[calc(68px+env(safe-area-inset-bottom))] left-1/2 z-40 hidden w-full max-w-[430px] -translate-x-1/2 bg-cream/95 px-4 pb-3 pt-2 backdrop-blur">
        <button type="button" data-submit class="btn-primary w-full py-3.5 text-[13.5px]">
            <span data-submit-label>Kirim Pesanan ke Dapur</span>
            <span class="opacity-70" data-submit-sep>—</span>
            <span data-submit-total>{{ Perkoci::rupiah(0) }}</span>
        </button>
    </div>

    {{-- Template kartu item --}}
    <template data-cart-card>
        <article class="card p-3" data-cart-item>
            <div class="flex gap-3">
                <div class="h-[84px] w-[84px] shrink-0 overflow-hidden rounded-2xl bg-sand" data-thumb-slot></div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-[14px] font-bold leading-snug text-cocoa" data-name></h3>

                        <button type="button" data-remove aria-label="Hapus"
                            class="no-tap-highlight -mr-0.5 -mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-cocoa/30 transition hover:bg-danger/10 hover:text-danger">
                            <span class="ms text-[17px] leading-none">delete</span>
                        </button>
                    </div>

                    <p class="mt-0.5 text-[11px] font-semibold text-olive" data-unit-price></p>

                    <p class="mt-1.5 font-display text-[15px] font-bold text-brand" data-line-total></p>

                    <a href="#" data-detail
                        class="mt-0.5 inline-block text-[11px] font-semibold text-cocoa/60 underline underline-offset-2 transition hover:text-brand">
                        Lihat Detail
                    </a>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between border-t border-cocoa/10 pt-3">
                <span class="text-[11.5px] font-semibold text-cocoa/55">Jumlah Pesanan</span>

                <div class="flex items-center gap-1 rounded-full bg-sand px-1.5 py-1">
                    <button type="button" data-qty-step="-1" data-minus aria-label="Kurangi jumlah"
                        class="no-tap-highlight flex h-7 w-7 items-center justify-center rounded-full bg-white text-cocoa shadow-soft transition active:scale-90 disabled:opacity-40">
                        <span class="ms text-[14px] leading-none">remove</span>
                    </button>

                    <span data-qty class="w-5 text-center font-display text-[14px] font-bold text-cocoa"></span>

                    <button type="button" data-qty-step="1" data-plus aria-label="Tambah jumlah"
                        class="no-tap-highlight flex h-7 w-7 items-center justify-center rounded-full bg-brand text-white shadow-soft transition active:scale-90 disabled:opacity-40">
                        <span class="ms text-[14px] leading-none">add</span>
                    </button>
                </div>
            </div>
        </article>
    </template>

    {{-- Thumbnail tiap menu (memakai komponen yang sama, dikloning oleh JavaScript) --}}
    @foreach ($menu as $item)
        <template data-thumb="{{ $item['id'] }}">
            <x-menu-thumb :item="$item" size="text-[26px]" />
        </template>
    @endforeach
@endsection

@push('scripts')
    @include('partials.cart-store')

    <script>
        (function () {
            var catalog = @json($catalog);
            var tableId = @json($table['id_meja'] ?? null);
            var nameRequired = @json((bool) $customer['name_required']);

            var listEl = document.querySelector('[data-cart-list]');
            var cardTpl = document.querySelector('[data-cart-card]');
            var emptyEl = document.querySelector('[data-cart-empty]');
            var bodyEl = document.querySelector('[data-cart-body]');
            var barEl = document.querySelector('[data-submit-bar]');
            var countEl = document.querySelector('[data-cart-count]');
            var subtotalEl = document.querySelector('[data-subtotal]');
            var totalEl = document.querySelector('[data-total]');
            var submitTotalEl = document.querySelector('[data-submit-total]');
            var submitLabelEl = document.querySelector('[data-submit-label]');
            var submitSepEl = document.querySelector('[data-submit-sep]');
            var submitBtn = document.querySelector('[data-submit]');
            var errorEl = document.querySelector('[data-submit-error]');
            var successEl = document.querySelector('[data-success]');

            var sent = false;
            var busy = false;

            function rupiah(value) {
                return 'Rp ' + value.toLocaleString('id-ID');
            }

            /* Gabungkan isi keranjang browser dengan data menu terbaru dari server. */
            function lines() {
                var rows = PerkociCart.read();
                var out = [];
                var clean = [];

                rows.forEach(function (row) {
                    var product = catalog[row.id];

                    if (!product || !product.available) {
                        return;
                    }

                    var qty = Math.max(1, Math.min(row.qty, product.stock));

                    out.push({ product: product, qty: qty });
                    clean.push({ id: product.id, qty: qty });
                });

                if (clean.length !== rows.length) {
                    PerkociCart.replace(clean);
                }

                return out;
            }

            function showError(message) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
                errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function hideError() {
                errorEl.classList.add('hidden');
                errorEl.textContent = '';
            }

            function messageFrom(status, body) {
                if (status === 429) {
                    return 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.';
                }

                if (status === 422 && body && body.errors) {
                    for (var key in body.errors) {
                        if (body.errors[key] && body.errors[key][0]) {
                            return body.errors[key][0];
                        }
                    }
                }

                if (body && body.message && status < 500) {
                    return body.message;
                }

                return 'Pesanan belum bisa dikirim. Coba lagi sebentar.';
            }

            function render() {
                var items = lines();
                var subtotal = 0;

                listEl.innerHTML = '';

                items.forEach(function (line) {
                    var product = line.product;
                    var node = cardTpl.content.cloneNode(true);
                    var thumbTpl = document.querySelector('[data-thumb="' + product.id + '"]');

                    node.querySelector('[data-cart-item]').setAttribute('data-id', product.id);
                    node.querySelector('[data-name]').textContent = product.name;
                    node.querySelector('[data-unit-price]').textContent = rupiah(product.price) + ' / ' + product.unit;
                    node.querySelector('[data-line-total]').textContent = rupiah(product.price * line.qty);
                    node.querySelector('[data-qty]').textContent = line.qty;
                    node.querySelector('[data-detail]').setAttribute('href', product.url);
                    node.querySelector('[data-remove]').setAttribute('aria-label', 'Hapus ' + product.name);
                    node.querySelector('[data-minus]').disabled = line.qty <= 1;
                    node.querySelector('[data-plus]').disabled = line.qty >= product.stock;

                    if (thumbTpl) {
                        node.querySelector('[data-thumb-slot]').appendChild(thumbTpl.content.cloneNode(true));
                    }

                    listEl.appendChild(node);
                    subtotal += product.price * line.qty;
                });

                var hasItems = items.length > 0;

                countEl.textContent = hasItems ? items.length + ' Menu Siap' : 'Belum ada menu';
                subtotalEl.textContent = rupiah(subtotal);
                totalEl.textContent = rupiah(subtotal);
                submitTotalEl.textContent = rupiah(subtotal);

                emptyEl.classList.toggle('hidden', hasItems || sent);
                bodyEl.classList.toggle('hidden', !hasItems);
                barEl.classList.toggle('hidden', !hasItems);

                submitBtn.disabled = busy || !tableId;
            }

            listEl.addEventListener('click', function (event) {
                var card = event.target.closest('[data-cart-item]');

                if (!card) {
                    return;
                }

                var id = parseInt(card.getAttribute('data-id'), 10);
                var product = catalog[id];

                if (!product) {
                    return;
                }

                var stepBtn = event.target.closest('[data-qty-step]');

                if (stepBtn) {
                    var step = parseInt(stepBtn.getAttribute('data-qty-step'), 10);

                    PerkociCart.setQty(id, PerkociCart.qty(id) + step, product.stock);
                    render();

                    return;
                }

                if (event.target.closest('[data-remove]')) {
                    PerkociCart.remove(id);
                    render();
                }
            });

            function setBusy(state) {
                busy = state;
                submitBtn.disabled = state || !tableId;
                submitLabelEl.textContent = state ? 'Mengirim pesanan…' : 'Kirim Pesanan ke Dapur';
                submitSepEl.classList.toggle('hidden', state);
                submitTotalEl.classList.toggle('hidden', state);
            }

            submitBtn.addEventListener('click', function () {
                var items = lines();

                if (busy || items.length === 0) {
                    return;
                }

                hideError();

                if (!tableId) {
                    showError('Meja belum terdeteksi. Scan QR di meja kamu dulu.');

                    return;
                }

                var nameEl = document.getElementById('customer-name');
                var phoneEl = document.getElementById('customer-phone');
                var name = nameEl ? nameEl.value.trim() : '';
                var phone = phoneEl ? phoneEl.value.trim() : '';

                if (nameRequired && !name) {
                    showError('Isi nama pemesan dulu ya.');

                    if (nameEl) {
                        nameEl.focus();
                    }

                    return;
                }

                setBusy(true);

                fetch('/api/pesanan', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_meja: tableId,
                        nama_pemesan: name,
                        no_whatsapp: phone,
                        items: items.map(function (line) {
                            return { id_produk: line.product.id, jumlah: line.qty };
                        })
                    })
                })
                    .then(function (response) {
                        return response.json()
                            .catch(function () { return {}; })
                            .then(function (body) {
                                return { ok: response.ok, status: response.status, body: body };
                            });
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            showError(messageFrom(result.status, result.body));

                            return;
                        }

                        var order = (result.body && result.body.data) || {};

                        try {
                            window.localStorage.setItem('perkoci_last_order', JSON.stringify({
                                id: order.id_pesanan,
                                nomor: order.nomor_antrean,
                                total: order.total_harga,
                                at: Date.now()
                            }));
                        } catch (e) {
                            /* abaikan jika penyimpanan ditolak */
                        }

                        PerkociCart.clear();
                        sent = true;

                        successEl.querySelector('[data-success-queue]').textContent = order.nomor_antrean || '-';
                        successEl.querySelector('[data-success-total]').textContent = rupiah(Number(order.total_harga) || 0);
                        successEl.classList.remove('hidden');
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    })
                    .catch(function () {
                        showError('Tidak bisa terhubung ke server. Coba lagi sebentar.');
                    })
                    .then(function () {
                        setBusy(false);
                        render();
                    });
            });

            render();
        })();
    </script>
@endpush
