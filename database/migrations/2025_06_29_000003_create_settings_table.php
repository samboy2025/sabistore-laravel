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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->enum('type', ['text', 'textarea', 'number', 'boolean', 'file', 'json'])->default('text');
            $table->string('group')->default('general'); // Group settings together
            $table->string('label')->nullable(); // Human readable label
            $table->text('description')->nullable(); // Help text
            $table->boolean('is_public')->default(false); // Can be accessed publicly
            $table->integer('order')->default(0); // For ordering in admin
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['key']);
            $table->index(['group']);
            $table->index(['is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
