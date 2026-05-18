<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // 🔗 relasi ke orders
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            // 🧺 detail layanan
            $table->string('service_name'); // contoh: cuci bersih
            $table->integer('price');       // harga per item
            $table->integer('qty')->default(1);

            // 💰 subtotal (optional tapi bagus)
            $table->integer('subtotal')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};