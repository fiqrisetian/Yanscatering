<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kuitansi Pembayaran #{{ $payment->id }} - {{ config('app.name', 'Yans Catering') }}</title>
    <style>
        @page { size: A5 landscape; margin: 1cm; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #1c1917; background: #f5f5f4; margin: 0; padding: 20px; font-size: 13px; line-height: 1.5; }
        .receipt-card { background: #fff; max-width: 650px; margin: 0 auto; padding: 30px; border-radius: 8px; border: 1px solid #e7e5e4; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px dashed #ea580c; padding-bottom: 15px; margin-bottom: 20px; }
        .brand-name { font-size: 20px; font-weight: 800; color: #ea580c; }
        .brand-sub { font-size: 11px; color: #78716c; }
        .title { text-align: right; }
        .title h1 { margin: 0; font-size: 16px; color: #1c1917; text-transform: uppercase; }
        .title .num { font-size: 12px; font-weight: 700; color: #78716c; margin-top: 2px; }
        .row { display: flex; margin-bottom: 12px; }
        .label { width: 170px; color: #78716c; font-size: 12px; }
        .val { flex: 1; font-weight: 600; color: #1c1917; }
        .amount-box { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px 18px; border-radius: 6px; display: inline-block; font-size: 20px; font-weight: 800; color: #15803d; margin: 15px 0; }
        .signatures { display: flex; justify-content: space-between; margin-top: 30px; text-align: center; }
        .sig-box { width: 180px; }
        .sig-space { height: 60px; }
        .sig-line { border-top: 1px solid #78716c; font-size: 11px; font-weight: 600; padding-top: 4px; }
        .no-print { max-width: 650px; margin: 0 auto 12px auto; display: flex; justify-content: space-between; align-items: center; }
        .btn-print { background: #ea580c; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt-card { border: none; box-shadow: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    @php
        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    @endphp

    <div class="no-print">
        <a href="{{ route('orders.show', $payment->order) }}" style="color: #78716c; text-decoration: none; font-size: 12px;">&larr; Kembali ke Pesanan</a>
        <button onclick="window.print()" class="btn-print">Cetak Kuitansi</button>
    </div>

    <div class="receipt-card">
        <div class="header">
            <div>
                <div class="brand-name">{{ config('app.name', 'Yans Catering') }}</div>
                <div class="brand-sub">Layanan Catering Prasmanan, Nasi Box & Tumpeng Bandung</div>
            </div>
            <div class="title">
                <h1>TANDA TERIMA / KUITANSI</h1>
                <div class="num">No. KW-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        <div class="row">
            <div class="label">Telah Diterima Dari:</div>
            <div class="val">{{ $payment->order->customer->name }} ({{ ucfirst($payment->order->customer->type) }})</div>
        </div>

        <div class="row">
            <div class="label">Untuk Pembayaran:</div>
            <div class="val">
                {{ $payment->type_label }} Pesanan No. <strong>{{ $payment->order->order_number }}</strong>
                <div style="font-size: 11px; color: #78716c; font-weight: normal; margin-top: 2px;">
                    Acara: {{ $payment->order->event_date->translatedFormat('d F Y') }} di {{ $payment->order->event_location }}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="label">Metode Pembayaran:</div>
            <div class="val">{{ $payment->method_label }}</div>
        </div>

        <div class="row">
            <div class="label">Tanggal Diterima:</div>
            <div class="val">{{ $payment->paid_at->translatedFormat('l, d F Y') }}</div>
        </div>

        @if ($payment->notes)
            <div class="row">
                <div class="label">Keterangan:</div>
                <div class="val" style="font-weight: normal; font-size: 12px; color: #57534e;">{{ $payment->notes }}</div>
            </div>
        @endif

        <div style="text-align: center;">
            <div class="amount-box">
                {{ $rp($payment->amount) }}
            </div>
        </div>

        <div style="font-size: 11px; color: #78716c; border-top: 1px dotted #e7e5e4; padding-top: 8px;">
            Total Tagihan Pesanan: {{ $rp($payment->order->total_amount) }} · Total Sudah Terbayar: {{ $rp($payment->order->paid_amount) }} · 
            <strong>Sisa Tagihan: {{ $rp($payment->order->remaining_amount) }}</strong>
        </div>

        <div class="signatures">
            <div class="sig-box">
                <p>Penyetor,</p>
                <div class="sig-space"></div>
                <div class="sig-line">{{ $payment->order->customer->name }}</div>
            </div>
            <div class="sig-box">
                <p>Penerima / Kasir {{ config('app.name', 'Yans Catering') }},</p>
                <div class="sig-space"></div>
                <div class="sig-line">Bagian Keuangan</div>
            </div>
        </div>
    </div>
</body>
</html>
