<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_token')->nullable()->unique()->after('payment_code');
            $table->timestamp('payment_token_expires_at')->nullable()->after('payment_token');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
            $table->text('payment_device_info')->nullable()->after('paid_at');
            $table->string('payment_ip_address')->nullable()->after('payment_device_info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_token',
                'payment_token_expires_at',
                'paid_at',
                'payment_device_info',
                'payment_ip_address',
            ]);
        });
    }
};
