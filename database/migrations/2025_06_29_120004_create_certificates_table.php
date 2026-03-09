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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('certificate_number')->unique();
            $table->string('recipient_name');
            $table->string('course_title');
            $table->date('completion_date');
            $table->date('issue_date');
            $table->string('file_path')->nullable(); // PDF file path
            $table->boolean('is_verified')->default(true);
            $table->json('metadata')->nullable(); // Additional certificate data
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
            $table->index(['certificate_number']);
            $table->index(['user_id', 'issue_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
