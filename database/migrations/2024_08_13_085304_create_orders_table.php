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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->uuid('no_ref_order')->unique();
            $table->dateTime('order_date');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status', [
                        'menunggu pembayaran',
                        'verifikasi pembayaran',
                        'gagal',
                        'disiapkan',
                        'dalam perjalanan',
                        'sudah sammpai'
            ])->default('menunggu pembayaran');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
