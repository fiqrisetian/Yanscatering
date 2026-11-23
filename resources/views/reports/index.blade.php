@extends('layouts.admin')

@section('title', 'Laporan Keuangan & Penjualan')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp

<header class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-stone-900">Laporan Penjualan & Keuangan</h1>
        <p class="text-sm text-stone-500">Analisis kinerja catering, perbandingan offline vs online, dan rekapitulasi piutang</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('reports.export', ['from' => $from, 'to' => $to]) }}" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 transition text-white px-4 py-2 text-sm font-semibold shadow-xs flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Laporan (CSV / Excel)
        </a>
    </div>
</header>

{{-- Filter Periode --}}
<div class="rounded-xl bg-white border border-stone-200 p-4 shadow-xs mb-6">
    <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-stone-600">Periode Laporan:</span>
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border border-stone-300 px-3 py-1.5 text-xs text-stone-800">
            <span class="text-xs text-stone-400">s/d</span>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border border-stone-300 px-3 py-1.5 text-xs text-stone-800">
            <button type="submit" class="rounded-lg bg-stone-800 hover:bg-stone-900 text-white px-3.5 py-1.5 text-xs font-semibold">
                Tampilkan
            </button>
        </div>

        <div class="flex items-center gap-1.5 text-xs">
            <a href="{{ route('reports.index', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->endOfMonth()->toDateString()]) }}" class="px-2.5 py-1 rounded bg-stone-100 hover:bg-stone-200 text-stone-700">Bulan Ini</a>
            <a href="{{ route('reports.index', ['from' => now()->subMonth()->startOfMonth()->toDateString(), 'to' => now()->subMonth()->endOfMonth()->toDateString()]) }}" class="px-2.5 py-1 rounded bg-stone-100 hover:bg-stone-200 text-stone-700">Bulan Lalu</a>
            <a href="{{ route('reports.index', ['from' => now()->subMonths(2)->startOfMonth()->toDateString(), 'to' => now()->endOfMonth()->toDateString()]) }}" class="px-2.5 py-1 rounded bg-stone-100 hover:bg-stone-200 text-stone-700">3 Bulan</a>
            <a href="{{ route('reports.index', ['from' => now()->startOfYear()->toDateString(), 'to' => now()->endOfYear()->toDateString()]) }}" class="px-2.5 py-1 rounded bg-stone-100 hover:bg-stone-200 text-stone-700">Tahun Ini</a>
        </div>
    </form>
</div>

{{-- Kartu Ringkasan Periode Ini --}}
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
        <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Total Pesanan Periode Ini</div>
        <div class="text-2xl font-bold text-stone-900 mt-1">{{ $totalOrderCount }} pesanan</div>
        <div class="text-xs text-stone-400 mt-0.5">Dihitung dari tanggal pesan</div>
    </div>

    <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
        <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Total Omzet Penjualan</div>
        <div class="text-2xl font-bold text-stone-900 mt-1">{{ $rp($totalOrderValue) }}</div>
        <div class="text-xs text-stone-400 mt-0.5">Total tagihan katering</div>
    </div>

    <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
        <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Uang Masuk Diterima</div>
        <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $rp($totalReceived) }}</div>
        <div class="text-xs text-emerald-700 mt-0.5">Kas tunai + transfer + QRIS</div>
    </div>

    <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
        <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Total Piutang Usaha</div>
        <div class="text-2xl font-bold text-red-600 mt-1">{{ $rp($totalReceivables) }}</div>
        <div class="text-xs text-stone-400 mt-0.5">{{ $receivables->count() }} pesanan belum lunas</div>
    </div>
</section>

{{-- Analisis Khusus: Pesanan Offline vs Online --}}
<section class="mb-6 rounded-xl bg-white border border-stone-200 p-5 shadow-xs space-y-4">
    <div class="flex items-center justify-between border-b border-stone-100 pb-3">
        <div>
            <h2 class="font-bold text-stone-900 text-lg">Analisis Komparasi: Pesanan Offline vs Online</h2>
            <p class="text-xs text-stone-500">Evaluasi kontribusi pesanan offline (toko/outlet/telepon) dibanding saluran online</p>
        </div>
        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-orange-100 text-orange-800">
            Mayoritas Offline: {{ $offlinePercent }}%
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Card Offline --}}
        <div class="rounded-xl bg-orange-50/60 border border-orange-200 p-5 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wide text-orange-800 flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-orange-600"></span>
                    Kanal Pesanan Offline
                </span>
                <span class="text-sm font-bold text-orange-900">{{ $offlinePercent }}% dari total</span>
            </div>
            <div class="space-y-2 text-sm pt-1">
                <div class="flex justify-between">
                    <span class="text-stone-600">Jumlah Pesanan:</span>
                    <span class="font-bold text-stone-900">{{ $offlineCount }} pesanan</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-stone-600">Total Nilai Tagihan:</span>
                    <span class="font-bold text-orange-800 text-base">{{ $rp($offlineValue) }}</span>
                </div>
                <div class="flex justify-between pt-1 border-t border-orange-200/60 text-xs">
                    <span class="text-stone-500">Rata-rata per Pesanan (AOV):</span>
                    <span class="font-semibold text-stone-700">{{ $rp($offlineAvg) }}</span>
                </div>
            </div>
            <div class="text-[11px] text-stone-500 bg-white/70 p-2.5 rounded-lg border border-orange-100">
                Mencakup pelanggan datang langsung ke kantor/outlet, pemesanan lewat telepon, dan pelanggan tetap/mitra.
            </div>
        </div>

        {{-- Card Online --}}
        <div class="rounded-xl bg-sky-50/60 border border-sky-200 p-5 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wide text-sky-800 flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-600"></span>
                    Kanal Pesanan Online
                </span>
                <span class="text-sm font-bold text-sky-900">{{ $onlinePercent }}% dari total</span>
            </div>
            <div class="space-y-2 text-sm pt-1">
                <div class="flex justify-between">
                    <span class="text-stone-600">Jumlah Pesanan:</span>
                    <span class="font-bold text-stone-900">{{ $onlineCount }} pesanan</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-stone-600">Total Nilai Tagihan:</span>
                    <span class="font-bold text-sky-800 text-base">{{ $rp($onlineValue) }}</span>
                </div>
                <div class="flex justify-between pt-1 border-t border-sky-200/60 text-xs">
                    <span class="text-stone-500">Rata-rata per Pesanan (AOV):</span>
                    <span class="font-semibold text-stone-700">{{ $rp($onlineAvg) }}</span>
                </div>
            </div>
            <div class="text-[11px] text-stone-500 bg-white/70 p-2.5 rounded-lg border border-sky-100">
                Mencakup pesanan masuk via WhatsApp resmi dan formulir website/lainnya.
            </div>
        </div>
    </div>
</section>

{{-- Breakdown Sumber & Metode Pembayaran --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Sumber Pesanan --}}
    <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
        <h2 class="font-bold text-stone-900 border-b border-stone-100 pb-3 mb-3">Rincian per Sumber Pesanan</h2>
        <table class="w-full text-sm">
            <thead class="text-left text-xs font-semibold text-stone-500 border-b border-stone-100">
                <tr>
                    <th class="pb-2">Sumber</th>
                    <th class="pb-2 text-center">Pesanan</th>
                    <th class="pb-2 text-right">Total Nilai</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach ($sourceBreakdown as $sb)
                    <tr>
                        <td class="py-2.5 font-medium text-stone-800">{{ $sb['label'] }}</td>
                        <td class="py-2.5 text-center font-semibold text-stone-900">{{ $sb['count'] }}</td>
                        <td class="py-2.5 text-right font-bold text-stone-900">{{ $rp($sb['total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Penerimaan Kas per Metode --}}
    <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
        <h2 class="font-bold text-stone-900 border-b border-stone-100 pb-3 mb-3">Metode Penerimaan Pembayaran</h2>
        <table class="w-full text-sm">
            <thead class="text-left text-xs font-semibold text-stone-500 border-b border-stone-100">
                <tr>
                    <th class="pb-2">Metode</th>
                    <th class="pb-2 text-center">Transaksi</th>
                    <th class="pb-2 text-right">Total Uang Diterima</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach ($methodBreakdown as $mk => $mb)
                    <tr>
                        <td class="py-2.5 font-medium text-stone-800 capitalize">{{ \App\Models\Payment::METHODS[$mk] ?? $mk }}</td>
                        <td class="py-2.5 text-center font-semibold text-stone-900">{{ $mb['count'] }}x</td>
                        <td class="py-2.5 text-right font-bold text-emerald-700">{{ $rp($mb['total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Daftar Piutang Belum Lunas --}}
<div class="rounded-xl bg-white border border-stone-200 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-stone-200 flex justify-between items-center">
        <div>
            <h2 class="font-bold text-stone-900">Daftar Tagihan Belum Lunas (Piutang)</h2>
            <p class="text-xs text-stone-500">Semua pesanan aktif yang masih menyisakan pembayaran</p>
        </div>
        <div class="text-right">
            <div class="text-xs text-stone-500">Total Piutang:</div>
            <div class="text-lg font-bold text-red-600">{{ $rp($totalReceivables) }}</div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[700px]">
            <thead class="text-left text-xs font-semibold text-stone-500 bg-stone-50 border-b border-stone-200">
                <tr>
                    <th class="py-3 px-4">No. Pesanan</th>
                    <th class="py-3 px-3">Pelanggan</th>
                    <th class="py-3 px-3">Kanal</th>
                    <th class="py-3 px-3">Tgl Acara</th>
                    <th class="py-3 px-3 text-right">Total Nilai</th>
                    <th class="py-3 px-3 text-right">Sudah Dibayar</th>
                    <th class="py-3 px-3 text-right">Sisa Tagihan</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($receivables as $r)
                    <tr class="hover:bg-stone-50/70">
                        <td class="py-3 px-4 font-bold">
                            <a href="{{ route('orders.show', $r) }}" class="text-orange-700 hover:underline">
                                {{ $r->order_number }}
                            </a>
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-medium text-stone-900">{{ $r->customer->name }}</div>
                            <div class="text-xs text-stone-400">{{ $r->customer->phone ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-3">
                            @if ($r->isOffline())
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-orange-100 text-orange-800">Offline</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-sky-100 text-sky-800">Online</span>
                            @endif
                        </td>
                        <td class="py-3 px-3">{{ $r->event_date->translatedFormat('d M Y') }}</td>
                        <td class="py-3 px-3 text-right font-medium text-stone-900">{{ $rp($r->total_amount) }}</td>
                        <td class="py-3 px-3 text-right text-emerald-600 font-medium">{{ $rp($r->paid_amount) }}</td>
                        <td class="py-3 px-3 text-right font-bold text-red-600">{{ $rp($r->remaining_amount) }}</td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <a href="{{ route('orders.show', $r) }}" class="text-xs font-semibold text-orange-600 hover:underline">
                                Catat Bayar / Detail &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-emerald-600 font-semibold">Semua tagihan pesanan telah lunas!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
