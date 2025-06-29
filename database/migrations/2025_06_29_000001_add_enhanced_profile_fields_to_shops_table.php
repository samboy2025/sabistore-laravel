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
        Schema::table('shops', function (Blueprint $table) {
            // Check if columns don't already exist before adding them
            if (!Schema::hasColumn('shops', 'facebook_handle')) {
                $table->string('facebook_handle')->nullable()->after('social_links');
            }
            if (!Schema::hasColumn('shops', 'instagram_handle')) {
                $table->string('instagram_handle')->nullable()->after('facebook_handle');
            }
            if (!Schema::hasColumn('shops', 'twitter_handle')) {
                $table->string('twitter_handle')->nullable()->after('instagram_handle');
            }
            if (!Schema::hasColumn('shops', 'tiktok_handle')) {
                $table->string('tiktok_handle')->nullable()->after('twitter_handle');
            }
            if (!Schema::hasColumn('shops', 'business_address')) {
                $table->text('business_address')->nullable()->after('tiktok_handle');
            }
            if (!Schema::hasColumn('shops', 'business_location')) {
                $table->string('business_location')->nullable()->after('business_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_handle',
                'instagram_handle', 
                'twitter_handle',
                'tiktok_handle',
                'business_address',
                'business_location'
            ]);
        });
    }
};
