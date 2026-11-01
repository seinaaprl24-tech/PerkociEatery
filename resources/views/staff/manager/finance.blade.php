{{-- Manager – Laporan Keuangan: pemasukan nyata dari /api/laporan/penjualan. Pengeluaran belum punya API. --}}
@extends('layouts.staff')

@section('title', $role['finance_page']['title'].' — Perkoci Eatery')

@section('staff_role', 'manager')
@section('active_nav', 'finance')

@section('topbar_left')
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-cocoa/40">{{ $role['eyebrow'] }}</p>
        <p class="truncate font-display text-[15px] font-bold leading-tight text-cocoa">{{ $role['finance_page']['title'] }}</p>
    </div>
@endsection

@section('content')
    @php $page = $role['finance_page']; @endphp

    <x-ui.page-header :title="$page['title']" :subtitle="$page['subtitle']">
        <x-ui.badge tone="neutral" icon="calendar_month"><span data-period-label>Bulan Ini</span></x-ui.badge>
    </x-ui.page-header>

    <p data-flash class="hidden" role="status"></p>

    {{-- Pilih periode --}}
    <x-ui.section class="mt-5" title="Periode" description="Pilih rentang tanggal. Maksimal 120 hari.">
        <div class="flex flex-wrap gap-2" data-presets>
            <button type="button" class="chip" data-preset="today" aria-pressed="false">Hari Ini</button>
            <button type="button" class="chip" data-preset="7" aria-pressed="false">7 Hari</button>
            <button type="button" class="chip" data-preset="30" aria-pressed="false">30 Hari</button>
            <button type="button" class="chip chip-active" data-preset="month" aria-pressed="true">Bulan Ini</button>
        </div>

        <div class="mt-3 flex flex-wrap items-end gap-3">
            <div class="w-full sm:w-44">
                <label class="field-label" for="period-start">Dari</label>
                <input id="period-start" type="date" class="field" data-start>
            </div>
            <div class="w-full sm:w-44">
                <label class="field-label" for="period-end">Sampai</label>
                <input id="period-end" type="date" class="field" data-end>
            </div>
            <button type="button" class="btn-primary btn-sm" data-apply>Terapkan</button>
        </div>
    </x-ui.section>

    <div class="mt-4 grid gap-3 sm:grid-cols-3">
        <x-ui.stat-card data-stat="income" label="Total Pemasukan" value="Rp 0" icon="south_west" tone="lime"
            hint="pembayaran lunas, tanpa kembalian" />
        <x-ui.stat-card data-stat="count" label="Jumlah Transaksi" value="0" icon="receipt_long" tone="brand"
            hint="pada periode ini" />
        <x-ui.stat-card data-stat="average" label="Rata-rata / Transaksi" value="Rp 0" icon="trending_up" tone="butter"
            hint="pemasukan / transaksi" />
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <x-ui.section title="Pemasukan per Metode Pembayaran" description="Nominal berdasarkan cara pembayaran." flush>
            <x-ui.table data-method-table class="rounded-none border-0" :head="['Metode', 'Transaksi', 'Nominal']">
                <tr data-placeholder>
                    <td colspan="3" class="px-4 py-10 text-center text-sm text-cocoa/45">Memuat…</td>
                </tr>
            </x-ui.table>
        </x-ui.section>

        <x-ui.section title="Pemasukan per Hari" description="Total pemasukan setiap tanggal." flush>
            <x-ui.table data-day-table class="rounded-none border-0" :head="['Tanggal', 'Transaksi', 'Nominal']">
                <tr data-placeholder>
                    <td colspan="3" class="px-4 py-10 text-center text-sm text-cocoa/45">Memuat…</td>
                </tr>
            </x-ui.table>
        </x-ui.section>
    </div>

    {{-- Pengeluaran --}}
    <x-ui.section class="mt-4" title="Pengeluaran"
        description="Pencatatan pengeluaran (bahan, gaji, operasional) belum tersedia.">
        <x-ui.empty-state icon="receipt" title="Belum ada pencatatan pengeluaran"
            description="Saldo bersih (pemasukan dikurangi pengeluaran) akan tampil setelah fitur pengeluaran dibuat." />
    </x-ui.section>
@endsection

@push('scripts')
    @include('partials.manager-data')

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var flashEl = document.querySelector('[data-flash]');
            var startInput = document.querySelector('[data-start]');
            var endInput = document.querySelector('[data-end]');
            var presetButtons = Array.prototype.slice.call(document.querySelectorAll('[data-preset]'));
            var periodLabel = document.querySelector('[data-period-label]');
            var methodBody = document.querySelector('[data-method-table] tbody');
            var dayBody = document.querySelector('[data-day-table] tbody');
            var PRESET_LABEL = { today: 'Hari Ini', '7': '7 Hari', '30': '30 Hari', month: 'Bulan Ini' };
            var busy = false;

            function markPreset(name) {
                presetButtons.forEach(function (button) {
                    var active = button.getAttribute('data-preset') === name;

                    button.classList.toggle('chip-active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            }

            function fillTable(body, rows, totals) {
                Manager.clear(body);

                rows.forEach(function (row) {
                    var tr = document.createElement('tr');

                    tr.appendChild(Manager.make('td', 'font-semibold text-cocoa', row.label));
                    tr.appendChild(Manager.make('td', '', row.count));
                    tr.appendChild(Manager.make('td', 'whitespace-nowrap font-semibold text-cocoa', Manager.rupiah(row.amount)));
                    body.appendChild(tr);
                });

                if (rows.length === 0) {
                    var empty = document.createElement('tr');
                    var cell = Manager.make('td', 'px-4 py-10 text-center text-sm text-cocoa/45', 'Belum ada pemasukan pada periode ini.');
                    cell.colSpan = 3;
                    empty.appendChild(cell);
                    body.appendChild(empty);

                    return;
                }

                var total = document.createElement('tr');
                total.className = 'bg-sand/60';
                total.appendChild(Manager.make('td', 'font-bold text-cocoa', 'Total'));
                total.appendChild(Manager.make('td', 'font-bold text-cocoa', totals.count));
                total.appendChild(Manager.make('td', 'whitespace-nowrap font-bold text-cocoa', Manager.rupiah(totals.amount)));
                body.appendChild(total);
            }

            function load(start, end, label) {
                var problem = Manager.validRange(start, end);

                if (problem) {
                    Manager.flash(flashEl, 'error', problem);

                    return;
                }

                if (busy) {
                    return;
                }

                busy = true;
                Manager.hideFlash(flashEl);
                periodLabel.textContent = label;

                Manager.loadSales(start, end)
                    .then(function (sales) {
                        var orders = sales.data || [];
                        var count = Number(sales.total_transaksi) || 0;
                        var income = Number(sales.total_pendapatan) || 0;

                        Manager.setStat('income', Manager.rupiah(income));
                        Manager.setStat('count', count);
                        Manager.setStat('average', Manager.rupiah(count > 0 ? Math.round(income / count) : 0));

                        var methods = Manager.byMethod(orders).map(function (row) {
                            return { label: row.name, count: row.count, amount: row.amount };
                        });

                        var days = Manager.groupByDay(orders, start, end)
                            .filter(function (day) { return day.orders > 0; })
                            .map(function (day) {
                                return { label: Manager.longDay(day.date), count: day.orders, amount: day.revenue };
                            });

                        fillTable(methodBody, methods, { count: count, amount: income });
                        fillTable(dayBody, days, { count: count, amount: income });
                    })
                    .catch(function (error) {
                        Manager.flash(flashEl, 'error', 'Gagal memuat laporan: ' + error.message);
                    })
                    .then(function () {
                        busy = false;
                    });
            }

            function usePreset(name) {
                var range = Manager.presetRange(name);

                startInput.value = range.start;
                endInput.value = range.end;
                markPreset(name);
                load(range.start, range.end, PRESET_LABEL[name]);
            }

            presetButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    usePreset(button.getAttribute('data-preset'));
                });
            });

            document.querySelector('[data-apply]').addEventListener('click', function () {
                markPreset('');
                load(startInput.value, endInput.value, Manager.longDay(startInput.value) + ' – ' + Manager.longDay(endInput.value));
            });

            usePreset('month');
        });
    </script>
@endpush
