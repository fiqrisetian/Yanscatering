<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CateringAppTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->user = User::first();
    }

    public function test_homepage_redirects_to_the_admin_dashboard(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/admin/dashboard');
        $this->followingRedirects()->get('/')->assertOk()->assertSee('Masuk Admin');

        $dashboard = $this->actingAs($this->user)->get('/');
        $dashboard->assertRedirect('/admin/dashboard');
    }

    public function test_public_inquiry_endpoint_is_no_longer_available(): void
    {
        $this->post('/inquiry', [])->assertNotFound();
    }

    public function test_dashboard_renders_successfully_with_offline_stats(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Mayoritas Pesanan Offline');
        $response->assertSee('Pesanan Terdekat');
    }

    public function test_orders_index_and_offline_filter(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/orders');
        $response->assertStatus(200);
        $response->assertSee('Pesanan Catering');

        $responseOffline = $this->actingAs($this->user)->get('/admin/orders?channel=offline');
        $responseOffline->assertStatus(200);
        $responseOffline->assertSee('Khusus Pesanan Offline');
    }

    public function test_can_create_offline_order_with_walk_in_customer_and_cash_dp(): void
    {
        $menu = Menu::first();

        $payload = [
            'customer_mode' => 'new',
            'new_customer_name' => 'Ibu Siti Masitoh',
            'new_customer_phone' => '081299887766',
            'new_customer_type' => 'perorangan',
            'new_customer_address' => 'Jl. Dago No. 100 Bandung',

            'source' => 'datang_langsung',
            'order_date' => now()->toDateString(),
            'event_date' => now()->addDays(5)->toDateString(),
            'event_location' => 'Ambil Sendiri di Outlet Yans Catering',
            'notes' => 'Pesanan offline datang ke outlet, DP tunai',

            'items' => [
                [
                    'type' => 'menu',
                    'id' => $menu->id,
                    'price' => $menu->price,
                    'qty' => 50,
                ],
            ],

            'record_payment' => 1,
            'payment_amount' => 500000,
            'payment_method' => 'tunai',
            'payment_notes' => 'DP Tunai di kasir outlet',
        ];

        $response = $this->actingAs($this->user)->post('/admin/orders', $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('customers', [
            'name' => 'Ibu Siti Masitoh',
            'phone' => '081299887766',
        ]);

        $this->assertDatabaseHas('orders', [
            'source' => 'datang_langsung',
            'event_location' => 'Ambil Sendiri di Outlet Yans Catering',
        ]);

        $this->assertDatabaseHas('payments', [
            'method' => 'tunai',
            'amount' => 500000,
        ]);
    }

    public function test_order_detail_and_invoice(): void
    {
        $order = Order::first();

        $responseDetail = $this->actingAs($this->user)->get("/admin/orders/{$order->id}");
        $responseDetail->assertStatus(200);
        $responseDetail->assertSee($order->order_number);

        $responseInvoice = $this->actingAs($this->user)->get("/admin/orders/{$order->id}/invoice");
        $responseInvoice->assertStatus(200);
        $responseInvoice->assertSee('NOTA PESANAN');
        $responseInvoice->assertSee($order->order_number);
    }

    public function test_customers_crud_views(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/customers');
        $response->assertStatus(200);
        $response->assertSee('Data Pelanggan');

        $customer = Customer::first();
        $responseShow = $this->actingAs($this->user)->get("/admin/customers/{$customer->id}");
        $responseShow->assertStatus(200);
        $responseShow->assertSee($customer->name);
    }

    public function test_menus_and_packages_view(): void
    {
        $responseMenu = $this->actingAs($this->user)->get('/admin/menus?tab=menu');
        $responseMenu->assertStatus(200);
        $responseMenu->assertSee('Menu Satuan');

        $responsePkg = $this->actingAs($this->user)->get('/admin/menus?tab=package');
        $responsePkg->assertStatus(200);
        $responsePkg->assertSee('Paket Catering');
    }

    public function test_payments_index_and_receipt(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/payments');
        $response->assertStatus(200);
        $response->assertSee('Kas Tunai (Offline)');

        $payment = Payment::first();
        if ($payment) {
            $responseReceipt = $this->actingAs($this->user)->get("/admin/payments/{$payment->id}/receipt");
            $responseReceipt->assertStatus(200);
            $responseReceipt->assertSee('TANDA TERIMA / KUITANSI');
        }
    }

    public function test_reports_and_csv_export(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/reports');
        $response->assertStatus(200);
        $response->assertSee('Laporan Penjualan');
        $response->assertSee('Pesanan Offline vs Online');

        $responseExport = $this->actingAs($this->user)->get('/admin/reports/export');
        $responseExport->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $responseExport->headers->get('content-type'));
    }
}
