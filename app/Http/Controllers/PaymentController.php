<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['order.customer'])->latest('paid_at')->latest('id');

        if ($search = $request->string('q')->trim()->toString()) {
            $query->whereHas('order', function ($oq) use ($search) {
                $oq->where('order_number', 'like', "%{$search}%")
                   ->orWhereHas('customer', function ($cq) use ($search) {
                       $cq->where('name', 'like', "%{$search}%");
                   });
            });
        }

        if ($method = $request->string('method')->toString()) {
            if (array_key_exists($method, Payment::METHODS)) {
                $query->where('method', $method);
            }
        }

        if ($type = $request->string('type')->toString()) {
            if (array_key_exists($type, Payment::TYPES)) {
                $query->where('type', $type);
            }
        }

        if ($from = $request->string('from')->toString()) {
            $query->whereDate('paid_at', '>=', $from);
        }
        if ($to = $request->string('to')->toString()) {
            $query->whereDate('paid_at', '<=', $to);
        }

        $payments = $query->paginate(20)->withQueryString();

        // Rekap Metode Pembayaran (Sangat penting untuk catering yang banyak offline/kasir tunai)
        $methodStats = [
            'tunai' => (int) Payment::where('method', 'tunai')->sum('amount'),
            'qris' => (int) Payment::where('method', 'qris')->sum('amount'),
            'transfer' => (int) Payment::where('method', 'transfer')->sum('amount'),
            'debit' => (int) Payment::where('method', 'debit')->sum('amount'),
            'total' => (int) Payment::sum('amount'),
        ];

        return view('payments.index', [
            'payments' => $payments,
            'methodStats' => $methodStats,
            'methods' => Payment::METHODS,
            'types' => Payment::TYPES,
            'selectedMethod' => $method,
            'selectedType' => $type,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:' . implode(',', array_keys(Payment::TYPES)),
            'method' => 'required|in:' . implode(',', array_keys(Payment::METHODS)),
            'paid_at' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $order = Order::withSum('payments', 'amount')->findOrFail($validated['order_id']);

        $payment = Payment::create([
            'order_id' => $order->id,
            'type' => $validated['type'],
            'amount' => (int) $validated['amount'],
            'method' => $validated['method'],
            'paid_at' => $validated['paid_at'],
            'notes' => $validated['notes'],
        ]);

        // Auto update order status if full payment received and status was 'baru'
        $newPaid = $order->paid_amount + $payment->amount;
        if ($newPaid >= $order->total_amount && in_array($order->status, ['baru', 'dikonfirmasi'], true)) {
            $order->update(['status' => 'diproses']);
        }

        return back()->with('success', "Pembayaran sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " berhasil dicatat.");
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['order.customer']);
        $payment->order->loadSum('payments', 'amount');

        return view('payments.receipt', [
            'payment' => $payment,
        ]);
    }

    public function destroy(Payment $payment)
    {
        $orderId = $payment->order_id;
        $payment->delete();

        return back()->with('success', "Data pembayaran berhasil dihapus.");
    }
}
