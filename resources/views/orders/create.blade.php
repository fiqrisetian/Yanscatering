@extends('layouts.admin')

@section('title', 'Input Pesanan Baru')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Input Pesanan Baru</h1>
            <p class="text-sm text-stone-500">Formulir pemesanan catering cepat (diutamakan pesanan offline / datang langsung)</p>
        </div>
        <a href="{{ route('orders.index') }}" class="text-sm text-stone-600 hover:text-stone-900 flex items-center gap-1">
            &larr; Kembali ke daftar
        </a>
    </div>

    <form method="POST" action="{{ route('orders.store') }}" id="orderForm" class="space-y-6">
        @csrf

        {{-- 1. Data Pelanggan --}}
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <h2 class="font-bold text-stone-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-700 text-xs font-bold grid place-items-center">1</span>
                    Data Pelanggan
                </h2>
                <div class="flex items-center gap-4 text-sm">
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="customer_mode" value="existing" id="modeExisting" checked class="text-orange-600 focus:ring-orange-500">
                        <span>Pilih Pelanggan Terdaftar</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="customer_mode" value="new" id="modeNew" class="text-orange-600 focus:ring-orange-500">
                        <span class="font-medium text-orange-700">+ Pelanggan Baru / Walk-In</span>
                    </label>
                </div>
            </div>

            {{-- Mode: Pelanggan Lama --}}
            <div id="existingCustomerSection">
                <label class="block text-xs font-semibold text-stone-600 mb-1">Pilih Pelanggan <span class="text-red-500">*</span></label>
                <select name="customer_id" id="customerId" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800">
                    <option value="">-- Pilih Pelanggan --</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>
                            {{ $c->name }} ({{ ucfirst($c->type) }}) - {{ $c->phone ?? 'Tanpa HP' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Mode: Pelanggan Baru --}}
            <div id="newCustomerSection" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 bg-orange-50/50 p-4 rounded-lg border border-orange-100">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Lengkap / Instansi <span class="text-red-500">*</span></label>
                    <input type="text" name="new_customer_name" value="{{ old('new_customer_name') }}" placeholder="Contoh: Ibu Rina / PT Citra Mandiri" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Nomor WhatsApp / HP</label>
                    <input type="text" name="new_customer_phone" value="{{ old('new_customer_phone') }}" placeholder="08123456789" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Tipe Pelanggan</label>
                    <select name="new_customer_type" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800">
                        <option value="perorangan">Perorangan</option>
                        <option value="instansi">Instansi / Kantor / Perusahaan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Alamat Pelanggan</label>
                    <input type="text" name="new_customer_address" value="{{ old('new_customer_address') }}" placeholder="Alamat tinggal / kantor" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800">
                </div>
            </div>
        </div>

        {{-- 2. Detail Pesanan & Acara --}}
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs space-y-4">
            <h2 class="font-bold text-stone-900 border-b border-stone-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-700 text-xs font-bold grid place-items-center">2</span>
                Informasi Acara & Sumber Pesanan
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-600 mb-1">Sumber Pesanan <span class="text-red-500">*</span></label>
                    <select name="source" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 font-medium">
                        @foreach ($sources as $k => $lbl)
                            <option value="{{ $k }}" @selected(old('source', 'datang_langsung') === $k)>
                                {{ $lbl }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-stone-400 mt-1 block">Default: Datang Langsung (Offline)</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-600 mb-1">Tanggal Pesan <span class="text-red-500">*</span></label>
                    <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-600 mb-1">Tanggal Acara / Pengiriman <span class="text-red-500">*</span></label>
                    <input type="date" name="event_date" value="{{ old('event_date', date('Y-m-d', strtotime('+3 days'))) }}" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Lokasi Acara / Pengantaran <span class="text-red-500">*</span></label>
                <input type="text" name="event_location" id="eventLocation" value="{{ old('event_location') }}" placeholder="Contoh: Gedung Balai Sartika / Rumah Jl. Buah Batu No. 12" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800" required>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-xs text-stone-400">Pilihan cepat:</span>
                    <button type="button" onclick="document.getElementById('eventLocation').value='Ambil Sendiri di Outlet Yans Catering'" class="text-xs bg-stone-100 hover:bg-stone-200 text-stone-700 px-2 py-0.5 rounded">
                        Ambil di Outlet (Offline)
                    </button>
                    <button type="button" onclick="document.getElementById('eventLocation').value='Diantar ke Alamat Pemesan'" class="text-xs bg-stone-100 hover:bg-stone-200 text-stone-700 px-2 py-0.5 rounded">
                        Antar ke Alamat Pemesan
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Catatan Khusus Pesanan</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Request pedas sedang, include sendok & tisu, antar jam 10 pagi..." class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- 3. Pilih Menu & Paket Catering --}}
        <div class="rounded-xl bg-white border border-stone-200 p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <h2 class="font-bold text-stone-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-700 text-xs font-bold grid place-items-center">3</span>
                    Rincian Item Menu / Paket Catering
                </h2>
                <button type="button" id="addItemBtn" class="text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-200 px-3 py-1.5 rounded-lg hover:bg-orange-100 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    + Tambah Baris Menu
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[650px]" id="itemsTable">
                    <thead class="text-left text-xs font-semibold text-stone-500 border-b border-stone-200">
                        <tr>
                            <th class="pb-2 w-36">Tipe Item</th>
                            <th class="pb-2">Pilihan Menu / Paket</th>
                            <th class="pb-2 w-32">Harga Satuan</th>
                            <th class="pb-2 w-24">Jumlah (Qty)</th>
                            <th class="pb-2 w-36 text-right">Subtotal</th>
                            <th class="pb-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100" id="itemsBody">
                        {{-- Baris pertama default --}}
                        <tr class="item-row">
                            <td class="py-2.5 pr-2">
                                <select class="item-type-select w-full rounded-lg border border-stone-300 bg-white px-2 py-1.5 text-xs text-stone-800">
                                    <option value="menu">Menu Ala Carte</option>
                                    <option value="package">Paket Catering</option>
                                </select>
                            </td>
                            <td class="py-2.5 pr-2">
                                <select class="item-id-select w-full rounded-lg border border-stone-300 bg-white px-2 py-1.5 text-xs text-stone-800" required>
                                    <option value="">-- Pilih Menu --</option>
                                    @foreach ($menus as $m)
                                        <option value="{{ $m->id }}" data-price="{{ $m->price }}" data-type="menu">
                                            {{ $m->name }} (Rp {{ number_format($m->price, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="items[0][type]" value="menu" class="hidden-item-type">
                                <input type="hidden" name="items[0][id]" value="" class="hidden-item-id">
                            </td>
                            <td class="py-2.5 pr-2">
                                <input type="number" name="items[0][price]" value="0" class="item-price w-full rounded-lg border border-stone-300 bg-stone-50 px-2 py-1.5 text-xs text-stone-800" readonly>
                            </td>
                            <td class="py-2.5 pr-2">
                                <input type="number" name="items[0][qty]" value="1" min="1" class="item-qty w-full rounded-lg border border-stone-300 bg-white px-2 py-1.5 text-xs text-stone-800" required>
                            </td>
                            <td class="py-2.5 pr-2 text-right font-semibold text-stone-900 item-subtotal">
                                Rp 0
                            </td>
                            <td class="py-2.5 text-center">
                                <button type="button" class="remove-item-btn text-red-500 hover:text-red-700 p-1">&times;</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Total Akhir --}}
            <div class="flex justify-end pt-3 border-t border-stone-100">
                <div class="w-72 bg-stone-50 rounded-xl p-4 border border-stone-200 space-y-2">
                    <div class="flex justify-between items-center text-sm font-semibold text-stone-600">
                        <span>Total Tagihan:</span>
                        <span class="text-xl font-bold text-stone-900" id="grandTotalDisplay">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Pembayaran Langsung (DP / Uang Muka di Tempat) --}}
        <div class="rounded-xl bg-emerald-50/60 border border-emerald-200 p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-emerald-100 pb-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="record_payment" id="recordPayment" value="1" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                    <span class="font-bold text-stone-900 text-sm">Catat Pembayaran Langsung / DP di Tempat (Kasir / Outlet)</span>
                </label>
            </div>

            <div id="paymentFields" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Nominal Pembayaran (Rp)</label>
                    <input type="number" name="payment_amount" id="paymentAmount" value="{{ old('payment_amount', 0) }}" min="0" placeholder="0" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm font-semibold text-stone-900">
                    <div class="flex gap-1.5 mt-1.5">
                        <button type="button" id="btnDp30" class="text-[11px] bg-white border border-stone-300 hover:bg-stone-50 px-2 py-0.5 rounded text-stone-700">DP 30%</button>
                        <button type="button" id="btnDp50" class="text-[11px] bg-white border border-stone-300 hover:bg-stone-50 px-2 py-0.5 rounded text-stone-700">DP 50%</button>
                        <button type="button" id="btnLunas" class="text-[11px] bg-emerald-100 text-emerald-800 font-semibold px-2 py-0.5 rounded">Lunas (100%)</button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Metode Pembayaran</label>
                    <select name="payment_method" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800">
                        @foreach ($paymentMethods as $pm => $pmlabel)
                            <option value="{{ $pm }}" @selected($pm === 'tunai')>{{ $pmlabel }}</option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-stone-400 mt-1 block">Khusus offline: utamakan Tunai / QRIS</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Catatan Pembayaran</label>
                    <input type="text" name="payment_notes" value="Tanda jadi / DP di kasir offline" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800">
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex items-center justify-end gap-3 pt-3">
            <a href="{{ route('orders.index') }}" class="px-5 py-2.5 rounded-lg border border-stone-300 bg-white text-stone-700 text-sm font-medium hover:bg-stone-50">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold shadow-xs flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan & Terbitkan Pesanan
            </button>
        </div>
    </form>
</div>

{{-- Data JSON untuk dynamic selection --}}
<script>
    const menus = @json($menus);
    const packages = @json($packages);

    // Customer mode toggle
    const modeExisting = document.getElementById('modeExisting');
    const modeNew = document.getElementById('modeNew');
    const existingSection = document.getElementById('existingCustomerSection');
    const newSection = document.getElementById('newCustomerSection');

    modeExisting.addEventListener('change', () => {
        if (modeExisting.checked) {
            existingSection.classList.remove('hidden');
            newSection.classList.add('hidden');
        }
    });

    modeNew.addEventListener('change', () => {
        if (modeNew.checked) {
            existingSection.classList.add('hidden');
            newSection.classList.remove('hidden');
        }
    });

    // Payment toggle
    const recordPayment = document.getElementById('recordPayment');
    const paymentFields = document.getElementById('paymentFields');
    recordPayment.addEventListener('change', () => {
        paymentFields.style.opacity = recordPayment.checked ? '1' : '0.4';
        paymentFields.style.pointerEvents = recordPayment.checked ? 'auto' : 'none';
    });

    // Dynamic Items Table
    let rowIndex = 0;
    const itemsBody = document.getElementById('itemsBody');
    const addItemBtn = document.getElementById('addItemBtn');
    let grandTotal = 0;

    function formatRp(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    function calculateTotal() {
        grandTotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const price = Number(row.querySelector('.item-price').value) || 0;
            const qty = Number(row.querySelector('.item-qty').value) || 0;
            const sub = price * qty;
            row.querySelector('.item-subtotal').textContent = formatRp(sub);
            grandTotal += sub;
        });
        document.getElementById('grandTotalDisplay').textContent = formatRp(grandTotal);
    }

    function populateOptions(selectEl, type) {
        selectEl.innerHTML = '<option value="">-- Pilih ' + (type === 'menu' ? 'Menu' : 'Paket') + ' --</option>';
        const list = type === 'menu' ? menus : packages;
        list.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.dataset.price = item.price;
            opt.dataset.type = type;
            opt.textContent = item.name + ' (' + formatRp(item.price) + ')';
            selectEl.appendChild(opt);
        });
    }

    function bindRowEvents(row, idx) {
        const typeSelect = row.querySelector('.item-type-select');
        const idSelect = row.querySelector('.item-id-select');
        const hiddenType = row.querySelector('.hidden-item-type');
        const hiddenId = row.querySelector('.hidden-item-id');
        const priceInput = row.querySelector('.item-price');
        const qtyInput = row.querySelector('.item-qty');
        const removeBtn = row.querySelector('.remove-item-btn');

        typeSelect.addEventListener('change', () => {
            const type = typeSelect.value;
            hiddenType.value = type;
            populateOptions(idSelect, type);
            priceInput.value = 0;
            calculateTotal();
        });

        idSelect.addEventListener('change', () => {
            const selectedOpt = idSelect.options[idSelect.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                hiddenId.value = selectedOpt.value;
                priceInput.value = selectedOpt.dataset.price || 0;
            } else {
                hiddenId.value = '';
                priceInput.value = 0;
            }
            calculateTotal();
        });

        qtyInput.addEventListener('input', calculateTotal);

        removeBtn.addEventListener('click', () => {
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                calculateTotal();
            } else {
                alert('Minimal harus ada 1 item pesanan.');
            }
        });
    }

    // Bind first row
    const firstRow = itemsBody.querySelector('.item-row');
    bindRowEvents(firstRow, 0);

    // Add item row
    addItemBtn.addEventListener('click', () => {
        rowIndex++;
        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.innerHTML = `
            <td class="py-2.5 pr-2">
                <select class="item-type-select w-full rounded-lg border border-stone-300 bg-white px-2 py-1.5 text-xs text-stone-800">
                    <option value="menu">Menu Ala Carte</option>
                    <option value="package">Paket Catering</option>
                </select>
            </td>
            <td class="py-2.5 pr-2">
                <select class="item-id-select w-full rounded-lg border border-stone-300 bg-white px-2 py-1.5 text-xs text-stone-800" required>
                    <option value="">-- Pilih Menu --</option>
                </select>
                <input type="hidden" name="items[${rowIndex}][type]" value="menu" class="hidden-item-type">
                <input type="hidden" name="items[${rowIndex}][id]" value="" class="hidden-item-id">
            </td>
            <td class="py-2.5 pr-2">
                <input type="number" name="items[${rowIndex}][price]" value="0" class="item-price w-full rounded-lg border border-stone-300 bg-stone-50 px-2 py-1.5 text-xs text-stone-800" readonly>
            </td>
            <td class="py-2.5 pr-2">
                <input type="number" name="items[${rowIndex}][qty]" value="1" min="1" class="item-qty w-full rounded-lg border border-stone-300 bg-white px-2 py-1.5 text-xs text-stone-800" required>
            </td>
            <td class="py-2.5 pr-2 text-right font-semibold text-stone-900 item-subtotal">
                Rp 0
            </td>
            <td class="py-2.5 text-center">
                <button type="button" class="remove-item-btn text-red-500 hover:text-red-700 p-1">&times;</button>
            </td>
        `;
        itemsBody.appendChild(newRow);
        populateOptions(newRow.querySelector('.item-id-select'), 'menu');
        bindRowEvents(newRow, rowIndex);
    });

    // Quick DP buttons
    document.getElementById('btnDp30').addEventListener('click', () => {
        document.getElementById('paymentAmount').value = Math.round(grandTotal * 0.3);
    });
    document.getElementById('btnDp50').addEventListener('click', () => {
        document.getElementById('paymentAmount').value = Math.round(grandTotal * 0.5);
    });
    document.getElementById('btnLunas').addEventListener('click', () => {
        document.getElementById('paymentAmount').value = grandTotal;
    });
</script>
@endsection
