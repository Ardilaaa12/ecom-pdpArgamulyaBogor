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
            $table->string('no_ref_order', 20)->unique();
            $table->dateTime('order_date');
            $table->string('total_amount', 20)->default(0);
            $table->enum('status', [
                        'verifikasi pengiriman',
                        'menunggu pembayaran',
                        'verifikasi pembayaran',
                        'gagal',
                        'berhasil'
            ])->default('verifikasi pengiriman');
            $table->string('notes', 100)->nullable();
            $table->string('check_by',100)->nullable();
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
