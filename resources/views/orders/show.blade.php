@extends('layouts.admin')

@section('title', 'Detail Pesanan ' . $order->order_number)

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
@endphp

<header class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('orders.index') }}" class="p-2 rounded-lg bg-white border border-stone-200 text-stone-600 hover:text-stone-900 shadow-xs" title="Kembali">
            &larr;
        </a>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-stone-900">{{ $order->order_number }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusBadge[$order->status] }}">
                    {{ \App\Models\Order::STATUSES[$order->status] }}
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $payBadge[$order->payment_status] }}">
                    {{ $order->payment_status }}
                </span>
            </div>
            <p class="text-xs text-stone-500 mt-0.5">Dibuat pada {{ $order->created_at->translatedFormat('l, d F Y H:i') }}</p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="rounded-lg bg-white border border-stone-300 hover:bg-stone-50 text-stone-700 px-3.5 py-2 text-sm font-semibold shadow-xs flex items-center gap-1.5">
            <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Nota / Invoice
        </a>
        <a href="{{ route('orders.edit', $order) }}" class="rounded-lg bg-white border border-stone-300 hover:bg-stone-50 text-stone-700 px-3.5 py-2 text-sm font-semibold shadow-xs">
            Edit Data
        </a>
        <form method="POST" action="{{ route('orders.destroy', $order) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesanan ini?')" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-lg bg-red-50 hover:bg-red-100 text-red-700 px-3.5 py-2 text-sm font-semibold border border-red-200">
                Hapus
            </button>
        </form>
    </div>
</header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Kolom Kiri / Utama: Info Acara & Item Pesanan --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Kartu Info Acara --}}
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3 mb-4">
                <h2 class="font-bold text-stone-900">Informasi Acara & Pengiriman</h2>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-stone-400">Kanal Pesanan:</span>
                    @if ($order->isOffline())
                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-orange-100 text-orange-800">
                            Offline ({{ $order->source_label }})
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-sky-100 text-sky-800">
                            Online ({{ $order->source_label }})
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-xs text-stone-400">Tanggal Acara / Pengiriman:</div>
                    <div class="font-semibold text-stone-900 mt-0.5 text-base">
                        {{ $order->event_date->translatedFormat('l, d F Y') }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-stone-400">Tanggal Pesan Diterima:</div>
                    <div class="font-semibold text-stone-900 mt-0.5">
                        {{ $order->order_date->translatedFormat('d F Y') }}
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <div class="text-xs text-stone-400">Lokasi Acara / Pengantaran:</div>
                    <div class="font-semibold text-stone-900 mt-0.5">{{ $order->event_location }}</div>
                </div>
                @if ($order->notes)
                    <div class="sm:col-span-2 bg-stone-50 p-3 rounded-lg border border-stone-100">
                        <div class="text-xs font-semibold text-stone-500">Catatan Pesanan:</div>
                        <div class="text-stone-700 mt-0.5 text-xs">{{ $order->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Rincian Item Pesanan --}}
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
            <h2 class="font-bold text-stone-900 border-b border-stone-100 pb-3 mb-4">
                Daftar Menu & Paket yang Dipesan
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs font-semibold text-stone-500 border-b border-stone-200">
                        <tr>
                            <th class="pb-2">Nama Menu / Paket</th>
                            <th class="pb-2 text-right">Harga Satuan</th>
                            <th class="pb-2 text-center">Jumlah</th>
                            <th class="pb-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="py-3">
                                    <div class="font-semibold text-stone-900">{{ $item->item_name }}</div>
                                    <div class="text-xs text-stone-400">
                                        {{ $item->package_id ? 'Paket Catering' : 'Menu Ala Carte' }}
                                    </div>
                                </td>
                                <td class="py-3 text-right text-stone-700">{{ $rp($item->unit_price) }}</td>
                                <td class="py-3 text-center font-semibold text-stone-900">{{ number_format($item->quantity) }}x</td>
                                <td class="py-3 text-right font-bold text-stone-900">{{ $rp($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-stone-200 font-bold">
                        <tr>
                            <td colspan="3" class="pt-3 text-right text-stone-700">Total Tagihan:</td>
                            <td class="pt-3 text-right text-lg text-stone-900">{{ $rp($order->total_amount) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Riwayat Pembayaran --}}
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3 mb-4">
                <div>
                    <h2 class="font-bold text-stone-900">Riwayat Pembayaran</h2>
                    <p class="text-xs text-stone-500">Catatan DP, tambahan, dan pelunasan</p>
                </div>
            </div>

            <table class="w-full text-sm mb-4">
                <thead class="text-left text-xs font-semibold text-stone-500 border-b border-stone-100">
                    <tr>
                        <th class="pb-2">Tanggal</th>
                        <th class="pb-2">Tipe</th>
                        <th class="pb-2">Metode</th>
                        <th class="pb-2">Catatan</th>
                        <th class="pb-2 text-right">Nominal</th>
                        <th class="pb-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($order->payments as $p)
                        <tr>
                            <td class="py-2.5 font-medium text-stone-800">{{ $p->paid_at->translatedFormat('d M Y') }}</td>
                            <td class="py-2.5"><span class="px-2 py-0.5 rounded text-xs font-semibold bg-stone-100 text-stone-800">{{ $p->type_label }}</span></td>
                            <td class="py-2.5">
                                <span class="px-2 py-0.5 rounded text-xs font-medium border {{ $methodBadge[$p->method] ?? 'bg-stone-100 text-stone-700' }}">
                                    {{ $p->method_label }}
                                </span>
                            </td>
                            <td class="py-2.5 text-xs text-stone-500">{{ $p->notes ?? '-' }}</td>
                            <td class="py-2.5 text-right font-bold text-emerald-700">+ {{ $rp($p->amount) }}</td>
                            <td class="py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('payments.receipt', $p) }}" target="_blank" class="p-1 rounded text-stone-600 hover:text-stone-900 hover:bg-stone-100" title="Cetak Kuitansi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-stone-400">Belum ada riwayat pembayaran yang dicatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Form Tambah Pembayaran Baru --}}
            @if ($order->remaining_amount > 0)
                <div class="bg-stone-50 rounded-xl p-4 border border-stone-200">
                    <h3 class="text-xs font-bold text-stone-700 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Catat Pembayaran Baru (Kasir / Outlet)
                    </h3>
                    <form method="POST" action="{{ route('payments.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">

                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">Nominal (Rp)</label>
                            <input type="number" name="amount" value="{{ $order->remaining_amount }}" max="{{ $order->remaining_amount }}" min="1" required class="w-full rounded-lg border border-stone-300 bg-white px-2.5 py-1.5 text-xs text-stone-800 font-bold">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">Tipe Pembayaran</label>
                            <select name="type" class="w-full rounded-lg border border-stone-300 bg-white px-2.5 py-1.5 text-xs text-stone-800">
                                <option value="pelunasan" selected>Pelunasan</option>
                                <option value="tambahan">Pembayaran Tambahan</option>
                                <option value="dp">DP (Uang Muka)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">Metode</label>
                            <select name="method" class="w-full rounded-lg border border-stone-300 bg-white px-2.5 py-1.5 text-xs text-stone-800">
                                @foreach ($paymentMethods as $k => $lbl)
                                    <option value="{{ $k }}" @selected($k === 'tunai')>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-stone-600 mb-1">Tanggal</label>
                            <input type="date" name="paid_at" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border border-stone-300 bg-white px-2.5 py-1.5 text-xs text-stone-800">
                        </div>

                        <div class="sm:col-span-3">
                            <input type="text" name="notes" placeholder="Catatan bukti (misal: Tunai di kasir / transfer bank BCA)" class="w-full rounded-lg border border-stone-300 bg-white px-2.5 py-1.5 text-xs text-stone-800">
                        </div>

                        <div class="sm:col-span-1 flex items-end">
                            <button type="submit" class="w-full rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold py-2 transition">
                                Simpan Bayar
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Kolom Kanan: Status & Pelanggan --}}
    <div class="space-y-6">
        {{-- Status Pesanan Cepat --}}
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
            <h2 class="font-bold text-stone-900 border-b border-stone-100 pb-3 mb-3">Update Status Pesanan</h2>
            <form method="POST" action="{{ route('orders.status', $order) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <select name="status" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 font-semibold">
                    @foreach ($statuses as $stk => $stlbl)
                        <option value="{{ $stk }}" @selected($order->status === $stk)>
                            Status: {{ $stlbl }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="w-full rounded-lg bg-stone-800 hover:bg-stone-900 text-white text-xs font-semibold py-2 transition">
                    Perbarui Status
                </button>
            </form>
        </div>

        {{-- Ringkasan Keuangan --}}
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs space-y-3">
            <h2 class="font-bold text-stone-900 border-b border-stone-100 pb-2">Status Pembayaran</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-stone-500">Total Tagihan:</span>
                    <span class="font-bold text-stone-900">{{ $rp($order->total_amount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-stone-500">Uang Diterima:</span>
                    <span class="font-bold text-emerald-600">{{ $rp($order->paid_amount) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-stone-100">
                    <span class="text-stone-700 font-semibold">Sisa Piutang:</span>
                    <span class="font-bold text-lg {{ $order->remaining_amount > 0 ? 'text-red-600' : 'text-stone-400' }}">
                        {{ $rp($order->remaining_amount) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Info Pelanggan --}}
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs">
            <h2 class="font-bold text-stone-900 border-b border-stone-100 pb-3 mb-3">Informasi Pelanggan</h2>
            <div class="space-y-2.5 text-sm">
                <div>
                    <div class="text-xs text-stone-400">Nama Pelanggan:</div>
                    <div class="font-semibold text-stone-900">{{ $order->customer->name }}</div>
                </div>
                <div>
                    <div class="text-xs text-stone-400">Tipe Pelanggan:</div>
                    <div class="text-stone-700 font-medium capitalize">{{ $order->customer->type }}</div>
                </div>
                <div>
                    <div class="text-xs text-stone-400">Nomor Telepon:</div>
                    <div class="font-medium text-stone-900">{{ $order->customer->phone ?? 'Tidak ada kontak' }}</div>
                </div>
                <div>
                    <div class="text-xs text-stone-400">Alamat:</div>
                    <div class="text-stone-700 text-xs">{{ $order->customer->address ?? '-' }}</div>
                </div>
            </div>
            <div class="pt-3 border-t border-stone-100 mt-3">
                <a href="{{ route('customers.show', $order->customer) }}" class="text-xs font-semibold text-orange-600 hover:underline">
                    Lihat Profil & Riwayat Pelanggan &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
