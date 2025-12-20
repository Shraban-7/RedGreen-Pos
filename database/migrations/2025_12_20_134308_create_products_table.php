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
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('buying_price', 10, 2);
            $table->decimal('selling_price', 10, 2);

            $table->string('discount_type')->nullable();
            $table->decimal('discount_value',6,1)->nullable();
            $table->decimal('discount_amount',8,2)->nullable();
            $table->decimal('discounted_price',10,2)->nullable();

            $table->integer('stock_in')->default(0);
            $table->integer('stock_out')->default(0);
            $table->integer('low_stock_quantity')->default(0);
            $table->timestamps();
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
