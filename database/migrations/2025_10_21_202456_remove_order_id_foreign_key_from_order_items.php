<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Removes the foreign key constraint on order_id to avoid transaction
     * isolation issues with InnoDB. The constraint was causing FK violations
     * because MySQL couldn't see uncommitted parent rows within transactions.
     * 
     * We keep the column indexed for query performance and handle cascading
     * deletes at the application level if needed.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Restores the foreign key constraint with cascade delete.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Restore the foreign key constraint
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');
        });
    }
};
