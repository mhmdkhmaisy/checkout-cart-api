<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('username')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'claimed', 'reserved']);
            $table->string('last_event');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->unique('order_id');
            $table->index(['status', 'updated_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_logs');
    }
};