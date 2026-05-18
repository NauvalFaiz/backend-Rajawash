<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_tasks', function (Blueprint $table) {
            $table->id();

            // 🔗 relasi ke order & kurir (users dengan role = kurir)
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('courier_id')->constrained('users')->onDelete('cascade');

            // 📦 penimbangan
            $table->decimal('weight', 8, 2)->nullable();      // berat (kg)
            $table->integer('price_per_kg')->nullable();      // harga/kg
            $table->integer('total_price')->nullable();       // total hasil timbang

            // 💳 pembayaran QR
            $table->string('payment_code')->nullable();       // id pembayaran
            $table->string('qr_code')->nullable();            // path / string QR

            // 📊 status proses kurir
            $table->enum('status', [
                'pickup',
                'weighing',
                'to_laundry',
                'delivery_back',
                'shipped',
                'completed'
            ])->default('pickup');

            // ⏱️ waktu aktivitas
            $table->timestamp('started_at')->nullable();
            $table->timestamp('picked_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_tasks');
    }
};