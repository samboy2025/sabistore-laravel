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
        Schema::create('followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete(); // Who is following
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete(); // Vendor being followed
            $table->timestamps();
            
            // Ensure a user can only follow a vendor once
            $table->unique(['follower_id', 'vendor_id']);
            
            // Add indexes for performance
            $table->index(['follower_id']);
            $table->index(['vendor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followers');
    }
};
