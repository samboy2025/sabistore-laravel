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
        Schema::create('course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug');
            $table->enum('type', ['video', 'pdf', 'article', 'quiz'])->default('video');
            $table->string('content_url')->nullable(); // YouTube URL or file path
            $table->text('content')->nullable(); // For article type lessons
            $table->integer('duration_minutes')->nullable(); // For videos
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_preview')->default(false); // Can be viewed without enrollment
            $table->timestamps();

            $table->index(['course_id', 'order']);
            $table->unique(['course_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_lessons');
    }
};
