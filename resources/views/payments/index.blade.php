@extends('layouts.admin')

@section('title', 'Riwayat Pembayaran')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $methodBadge = [
        'tunai' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'qris' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
        'transfer' => 'bg-blue-50 text-blue-800 border-blue-200',
        'debit' => 'bg-purple-50 text-purple-800 border-purple-200',
    ];
@endphp

<header class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-stone-900">Pembayaran & Kas Masuk</h1>
        <p class="text-sm text-stone-500">Rekapitulasi penerimaan kas tunai offline, transfer, QRIS, dan debit</p>
    </div>
</header>

{{-- Kartu Ringkasan Metode Pembayaran (Sangat vital untuk balancing kas offline & bank) --}}
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-emerald-800 uppercase tracking-wide flex items-center justify-between">
            <span>Kas Tunai (Offline)</span>
            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
        </div>
        <div class="text-xl font-bold text-emerald-950 mt-1">{{ $rp($methodStats['tunai']) }}</div>
        <div class="text-[11px] text-emerald-700 mt-0.5">Uang cash diterima di kasir/outlet</div>
    </div>

    <div class="rounded-xl bg-indigo-50 border border-indigo-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-indigo-800 uppercase tracking-wide flex items-center justify-between">
            <span>QRIS Kasir</span>
            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
        </div>
        <div class="text-xl font-bold text-indigo-950 mt-1">{{ $rp($methodStats['qris']) }}</div>
        <div class="text-[11px] text-indigo-700 mt-0.5">Scan QRIS toko / outlet</div>
    </div>

    <div class="rounded-xl bg-blue-50 border border-blue-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-blue-800 uppercase tracking-wide flex items-center justify-between">
            <span>Transfer Bank</span>
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
        </div>
        <div class="text-xl font-bold text-blue-950 mt-1">{{ $rp($methodStats['transfer']) }}</div>
        <div class="text-[11px] text-blue-700 mt-0.5">BCA / Mandiri / BNI</div>
    </div>

    <div class="rounded-xl bg-purple-50 border border-purple-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-purple-800 uppercase tracking-wide flex items-center justify-between">
            <span>Debit / EDC</span>
            <span class="w-2 h-2 rounded-full bg-purple-600"></span>
        </div>
        <div class="text-xl font-bold text-purple-950 mt-1">{{ $rp($methodStats['debit']) }}</div>
        <div class="text-[11px] text-purple-700 mt-0.5">Mesin gesek outlet</div>
    </div>

    <div class="rounded-xl bg-stone-900 text-white p-4 shadow-xs">
        <div class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Total Semua Masuk</div>
        <div class="text-xl font-bold text-orange-400 mt-1">{{ $rp($methodStats['total']) }}</div>
        <div class="text-[11px] text-stone-400 mt-0.5">Semua kanal pembayaran</div>
    </div>
</section>

{{-- Filter --}}
<div class="rounded-xl bg-white border border-stone-200 p-4 shadow-xs mb-6">
    <form method="GET" action="{{ route('payments.index') }}" class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[240px]">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor pesanan atau nama pelanggan..."
                   class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-800">
        </div>

        <select name="method" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700">
            <option value="">Semua Metode</option>
            @foreach ($methods as $mk => $mlbl)
                <option value="{{ $mk }}" @selected($selectedMethod === $mk)>{{ $mlbl }}</option>
            @endforeach
        </select>

        <select name="type" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700">
            <option value="">Semua Tipe</option>
            @foreach ($types as $tk => $tlbl)
                <option value="{{ $tk }}" @selected($selectedType === $tk)>{{ $tlbl }}</option>
            @endforeach
        </select>

        <div class="flex items-center gap-1.5 text-xs text-stone-600">
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-stone-300 px-2 py-1.5">
            <span>s/d</span>
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-stone-300 px-2 py-1.5">
        </div>

        <button type="submit" class="rounded-lg bg-stone-800 hover:bg-stone-900 text-white px-4 py-2 text-sm font-medium transition">
            Filter
        </button>
        @if (request()->hasAny(['q', 'method', 'type', 'from', 'to']))
            <a href="{{ route('payments.index') }}" class="text-xs text-red-600 hover:underline self-center">Reset</a>
        @endif
    </form>
</div>

{{-- Tabel Pembayaran --}}
<div class="rounded-xl bg-white border border-stone-200 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[800px]">
            <thead class="text-left text-xs font-semibold text-stone-500 bg-stone-50 border-b border-stone-200">
                <tr>
                    <th class="py-3 px-4">Tanggal Pembayaran</th>
                    <th class="py-3 px-3">No. Pesanan</th>
                    <th class="py-3 px-3">Pelanggan</th>
                    <th class="py-3 px-3">Tipe</th>
                    <th class="py-3 px-3">Metode</th>
                    <th class="py-3 px-3">Catatan / Bukti</th>
                    <th class="py-3 px-3 text-right">Nominal</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($payments as $p)
                    <tr class="hover:bg-stone-50/70">
                        <td class="py-3 px-4 font-semibold text-stone-800">
                            {{ $p->paid_at->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-3 px-3">
                            <a href="{{ route('orders.show', $p->order) }}" class="font-bold text-orange-700 hover:underline">
                                {{ $p->order->order_number }}
                            </a>
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-medium text-stone-900">{{ $p->order->customer->name }}</div>
                            <div class="text-[11px] text-stone-400">Acara: {{ $p->order->event_date->format('d/m/Y') }}</div>
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-stone-100 text-stone-700">
                                {{ $p->type_label }}
                            </span>
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium border {{ $methodBadge[$p->method] ?? 'bg-stone-100 text-stone-700' }}">
                                {{ $p->method_label }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-xs text-stone-600 max-w-[200px] truncate" title="{{ $p->notes }}">
                            {{ $p->notes ?? '-' }}
                        </td>
                        <td class="py-3 px-3 text-right font-bold text-emerald-700 text-base">
                            + {{ $rp($p->amount) }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('payments.receipt', $p) }}" target="_blank" class="p-1 rounded text-stone-600 hover:text-stone-900 hover:bg-stone-100" title="Cetak Kuitansi Pembayaran">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('payments.destroy', $p) }}" onsubmit="return confirm('Hapus catatan pembayaran ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded text-red-500 hover:text-red-700 hover:bg-red-50" title="Hapus Pembayaran">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-stone-400">Belum ada catatan pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($payments->hasPages())
        <div class="px-4 py-3 border-t border-stone-200">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
