<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nota Pesanan - {{ $order->order_number }} - {{ config('app.name', 'Yans Catering') }}</title>
    <style>
        @page { size: A4 portrait; margin: 1.5cm; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #1c1917; background: #f5f5f4; margin: 0; padding: 20px; font-size: 13px; line-height: 1.4; }
        .invoice-card { background: #fff; max-width: 800px; margin: 0 auto; padding: 40px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #ea580c; padding-bottom: 20px; margin-bottom: 24px; }
        .brand-logo { font-size: 24px; font-weight: 800; color: #ea580c; letter-spacing: -0.5px; }
        .brand-sub { font-size: 11px; color: #78716c; margin-top: 2px; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { margin: 0; font-size: 20px; color: #1c1917; text-transform: uppercase; letter-spacing: 0.5px; }
        .invoice-title .num { font-size: 14px; font-weight: 700; color: #ea580c; margin-top: 4px; }
        .grid-info { display: flex; justify-content: space-between; gap: 20px; margin-bottom: 24px; }
        .info-col { flex: 1; }
        .info-col h3 { font-size: 11px; text-transform: uppercase; color: #78716c; margin: 0 0 6px 0; letter-spacing: 0.5px; }
        .info-col p { margin: 2px 0; color: #292524; }
        table.items-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        table.items-table th { background: #f5f5f4; color: #44403c; font-size: 11px; text-transform: uppercase; text-align: left; padding: 10px 12px; border-top: 1px solid #e7e5e4; border-bottom: 1px solid #e7e5e4; }
        table.items-table td { padding: 12px; border-bottom: 1px solid #f5f5f4; color: #292524; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-box { display: flex; justify-content: flex-end; margin-bottom: 24px; }
        .summary-table { width: 320px; border-collapse: collapse; }
        .summary-table td { padding: 6px 12px; }
        .summary-table tr.total-row td { border-top: 2px solid #e7e5e4; font-weight: 800; font-size: 15px; color: #1c1917; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-lunas { background: #d1fae5; color: #065f46; }
        .badge-dp { background: #fef3c7; color: #92400e; }
        .badge-belum { background: #fee2e2; color: #991b1b; }
        .terms { background: #fafaf9; border: 1px solid #f5f5f4; border-radius: 6px; padding: 14px; font-size: 11px; color: #57534e; margin-bottom: 30px; }
        .signatures { display: flex; justify-content: space-between; text-align: center; margin-top: 40px; }
        .sig-box { width: 200px; }
        .sig-space { height: 70px; }
        .sig-name { border-top: 1px solid #78716c; padding-top: 6px; font-weight: 600; font-size: 12px; }
        .no-print-bar { max-width: 800px; margin: 0 auto 16px auto; display: flex; justify-content: space-between; align-items: center; }
        .btn-print { background: #ea580c; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .btn-print:hover { background: #c2410c; }
        .btn-back { color: #57534e; text-decoration: none; font-size: 13px; }
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-card { box-shadow: none; padding: 0; }
            .no-print-bar { display: none; }
        }
    </style>
</head>
<body>
    @php
        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    @endphp

    <div class="no-print-bar">
        <a href="{{ route('orders.show', $order) }}" class="btn-back">&larr; Kembali ke Detail Pesanan</a>
        <button onclick="window.print()" class="btn-print">Cetak / Simpan PDF</button>
    </div>

    <div class="invoice-card">
        {{-- Header Kop --}}
        <div class="header">
            <div>
                <div class="brand-logo">{{ config('app.name', 'Yans Catering') }}</div>
                <div class="brand-sub">Layanan Catering Prasmanan, Nasi Box, Tumpeng & Paket Pernikahan</div>
                <div class="brand-sub">Jl. Catering No. 12, Bandung · Telp/WA: 0812-3456-7890</div>
            </div>
            <div class="invoice-title">
                <h1>NOTA PESANAN</h1>
                <div class="num">{{ $order->order_number }}</div>
                <div style="margin-top: 6px;">
                    @if ($order->payment_status === 'Lunas')
                        <span class="badge badge-lunas">LUNAS</span>
                    @elseif ($order->payment_status === 'DP')
                        <span class="badge badge-dp">DP TERBAYAR</span>
                    @else
                        <span class="badge badge-belum">BELUM BAYAR</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Info Acara & Pemesan --}}
        <div class="grid-info">
            <div class="info-col">
                <h3>Pemesan / Klien:</h3>
                <p><strong>{{ $order->customer->name }}</strong> ({{ ucfirst($order->customer->type) }})</p>
                <p>Telepon: {{ $order->customer->phone ?? '-' }}</p>
                <p>Alamat: {{ $order->customer->address ?? '-' }}</p>
            </div>
            <div class="info-col">
                <h3>Detail Acara & Pelaksanaan:</h3>
                <p><strong>Tanggal Acara:</strong> {{ $order->event_date->translatedFormat('l, d F Y') }}</p>
                <p><strong>Tanggal Pesan:</strong> {{ $order->order_date->translatedFormat('d F Y') }}</p>
                <p><strong>Lokasi Acara:</strong> {{ $order->event_location }}</p>
                <p><strong>Kanal Pesanan:</strong> {{ $order->isOffline() ? 'Pesanan Offline' : 'Pesanan Online' }} ({{ $order->source_label }})</p>
            </div>
        </div>

        {{-- Tabel Menu --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th>Deskripsi Menu / Paket Catering</th>
                    <th class="text-right" style="width: 130px;">Harga Satuan</th>
                    <th class="text-center" style="width: 70px;">Jumlah</th>
                    <th class="text-right" style="width: 140px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item->item_name }}</strong>
                            <div style="font-size: 11px; color: #78716c;">
                                {{ $item->package_id ? 'Paket Catering' : 'Menu Satuan / Box' }}
                            </div>
                        </td>
                        <td class="text-right">{{ $rp($item->unit_price) }}</td>
                        <td class="text-center"><strong>{{ number_format($item->quantity) }}</strong></td>
                        <td class="text-right"><strong>{{ $rp($item->subtotal) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary Total & Pembayaran --}}
        <div class="summary-box">
            <table class="summary-table">
                <tr class="total-row">
                    <td>Total Tagihan</td>
                    <td class="text-right">{{ $rp($order->total_amount) }}</td>
                </tr>
                <tr>
                    <td style="color: #059669;">Total Uang Diterima</td>
                    <td class="text-right" style="color: #059669; font-weight: 700;">- {{ $rp($order->paid_amount) }}</td>
                </tr>
                <tr style="border-top: 1px solid #e7e5e4;">
                    <td style="font-weight: 700; color: {{ $order->remaining_amount > 0 ? '#dc2626' : '#57534e' }};">
                        Sisa Tagihan (Piutang)
                    </td>
                    <td class="text-right" style="font-weight: 800; font-size: 14px; color: {{ $order->remaining_amount > 0 ? '#dc2626' : '#57534e' }};">
                        {{ $rp($order->remaining_amount) }}
                    </td>
                </tr>
            </table>
        </div>

        @if ($order->notes)
            <div style="margin-bottom: 20px; font-size: 11px; background: #fffbeb; padding: 10px; border-radius: 4px; border: 1px solid #fef3c7;">
                <strong>Catatan Khusus:</strong> {{ $order->notes }}
            </div>
        @endif

        {{-- Ketentuan --}}
        <div class="terms">
            <strong>Ketentuan Pemesanan:</strong>
            <ul style="margin: 4px 0 0 0; padding-left: 18px;">
                <li>Uang Muka (DP) minimal 30% dibayarkan saat pemesanan dikonfirmasi.</li>
                <li>Pelunasan dilakukan paling lambat H-1 sebelum tanggal pelaksanaan acara atau saat pengantaran sesuai kesepakatan.</li>
                <li>Pembayaran via transfer dapat dilakukan ke Rekening Resmi Yans Catering: <strong>BCA 123-456-7890 a.n. Yans Catering</strong>.</li>
            </ul>
        </div>

        {{-- Tanda Tangan --}}
        <div class="signatures">
            <div class="sig-box">
                <p>Pemesan / Klien,</p>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $order->customer->name }}</div>
            </div>
            <div class="sig-box">
                <p>Hormat Kami,<br>{{ config('app.name', 'Yans Catering') }}</p>
                <div class="sig-space"></div>
                <div class="sig-name">Bagian Administrasi / Kasir</div>
            </div>
        </div>
    </div>
</body>
</html>
