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
        Schema::create('reseller_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reseller_id')->constrained('users')->cascadeOnDelete();
            $table->string('code')->unique(); // Unique reseller code
            $table->decimal('commission_rate', 5, 2)->default(10.00); // Percentage
            $table->decimal('total_earned', 10, 2)->default(0);
            $table->integer('clicks_count')->default(0);
            $table->integer('sales_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_clicked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reseller_links');
    }
};
