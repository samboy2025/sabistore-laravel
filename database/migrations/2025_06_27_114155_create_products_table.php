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
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->enum('type', ['physical', 'digital'])->default('physical');
            $table->json('images')->nullable(); // Store multiple image paths
            $table->string('file_path')->nullable(); // For digital products
            $table->boolean('is_resellable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('tags')->nullable();
            $table->integer('stock_quantity')->nullable(); // For physical products
            $table->decimal('weight', 8, 2)->nullable(); // For shipping
            $table->json('dimensions')->nullable(); // L x W x H for shipping
            $table->integer('views_count')->default(0);
            $table->integer('orders_count')->default(0);
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
