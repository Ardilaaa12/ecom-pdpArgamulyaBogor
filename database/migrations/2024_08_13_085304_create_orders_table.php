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
            $table->decimal('total_amount', 20, 2)->default(0);
            $table->enum('status', [
                        'menunggu pembayaran',
                        'verifikasi pembayaran',
                        'gagal',
                        'berhasil'
            ])->default('menunggu pembayaran');
            $table->string('notes')->nullable();
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
