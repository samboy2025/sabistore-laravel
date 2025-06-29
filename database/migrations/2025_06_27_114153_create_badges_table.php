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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Bronze, Silver, Gold, Top Vendor
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->default('#B10020'); // Badge color
            $table->integer('min_products')->default(0);
            $table->integer('min_orders')->default(0);
            $table->integer('min_reviews')->default(0);
            $table->integer('order')->default(0); // For sorting badges
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
