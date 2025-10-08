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
            // Add separate column for PayPal capture ID
            $table->string('paypal_capture_id')->nullable()->after('payment_id');
            
            // Add index for faster lookups
            $table->index('paypal_capture_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['paypal_capture_id']);
            $table->dropColumn('paypal_capture_id');
        });
    }
};