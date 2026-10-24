@extends('layouts.customer')

@section('title', ($item['name'] ?? 'Menu') . ' — Perkoci Eatery')

@section('header_title')
    Detail Menu
@endsection

@section('back_href')
    {{ url('/menu') }}
@endsection

{{-- Tanpa bottom nav pada halaman ini --}}
@section('bottomnav')@endsection

@section('content')
    @php
        use App\Support\Perkoci;

        $noteConfig = config('perkoci.note');
        $basePrice = $item['price'];
        $unit = $item['unit'] ?? 'porsi';
        $categoryName = Perkoci::categoryName($item['category']);
    @endphp

    {{-- ============================= HERO ============================= --}}
    <div class="relative h-[260px] w-full overflow-hidden bg-sand">
        <x-menu-thumb :item="$item" size="text-[72px]" />

        @if (! empty($item['image']))
            <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_bottom,rgba(249,248,243,0)_45%,rgba(249,248,243,.55)_72%,#f9f8f3_100%)]"></div>
        @endif

        {{-- Kategori kiri atas --}}
        <span class="pill absolute left-4 top-4 bg-brand px-3 py-1.5 text-[10px] text-white shadow-soft">
            <span class="ms text-[13px] leading-none">restaurant_menu</span>
            {{ $categoryName }}
        </span>

        {{-- Tombol hati kanan atas --}}
        <button type="button" data-favorite aria-pressed="false"
            class="no-tap-highlight absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white text-cocoa/70 shadow-soft transition active:scale-90"
            aria-label="Tambahkan ke favorit">
            <span class="ms ms-wght text-[20px] leading-none">favorite</span>
        </button>
    </div>

    {{-- =========================== IDENTITAS =========================== --}}
    <div class="px-4">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-brand">
            {{ $categoryName }}
        </p>

        <div class="mt-1.5 flex items-start justify-between gap-3">
            <h1 class="font-display text-[24px] font-bold leading-tight text-cocoa">
                {{ $item['name'] }}
            </h1>

            <p class="shrink-0 text-right font-display text-[19px] font-bold leading-tight text-brand">
                {{ Perkoci::rupiah($basePrice) }}
                <span class="block text-[10px] font-semibold tracking-normal text-cocoa/45">/ {{ $unit }}</span>
            </p>
        </div>

        @if (! empty($item['description']))
            <p class="mt-2 text-[13px] leading-relaxed text-cocoa/60">
                {{ $item['description'] }}
            </p>
        @endif
    </div>

    {{-- ========================== KARTU CATATAN ======================== --}}
    <div class="px-4 pt-5">
        <div class="card p-4">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-[13.5px] font-bold text-cocoa">{{ $noteConfig['label'] }}</h2>
                <span class="text-[11px] font-semibold text-cocoa/40"><span data-note-count>0</span>/{{ $noteConfig['max'] }}</span>
            </div>

            <textarea name="note" rows="3" maxlength="{{ $noteConfig['max'] }}" data-note
                aria-label="{{ $noteConfig['label'] }}"
                placeholder="{{ $noteConfig['placeholder'] }}"
                class="field mt-2 resize-none text-[13px] leading-relaxed"></textarea>

            <div class="mt-4 flex items-center justify-between border-t border-cocoa/10 pt-3.5">
                <p class="text-[12px] text-cocoa/55">Harga satuan</p>
                <p class="text-[12.5px] font-bold text-cocoa">
                    {{ Perkoci::rupiah($basePrice) }} / {{ $unit }}
                </p>
            </div>
        </div>
    </div>

    <div class="h-[140px]"></div>

    {{-- ======================== BAR BAWAH FIXED ======================= --}}
    <div class="fixed bottom-0 left-1/2 z-40 w-full max-w-[430px] -translate-x-1/2 border-t border-cocoa/10 bg-white px-4 pb-[calc(0.75rem+env(safe-area-inset-bottom))] pt-2.5 shadow-bar">
        <p class="mb-2 text-center text-[10px] font-bold text-cocoa/45">
            {{ Perkoci::rupiah($basePrice) }} / {{ $unit }}
        </p>

        <div class="flex items-center gap-3">
            <div class="flex shrink-0 items-center gap-1 rounded-full bg-sand px-1.5 py-1.5">
                <button type="button" data-step="minus" aria-label="Kurangi jumlah {{ $unit }}"
                    class="no-tap-highlight flex h-8 w-8 items-center justify-center rounded-full bg-white text-cocoa shadow-soft transition active:scale-90">
                    <span class="ms text-[16px] leading-none">remove</span>
                </button>

                <span data-qty class="w-5 text-center font-display text-[15px] font-bold text-cocoa">1</span>

                <button type="button" data-step="plus" aria-label="Tambah jumlah {{ $unit }}"
                    class="no-tap-highlight flex h-8 w-8 items-center justify-center rounded-full bg-brand text-white shadow-soft transition active:scale-90">
                    <span class="ms text-[16px] leading-none">add</span>
                </button>
            </div>

            <button type="button" data-add-to-cart class="btn-primary flex-1 py-3.5">
                Keranjang
                <span class="opacity-70">•</span>
                <span data-total>{{ Perkoci::rupiah($basePrice) }}</span>
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var basePrice = @json($basePrice);
            var qty = 1;

            var qtyEl = document.querySelector('[data-qty]');
            var totalEl = document.querySelector('[data-total]');

            function rupiah(value) {
                return 'Rp ' + value.toLocaleString('id-ID');
            }

            function render() {
                qtyEl.textContent = qty;
                totalEl.textContent = rupiah(basePrice * qty);
            }

            document.querySelector('[data-step="minus"]').addEventListener('click', function () {
                qty = Math.max(1, qty - 1);
                render();
            });

            document.querySelector('[data-step="plus"]').addEventListener('click', function () {
                qty = qty + 1;
                render();
            });

            // Catatan barista
            var note = document.querySelector('[data-note]');
            var noteCount = document.querySelector('[data-note-count]');

            note.addEventListener('input', function () {
                noteCount.textContent = note.value.length;
            });

            // Favorit
            document.querySelector('[data-favorite]').addEventListener('click', function () {
                var button = this;
                var icon = button.querySelector('.ms');
                var isActive = button.getAttribute('aria-pressed') === 'true';

                button.setAttribute('aria-pressed', isActive ? 'false' : 'true');
                icon.classList.toggle('ms-fill', !isActive);
                icon.classList.toggle('text-danger', !isActive);
            });

            document.querySelector('[data-add-to-cart]').addEventListener('click', function () {
                this.innerHTML = 'Ditambahkan <span class="opacity-70">•</span> <span>' + rupiah(basePrice * qty) + '</span>';
            });

            render();
        })();
    </script>
@endpush
