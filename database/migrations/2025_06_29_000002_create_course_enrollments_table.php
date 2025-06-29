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
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['enrolled', 'in_progress', 'completed', 'dropped'])->default('enrolled');
            $table->integer('progress_percentage')->default(0);
            $table->timestamp('enrolled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent_minutes')->default(0); // Total time spent
            $table->json('progress_data')->nullable(); // Store detailed progress
            $table->decimal('score', 5, 2)->nullable(); // Final score if applicable
            $table->timestamps();
            
            // Ensure a user can only enroll once per course
            $table->unique(['user_id', 'course_id']);
            
            // Indexes for performance
            $table->index(['user_id']);
            $table->index(['course_id']);
            $table->index(['status']);
            $table->index(['enrolled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
    }
};
