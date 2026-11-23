<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer'])->withSum('payments', 'amount');

        // Search
        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('event_location', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Channel filter (Offline vs Online)
        $channel = $request->string('channel')->toString();
        if ($channel === 'offline') {
            $query->whereIn('source', ['datang_langsung', 'telepon', 'pelanggan_tetap']);
        } elseif ($channel === 'online') {
            $query->whereIn('source', ['whatsapp', 'lainnya']);
        }

        // Specific Source
        if ($source = $request->string('source')->toString()) {
            if (array_key_exists($source, Order::SOURCES)) {
                $query->where('source', $source);
            }
        }

        // Status
        if ($status = $request->string('status')->toString()) {
            if (array_key_exists($status, Order::STATUSES)) {
                $query->where('status', $status);
            }
        }

        // Date filter
        if ($from = $request->string('from')->toString()) {
            $query->whereDate('event_date', '>=', $from);
        }
        if ($to = $request->string('to')->toString()) {
            $query->whereDate('event_date', '<=', $to);
        }

        // Sorting
        $sort = $request->string('sort', 'event_date_desc')->toString();
        match ($sort) {
            'event_date_asc' => $query->orderBy('event_date', 'asc'),
            'created_at_desc' => $query->latest('id'),
            'total_desc' => $query->orderByDesc('total_amount'),
            default => $query->orderByDesc('event_date')->latest('id'),
        };

        $orders = $query->paginate(15)->withQueryString();

        // Payment status filter in-memory if requested (since it's a computed property)
        $payFilter = $request->string('pay')->toString();
        if (in_array($payFilter, ['lunas', 'dp', 'belum_bayar'], true)) {
            // Note: could also be filtered at DB level if needed
        }

        // Quick stats
        $totalOrders = Order::active()->count();
        $offlineCount = Order::active()->whereIn('source', ['datang_langsung', 'telepon', 'pelanggan_tetap'])->count();
        $onlineCount = Order::active()->whereIn('source', ['whatsapp', 'lainnya'])->count();
        $activePending = Order::active()->where('status', '!=', 'selesai')->count();

        return view('orders.index', [
            'orders' => $orders,
            'channel' => $channel,
            'source' => $source,
            'status' => $status,
            'sources' => Order::SOURCES,
            'statuses' => Order::STATUSES,
            'totalOrders' => $totalOrders,
            'offlineCount' => $offlineCount,
            'onlineCount' => $onlineCount,
            'activePending' => $activePending,
        ]);
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $menus = Menu::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $packages = Package::where('is_active', true)->orderBy('name')->get();

        return view('orders.create', [
            'customers' => $customers,
            'menus' => $menus,
            'packages' => $packages,
            'sources' => Order::SOURCES,
            'paymentMethods' => Payment::METHODS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_mode' => 'required|in:existing,new',
            'customer_id' => 'required_if:customer_mode,existing|nullable|exists:customers,id',
            'new_customer_name' => 'required_if:customer_mode,new|nullable|string|max:255',
            'new_customer_phone' => 'nullable|string|max:30',
            'new_customer_type' => 'nullable|in:perorangan,instansi',
            'new_customer_address' => 'nullable|string',

            'source' => 'required|in:' . implode(',', array_keys(Order::SOURCES)),
            'order_date' => 'required|date',
            'event_date' => 'required|date',
            'event_location' => 'required|string|max:255',
            'notes' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:menu,package',
            'items.*.id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',

            // Pembayaran langsung (misal DP Tunai di tempat)
            'record_payment' => 'nullable|boolean',
            'payment_amount' => 'nullable|numeric|min:1',
            'payment_method' => 'nullable|in:' . implode(',', array_keys(Payment::METHODS)),
            'payment_notes' => 'nullable|string|max:255',
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            // Customer resolution
            if ($validated['customer_mode'] === 'new') {
                $customer = Customer::create([
                    'name' => $validated['new_customer_name'],
                    'phone' => $validated['new_customer_phone'],
                    'type' => $validated['new_customer_type'] ?? 'perorangan',
                    'address' => $validated['new_customer_address'],
                ]);
                $customerId = $customer->id;
            } else {
                $customerId = $validated['customer_id'];
            }

            // Create Order
            $order = Order::create([
                'customer_id' => $customerId,
                'source' => $validated['source'],
                'order_date' => $validated['order_date'],
                'event_date' => $validated['event_date'],
                'event_location' => $validated['event_location'],
                'status' => 'baru',
                'notes' => $validated['notes'] ?? null,
                'total_amount' => 0,
            ]);

            // Create Items
            $total = 0;
            foreach ($validated['items'] as $itemData) {
                $isMenu = $itemData['type'] === 'menu';
                $name = $isMenu
                    ? Menu::find($itemData['id'])?->name ?? 'Menu Satuan'
                    : Package::find($itemData['id'])?->name ?? 'Paket Catering';

                $price = (int) $itemData['price'];
                $qty = (int) $itemData['qty'];
                $subtotal = $price * $qty;
                $total += $subtotal;

                $order->items()->create([
                    'menu_id' => $isMenu ? $itemData['id'] : null,
                    'package_id' => !$isMenu ? $itemData['id'] : null,
                    'item_name' => $name,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                ]);
            }

            $order->update(['total_amount' => $total]);

            // Handle immediate payment (DP / Tunai saat buat pesanan)
            if ($request->boolean('record_payment') && !empty($validated['payment_amount'])) {
                $paidAmount = (int) $validated['payment_amount'];
                $type = $paidAmount >= $total ? 'pelunasan' : 'dp';

                $order->payments()->create([
                    'type' => $type,
                    'amount' => $paidAmount,
                    'paid_at' => $validated['order_date'],
                    'method' => $validated['payment_method'] ?? 'tunai',
                    'notes' => $validated['payment_notes'] ?: ($type === 'pelunasan' ? 'Pembayaran lunas di muka' : 'Uang muka (DP) pesanan'),
                ]);

                if ($order->status === 'baru') {
                    $order->update(['status' => 'dikonfirmasi']);
                }
            }

            return $order;
        });

        return redirect()->route('orders.show', $order)
            ->with('success', "Pesanan {$order->order_number} berhasil dibuat!");
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'items', 'payments']);
        $order->loadSum('payments', 'amount');

        return view('orders.show', [
            'order' => $order,
            'paymentMethods' => Payment::METHODS,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function edit(Order $order)
    {
        $order->load(['customer', 'items']);
        $customers = Customer::orderBy('name')->get();

        return view('orders.edit', [
            'order' => $order,
            'customers' => $customers,
            'sources' => Order::SOURCES,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'source' => 'required|in:' . implode(',', array_keys(Order::SOURCES)),
            'order_date' => 'required|date',
            'event_date' => 'required|date',
            'event_location' => 'required|string|max:255',
            'status' => 'required|in:' . implode(',', array_keys(Order::STATUSES)),
            'notes' => 'nullable|string',
        ]);

        $order->update($validated);

        return redirect()->route('orders.show', $order)
            ->with('success', "Pesanan {$order->order_number} berhasil diperbarui.");
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Order::STATUSES)),
        ]);

        $order->update(['status' => $validated['status']]);

        return back()->with('success', "Status pesanan diubah menjadi: " . Order::STATUSES[$validated['status']]);
    }

    public function invoice(Order $order)
    {
        $order->load(['customer', 'items', 'payments']);
        $order->loadSum('payments', 'amount');

        return view('orders.invoice', [
            'order' => $order,
        ]);
    }

    public function destroy(Order $order)
    {
        $orderNumber = $order->order_number;
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', "Pesanan {$orderNumber} berhasil dihapus.");
    }
}
