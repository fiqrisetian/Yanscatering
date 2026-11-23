@extends('layouts.admin')

@section('title', 'Profil Pelanggan - ' . $customer->name)

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
    $totalSpent = $customer->orders->sum('total_amount');
    $totalPaid = $customer->orders->sum(fn ($o) => $o->paid_amount);
    $totalDebt = $customer->orders->sum(fn ($o) => $o->remaining_amount);
@endphp

<header class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('customers.index') }}" class="p-2 rounded-lg bg-white border border-stone-200 text-stone-600 hover:text-stone-900 shadow-xs" title="Kembali">
            &larr;
        </a>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-stone-900">{{ $customer->name }}</h1>
                <span class="px-2.5 py-0.5 rounded text-xs font-semibold {{ $customer->type === 'instansi' ? 'bg-sky-100 text-sky-800' : 'bg-stone-100 text-stone-700' }}">
                    {{ ucfirst($customer->type) }}
                </span>
            </div>
            <p class="text-xs text-stone-500 mt-0.5">Terdaftar sejak {{ $customer->created_at->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <button type="button" onclick="document.getElementById('modalEditCustomer').classList.remove('hidden')" class="rounded-lg bg-white border border-stone-300 hover:bg-stone-50 text-stone-700 px-3.5 py-2 text-sm font-semibold shadow-xs">
            Edit Data Pelanggan
        </button>
        <a href="{{ route('orders.create') }}" class="rounded-lg bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 text-sm font-semibold shadow-xs">
            + Buat Pesanan Baru
        </a>
    </div>
</header>

{{-- Ringkasan Pelanggan --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="rounded-xl bg-white border border-stone-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Jumlah Pesanan</div>
        <div class="text-2xl font-bold text-stone-900 mt-1">{{ $customer->orders->count() }} pesanan</div>
    </div>
    <div class="rounded-xl bg-white border border-stone-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Total Nilai Pesanan</div>
        <div class="text-2xl font-bold text-stone-900 mt-1">{{ $rp($totalSpent) }}</div>
    </div>
    <div class="rounded-xl bg-white border border-stone-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Total Uang Diterima</div>
        <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $rp($totalPaid) }}</div>
    </div>
    <div class="rounded-xl bg-white border border-stone-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Sisa Piutang</div>
        <div class="text-2xl font-bold {{ $totalDebt > 0 ? 'text-red-600' : 'text-stone-400' }} mt-1">
            {{ $rp($totalDebt) }}
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Tabel Riwayat Pesanan --}}
    <div class="lg:col-span-2">
        <div class="rounded-xl bg-white border border-stone-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-stone-200 flex justify-between items-center">
                <h2 class="font-bold text-stone-900">Riwayat Pesanan Catering</h2>
                <span class="text-xs text-stone-500">{{ $customer->orders->count() }} transaksi tercatat</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[650px]">
                    <thead class="text-left text-xs font-semibold text-stone-500 bg-stone-50 border-b border-stone-200">
                        <tr>
                            <th class="py-2.5 px-3">No. Pesanan</th>
                            <th class="py-2.5 px-3">Kanal</th>
                            <th class="py-2.5 px-3">Tgl Acara</th>
                            <th class="py-2.5 px-3 text-right">Total</th>
                            <th class="py-2.5 px-3 text-right">Sisa</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($customer->orders as $o)
                            <tr class="hover:bg-stone-50/70">
                                <td class="py-3 px-3 font-semibold">
                                    <a href="{{ route('orders.show', $o) }}" class="text-orange-700 hover:underline">
                                        {{ $o->order_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-3 text-xs">
                                    @if ($o->isOffline())
                                        <span class="px-1.5 py-0.5 rounded font-semibold bg-orange-100 text-orange-800">Offline</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded font-semibold bg-sky-100 text-sky-800">Online</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">{{ $o->event_date->translatedFormat('d M Y') }}</td>
                                <td class="py-3 px-3 text-right font-medium">{{ $rp($o->total_amount) }}</td>
                                <td class="py-3 px-3 text-right font-semibold {{ $o->remaining_amount > 0 ? 'text-red-600' : 'text-stone-400' }}">
                                    {{ $rp($o->remaining_amount) }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge[$o->status] }}">
                                        {{ \App\Models\Order::STATUSES[$o->status] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <a href="{{ route('orders.show', $o) }}" class="text-xs font-semibold text-orange-600 hover:underline">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-stone-400">Belum ada pesanan dari pelanggan ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Detail Info Kontak --}}
    <div>
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs space-y-4">
            <h2 class="font-bold text-stone-900 border-b border-stone-100 pb-2">Informasi Kontak</h2>
            <div class="space-y-3 text-sm">
                <div>
                    <div class="text-xs text-stone-400">Nomor Telepon / WhatsApp:</div>
                    <div class="font-semibold text-stone-900 mt-0.5 flex items-center justify-between">
                        <span>{{ $customer->phone ?? 'Tidak ada nomor' }}</span>
                        @if ($customer->phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->phone) }}" target="_blank" class="text-xs text-emerald-600 hover:underline flex items-center gap-1">
                                Chat WhatsApp &rarr;
                            </a>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-stone-400">Alamat Lengkap:</div>
                    <div class="text-stone-800 mt-0.5 leading-relaxed">{{ $customer->address ?? 'Alamat belum diisi' }}</div>
                </div>
                <div>
                    <div class="text-xs text-stone-400">Tipe Pelanggan:</div>
                    <div class="font-medium text-stone-800 mt-0.5 capitalize">{{ $customer->type }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Pelanggan --}}
<div id="modalEditCustomer" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-stone-900 text-base">Edit Data Pelanggan</h3>
            <button type="button" onclick="document.getElementById('modalEditCustomer').classList.add('hidden')" class="text-stone-400 hover:text-stone-700">&times;</button>
        </div>
        <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Lengkap / Instansi</label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Tipe Pelanggan</label>
                <select name="type" required class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
                    <option value="perorangan" @selected($customer->type === 'perorangan')>Perorangan</option>
                    <option value="instansi" @selected($customer->type === 'instansi')>Instansi / Perusahaan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Nomor Telepon / WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Alamat</label>
                <textarea name="address" rows="2" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">{{ old('address', $customer->address) }}</textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalEditCustomer').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-stone-300 text-stone-600 text-sm font-medium hover:bg-stone-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
