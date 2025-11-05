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
        Schema::table('updates', function (Blueprint $table) {
            $table->unsignedBigInteger('attached_to_update_id')->nullable()->after('id');
            $table->foreign('attached_to_update_id')->references('id')->on('updates')->onDelete('cascade');
            $table->index('attached_to_update_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('updates', function (Blueprint $table) {
            $table->dropForeign(['attached_to_update_id']);
            $table->dropColumn('attached_to_update_id');
        });
    }
};
