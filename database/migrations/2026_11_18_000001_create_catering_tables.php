<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('phone', 30)->nullable();
            $t->text('address')->nullable();
            $t->enum('type', ['perorangan', 'instansi'])->default('perorangan');
            $t->timestamps();
        });

        Schema::create('menus', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('category')->nullable();
            $t->unsignedBigInteger('price');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('packages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->text('description')->nullable();
            $t->unsignedBigInteger('price');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('order_number', 30)->unique();
            $t->foreignId('customer_id')->constrained()->restrictOnDelete();
            $t->enum('source', ['whatsapp', 'telepon', 'datang_langsung', 'pelanggan_tetap', 'lainnya'])->default('whatsapp');
            $t->date('order_date');
            $t->date('event_date');
            $t->string('event_location')->nullable();
            $t->enum('status', ['baru', 'dikonfirmasi', 'diproses', 'selesai', 'dibatalkan'])->default('baru');
            $t->unsignedBigInteger('total_amount')->default(0); // total tagihan, BUKAN uang yang sudah diterima
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['order_date', 'status']);
            $t->index('event_date');
        });

        // Harga disimpan sebagai snapshot saat pesanan dibuat
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('menu_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $t->string('item_name');
            $t->unsignedBigInteger('unit_price');
            $t->unsignedInteger('quantity');
            $t->unsignedBigInteger('subtotal');
            $t->timestamps();
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['dp', 'tambahan', 'pelunasan']);
            $t->unsignedBigInteger('amount');
            $t->date('paid_at');
            $t->string('method', 30)->default('transfer');
            $t->string('notes')->nullable();
            $t->timestamps();
            $t->index('paid_at');
        });
    }

    public function down(): void
    {
        foreach (['payments', 'order_items', 'orders', 'packages', 'menus', 'customers'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
