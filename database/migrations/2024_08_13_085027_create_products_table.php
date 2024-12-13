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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id');
            $table->string('name_product', 100);
            $table->string('age', 10)->nullable();
            $table->string('weight', 10)->nullable();
            $table->string('description', 225)->nullable();
            $table->string('price', 20);
            $table->integer('stock');
            $table->enum('condition', [
                'sehat',
                'sakit'
            ])->default('sehat');
            $table->string('photo_product', 100);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
