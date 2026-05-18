<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * History per user (tidak tercampur):
 * - relasi ke user_id
 * - relasi ke order_id
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();

            // 🔗 relasi utama (ini yang bikin history tidak tercampur)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            // 📊 status yang dicatat
            $table->string('status'); // contoh: pending, paid, process, done

            // 📝 keterangan tambahan
            $table->text('note')->nullable();

            // ⏱️ waktu kejadian
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            // 🚀 optional: index biar cepat query per user
            $table->index(['user_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_histories');
    }
};