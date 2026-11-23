@extends('layouts.admin')

@section('title', 'Daftar Pesanan')

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
@endphp

<header class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-stone-900">Pesanan Catering</h1>
        <p class="text-sm text-stone-500">Kelola semua pesanan catering (offline & online)</p>
    </div>
    <a href="{{ route('orders.create') }}" class="rounded-lg bg-orange-600 hover:bg-orange-700 transition text-white px-4 py-2 text-sm font-semibold shadow-xs flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        + Input Pesanan Baru
    </a>
</header>

{{-- Kartu Ringkasan --}}
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="rounded-xl bg-white border border-stone-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Total Pesanan Aktif</div>
        <div class="text-2xl font-bold text-stone-900 mt-1">{{ $totalOrders }}</div>
    </div>
    <div class="rounded-xl bg-orange-50 border border-orange-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-orange-800 uppercase tracking-wide flex items-center justify-between">
            <span>Pesanan Offline</span>
            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
        </div>
        <div class="text-2xl font-bold text-orange-950 mt-1">{{ $offlineCount }}</div>
        <div class="text-xs text-orange-700 mt-0.5">Datang langsung, telepon, pelanggan tetap</div>
    </div>
    <div class="rounded-xl bg-sky-50 border border-sky-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-sky-800 uppercase tracking-wide flex items-center justify-between">
            <span>Pesanan Online</span>
            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
        </div>
        <div class="text-2xl font-bold text-sky-950 mt-1">{{ $onlineCount }}</div>
        <div class="text-xs text-sky-700 mt-0.5">WhatsApp & saluran lainnya</div>
    </div>
    <div class="rounded-xl bg-white border border-stone-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Belum Selesai</div>
        <div class="text-2xl font-bold text-amber-600 mt-1">{{ $activePending }}</div>
        <div class="text-xs text-stone-400 mt-0.5">Status baru, dikonfirmasi, diproses</div>
    </div>
</section>

{{-- Filter & Pencarian --}}
<div class="rounded-xl bg-white border border-stone-200 p-4 shadow-xs mb-6">
    <form method="GET" action="{{ route('orders.index') }}" class="space-y-3">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Search Bar --}}
            <div class="flex-1 min-w-[260px]">
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Cari no pesanan, nama pelanggan, no telepon, atau lokasi..."
                           class="w-full rounded-lg border border-stone-300 bg-white pl-9 pr-3 py-2 text-sm text-stone-800 placeholder-stone-400 focus:ring-2 focus:ring-orange-500">
                    <svg class="w-4 h-4 text-stone-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            {{-- Filter Status --}}
            <select name="status" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700">
                <option value="">Semua Status</option>
                @foreach ($statuses as $k => $lbl)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $lbl }}</option>
                @endforeach
            </select>

            {{-- Tanggal Acara --}}
            <div class="flex items-center gap-1.5 text-sm text-stone-600">
                <span>Acara:</span>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-stone-300 bg-white px-2.5 py-1.5 text-xs text-stone-700">
                <span>s/d</span>
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-stone-300 bg-white px-2.5 py-1.5 text-xs text-stone-700">
            </div>

            <button type="submit" class="rounded-lg bg-stone-800 hover:bg-stone-900 text-white px-4 py-2 text-sm font-medium transition">
                Terapkan
            </button>
            @if (request()->hasAny(['q', 'channel', 'status', 'from', 'to']))
                <a href="{{ route('orders.index') }}" class="text-xs text-red-600 hover:underline self-center">Reset Filter</a>
            @endif
        </div>

        {{-- Filter Kanal Tab Pills --}}
        <div class="flex items-center gap-2 pt-2 border-t border-stone-100">
            <span class="text-xs text-stone-500 font-semibold mr-1">Kanal:</span>
            <a href="{{ route('orders.index', array_merge(request()->except('channel', 'page'), ['channel' => ''])) }}"
               class="px-3 py-1 rounded-full text-xs font-semibold {{ empty($channel) ? 'bg-stone-800 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
                Semua Kanal
            </a>
            <a href="{{ route('orders.index', array_merge(request()->except('channel', 'page'), ['channel' => 'offline'])) }}"
               class="px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 {{ $channel === 'offline' ? 'bg-orange-600 text-white' : 'bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-100' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $channel === 'offline' ? 'bg-white' : 'bg-orange-600' }}"></span>
                Khusus Pesanan Offline ({{ $offlineCount }})
            </a>
            <a href="{{ route('orders.index', array_merge(request()->except('channel', 'page'), ['channel' => 'online'])) }}"
               class="px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 {{ $channel === 'online' ? 'bg-sky-600 text-white' : 'bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $channel === 'online' ? 'bg-white' : 'bg-sky-600' }}"></span>
                Khusus Pesanan Online ({{ $onlineCount }})
            </a>
        </div>
    </form>
</div>

{{-- Tabel Pesanan --}}
<div class="rounded-xl bg-white border border-stone-200 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[900px]">
            <thead class="text-left text-xs font-semibold text-stone-500 bg-stone-50 border-b border-stone-200">
                <tr>
                    <th class="py-3 px-4">No. Pesanan</th>
                    <th class="py-3 px-3">Pelanggan</th>
                    <th class="py-3 px-3">Kanal / Sumber</th>
                    <th class="py-3 px-3">Tgl Acara</th>
                    <th class="py-3 px-3">Lokasi</th>
                    <th class="py-3 px-3 text-right">Total</th>
                    <th class="py-3 px-3 text-right">Sisa Piutang</th>
                    <th class="py-3 px-3">Pembayaran</th>
                    <th class="py-3 px-3">Status</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($orders as $o)
                    <tr class="hover:bg-stone-50/70 transition-colors">
                        <td class="py-3.5 px-4 font-bold">
                            <a href="{{ route('orders.show', $o) }}" class="text-orange-700 hover:underline">
                                {{ $o->order_number }}
                            </a>
                            <div class="text-[11px] text-stone-400 font-normal">Pesan: {{ $o->order_date->format('d/m/Y') }}</div>
                        </td>
                        <td class="py-3.5 px-3">
                            <div class="font-medium text-stone-900">{{ $o->customer->name }}</div>
                            <div class="text-xs text-stone-400">{{ $o->customer->phone ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-3">
                            @if ($o->isOffline())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-orange-100 text-orange-800">
                                    Offline
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-sky-100 text-sky-800">
                                    Online
                                </span>
                            @endif
                            <div class="text-[11px] text-stone-500 mt-0.5">{{ $o->source_label }}</div>
                        </td>
                        <td class="py-3.5 px-3 whitespace-nowrap">
                            <div class="font-medium text-stone-800">{{ $o->event_date->translatedFormat('d M Y') }}</div>
                            @if ($o->event_date->isToday()) <span class="text-xs font-semibold text-orange-600 block">(Hari ini)</span>
                            @elseif ($o->event_date->isTomorrow()) <span class="text-xs font-semibold text-orange-600 block">(Besok)</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 max-w-[180px] truncate text-stone-600 text-xs" title="{{ $o->event_location }}">
                            {{ $o->event_location }}
                        </td>
                        <td class="py-3.5 px-3 text-right font-medium text-stone-900">{{ $rp($o->total_amount) }}</td>
                        <td class="py-3.5 px-3 text-right font-semibold {{ $o->remaining_amount > 0 ? 'text-red-600' : 'text-stone-400' }}">
                            {{ $rp($o->remaining_amount) }}
                        </td>
                        <td class="py-3.5 px-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $payBadge[$o->payment_status] }}">
                                {{ $o->payment_status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge[$o->status] }}">
                                {{ \App\Models\Order::STATUSES[$o->status] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('orders.show', $o) }}" class="p-1 rounded text-stone-600 hover:text-stone-900 hover:bg-stone-100" title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('orders.invoice', $o) }}" target="_blank" class="p-1 rounded text-orange-600 hover:text-orange-900 hover:bg-orange-50" title="Cetak Nota / Invoice">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                <a href="{{ route('orders.edit', $o) }}" class="p-1 rounded text-blue-600 hover:text-blue-900 hover:bg-blue-50" title="Edit Pesanan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-stone-400">
                            Tidak ada data pesanan yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orders->hasPages())
        <div class="px-4 py-3 border-t border-stone-200">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
