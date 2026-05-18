<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Tambahkan ini di atas

return new class extends Migration
{
    public function up(): void
    {
        // CARA KERAS: Matikan pengecekan foreign key agar tidak terhalang relasi lain
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Hapus tabel jika sudah ada sebelum mencoba membuat baru
        Schema::dropIfExists('orders');

        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // relasi
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('owners')->onDelete('cascade');

            // layanan
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');

            // lokasi
            $table->string('laundry_location');

            // pengantaran
            $table->enum('delivery_type', ['standart', 'hemat', 'kilat'])->nullable();
            $table->enum('pickup_type', ['pickup', 'self'])->default('pickup');

            // pembayaran
            $table->enum('payment_method', ['qris', 'tunai']);
            $table->string('payment_code')->unique();

            // promo
            $table->string('promo_code')->nullable();
            $table->integer('discount')->default(0);

            // harga
            $table->integer('total_price');

            // status order
            $table->enum('status', [
                'pending',
                'waiting_payment',
                'paid',
                'pickup',
                'weighing',
                'to_laundry',
                'received',
                'process',
                'done',
                'delivery_back',
                'shipped',
                'completed',
                'cancel'
            ])->default('pending');

            // status pembayaran
            $table->enum('payment_status', [
                'unpaid',
                'paid'
            ])->default('unpaid');

            $table->timestamps();
        });

        // Nyalakan kembali pengecekan foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('orders');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};