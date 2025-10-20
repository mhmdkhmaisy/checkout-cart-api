<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->onDelete('cascade');
            $table->string('username');
            $table->integer('claim_count')->default(0);
            $table->decimal('total_spent_during_promo', 10, 2)->default(0);
            $table->timestamp('last_claimed_at')->nullable();
            $table->tinyInteger('claimed_ingame')->default(0);
            $table->timestamps();
            
            $table->unique(['promotion_id', 'username']);
            $table->index('username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_claims');
    }
};
