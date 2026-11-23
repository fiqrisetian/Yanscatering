@extends('layouts.admin')

@section('title', 'Menu & Paket Catering')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp

<header class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-stone-900">Menu & Paket Catering</h1>
        <p class="text-sm text-stone-500">Kelola daftar menu satuan, nasi box, dan paket catering prasmanan/acara</p>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="document.getElementById('modalAddMenu').classList.remove('hidden')" class="rounded-lg bg-orange-600 hover:bg-orange-700 transition text-white px-3.5 py-2 text-sm font-semibold shadow-xs flex items-center gap-1.5">
            + Tambah Menu Satuan
        </button>
        <button type="button" onclick="document.getElementById('modalAddPackage').classList.remove('hidden')" class="rounded-lg bg-stone-800 hover:bg-stone-900 transition text-white px-3.5 py-2 text-sm font-semibold shadow-xs flex items-center gap-1.5">
            + Tambah Paket Catering
        </button>
    </div>
</header>

{{-- Tab Selector --}}
<div class="flex items-center gap-2 mb-6 border-b border-stone-200 pb-3">
    <a href="{{ route('menus.index', ['tab' => 'menu']) }}"
       class="px-4 py-2 rounded-lg text-sm font-bold transition {{ $tab === 'menu' ? 'bg-orange-600 text-white shadow-xs' : 'bg-white border border-stone-200 text-stone-600 hover:bg-stone-50' }}">
        Menu Satuan & Box ({{ $menus->count() }})
    </a>
    <a href="{{ route('menus.index', ['tab' => 'package']) }}"
       class="px-4 py-2 rounded-lg text-sm font-bold transition {{ $tab === 'package' ? 'bg-orange-600 text-white shadow-xs' : 'bg-white border border-stone-200 text-stone-600 hover:bg-stone-50' }}">
        Paket Catering & Prasmanan ({{ $packages->count() }})
    </a>
</div>

@if ($tab === 'menu')
    {{-- TAB MENU SATUAN --}}
    <div class="rounded-xl bg-white border border-stone-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-stone-200 flex flex-wrap items-center justify-between gap-3 bg-stone-50/50">
            <h2 class="font-bold text-stone-900">Daftar Menu Ala Carte & Box</h2>
            <form method="GET" action="{{ route('menus.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="menu">
                <input type="text" name="qm" value="{{ request('qm') }}" placeholder="Cari nama menu..." class="rounded-lg border border-stone-300 px-3 py-1.5 text-xs text-stone-800">
                <button type="submit" class="bg-stone-800 text-white px-3 py-1.5 rounded-lg text-xs font-semibold">Cari</button>
                @if (request('qm'))
                    <a href="{{ route('menus.index', ['tab' => 'menu']) }}" class="text-xs text-red-600 hover:underline">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs font-semibold text-stone-500 bg-stone-50 border-b border-stone-200">
                    <tr>
                        <th class="py-3 px-4">Nama Menu</th>
                        <th class="py-3 px-3">Kategori</th>
                        <th class="py-3 px-3 text-right">Harga Jual</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($menus as $m)
                        <tr class="hover:bg-stone-50/70">
                            <td class="py-3 px-4 font-bold text-stone-900">{{ $m->name }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-xs bg-stone-100 text-stone-700 font-medium">
                                    {{ $m->category ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-orange-700">{{ $rp($m->price) }}</td>
                            <td class="py-3 px-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $m->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-600' }}">
                                    {{ $m->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="editMenu({{ json_encode($m) }})" class="text-xs text-blue-600 hover:underline">Edit</button>
                                    <form method="POST" action="{{ route('menus.destroy', $m) }}" onsubmit="return confirm('Hapus menu ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-stone-400">Belum ada menu terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    {{-- TAB PAKET CATERING --}}
    <div class="rounded-xl bg-white border border-stone-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-stone-200 flex flex-wrap items-center justify-between gap-3 bg-stone-50/50">
            <h2 class="font-bold text-stone-900">Daftar Paket Catering & Prasmanan</h2>
            <form method="GET" action="{{ route('menus.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="package">
                <input type="text" name="qp" value="{{ request('qp') }}" placeholder="Cari nama paket..." class="rounded-lg border border-stone-300 px-3 py-1.5 text-xs text-stone-800">
                <button type="submit" class="bg-stone-800 text-white px-3 py-1.5 rounded-lg text-xs font-semibold">Cari</button>
                @if (request('qp'))
                    <a href="{{ route('menus.index', ['tab' => 'package']) }}" class="text-xs text-red-600 hover:underline">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs font-semibold text-stone-500 bg-stone-50 border-b border-stone-200">
                    <tr>
                        <th class="py-3 px-4">Nama Paket</th>
                        <th class="py-3 px-3">Deskripsi Menu / Fasilitas</th>
                        <th class="py-3 px-3 text-right">Harga Paket</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($packages as $p)
                        <tr class="hover:bg-stone-50/70">
                            <td class="py-3 px-4 font-bold text-stone-900">{{ $p->name }}</td>
                            <td class="py-3 px-3 text-xs text-stone-600 max-w-md leading-relaxed">
                                {{ $p->description ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-orange-700 whitespace-nowrap">{{ $rp($p->price) }}</td>
                            <td class="py-3 px-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $p->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-200 text-stone-600' }}">
                                    {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="editPackage({{ json_encode($p) }})" class="text-xs text-blue-600 hover:underline">Edit</button>
                                    <form method="POST" action="{{ route('packages.destroy', $p) }}" onsubmit="return confirm('Hapus paket ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-stone-400">Belum ada paket catering terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Modal Tambah Menu --}}
<div id="modalAddMenu" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-stone-900 text-base">Tambah Menu Satuan</h3>
            <button type="button" onclick="document.getElementById('modalAddMenu').classList.add('hidden')" class="text-stone-400 hover:text-stone-700">&times;</button>
        </div>
        <form method="POST" action="{{ route('menus.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Menu</label>
                <input type="text" name="name" required placeholder="Contoh: Nasi Box Ayam Bakar" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Kategori</label>
                <input type="text" name="category" placeholder="Contoh: Nasi Box, Snack, Tumpeng, Minuman" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Harga (Rp)</label>
                <input type="number" name="price" required min="0" placeholder="Contoh: 35000" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="rounded text-orange-600">
                <span class="text-xs font-semibold text-stone-700">Menu Aktif (Dapat Dipesan)</span>
            </label>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalAddMenu').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-stone-300 text-stone-600 text-sm">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-orange-600 text-white text-sm font-semibold">Simpan Menu</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Paket --}}
<div id="modalAddPackage" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-stone-900 text-base">Tambah Paket Catering</h3>
            <button type="button" onclick="document.getElementById('modalAddPackage').classList.add('hidden')" class="text-stone-400 hover:text-stone-700">&times;</button>
        </div>
        <form method="POST" action="{{ route('packages.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Paket</label>
                <input type="text" name="name" required placeholder="Contoh: Prasmanan Paket Melati (100 Pax)" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Deskripsi Menu & Fasilitas</label>
                <textarea name="description" rows="3" placeholder="Rincian makanan, fasilitas waiter, meja prasmanan, dsb" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Harga Paket (Rp)</label>
                <input type="number" name="price" required min="0" placeholder="Contoh: 6500000" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="rounded text-orange-600">
                <span class="text-xs font-semibold text-stone-700">Paket Aktif (Dapat Dipesan)</span>
            </label>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalAddPackage').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-stone-300 text-stone-600 text-sm">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-orange-600 text-white text-sm font-semibold">Simpan Paket</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Menu --}}
<div id="modalEditMenu" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-stone-900 text-base">Edit Menu</h3>
            <button type="button" onclick="document.getElementById('modalEditMenu').classList.add('hidden')" class="text-stone-400 hover:text-stone-700">&times;</button>
        </div>
        <form method="POST" id="formEditMenu" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Menu</label>
                <input type="text" name="name" id="editMenuName" required class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Kategori</label>
                <input type="text" name="category" id="editMenuCategory" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Harga (Rp)</label>
                <input type="number" name="price" id="editMenuPrice" required min="0" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" id="editMenuIsActive" value="1" class="rounded text-orange-600">
                <span class="text-xs font-semibold text-stone-700">Menu Aktif</span>
            </label>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalEditMenu').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-stone-300 text-stone-600 text-sm">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-orange-600 text-white text-sm font-semibold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Paket --}}
<div id="modalEditPackage" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-stone-900 text-base">Edit Paket Catering</h3>
            <button type="button" onclick="document.getElementById('modalEditPackage').classList.add('hidden')" class="text-stone-400 hover:text-stone-700">&times;</button>
        </div>
        <form method="POST" id="formEditPackage" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Nama Paket</label>
                <input type="text" name="name" id="editPkgName" required class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Deskripsi Menu & Fasilitas</label>
                <textarea name="description" id="editPkgDesc" rows="3" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Harga Paket (Rp)</label>
                <input type="number" name="price" id="editPkgPrice" required min="0" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" id="editPkgIsActive" value="1" class="rounded text-orange-600">
                <span class="text-xs font-semibold text-stone-700">Paket Aktif</span>
            </label>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalEditPackage').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-stone-300 text-stone-600 text-sm">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-orange-600 text-white text-sm font-semibold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editMenu(menu) {
        document.getElementById('formEditMenu').action = '/admin/menus/' + menu.id;
        document.getElementById('editMenuName').value = menu.name;
        document.getElementById('editMenuCategory').value = menu.category || '';
        document.getElementById('editMenuPrice').value = menu.price;
        document.getElementById('editMenuIsActive').checked = !!menu.is_active;
        document.getElementById('modalEditMenu').classList.remove('hidden');
    }

    function editPackage(pkg) {
        document.getElementById('formEditPackage').action = '/admin/packages/' + pkg.id;
        document.getElementById('editPkgName').value = pkg.name;
        document.getElementById('editPkgDesc').value = pkg.description || '';
        document.getElementById('editPkgPrice').value = pkg.price;
        document.getElementById('editPkgIsActive').checked = !!pkg.is_active;
        document.getElementById('modalEditPackage').classList.remove('hidden');
    }
</script>
@endsection
