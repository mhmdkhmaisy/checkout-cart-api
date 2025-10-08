<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('event_type');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'claimed', 'reserved']);
            $table->json('payload')->nullable();
            $table->timestamp('created_at');

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->index(['order_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_events');
    }
};