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
        Schema::table('promotion_claims', function (Blueprint $table) {
            $table->timestamp('claimable_at')->nullable()->after('total_spent_during_promo');
            $table->index('claimable_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotion_claims', function (Blueprint $table) {
            $table->dropIndex(['claimable_at']);
            $table->dropColumn('claimable_at');
        });
    }
};
