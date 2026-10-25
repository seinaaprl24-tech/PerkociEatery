@extends('layouts.customer')

@section('title', 'Katalog Menu — Perkoci Eatery')

@section('content')
    @php
        use App\Support\Perkoci;

        $table = config('perkoci.table');
        $menu = Perkoci::menu();
        $totals = Perkoci::cartTotals();
        $initialSelected = array_map(static function (array $item): array {
            return [
                'name' => $item['name'],
                'price' => $item['price'],
                'qty' => $item['qty'] ?? 1,
            ];
        }, Perkoci::cartItems());
    @endphp

    <div class="px-4 pt-4">

        {{-- Baris meja + status --}}
        <div class="panel flex items-center justify-between gap-3 px-4 py-3">
            <p class="flex min-w-0 items-center gap-2 text-xs font-semibold text-cocoa/70">
                <span class="ms shrink-0 text-[17px] leading-none text-brand">location_on</span>
                <span class="truncate">{{ $table['location'] }}</span>
            </p>

            <span class="pill shrink-0 bg-lime text-cocoa">
                <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>
                {{ $table['order_mode'] }}
            </span>
        </div>

        {{-- Pencarian --}}
        <div class="relative mt-3">
            <span class="ms pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[18px] leading-none text-cocoa/40">search</span>
            <input type="search" data-search autocomplete="off" aria-label="Cari menu"
                placeholder="Cari menu, kopi, atau makanan..."
                class="w-full rounded-full bg-sand py-3 pl-11 pr-10 text-sm text-cocoa outline-none transition placeholder:text-cocoa/40 focus:bg-white focus:ring-2 focus:ring-brand/15">
            <button type="button" data-search-clear
                class="no-tap-highlight absolute right-3 top-1/2 hidden h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full bg-cocoa/10 text-cocoa/60"
                aria-label="Bersihkan pencarian">
                <span class="ms text-[14px] leading-none">close</span>
            </button>
        </div>

        {{-- Kategori --}}
        <div class="scrollbar-none -mx-4 mt-5 flex gap-2 overflow-x-auto px-4 pb-1">
            @foreach (config('perkoci.categories', []) as $index => $category)
                <button type="button" data-filter-chip="{{ $category['id'] }}"
                    aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"
                    class="chip {{ $index === 0 ? 'chip-active' : '' }}">
                    {{ $category['name'] }}
                </button>
            @endforeach
        </div>

        {{-- Daftar menu --}}
        <div class="mt-4 space-y-3">
            @foreach ($menu as $item)
                <article data-menu-card data-category="{{ $item['category'] }}"
                    data-searchable="{{ $item['name'] }} {{ $item['description'] }}" class="card flex gap-3 p-3">

                    <a href="{{ url('/menu/'.$item['slug']) }}"
                        class="relative h-[110px] w-[110px] shrink-0 overflow-hidden rounded-2xl bg-sand">
                        <x-menu-thumb :item="$item" size="text-[34px]" />
                    </a>

                    <div class="flex min-w-0 flex-1 flex-col">
                        <h3 class="truncate text-[15px] font-bold leading-snug text-cocoa">
                            <a href="{{ url('/menu/'.$item['slug']) }}" class="transition hover:text-brand">{{ $item['name'] }}</a>
                        </h3>

                        <p class="mt-1 line-clamp-2 text-[11.5px] leading-relaxed text-cocoa/55">
                            {{ $item['description'] ?: Perkoci::categoryName($item['category']) }}
                        </p>

                        <div class="mt-auto flex items-center justify-between gap-2 pt-2">
                            <p class="font-display text-[15px] font-bold text-brand">
                                {{ Perkoci::rupiah($item['price']) }}
                                <span class="text-[10px] font-semibold text-cocoa/40">/ {{ $item['unit'] ?? 'porsi' }}</span>
                            </p>

                            <button type="button" data-add="{{ $item['name'] }}" data-price="{{ $item['price'] }}"
                                class="no-tap-highlight flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand text-xl font-bold leading-none text-white shadow-soft transition active:scale-90"
                                aria-label="Tambah {{ $item['name'] }}">
                                +
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Kosong --}}
        <div data-empty class="hidden py-14 text-center">
            <span class="ms text-[40px] leading-none text-cocoa/20">search_off</span>
            <p class="mt-3 text-sm font-semibold text-cocoa/60">Menu tidak ditemukan</p>
            <p class="mt-1 text-xs text-cocoa/40">Coba kata kunci atau kategori lain ya.</p>
        </div>
    </div>

    {{-- Floating bar pilihan --}}
    <div class="fixed bottom-[calc(84px+env(safe-area-inset-bottom))] left-1/2 z-40 w-[calc(100%-32px)] max-w-[398px] -translate-x-1/2">
        <div class="flex items-center justify-between gap-3 rounded-full bg-brand px-4 py-3 shadow-bar">
            <div class="min-w-0">
                <p class="text-[9px] font-bold uppercase tracking-[0.16em] text-lime" data-selected-count>
                    {{ $totals['count'] }} Item Dipilih
                </p>
                <p class="font-display text-[15px] font-bold leading-tight text-white" data-selected-total>
                    {{ Perkoci::rupiah($totals['total']) }}
                </p>
            </div>

            <a href="{{ url('/keranjang') }}"
                class="shrink-0 rounded-full bg-butter px-4 py-2 text-[11px] font-bold text-cocoa transition hover:bg-white">
                Lihat Keranjang →
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var chips = Array.prototype.slice.call(document.querySelectorAll('[data-filter-chip]'));
            var cards = Array.prototype.slice.call(document.querySelectorAll('[data-menu-card]'));
            var search = document.querySelector('[data-search]');
            var clearBtn = document.querySelector('[data-search-clear]');
            var emptyState = document.querySelector('[data-empty]');
            var countEl = document.querySelector('[data-selected-count]');
            var totalEl = document.querySelector('[data-selected-total]');

            var activeCategory = 'all';
            var selected = @json($initialSelected);

            function rupiah(value) {
                return 'Rp ' + value.toLocaleString('id-ID');
            }

            function renderSelected() {
                var total = selected.reduce(function (sum, item) {
                    return sum + (item.price * (item.qty || 1));
                }, 0);

                countEl.textContent = selected.length + ' Item Dipilih';
                totalEl.textContent = rupiah(total);
            }

            function applyFilter() {
                var query = ((search && search.value) || '').trim().toLowerCase();
                var visible = 0;

                cards.forEach(function (card) {
                    var category = card.getAttribute('data-category');
                    var haystack = (card.getAttribute('data-searchable') || '').toLowerCase();

                    var matchCategory = activeCategory === 'all' || category === activeCategory;
                    var matchQuery = !query || haystack.indexOf(query) !== -1;

                    var show = matchCategory && matchQuery;
                    card.classList.toggle('hidden', !show);

                    if (show) visible++;
                });

                emptyState.classList.toggle('hidden', visible !== 0);

                if (clearBtn) {
                    clearBtn.classList.toggle('hidden', !query);
                    clearBtn.classList.toggle('flex', !!query);
                }
            }

            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    activeCategory = chip.getAttribute('data-filter-chip');

                    chips.forEach(function (other) {
                        var isActive = other === chip;
                        other.classList.toggle('chip-active', isActive);
                        other.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    });

                    applyFilter();
                });
            });

            if (search) {
                search.addEventListener('input', applyFilter);
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    search.value = '';
                    applyFilter();
                    search.focus();
                });
            }

            document.querySelectorAll('[data-add]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var name = button.getAttribute('data-add');
                    var price = parseInt(button.getAttribute('data-price'), 10);
                    var found = selected.filter(function (item) { return item.name === name; })[0];

                    if (found) {
                        found.qty = (found.qty || 1) + 1;
                    } else {
                        selected.push({ name: name, price: price, qty: 1 });
                    }

                    renderSelected();

                    button.animate(
                        [{ transform: 'scale(1)' }, { transform: 'scale(0.82)' }, { transform: 'scale(1)' }],
                        { duration: 260, easing: 'ease-out' }
                    );
                });
            });

            renderSelected();
        })();
    </script>
@endpush
