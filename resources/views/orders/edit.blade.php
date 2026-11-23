@extends('layouts.admin')

@section('title', 'Edit Pesanan ' . $order->order_number)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Edit Pesanan: {{ $order->order_number }}</h1>
            <p class="text-sm text-stone-500">Ubah data tanggal, pelanggan, lokasi, atau status pesanan</p>
        </div>
        <a href="{{ route('orders.show', $order) }}" class="text-sm text-stone-600 hover:text-stone-900 flex items-center gap-1">
            &larr; Kembali ke detail
        </a>
    </div>

    <form method="POST" action="{{ route('orders.update', $order) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Pelanggan <span class="text-red-500">*</span></label>
                <select name="customer_id" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800" required>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id', $order->customer_id) == $c->id)>
                            {{ $c->name }} ({{ ucfirst($c->type) }}) - {{ $c->phone ?? 'Tanpa HP' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-600 mb-1">Sumber Pesanan <span class="text-red-500">*</span></label>
                    <select name="source" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800" required>
                        @foreach ($sources as $k => $lbl)
                            <option value="{{ $k }}" @selected(old('source', $order->source) === $k)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-600 mb-1">Status Pesanan <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800" required>
                        @foreach ($statuses as $sk => $slbl)
                            <option value="{{ $sk }}" @selected(old('status', $order->status) === $sk)>{{ $slbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-600 mb-1">Tanggal Pesan Dibuat <span class="text-red-500">*</span></label>
                    <input type="date" name="order_date" value="{{ old('order_date', $order->order_date->format('Y-m-d')) }}" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-600 mb-1">Tanggal Acara / Pengiriman <span class="text-red-500">*</span></label>
                    <input type="date" name="event_date" value="{{ old('event_date', $order->event_date->format('Y-m-d')) }}" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Lokasi Acara / Pengantaran <span class="text-red-500">*</span></label>
                <input type="text" name="event_location" value="{{ old('event_location', $order->event_location) }}" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800" required>
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Catatan Tambahan</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800">{{ old('notes', $order->notes) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('orders.show', $order) }}" class="px-5 py-2.5 rounded-lg border border-stone-300 bg-white text-stone-700 text-sm font-medium hover:bg-stone-50">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold shadow-xs">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
