@extends('layouts.customer')

@section('title', 'Pesanan Saya — Perkoci Eatery')

@section('content')
    @php
        use App\Support\Perkoci;

        $table = config('perkoci.table');
        $customer = config('perkoci.customer');
        $items = Perkoci::cartItems();
        $totals = Perkoci::cartTotals($items);
        $payments = config('perkoci.payment_methods');
    @endphp

    <div class="px-4 pt-4">

        {{-- Judul --}}
        <div class="flex items-center justify-between gap-3">
            <h1 class="font-display text-[26px] font-bold leading-none text-brand">Pesanan Saya</h1>
            <span class="pill shrink-0 bg-lime px-3 py-1.5 text-[10px] text-cocoa">
                {{ $totals['count'] }} Menu Siap
            </span>
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
                </div>
            </div>
        </div>

        {{-- Kartu item --}}
        <div class="mt-4 space-y-3" data-cart-list>
            @foreach ($items as $index => $item)
                <article class="card p-3" data-cart-item data-index="{{ $index }}" data-price="{{ $item['price'] }}">
                    <div class="flex gap-3">
                        <div class="h-[84px] w-[84px] shrink-0 overflow-hidden rounded-2xl bg-sand">
                            <x-menu-thumb :item="$item" size="text-[26px]" />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-[14px] font-bold leading-snug text-cocoa">{{ $item['name'] }}</h3>

                                <button type="button" data-remove aria-label="Hapus {{ $item['name'] }}"
                                    class="no-tap-highlight -mr-0.5 -mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-cocoa/30 transition hover:bg-danger/10 hover:text-danger">
                                    <span class="ms text-[17px] leading-none">delete</span>
                                </button>
                            </div>

                            <p class="mt-0.5 text-[11px] font-semibold text-olive">
                                {{ Perkoci::rupiah($item['price']) }} / {{ $item['unit'] ?? 'porsi' }}
                            </p>

                            @if (! empty($item['note']))
                                <span class="pill-sm mt-1.5 bg-danger/10 italic text-danger">{{ $item['note'] }}</span>
                            @endif

                            <p class="mt-1.5 font-display text-[15px] font-bold text-brand" data-line-total>
                                {{ Perkoci::rupiah($item['price'] * $item['qty']) }}
                            </p>

                            <a href="{{ url('/menu/'.$item['slug']) }}"
                                class="mt-0.5 inline-block text-[11px] font-semibold text-cocoa/60 underline underline-offset-2 transition hover:text-brand">
                                Lihat Detail
                            </a>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between border-t border-cocoa/10 pt-3">
                        <span class="text-[11.5px] font-semibold text-cocoa/55">Jumlah Pesanan</span>

                        <div class="flex items-center gap-1 rounded-full bg-sand px-1.5 py-1">
                            <button type="button" data-qty-step="-1" aria-label="Kurangi jumlah {{ $item['unit'] ?? 'porsi' }}"
                                class="no-tap-highlight flex h-7 w-7 items-center justify-center rounded-full bg-white text-cocoa shadow-soft transition active:scale-90">
                                <span class="ms text-[14px] leading-none">remove</span>
                            </button>

                            <span data-qty class="w-5 text-center font-display text-[14px] font-bold text-cocoa">{{ $item['qty'] }}</span>

                            <button type="button" data-qty-step="1" aria-label="Tambah jumlah {{ $item['unit'] ?? 'porsi' }}"
                                class="no-tap-highlight flex h-7 w-7 items-center justify-center rounded-full bg-brand text-white shadow-soft transition active:scale-90">
                                <span class="ms text-[14px] leading-none">add</span>
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Tambah menu lain --}}
        <a href="{{ url('/menu') }}"
            class="mt-3 flex w-full items-center justify-center gap-2 rounded-2xl bg-sand px-5 py-3.5 text-[13px] font-bold text-cocoa/70 transition hover:bg-cocoa/10">
            <span class="ms text-[16px] leading-none">add_circle</span>
            Tambah Menu Lainnya
        </a>

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
                    <dd class="font-semibold text-cocoa" data-subtotal>{{ Perkoci::rupiah($totals['subtotal']) }}</dd>
                </div>

                <div class="flex items-center justify-between border-t border-dashed border-cocoa/15 pt-3">
                    <dt class="text-[13px] font-bold text-cocoa">Total Pembayaran</dt>
                    <dd class="font-display text-[17px] font-bold text-brand" data-total>{{ Perkoci::rupiah($totals['total']) }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="h-[150px]"></div>

    {{-- ==================== TOMBOL KIRIM FIXED ===================== --}}
    <div class="fixed bottom-[calc(68px+env(safe-area-inset-bottom))] left-1/2 z-40 w-full max-w-[430px] -translate-x-1/2 bg-cream/95 px-4 pb-3 pt-2 backdrop-blur">
        <button type="button" data-submit class="btn-primary w-full py-3.5 text-[13.5px]">
            Kirim Pesanan ke Dapur
            <span class="opacity-70">—</span>
            <span data-submit-total>{{ Perkoci::rupiah($totals['total']) }}</span>
        </button>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var listEl = document.querySelector('[data-cart-list]');
            var subtotalEl = document.querySelector('[data-subtotal]');
            var totalEl = document.querySelector('[data-total]');
            var submitTotalEl = document.querySelector('[data-submit-total]');

            function rupiah(value) {
                return 'Rp ' + value.toLocaleString('id-ID');
            }

            function readItems() {
                return Array.prototype.map.call(listEl.querySelectorAll('[data-cart-item]'), function (card) {
                    var qty = parseInt(card.querySelector('[data-qty]').textContent, 10) || 1;

                    return {
                        card: card,
                        price: parseInt(card.getAttribute('data-price'), 10) || 0,
                        qty: qty,
                    };
                });
            }

            function render() {
                var items = readItems();

                var subtotal = items.reduce(function (sum, item) {
                    return sum + item.price * item.qty;
                }, 0);

                var total = subtotal;

                items.forEach(function (item) {
                    item.card.querySelector('[data-line-total]').textContent = rupiah(item.price * item.qty);
                });

                subtotalEl.textContent = rupiah(subtotal);
                totalEl.textContent = rupiah(total);
                submitTotalEl.textContent = rupiah(total);
            }

            listEl.addEventListener('click', function (event) {
                var card = event.target.closest('[data-cart-item]');
                if (! card) return;

                var stepBtn = event.target.closest('[data-qty-step]');
                if (stepBtn) {
                    var qtyEl = card.querySelector('[data-qty]');
                    var qty = parseInt(qtyEl.textContent, 10) || 1;

                    qty = Math.max(1, qty + parseInt(stepBtn.getAttribute('data-qty-step'), 10));
                    qtyEl.textContent = qty;

                    render();
                    return;
                }

                var removeBtn = event.target.closest('[data-remove]');
                if (removeBtn) {
                    card.remove();
                    render();
                }
            });

            document.querySelector('[data-submit]').addEventListener('click', function () {
                this.textContent = 'Pesanan Terkirim ke Dapur ✓';
                this.classList.add('bg-brand-dark');
            });

            render();
        })();
    </script>
@endpush
