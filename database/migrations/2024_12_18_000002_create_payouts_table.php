<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_member_id')->constrained()->onDelete('cascade');
            $table->string('paypal_email');
            $table->decimal('gross_amount', 10, 2);
            $table->decimal('net_amount', 10, 2);
            $table->decimal('payout_amount', 10, 2);
            $table->decimal('percentage', 5, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('paypal_batch_id')->nullable();
            $table->string('paypal_payout_item_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['order_id', 'team_member_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
