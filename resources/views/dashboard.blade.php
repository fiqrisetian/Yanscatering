@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $payBadge = [
        'Lunas' => 'bg-emerald-100 text-emerald-800',
        'DP' => 'bg-amber-100 text-amber-800',
        'Belum Bayar' => 'bg-red-100 text-red-800',
    ];
    $statusBadge = [
        'baru' => 'bg-stone-200 text-stone-700',
        'dikonfirmasi' => 'bg-sky-100 text-sky-800',
        'diproses' => 'bg-violet-100 text-violet-800',
        'selesai' => 'bg-emerald-100 text-emerald-800',
        'dibatalkan' => 'bg-red-100 text-red-800',
    ];
    $methodBadge = [
        'tunai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'qris' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'transfer' => 'bg-blue-50 text-blue-700 border-blue-200',
        'debit' => 'bg-purple-50 text-purple-700 border-purple-200',
    ];
    $maxQty = max(1, (int) $topItems->max('qty'));
@endphp

<header class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-stone-900">Dashboard</h1>
        <p class="text-sm text-stone-500">{{ now()->translatedFormat('l, d F Y') }} · Selamat datang di {{ config('app.name', 'Yans Catering') }}</p>
    </div>
    <div class="flex gap-2">
        <form method="GET" action="{{ route('dashboard') }}">
            <select name="periode" onchange="this.form.submit()"
                    class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 shadow-xs focus:ring-2 focus:ring-orange-500">
                @foreach ($periods as $key => $label)
                    <option value="{{ $key }}" @selected($period === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('orders.create') }}" class="rounded-lg bg-orange-600 hover:bg-orange-700 transition text-white px-4 py-2 text-sm font-semibold shadow-xs flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            + Pesanan Baru
        </a>
    </div>
</header>

{{-- Kartu Ringkasan Utama --}}
<section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
        <div class="text-sm font-medium text-stone-500">Total Pesanan</div>
        <div class="text-2xl font-bold mt-1 text-stone-900">{{ $stats['order_count'] }}</div>
        @if (!is_null($stats['order_delta']))
            <div class="text-xs mt-1 font-medium {{ $stats['order_delta'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                {{ $stats['order_delta'] >= 0 ? '+' : '' }}{{ $stats['order_delta'] }}% dari periode sebelumnya
            </div>
        @else
            <div class="text-xs mt-1 text-stone-400">Belum ada pembanding</div>
        @endif
    </div>

    <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
        <div class="text-sm font-medium text-stone-500">Total Nilai Pesanan</div>
        <div class="text-2xl font-bold mt-1 text-stone-900">{{ $rp($stats['order_value']) }}</div>
        <div class="text-xs mt-1 text-stone-400">Total nilai tagihan periode ini</div>
    </div>

    <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
        <div class="text-sm font-medium text-stone-500">Uang Diterima</div>
        <div class="text-2xl font-bold mt-1 text-emerald-600">{{ $rp($stats['received']) }}</div>
        <div class="text-xs mt-1 font-medium text-emerald-700">{{ $stats['collection_rate'] }}% dari nilai tagihan</div>
    </div>

    <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
        <div class="text-sm font-medium text-stone-500">Belum Lunas (Piutang)</div>
        <div class="text-2xl font-bold mt-1 text-red-600">{{ $rp($stats['outstanding']) }}</div>
        <div class="text-xs mt-1 text-stone-400">{{ $stats['outstanding_count'] }} pesanan, semua periode</div>
    </div>
</section>

{{-- Sorotan Kanal: Pesanan Offline vs Online (Fokus Utama Yans Catering) --}}
<section class="mb-6 rounded-xl bg-gradient-to-r from-orange-50 via-amber-50 to-stone-50 border border-orange-200 p-5 shadow-xs">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-orange-600 animate-pulse"></span>
            <h2 class="font-bold text-stone-900">Performa Kanal: Mayoritas Pesanan Offline</h2>
            <span class="text-xs px-2 py-0.5 rounded-full bg-orange-200/80 text-orange-900 font-semibold">{{ $stats['offline_percent'] }}% Offline</span>
        </div>
        <a href="{{ route('orders.index', ['channel' => 'offline']) }}" class="text-xs font-semibold text-orange-700 hover:text-orange-900 hover:underline">
            Lihat Pesanan Offline &rarr;
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Sisi Offline --}}
        <div class="bg-white/80 rounded-lg p-3.5 border border-orange-100 flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Pesanan Offline (Datang Langsung / Telepon / Toko)</div>
                <div class="text-xl font-bold text-stone-900 mt-0.5">
                    {{ $stats['offline_count'] }} <span class="text-xs font-normal text-stone-500">pesanan ({{ $stats['offline_percent'] }}%)</span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs text-stone-500">Total Nilai</div>
                <div class="text-sm font-bold text-orange-700">{{ $rp($stats['offline_value']) }}</div>
            </div>
        </div>

        {{-- Sisi Online --}}
        <div class="bg-white/80 rounded-lg p-3.5 border border-stone-200 flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Pesanan Online (WhatsApp & Lainnya)</div>
                <div class="text-xl font-bold text-stone-900 mt-0.5">
                    {{ $stats['online_count'] }} <span class="text-xs font-normal text-stone-500">pesanan ({{ $stats['online_percent'] }}%)</span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs text-stone-500">Total Nilai</div>
                <div class="text-sm font-bold text-sky-700">{{ $rp($stats['online_value']) }}</div>
            </div>
        </div>
    </div>

    {{-- Progress bar perbandingan --}}
    <div class="mt-3">
        <div class="flex justify-between text-xs text-stone-500 mb-1">
            <span>Offline ({{ $stats['offline_percent'] }}%)</span>
            <span>Online ({{ $stats['online_percent'] }}%)</span>
        </div>
        <div class="w-full h-2.5 rounded-full bg-sky-200 overflow-hidden flex">
            <div class="bg-orange-500 h-full transition-all duration-500" style="width: {{ $stats['offline_percent'] }}%"></div>
            <div class="bg-sky-500 h-full transition-all duration-500" style="width: {{ $stats['online_percent'] }}%"></div>
        </div>
    </div>
</section>

{{-- Grafik & Sumber Pesanan --}}
<section class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-6">
    <div class="xl:col-span-2 rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
        <h2 class="font-bold text-stone-900">Nilai Pesanan vs Uang Diterima</h2>
        <p class="text-xs text-stone-500 mb-3">6 bulan terakhir, dalam juta rupiah</p>
        <div class="h-64"><canvas id="cRev"></canvas></div>
    </div>
    <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
        <div class="flex justify-between items-center mb-1">
            <h2 class="font-bold text-stone-900">Sumber Pesanan</h2>
            <span class="text-xs text-stone-500">{{ $periods[$period] }}</span>
        </div>
        <p class="text-xs text-stone-400 mb-3">Rincian kanal masuk pesanan catering</p>
        @if (array_sum($sources['data']) > 0)
            <div class="h-64"><canvas id="cSrc"></canvas></div>
        @else
            <div class="h-64 grid place-items-center text-sm text-stone-400">Belum ada pesanan</div>
        @endif
    </div>
</section>

{{-- Pesanan Terdekat & Sidebar Info --}}
<section class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    <div class="xl:col-span-2 space-y-4">
        {{-- Pesanan terdekat --}}
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
            <div class="flex justify-between items-center mb-3">
                <div>
                    <h2 class="font-bold text-stone-900">Pesanan Terdekat</h2>
                    <p class="text-xs text-stone-500">Jadwal acara catering dalam waktu dekat</p>
                </div>
                <a href="{{ route('orders.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700 hover:underline">Lihat semua pesanan &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[700px]">
                    <thead class="text-left text-xs font-semibold text-stone-500 border-b border-stone-200">
                        <tr>
                            <th class="py-2.5 pr-3">No. Pesanan</th>
                            <th class="pr-3">Pelanggan</th>
                            <th class="pr-3">Kanal</th>
                            <th class="pr-3">Tanggal Acara</th>
                            <th class="pr-3 text-right">Total</th>
                            <th class="pr-3 text-right">Sisa Tagihan</th>
                            <th class="pr-3">Bayar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($upcoming as $o)
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                <td class="py-3 pr-3 font-semibold">
                                    <a href="{{ route('orders.show', $o) }}" class="text-orange-700 hover:underline">
                                        {{ $o->order_number }}
                                    </a>
                                </td>
                                <td class="pr-3">
                                    <div class="font-medium text-stone-900">{{ $o->customer->name }}</div>
                                    <div class="text-xs text-stone-400">{{ $o->event_location }}</div>
                                </td>
                                <td class="pr-3">
                                    @if ($o->isOffline())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-orange-100 text-orange-800">
                                            Offline
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-sky-100 text-sky-800">
                                            Online
                                        </span>
                                    @endif
                                </td>
                                <td class="pr-3 whitespace-nowrap">
                                    <span class="font-medium text-stone-800">{{ $o->event_date->translatedFormat('d M Y') }}</span>
                                    @if ($o->event_date->isToday()) <span class="text-xs font-semibold text-orange-600 block">(hari ini)</span>
                                    @elseif ($o->event_date->isTomorrow()) <span class="text-xs font-semibold text-orange-600 block">(besok)</span> @endif
                                </td>
                                <td class="pr-3 text-right font-medium text-stone-900">{{ $rp($o->total_amount) }}</td>
                                <td class="pr-3 text-right font-semibold {{ $o->remaining_amount > 0 ? 'text-red-600' : 'text-stone-400' }}">
                                    {{ $rp($o->remaining_amount) }}
                                </td>
                                <td class="pr-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $payBadge[$o->payment_status] }}">
                                        {{ $o->payment_status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge[$o->status] }}">
                                        {{ \App\Models\Order::STATUSES[$o->status] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-8 text-center text-stone-400">Tidak ada pesanan mendatang</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pembayaran terbaru --}}
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
            <div class="flex justify-between items-center mb-3">
                <div>
                    <h2 class="font-bold text-stone-900">Pembayaran Terbaru</h2>
                    <p class="text-xs text-stone-500">Transaksi masuk kas / rekening terakhir</p>
                </div>
                <a href="{{ route('payments.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-700 hover:underline">Semua Pembayaran &rarr;</a>
            </div>
            <ul class="divide-y divide-stone-100 text-sm">
                @forelse ($recentPayments as $p)
                    <li class="py-2.5 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-medium text-stone-900 truncate">{{ $p->order->customer->name }}</div>
                            <div class="text-xs text-stone-500 flex items-center gap-1.5 mt-0.5">
                                <span>{{ $p->order->order_number }}</span>
                                <span>·</span>
                                <span>{{ \App\Models\Payment::TYPES[$p->type] }}</span>
                                <span>·</span>
                                <span class="px-1.5 py-0.2 rounded border text-[10px] font-semibold uppercase {{ $methodBadge[$p->method] ?? 'bg-stone-100 text-stone-700' }}">
                                    {{ $p->method_label }}
                                </span>
                                <span>·</span>
                                <span>{{ $p->paid_at->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>
                        <div class="font-bold text-emerald-700 whitespace-nowrap">+ {{ $rp($p->amount) }}</div>
                    </li>
                @empty
                    <li class="py-6 text-center text-stone-400">Belum ada pembayaran</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Sidebar kolom kanan --}}
    <div class="space-y-4">
        {{-- Menu / paket terlaris --}}
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
            <h2 class="font-bold text-stone-900 mb-1">Menu / Paket Terlaris</h2>
            <p class="text-xs text-stone-500 mb-3">{{ $periods[$period] }}</p>
            <ul class="space-y-3 text-sm">
                @forelse ($topItems as $t)
                    <li>
                        <div class="flex justify-between mb-1">
                            <span class="truncate pr-2 font-medium text-stone-800">{{ $t->item_name }}</span>
                            <span class="text-stone-500 whitespace-nowrap font-semibold">{{ number_format($t->qty, 0, ',', '.') }}x</span>
                        </div>
                        <div class="h-2 rounded bg-stone-100 overflow-hidden">
                            <div class="h-2 rounded bg-orange-500 transition-all" style="width: {{ $t->qty / $maxQty * 100 }}%"></div>
                        </div>
                    </li>
                @empty
                    <li class="text-center text-stone-400 py-4">Belum ada data</li>
                @endforelse
            </ul>
        </div>

        {{-- Perlu tindak lanjut --}}
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
            <h2 class="font-bold text-stone-900 mb-1">Perlu Tindak Lanjut</h2>
            <p class="text-xs text-stone-500 mb-3">Agenda dan peringatan operasional</p>
            <ul class="space-y-2.5 text-sm">
                @foreach ($todo as $t)
                    <li class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-stone-50 transition-colors">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $t['color'] }}"></span>
                        <span class="flex-1 text-stone-700">{{ $t['text'] }}</span>
                        <span class="font-bold {{ $t['count'] ? 'text-stone-900' : 'text-stone-300' }}">{{ $t['count'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const chart = @json($chart);
    const sources = @json($sources);
    Chart.defaults.color = '#78716c';

    new Chart(document.getElementById('cRev'), {
        type: 'bar',
        data: {
            labels: chart.labels,
            datasets: [
                { label: 'Nilai pesanan', data: chart.value, backgroundColor: '#fdba74', borderRadius: 4 },
                { label: 'Uang diterima', data: chart.received, backgroundColor: '#ea580c', borderRadius: 4 },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { grid: { color: 'rgba(120,113,108,.15)' } }, x: { grid: { display: false } } },
        },
    });

    const src = document.getElementById('cSrc');
    if (src) {
        new Chart(src, {
            type: 'doughnut',
            data: {
                labels: sources.labels,
                datasets: [{
                    data: sources.data,
                    backgroundColor: ['#ea580c', '#f97316', '#fb923c', '#0ea5e9', '#a8a29e'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } }
                }
            },
        });
    }
</script>
@endpush
