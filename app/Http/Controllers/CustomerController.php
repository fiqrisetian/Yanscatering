<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('orders')
            ->withSum(['orders' => fn ($q) => $q->active()], 'total_amount');

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($type = $request->string('type')->toString()) {
            if (in_array($type, ['perorangan', 'instansi'], true)) {
                $query->where('type', $type);
            }
        }

        $customers = $query->latest('id')->paginate(15)->withQueryString();

        $stats = [
            'total' => Customer::count(),
            'perorangan' => Customer::where('type', 'perorangan')->count(),
            'instansi' => Customer::where('type', 'instansi')->count(),
        ];

        return view('customers.index', [
            'customers' => $customers,
            'type' => $type,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'type' => 'required|in:perorangan,instansi',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', "Pelanggan {$customer->name} berhasil ditambahkan.");
    }

    public function show(Customer $customer)
    {
        $customer->load(['orders' => fn ($q) => $q->withSum('payments', 'amount')->latest('event_date')]);

        return view('customers.show', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'type' => 'required|in:perorangan,instansi',
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);

        return back()->with('success', "Data pelanggan {$customer->name} berhasil diperbarui.");
    }

    public function destroy(Customer $customer)
    {
        if ($customer->orders()->exists()) {
            return back()->with('error', "Pelanggan tidak dapat dihapus karena memiliki riwayat pesanan.");
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', "Pelanggan berhasil dihapus.");
    }
}
