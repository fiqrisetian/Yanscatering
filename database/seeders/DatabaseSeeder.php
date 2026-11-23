<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Data contoh untuk Yans Catering.
 * Disesuaikan dengan karakteristik usaha catering nyata:
 * - Mayoritas pesanan adalah offline (Datang Langsung, Telepon, Pelanggan Tetap).
 * - Metode pembayaran mencakup Tunai (Cash), Transfer, QRIS, dan Kartu Debit.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(42);

        // Hapus data lama jika ada
        Schema::disableForeignKeyConstraints();
        Payment::truncate();
        OrderItem::truncate();
        Order::truncate();
        Package::truncate();
        Menu::truncate();
        Customer::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Admin Akun
        User::updateOrCreate(
            ['email' => 'admin@catering.test'],
            ['name' => 'Admin Yans Catering', 'password' => Hash::make('password')]
        );

        // 2. Data Pelanggan (Perorangan & Instansi)
        $customerList = [
            ['Ibu Hj. Ratna Dewi', 'perorangan', '081223344551', 'Jl. Buah Batu No. 45, Bandung'],
            ['PT Maju Bersama Properti', 'instansi', '081398765432', 'Gedung Wisma Asia Lt. 4, Bandung'],
            ['Bapak Hendra Gunawan', 'perorangan', '081812345678', 'Komp. Antapani Mas Blok C2, Bandung'],
            ['Yayasan Pendidikan Al-Ikhlas', 'instansi', '082155667788', 'Jl. Soekarno Hatta No. 120, Bandung'],
            ['Ibu Sari Handayani', 'perorangan', '085712398765', 'Jl. Riau No. 88, Bandung'],
            ['CV Karya Mandiri Teknik', 'instansi', '081290001122', 'Kawasan Industri Cimahi No. 12'],
            ['Bapak Agus Salim', 'perorangan', '087823456789', 'Jl. Gegerkalong Hilir No. 17, Bandung'],
            ['Dinda Permata Putri', 'perorangan', '089611223344', 'Jl. Tubagus Ismail No. 3, Bandung'],
            ['Dinas Kesehatan Kota Bandung', 'instansi', '081122334455', 'Jl. Supratman No. 73, Bandung'],
            ['Bapak Mochammad Rizki', 'perorangan', '081388990011', 'Jl. Pasir Kaliki No. 102, Bandung'],
        ];

        $customers = collect($customerList)->map(fn ($c) => Customer::create([
            'name' => $c[0],
            'type' => $c[1],
            'phone' => $c[2],
            'address' => $c[3],
        ]));

        // 3. Menu Satuan / Ala Carte
        $menusData = [
            ['Nasi Box Ayam Bakar Madu', 'Nasi Box', 32000],
            ['Nasi Box Rendang Daging Sapi', 'Nasi Box', 38000],
            ['Nasi Box Ayam Geprek Sambal Korek', 'Nasi Box', 28000],
            ['Nasi Liwet Komplit Bakul', 'Tradisional', 45000],
            ['Snack Box Premium (3 Kue + Air Mineral)', 'Snack', 22000],
            ['Snack Box Regular (2 Kue + Teh Kotak)', 'Snack', 16000],
            ['Tumpeng Mini Special Yans', 'Tumpeng', 185000],
            ['Tumpeng Besar Komplit (20 Porsi)', 'Tumpeng', 1250000],
            ['Kambing Guling Utuh + Lontong', 'Spesial', 3200000],
            ['Es Campur / Es Buah Prasmanan (per pax)', 'Minuman', 15000],
        ];

        $sellables = collect();
        foreach ($menusData as $m) {
            $menu = Menu::create(['name' => $m[0], 'category' => $m[1], 'price' => $m[2], 'is_active' => true]);
            $sellables->push(['menu_id' => $menu->id, 'package_id' => null, 'name' => $menu->name, 'price' => $menu->price]);
        }

        // 4. Paket Catering
        $packagesData = [
            ['Prasmanan Paket Melati (100 Pax)', 'Menu prasmanan lengkap 100 porsi: Nasi putih, sup kimlo, ayam suwir, rolade daging, asinan, kerupuk, buah potong, es doger.', 6500000],
            ['Prasmanan Paket Mawar (200 Pax)', 'Menu prasmanan mewah 200 porsi: Nasi, 2 menu daging/ayam, ikan kakap asam manis, sup jamur, salad buah, puding, minuman segar.', 14000000],
            ['Paket Pernikahan Exclusive (500 Pax)', 'Paket catering pernikahan lengkap: Prasmanan utama 500 pax, 4 stall makanan pondokan, dekorasi buffet, waiter & service.', 38000000],
            ['Paket Rapat Kantor (50 Pax)', '50 porsi Nasi Box VIP + 50 Snack Box Coffee Break pagi & sore.', 2750000],
            ['Paket Aqiqah Hemat (2 Kambing Masak)', '2 ekor kambing olah gulai & sate + 100 paket nasi box aqiqah lengkap kartu ucapan.', 4200000],
        ];

        foreach ($packagesData as $p) {
            $pkg = Package::create(['name' => $p[0], 'description' => $p[1], 'price' => $p[2], 'is_active' => true]);
            $sellables->push(['menu_id' => null, 'package_id' => $pkg->id, 'name' => $pkg->name, 'price' => $pkg->price]);
        }

        // 5. Generate Pesanan Realistis
        // Sesuai catatan: "Banyaknya pesanan offline"
        // Bobot:
        // - Datang Langsung (Offline Walk-in ke kantor/outlet): 48%
        // - Telepon (Offline by phone): 20%
        // - Pelanggan Tetap (Offline repeat order/kantor): 16%
        // - WhatsApp (Online chat): 12%
        // - Lainnya: 4%
        // Total Offline ~84%!
        $locations = [
            'Bandung (Kantor Yans Catering - Ambil Sendiri)',
            'Gedung Balai Sartika, Suryalaya Bandung',
            'Graha Batununggal Indah, Buahbatu',
            'Gedung Pussenif TNI AD, Supratman Bandung',
            'Kantor Pemda Gedung Sate Lt. 3',
            'Rumah Duka & Resepsi Jl. Riau Bandung',
            'Hotel Horison Pasteur Bandung',
            'Komp. Antapani Mas Blok C2 Bandung',
            'Aula Kampus ITB Ganesha Bandung',
            'Masjid Agung Pusdai Bandung',
        ];

        for ($i = 0; $i < 65; $i++) {
            $orderDate = now()->subDays(mt_rand(0, 160))->startOfDay();
            $eventDate = $orderDate->copy()->addDays(mt_rand(2, 28));
            $isPast = $eventDate->lt(today());

            $status = $isPast
                ? (mt_rand(1, 15) === 1 ? 'dibatalkan' : 'selesai')
                : ['baru', 'dikonfirmasi', 'dikonfirmasi', 'diproses'][mt_rand(0, 3)];

            $roll = mt_rand(1, 100);
            if ($roll <= 48) {
                $source = 'datang_langsung';
                $notes = 'Pesanan offline datang langsung ke outlet. Pelanggan berkonsultasi menu tatap muka.';
            } elseif ($roll <= 68) {
                $source = 'telepon';
                $notes = 'Pesanan offline via panggilan telepon langsung ke kantor.';
            } elseif ($roll <= 84) {
                $source = 'pelanggan_tetap';
                $notes = 'Pesanan rutin pelanggan tetap / mitra instansi.';
            } elseif ($roll <= 96) {
                $source = 'whatsapp';
                $notes = 'Pesanan masuk melalui WhatsApp customer service.';
            } else {
                $source = 'lainnya';
                $notes = 'Rekomendasi kenalan keluarga.';
            }

            $order = Order::create([
                'customer_id' => $customers->random()->id,
                'source' => $source,
                'order_date' => $orderDate,
                'event_date' => $eventDate,
                'event_location' => $locations[mt_rand(0, count($locations) - 1)],
                'status' => $status,
                'notes' => $notes,
            ]);

            // Item pesanan
            $total = 0;
            $pickedSellables = $sellables->shuffle()->take(mt_rand(1, 3));
            foreach ($pickedSellables as $s) {
                if ($s['menu_id']) {
                    // Menu satuan
                    $qty = $s['price'] < 100000 ? mt_rand(30, 200) : mt_rand(1, 5);
                } else {
                    // Paket catering
                    $qty = mt_rand(1, 2);
                }

                $sub = $s['price'] * $qty;
                $total += $sub;

                $order->items()->create([
                    'menu_id' => $s['menu_id'],
                    'package_id' => $s['package_id'],
                    'item_name' => $s['name'],
                    'unit_price' => $s['price'],
                    'quantity' => $qty,
                    'subtotal' => $sub,
                ]);
            }

            $order->update(['total_amount' => $total]);

            if ($status === 'dibatalkan') {
                continue;
            }

            $cap = fn ($d) => $d->gt(today()) ? today() : $d;
            $dp = (int) round($total * (mt_rand(30, 50) / 100), -3);

            // Metode pembayaran: untuk pesanan offline, tunai & QRIS mendominasi
            $pickMethod = function (string $source) {
                if (in_array($source, ['datang_langsung', 'telepon', 'pelanggan_tetap'], true)) {
                    $mRoll = mt_rand(1, 100);
                    return match (true) {
                        $mRoll <= 50 => 'tunai',    // 50% Tunai di kasir/outlet
                        $mRoll <= 75 => 'qris',     // 25% QRIS di outlet
                        $mRoll <= 90 => 'transfer', // 15% Transfer bank
                        default => 'debit',         // 10% Kartu Debit / EDC
                    };
                }
                return mt_rand(1, 10) <= 8 ? 'transfer' : 'qris';
            };

            if ($status === 'selesai') {
                $order->payments()->create([
                    'type' => 'dp',
                    'amount' => $dp,
                    'paid_at' => $cap($orderDate->copy()->addDay()),
                    'method' => $pickMethod($source),
                    'notes' => 'Uang muka (DP) pesanan',
                ]);

                // 85% pesanan selesai sudah lunas, sebagian kecil masih ada sisa piutang
                if (mt_rand(1, 7) !== 1) {
                    $order->payments()->create([
                        'type' => 'pelunasan',
                        'amount' => $total - $dp,
                        'paid_at' => $cap($eventDate->copy()->subDays(mt_rand(0, 2))),
                        'method' => $pickMethod($source),
                        'notes' => 'Pelunasan pesanan catering',
                    ]);
                }
            } else {
                $paymentRoll = mt_rand(1, 10);
                if ($paymentRoll <= 6) {
                    $order->payments()->create([
                        'type' => 'dp',
                        'amount' => $dp,
                        'paid_at' => $cap($orderDate->copy()->addDay()),
                        'method' => $pickMethod($source),
                        'notes' => 'Uang muka (DP) pesanan',
                    ]);
                } elseif ($paymentRoll <= 8) {
                    $order->payments()->create([
                        'type' => 'pelunasan',
                        'amount' => $total,
                        'paid_at' => $cap($orderDate->copy()->addDay()),
                        'method' => $pickMethod($source),
                        'notes' => 'Pembayaran lunas langsung di muka',
                    ]);
                }
            }
        }
    }
}
