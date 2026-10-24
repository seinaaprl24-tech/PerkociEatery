@extends('layouts.customer')

@section('title', 'Status Pesanan — Perkoci Eatery')

@section('content')
    @php
        use App\Support\Perkoci;

        $order = Perkoci::statusOrder();
        $location = $order['location'];
        $items = $order['items_data'];
        $totals = $order['totals'];
        $payment = $order['payment'];
        $qr = Perkoci::qrPattern($payment['code'] ?? 'PERKOCI');
        $qrSize = count($qr);
    @endphp

    <div class="px-4 pt-4">

        {{-- Judul halaman --}}
        <div class="flex items-center justify-between gap-3">
            <h1 class="font-display text-[26px] font-bold leading-none text-brand">Status Pesanan</h1>
            <span class="pill shrink-0 bg-lime px-3 py-1.5 text-[10px] text-cocoa">
                {{ $location }}
            </span>
        </div>

        {{-- ==================== NOMOR ANTREAN ==================== --}}
        <section class="card mt-4 overflow-hidden" aria-labelledby="queue-label">
            <div class="px-5 pt-6 text-center">
                <p id="queue-label" class="text-[10px] font-bold uppercase tracking-[0.22em] text-cocoa/40">
                    {{ $order['queue']['label'] }}
                </p>

                <p class="mt-1.5 font-display text-[64px] font-bold leading-none tracking-tight text-brand">
                    {{ $order['queue']['number'] }}
                </p>

                <span class="pill mt-3 bg-butter text-cocoa">
                    <span class="ms text-[13px] leading-none">hourglass_top</span>
                    {{ $order['queue']['eta'] }}
                </span>
            </div>

            {{-- Meja & pemesan --}}
            <div class="mx-4 mt-5 flex items-start gap-2.5 rounded-2xl bg-sand px-4 py-3">
                <span class="ms mt-px shrink-0 text-[16px] leading-none text-brand">location_on</span>

                <div class="min-w-0 text-[11.5px] leading-relaxed">
                    <p class="font-bold text-cocoa">{{ $location }}</p>
                    <p class="text-cocoa/55">
                        {{ $order['order']['customer'] }} <span class="text-cocoa/30">•</span> Order ID {{ $order['order']['id'] }}
                    </p>
                </div>
            </div>

            {{-- Status terkini --}}
            <div class="mt-4 bg-brand px-5 py-4 text-center">
                <p class="text-[9px] font-bold uppercase tracking-[0.22em] text-white/55">
                    {{ $order['current']['label'] }}
                </p>
                <p class="mt-1 font-display text-[16px] font-bold leading-snug text-white">
                    {{ $order['current']['title'] }}
                </p>
            </div>
        </section>

        {{-- ===================== PROGRES SAJIAN =================== --}}
        <section class="card mt-3 p-5">
            <div class="flex items-start justify-between gap-3">
                <h2 class="font-display text-[16px] font-bold leading-tight text-cocoa">Progres Sajian</h2>

                <span class="pill shrink-0 bg-sand text-[9px] text-cocoa/55">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand/50"></span>
                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-brand"></span>
                    </span>
                    {{ $order['auto_update'] }}
                </span>
            </div>

            <ol class="mt-5">
                @foreach ($order['steps'] as $step)
                    @php
                        $state = $step['state'] ?? 'pending';
                        $isDone = $state === 'done';
                        $isActive = $state === 'active';
                    @endphp

                    <li class="relative flex gap-3 pb-5 last:pb-0">
                        {{-- Garis + ikon bulat --}}
                        <div class="relative flex w-11 shrink-0 justify-center">
                            @if (! $loop->last)
                                <span
                                    class="absolute left-1/2 w-[2px] -translate-x-1/2 {{ $isActive ? 'top-11' : 'top-9' }} -bottom-5 {{ $isDone ? 'bg-brand' : 'bg-cocoa/10' }}"></span>
                            @endif

                            <span
                                class="relative z-10 flex items-center justify-center rounded-full transition {{ $isActive ? 'h-11 w-11 bg-brand text-white shadow-soft ring-4 ring-lime' : 'h-9 w-9' }} {{ $isDone ? 'bg-brand text-white' : '' }} {{ $state === 'pending' ? 'bg-sand text-cocoa/30' : '' }}">
                                <span class="ms leading-none {{ $isActive ? 'text-[20px]' : 'text-[16px]' }}">{{ $step['icon'] }}</span>
                            </span>
                        </div>

                        {{-- Keterangan --}}
                        <div class="min-w-0 flex-1 {{ $isActive ? 'pt-3' : 'pt-2' }}">
                            <div class="flex items-baseline justify-between gap-2">
                                <p
                                    class="text-[13px] font-bold leading-snug {{ $isActive ? 'text-brand' : ($isDone ? 'text-cocoa' : 'text-cocoa/40') }}">
                                    {{ $step['title'] }}
                                </p>

                                <p
                                    class="shrink-0 text-[10px] font-semibold {{ $isActive ? 'text-brand/70' : 'text-cocoa/35' }}">
                                    {{ $step['time'] }}
                                </p>
                            </div>

                            <p class="mt-0.5 text-[11px] leading-relaxed {{ $isActive ? 'text-cocoa/60' : 'text-cocoa/40' }}">
                                {{ $step['desc'] }}
                            </p>

                            @if ($isActive)
                                <span class="pill-sm mt-2 bg-lime text-cocoa">
                                    <span class="ms text-[11px] leading-none">autorenew</span>
                                    Sedang berjalan
                                </span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- =================== METODE PEMBAYARAN ================== --}}
        <section class="card mt-3 p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="font-display text-[16px] font-bold leading-tight text-cocoa">{{ $payment['title'] }}</h2>
                    <p class="mt-1 text-[12px] font-semibold text-cocoa/55">{{ $payment['method'] }}</p>
                </div>

                <span class="pill shrink-0 bg-butter text-cocoa">
                    <span class="ms text-[12px] leading-none">schedule</span>
                    {{ $payment['status'] }}
                </span>
            </div>

            <div class="mt-4 rounded-2xl bg-sand p-4">
                <div class="flex items-center gap-4">
                    {{-- Placeholder QR (pola statis, bukan QR pembayaran asli) --}}
                    <div class="grid h-[96px] w-[96px] shrink-0 place-items-center rounded-xl bg-white p-1.5 shadow-soft">
                        <svg viewBox="0 0 {{ $qrSize }} {{ $qrSize }}" class="h-full w-full text-cocoa"
                            shape-rendering="crispEdges" role="img" aria-label="Kode pembayaran contoh">
                            <g fill="currentColor">
                                @foreach ($qr as $y => $row)
                                    @foreach ($row as $x => $filled)
                                        @if ($filled)
                                            <rect x="{{ $x }}" y="{{ $y }}" width="1" height="1" />
                                        @endif
                                    @endforeach
                                @endforeach
                            </g>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[11px] leading-relaxed text-cocoa/55">{{ $payment['hint'] }}</p>

                        <p class="mt-2 font-display text-[15px] font-bold tracking-[0.1em] text-cocoa">
                            {{ $payment['code'] }}
                        </p>

                        <p class="mt-2.5 text-[9px] font-bold uppercase tracking-[0.14em] text-cocoa/40">
                            {{ $payment['total_label'] }}
                        </p>
                        <p class="font-display text-[19px] font-bold leading-tight text-brand">
                            {{ Perkoci::rupiah($totals['total']) }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== RINCIAN ITEM ====================== --}}
        <details class="card group mt-3 overflow-hidden">
            <summary
                class="no-tap-highlight flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 [&::-webkit-details-marker]:hidden">
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand">
                        <span class="ms text-[17px] leading-none">receipt_long</span>
                    </span>

                    <div class="min-w-0">
                        <p class="font-display text-[15px] font-bold leading-tight text-cocoa">
                            {{ $order['items']['title'] }} ({{ count($items) }} Menu)
                        </p>
                        <p class="mt-0.5 text-[10.5px] text-cocoa/45">
                            {{ $totals['count'] }} item <span class="text-cocoa/25">•</span>
                            {{ Perkoci::rupiah($totals['total']) }}
                        </p>
                    </div>
                </div>

                <span
                    class="ms shrink-0 text-[22px] leading-none text-cocoa/35 transition-transform duration-200 group-open:rotate-180">expand_more</span>
            </summary>

            <div class="border-t border-cocoa/10 px-5 py-4">
                <ul class="space-y-3">
                    @foreach ($items as $item)
                        <li class="flex items-center gap-3">
                            <div class="h-11 w-11 shrink-0 overflow-hidden rounded-xl bg-sand">
                                <x-menu-thumb :item="$item" size="text-[18px]" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[12.5px] font-bold leading-snug text-cocoa">{{ $item['name'] }}</p>
                                <p class="truncate text-[10.5px] text-cocoa/50">
                                    {{ Perkoci::rupiah($item['price']) }} / {{ $item['unit'] ?? 'porsi' }}
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="font-display text-[12.5px] font-bold text-cocoa">
                                    {{ Perkoci::rupiah($item['price'] * $item['qty']) }}
                                </p>
                                <p class="text-[10px] text-cocoa/40">{{ $item['qty'] }}x</p>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-4 space-y-2 rounded-xl bg-sand px-4 py-3 text-[12px]">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-cocoa/55">Subtotal</dt>
                        <dd class="shrink-0 font-semibold text-cocoa">{{ Perkoci::rupiah($totals['subtotal']) }}</dd>
                    </div>
                </dl>

                <div class="mt-2 flex items-center justify-between border-t border-dashed border-cocoa/15 px-1 pt-2.5">
                    <span class="text-[12.5px] font-bold text-cocoa">{{ $order['items']['total'] }}</span>
                    <span class="font-display text-[16px] font-bold text-brand">{{ Perkoci::rupiah($totals['total']) }}</span>
                </div>
            </div>
        </details>

        {{-- ==================== TOMBOL AKSI ======================= --}}
        <div class="mt-3 grid grid-cols-2 gap-3">
            @foreach ($order['actions'] as $action)
                <button type="button" data-action="{{ $action['key'] }}" data-label="{{ $action['label'] }}"
                    data-done="{{ $action['done'] }}" data-default-icon="{{ $action['icon'] }}"
                    data-default-hint="{{ $action['note'] }}"
                    class="card no-tap-highlight group flex flex-col items-center gap-2 px-3 py-4 text-center transition active:scale-[0.98] disabled:pointer-events-none">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-full bg-brand/10 text-brand transition group-disabled:bg-brand group-disabled:text-white">
                        <span class="ms text-[22px] leading-none" data-icon>{{ $action['icon'] }}</span>
                    </span>

                    <span class="text-[12px] font-bold leading-tight text-cocoa" data-label-text>{{ $action['label'] }}</span>
                    <span class="text-[9.5px] leading-tight text-cocoa/40" data-hint>{{ $action['note'] }}</span>
                </button>
            @endforeach
        </div>

        <div data-action-feedback data-table="{{ $location }}" role="status" aria-live="polite"
            class="mt-2.5 hidden rounded-xl bg-brand/10 px-4 py-2.5 text-center text-[11px] font-semibold text-brand"></div>

        {{-- ====================== PENUTUP ========================= --}}
        <div class="mt-3 rounded-2xl bg-sand px-5 py-5 text-center">
            <span class="ms text-[24px] leading-none text-brand/70">self_improvement</span>
            <p class="mt-1.5 text-[11.5px] leading-relaxed text-cocoa/55">{{ $order['farewell'] }}</p>
        </div>

        <p class="mt-3 text-center text-[9px] leading-relaxed text-cocoa/30">{{ $order['demo_note'] }}</p>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var buttons = document.querySelectorAll('[data-action]');
            var feedback = document.querySelector('[data-action-feedback]');

            if (! buttons.length || ! feedback) {
                return;
            }

            var tableName = feedback.getAttribute('data-table');

            Array.prototype.forEach.call(buttons, function (button) {
                var labelEl = button.querySelector('[data-label-text]');
                var hintEl = button.querySelector('[data-hint]');
                var iconEl = button.querySelector('[data-icon]');

                button.addEventListener('click', function () {
                    if (button.disabled) {
                        return;
                    }

                    button.disabled = true;
                    labelEl.textContent = button.getAttribute('data-done');
                    hintEl.textContent = 'Permintaan terkirim';
                    iconEl.textContent = 'check_circle';
                    iconEl.classList.add('ms-fill');

                    feedback.textContent = button.getAttribute('data-done') + ' \u2014 ' + tableName + ' (demo frontend)';
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
        })();
    </script>
@endpush
