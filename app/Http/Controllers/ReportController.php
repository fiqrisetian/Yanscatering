<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->string('from', now()->startOfMonth()->toDateString())->toString();
        $to = $request->string('to', now()->endOfMonth()->toDateString())->toString();

        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate = Carbon::parse($to)->endOfDay();

        // Orders within range
        $ordersInRange = Order::active()
            ->whereBetween('order_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->with(['customer'])
            ->withSum('payments', 'amount')
            ->get();

        $totalOrderCount = $ordersInRange->count();
        $totalOrderValue = (int) $ordersInRange->sum('total_amount');

        // Payments received within range
        $paymentsInRange = Payment::whereBetween('paid_at', [$fromDate->toDateString(), $toDate->toDateString()])->get();
        $totalReceived = (int) $paymentsInRange->sum('amount');

        // Offline vs Online Analysis
        $offlineOrders = $ordersInRange->filter(fn (Order $o) => $o->isOffline());
        $onlineOrders = $ordersInRange->filter(fn (Order $o) => !$o->isOffline());

        $offlineCount = $offlineOrders->count();
        $offlineValue = (int) $offlineOrders->sum('total_amount');
        $offlineAvg = $offlineCount > 0 ? (int) round($offlineValue / $offlineCount) : 0;
        $offlinePercent = $totalOrderCount > 0 ? round(($offlineCount / $totalOrderCount) * 100, 1) : 0;

        $onlineCount = $onlineOrders->count();
        $onlineValue = (int) $onlineOrders->sum('total_amount');
        $onlineAvg = $onlineCount > 0 ? (int) round($onlineValue / $onlineCount) : 0;
        $onlinePercent = $totalOrderCount > 0 ? round(($onlineCount / $totalOrderCount) * 100, 1) : 0;

        // Payment method breakdown in period
        $methodBreakdown = $paymentsInRange->groupBy('method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => (int) $group->sum('amount'),
            ];
        });

        // Source breakdown
        $sourceBreakdown = $ordersInRange->groupBy('source')->map(function ($group, $src) {
            return [
                'label' => Order::SOURCES[$src] ?? ucfirst($src),
                'count' => $group->count(),
                'total' => (int) $group->sum('total_amount'),
            ];
        });

        // Outstanding receivables (Piutang)
        $receivables = Order::active()
            ->with(['customer'])
            ->withSum('payments', 'amount')
            ->get()
            ->filter(fn (Order $o) => $o->remaining_amount > 0)
            ->sortBy('event_date');

        $totalReceivables = (int) $receivables->sum('remaining_amount');

        return view('reports.index', [
            'from' => $fromDate->toDateString(),
            'to' => $toDate->toDateString(),
            'totalOrderCount' => $totalOrderCount,
            'totalOrderValue' => $totalOrderValue,
            'totalReceived' => $totalReceived,
            'totalReceivables' => $totalReceivables,
            'offlineCount' => $offlineCount,
            'offlineValue' => $offlineValue,
            'offlineAvg' => $offlineAvg,
            'offlinePercent' => $offlinePercent,
            'onlineCount' => $onlineCount,
            'onlineValue' => $onlineValue,
            'onlineAvg' => $onlineAvg,
            'onlinePercent' => $onlinePercent,
            'methodBreakdown' => $methodBreakdown,
            'sourceBreakdown' => $sourceBreakdown,
            'receivables' => $receivables,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $from = $request->string('from', now()->startOfMonth()->toDateString())->toString();
        $to = $request->string('to', now()->endOfMonth()->toDateString())->toString();

        $orders = Order::active()
            ->whereBetween('order_date', [$from, $to])
            ->with(['customer'])
            ->withSum('payments', 'amount')
            ->orderBy('order_date')
            ->get();

        $filename = "laporan-pesanan-catering-{$from}-sd-{$to}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['No Pesanan', 'Pelanggan', 'Tipe Pelanggan', 'Kanal (Offline/Online)', 'Sumber', 'Tgl Pesan', 'Tgl Acara', 'Lokasi', 'Total Tagihan', 'Uang Diterima', 'Sisa Piutang', 'Status Pembayaran', 'Status Pesanan']);

            foreach ($orders as $o) {
                fputcsv($handle, [
                    $o->order_number,
                    $o->customer->name,
                    ucfirst($o->customer->type),
                    $o->channel,
                    $o->source_label,
                    $o->order_date->format('Y-m-d'),
                    $o->event_date->format('Y-m-d'),
                    $o->event_location,
                    $o->total_amount,
                    $o->paid_amount,
                    $o->remaining_amount,
                    $o->payment_status,
                    Order::STATUSES[$o->status] ?? $o->status,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
