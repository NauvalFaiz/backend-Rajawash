<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->id();

            // 🔐 login owner
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            // 🧺 data laundry
            $table->string('laundry_name');
            $table->text('laundry_address')->nullable();
            $table->string('phone')->nullable();

            // 📊 status akun
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owners');
    }
};