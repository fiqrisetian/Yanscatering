<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'Yans Catering') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 text-stone-800 antialiased">
@php
    $nav = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard*'],
        ['label' => 'Pesanan', 'route' => 'orders.index', 'active' => 'orders*'],
        ['label' => 'Pelanggan', 'route' => 'customers.index', 'active' => 'customers*'],
        ['label' => 'Menu & Paket', 'route' => 'menus.index', 'active' => 'menus*'],
        ['label' => 'Pembayaran', 'route' => 'payments.index', 'active' => 'payments*'],
        ['label' => 'Laporan', 'route' => 'reports.index', 'active' => 'reports*'],
    ];
@endphp

<div id="overlay" class="fixed inset-0 z-30 bg-black/40 hidden lg:hidden"></div>

<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full lg:translate-x-0 transition-transform bg-white border-r border-stone-200 flex flex-col p-4 shadow-sm lg:shadow-none">
    <div class="flex items-center gap-3 px-2 py-3 mb-4 border-b border-stone-100 pb-4">
        <div class="w-10 h-10 rounded-xl bg-orange-600 text-white grid place-items-center font-bold text-lg shadow-sm">Y</div>
        <div>
            <div class="font-bold text-stone-900 leading-tight">{{ config('app.name', 'Yans Catering') }}</div>
            <div class="text-xs text-stone-500">Panel Manajemen Catering</div>
        </div>
    </div>

    <nav class="space-y-1 text-sm flex-1">
        @foreach ($nav as $item)
            @if ($item['route'])
                <a href="{{ route($item['route']) }}"
                   class="flex items-center justify-between rounded-lg px-3 py-2.5 transition-colors {{ request()->routeIs($item['active']) ? 'bg-orange-50 text-orange-700 font-semibold shadow-xs' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' }}">
                    <span>{{ $item['label'] }}</span>
                    @if (request()->routeIs($item['active']))
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-600"></span>
                    @endif
                </a>
            @else
                <span class="flex items-center justify-between rounded-lg px-3 py-2 text-stone-400 cursor-not-allowed">
                    {{ $item['label'] }} <span class="text-[10px] uppercase tracking-wide">segera</span>
                </span>
            @endif
        @endforeach
    </nav>

    <div class="border-t border-stone-200 pt-3 text-sm">
        <div class="px-2 mb-2">
            <div class="font-medium truncate text-stone-900">{{ auth()->user()->name }}</div>
            <div class="text-xs text-stone-500 truncate">{{ auth()->user()->email }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full text-left rounded-lg px-3 py-2 text-stone-600 hover:bg-red-50 hover:text-red-700 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Keluar
            </button>
        </form>
    </div>
</aside>

<div class="lg:pl-64 min-h-screen">
    <div class="lg:hidden sticky top-0 z-20 flex items-center gap-3 bg-white border-b border-stone-200 px-4 py-3 shadow-xs">
        <button id="menuBtn" class="p-1 -ml-1 text-stone-700 hover:text-stone-900" aria-label="Buka menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <span class="font-bold text-stone-900">{{ config('app.name', 'Yans Catering') }}</span>
    </div>

    <main class="p-4 sm:p-6 lg:p-8">
        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="mb-5 flex items-center justify-between rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 flex items-center justify-between rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800 shadow-xs">
                <div class="font-semibold mb-1">Terdapat kesalahan pada input formulir:</div>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
    const sb = document.getElementById('sidebar'), ov = document.getElementById('overlay');
    const toggle = () => { sb.classList.toggle('-translate-x-full'); ov.classList.toggle('hidden'); };
    document.getElementById('menuBtn').addEventListener('click', toggle);
    ov.addEventListener('click', toggle);
</script>
@stack('scripts')
</body>
</html>
