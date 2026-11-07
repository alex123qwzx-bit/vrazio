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
            $table->string('sku', 30)->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 50)->index();
            $table->string('metal_type', 50)->nullable(); // '18KT Yellow Gold', 'Rose Gold', 'Platinum 950' or fabric type
            $table->decimal('gross_weight_grams', 8, 2)->nullable();
            $table->decimal('net_weight_grams', 8, 2)->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('bis_hallmarked')->default(true);
            $table->string('certification', 100)->nullable(); // 'GIA / IGI / SGL Certified'
            $table->longText('description')->nullable();
            $table->json('specs')->nullable();
            $table->json('images')->nullable(); // JSON array of image URLs
            $table->json('tags')->nullable(); // JSON array of tags (e.g. Bridal, Haute Couture, Silk)
            $table->timestamps();
            $table->index('is_featured');
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
