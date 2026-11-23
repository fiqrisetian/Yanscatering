<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const PERIODS = [
        'bulan_ini' => 'Bulan ini',
        'bulan_lalu' => 'Bulan lalu',
        '3_bulan' => '3 bulan terakhir',
        'tahun_ini' => 'Tahun ini',
    ];

    private const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function index(Request $request)
    {
        $period = $request->string('periode')->toString();
        $period = array_key_exists($period, self::PERIODS) ? $period : 'bulan_ini';
        [$start, $end, $prevStart, $prevEnd] = $this->range($period);

        $between = fn ($q, Carbon $a, Carbon $b, string $col = 'order_date') =>
            $q->whereBetween($col, [$a->toDateString(), $b->toDateString()]);

        // ---- Kartu ringkasan -------------------------------------------------
        $periodOrders = fn () => $between(Order::active(), $start, $end);

        $orderCount = $periodOrders()->count();
        $prevCount = $between(Order::active(), $prevStart, $prevEnd)->count();
        $orderValue = (int) $periodOrders()->sum('total_amount');
        $received = (int) $between(Payment::query(), $start, $end, 'paid_at')->sum('amount');

        // Piutang: sisa tagihan semua pesanan aktif yang belum lunas (tidak terbatas periode)
        $open = Order::active()->withSum('payments', 'amount')->get()
            ->filter(fn (Order $o) => $o->remaining_amount > 0);
        $outstanding = (int) $open->sum('remaining_amount');

        // Analisis Kanal: Pesanan Offline vs Online
        $offlineCount = $periodOrders()->whereIn('source', ['datang_langsung', 'telepon', 'pelanggan_tetap'])->count();
        $onlineCount = $periodOrders()->whereIn('source', ['whatsapp', 'lainnya'])->count();
        $offlineValue = (int) $periodOrders()->whereIn('source', ['datang_langsung', 'telepon', 'pelanggan_tetap'])->sum('total_amount');
        $onlineValue = (int) $periodOrders()->whereIn('source', ['whatsapp', 'lainnya'])->sum('total_amount');
        $offlinePercent = $orderCount > 0 ? (int) round(($offlineCount / $orderCount) * 100) : 0;
        $onlinePercent = $orderCount > 0 ? 100 - $offlinePercent : 0;

        $stats = [
            'order_count' => $orderCount,
            'order_delta' => $prevCount > 0 ? (int) round(($orderCount - $prevCount) / $prevCount * 100) : null,
            'order_value' => $orderValue,
            'received' => $received,
            'collection_rate' => $orderValue > 0 ? min(100, (int) round($received / $orderValue * 100)) : 0,
            'outstanding' => $outstanding,
            'outstanding_count' => $open->count(),
            'offline_count' => $offlineCount,
            'offline_value' => $offlineValue,
            'offline_percent' => $offlinePercent,
            'online_count' => $onlineCount,
            'online_value' => $onlineValue,
            'online_percent' => $onlinePercent,
        ];

        // ---- Grafik 6 bulan terakhir ----------------------------------------
        $months = collect(range(5, 0))->map(fn ($i) => now()->startOfMonth()->subMonthsNoOverflow($i));
        $from = $months->first()->toDateString();

        $valueByMonth = Order::active()->where('order_date', '>=', $from)->get(['order_date', 'total_amount'])
            ->groupBy(fn ($o) => $o->order_date->format('Y-m'))->map->sum('total_amount');
        $paidByMonth = Payment::where('paid_at', '>=', $from)->get(['paid_at', 'amount'])
            ->groupBy(fn ($p) => $p->paid_at->format('Y-m'))->map->sum('amount');

        $chart = [
            'labels' => $months->map(fn ($m) => self::MONTHS[$m->month - 1])->all(),
            'value' => $months->map(fn ($m) => round(($valueByMonth[$m->format('Y-m')] ?? 0) / 1_000_000, 1))->all(),
            'received' => $months->map(fn ($m) => round(($paidByMonth[$m->format('Y-m')] ?? 0) / 1_000_000, 1))->all(),
        ];

        // ---- Sumber pesanan --------------------------------------------------
        $bySource = $periodOrders()->select('source', DB::raw('COUNT(*) as c'))->groupBy('source')->pluck('c', 'source');
        $sources = [
            'labels' => array_values(Order::SOURCES),
            'data' => collect(array_keys(Order::SOURCES))->map(fn ($k) => (int) ($bySource[$k] ?? 0))->all(),
        ];

        // ---- Pesanan terdekat ------------------------------------------------
        $upcoming = Order::active()->where('status', '!=', 'selesai')
            ->whereDate('event_date', '>=', today())
            ->with('customer')->withSum('payments', 'amount')
            ->orderBy('event_date')->limit(6)->get();

        // ---- Menu / paket terlaris ------------------------------------------
        $topItems = OrderItem::select('item_name', DB::raw('SUM(quantity) as qty'))
            ->whereHas('order', fn ($q) => $between($q->active(), $start, $end))
            ->groupBy('item_name')->orderByDesc('qty')->limit(5)->get();

        // ---- Perlu tindak lanjut --------------------------------------------
        $todo = [
            ['text' => 'Acara hari ini', 'count' => Order::active()->whereDate('event_date', today())->where('status', '!=', 'selesai')->count(), 'color' => 'bg-orange-500'],
            ['text' => 'Pesanan baru belum dikonfirmasi', 'count' => Order::where('status', 'baru')->count(), 'color' => 'bg-sky-500'],
            ['text' => 'Acara H-3 belum dikonfirmasi', 'count' => Order::where('status', 'baru')->whereBetween('event_date', [today()->toDateString(), today()->addDays(3)->toDateString()])->count(), 'color' => 'bg-amber-500'],
            ['text' => 'Pesanan belum lunas', 'count' => $open->count(), 'color' => 'bg-red-500'],
        ];

        // ---- Pembayaran terbaru ---------------------------------------------
        $recentPayments = Payment::with('order.customer')->latest('paid_at')->latest('id')->limit(5)->get();

        return view('dashboard', [
            'periods' => self::PERIODS,
            'period' => $period,
            'stats' => $stats,
            'chart' => $chart,
            'sources' => $sources,
            'upcoming' => $upcoming,
            'topItems' => $topItems,
            'todo' => $todo,
            'recentPayments' => $recentPayments,
        ]);
    }

    /** @return array{Carbon, Carbon, Carbon, Carbon} [mulai, selesai, mulai pembanding, selesai pembanding] */
    private function range(string $period): array
    {
        $n = now();

        return match ($period) {
            'bulan_lalu' => [
                $n->copy()->subMonthNoOverflow()->startOfMonth(), $n->copy()->subMonthNoOverflow()->endOfMonth(),
                $n->copy()->subMonthsNoOverflow(2)->startOfMonth(), $n->copy()->subMonthsNoOverflow(2)->endOfMonth(),
            ],
            '3_bulan' => [
                $n->copy()->subMonthsNoOverflow(2)->startOfMonth(), $n->copy()->endOfMonth(),
                $n->copy()->subMonthsNoOverflow(5)->startOfMonth(), $n->copy()->subMonthsNoOverflow(3)->endOfMonth(),
            ],
            'tahun_ini' => [
                $n->copy()->startOfYear(), $n->copy()->endOfYear(),
                $n->copy()->subYear()->startOfYear(), $n->copy()->subYear()->endOfYear(),
            ],
            default => [
                $n->copy()->startOfMonth(), $n->copy()->endOfMonth(),
                $n->copy()->subMonthNoOverflow()->startOfMonth(), $n->copy()->subMonthNoOverflow()->endOfMonth(),
            ],
        };
    }
}
