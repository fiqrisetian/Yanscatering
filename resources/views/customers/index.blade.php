@extends('layouts.admin')

@section('title', 'Data Pelanggan')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp

<header class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-stone-900">Data Pelanggan</h1>
        <p class="text-sm text-stone-500">Kelola database pelanggan catering perorangan dan instansi</p>
    </div>
    <button type="button" onclick="document.getElementById('modalAddCustomer').classList.remove('hidden')" class="rounded-lg bg-orange-600 hover:bg-orange-700 transition text-white px-4 py-2 text-sm font-semibold shadow-xs flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        + Tambah Pelanggan
    </button>
</header>

{{-- Kartu Ringkasan Pelanggan --}}
<section class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="rounded-xl bg-white border border-stone-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Total Pelanggan</div>
        <div class="text-2xl font-bold text-stone-900 mt-1">{{ $stats['total'] }}</div>
    </div>
    <div class="rounded-xl bg-white border border-stone-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Pelanggan Perorangan</div>
        <div class="text-2xl font-bold text-orange-600 mt-1">{{ $stats['perorangan'] }}</div>
    </div>
    <div class="rounded-xl bg-white border border-stone-200 p-4 shadow-xs">
        <div class="text-xs font-semibold text-stone-500 uppercase tracking-wide">Instansi / Kantor / Yayasan</div>
        <div class="text-2xl font-bold text-sky-600 mt-1">{{ $stats['instansi'] }}</div>
    </div>
</section>

{{-- Filter & Pencarian --}}
<div class="rounded-xl bg-white border border-stone-200 p-4 shadow-xs mb-6">
    <form method="GET" action="{{ route('customers.index') }}" class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[260px]">
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Cari nama pelanggan, nomor telepon, atau alamat..."
                   class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 placeholder-stone-400 focus:ring-2 focus:ring-orange-500">
        </div>

        <select name="type" class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700">
            <option value="">Semua Tipe</option>
            <option value="perorangan" @selected(request('type') === 'perorangan')>Perorangan</option>
            <option value="instansi" @selected(request('type') === 'instansi')>Instansi / Perusahaan</option>
        </select>

        <button type="submit" class="rounded-lg bg-stone-800 hover:bg-stone-900 text-white px-4 py-2 text-sm font-medium transition">
            Cari
        </button>
        @if (request()->hasAny(['q', 'type']))
            <a href="{{ route('customers.index') }}" class="text-xs text-red-600 hover:underline self-center">Reset</a>
        @endif
    </form>
</div>

{{-- Tabel Pelanggan --}}
<div class="rounded-xl bg-white border border-stone-200 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[750px]">
            <thead class="text-left text-xs font-semibold text-stone-500 bg-stone-50 border-b border-stone-200">
                <tr>
                    <th class="py-3 px-4">Nama Pelanggan</th>
                    <th class="py-3 px-3">Tipe</th>
                    <th class="py-3 px-3">Kontak / Telepon</th>
                    <th class="py-3 px-3">Alamat</th>
                    <th class="py-3 px-3 text-center">Jumlah Pesanan</th>
                    <th class="py-3 px-3 text-right">Total Transaksi</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($customers as $c)
                    <tr class="hover:bg-stone-50/70 transition-colors">
                        <td class="py-3.5 px-4 font-bold">
                            <a href="{{ route('customers.show', $c) }}" class="text-stone-900 hover:text-orange-700 hover:underline">
                                {{ $c->name }}
                            </a>
                        </td>
                        <td class="py-3.5 px-3">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $c->type === 'instansi' ? 'bg-sky-100 text-sky-800' : 'bg-stone-100 text-stone-700' }}">
                                {{ ucfirst($c->type) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-3 text-stone-700 font-medium">{{ $c->phone ?? '-' }}</td>
                        <td class="py-3.5 px-3 max-w-[200px] truncate text-stone-500 text-xs" title="{{ $c->address }}">
                            {{ $c->address ?? '-' }}
                        </td>
                        <td class="py-3.5 px-3 text-center font-semibold text-stone-900">
                            {{ $c->orders_count }} pesanan
                        </td>
                        <td class="py-3.5 px-3 text-right font-bold text-orange-700">
                            {{ $rp($c->orders_sum_total_amount ?? 0) }}
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <a href="{{ route('customers.show', $c) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-orange-600 hover:text-orange-800 bg-orange-50 hover:bg-orange-100 px-2.5 py-1 rounded">
                                Riwayat & Profil &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-stone-400">Belum ada data pelanggan yang sesuai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($customers->hasPages())
        <div class="px-4 py-3 border-t border-stone-200">
            {{ $customers->links() }}
        </div>
    @endif
</div>

{{-- Modal Tambah Pelanggan Baru --}}
<div id="modalAddCustomer" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-stone-900 text-base">Tambah Pelanggan Baru</h3>
            <button type="button" onclick="document.getElementById('modalAddCustomer').classList.add('hidden')" class="text-stone-400 hover:text-stone-700">&times;</button>
        </div>
        <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Lengkap / Instansi <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Ibu Rina / PT Citra Mandiri" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Tipe Pelanggan <span class="text-red-500">*</span></label>
                <select name="type" required class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
                    <option value="perorangan">Perorangan</option>
                    <option value="instansi">Instansi / Kantor / Yayasan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Nomor Telepon / WhatsApp</label>
                <input type="text" name="phone" placeholder="08123456789" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Alamat</label>
                <textarea name="address" rows="2" placeholder="Alamat tinggal atau kantor" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalAddCustomer').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-stone-300 text-stone-600 text-sm font-medium hover:bg-stone-50">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold">
                    Simpan Pelanggan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
