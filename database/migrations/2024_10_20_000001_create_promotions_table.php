<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title', 128);
            $table->text('description');
            $table->json('reward_items');
            $table->decimal('min_amount', 10, 2);
            $table->enum('bonus_type', ['single', 'recurrent'])->default('single');
            $table->integer('claim_limit_per_user')->nullable();
            $table->integer('global_claim_limit')->nullable();
            $table->integer('claimed_global')->default(0);
            $table->timestamp('start_at')->useCurrent();
            $table->timestamp('end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active', 'start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
