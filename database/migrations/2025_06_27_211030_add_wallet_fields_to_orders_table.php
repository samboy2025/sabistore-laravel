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
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['wallet', 'whatsapp', 'external'])->default('whatsapp')->after('payment_status');
            $table->foreignId('reseller_id')->nullable()->constrained('users')->nullOnDelete()->after('buyer_id');
            $table->decimal('reseller_commission', 10, 2)->default(0.00)->after('reseller_id');
            $table->boolean('commission_paid')->default(false)->after('reseller_commission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'reseller_id', 'reseller_commission', 'commission_paid']);
        });
    }
};
