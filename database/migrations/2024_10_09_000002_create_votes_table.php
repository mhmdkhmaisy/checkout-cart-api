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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->timestamp('callback_date')->nullable();
            $table->timestamp('started')->useCurrent();
            $table->string('ip_address')->nullable();
            $table->unsignedBigInteger('site_id');
            $table->string('uid');
            $table->boolean('claimed')->default(false);
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('vote_sites')->onDelete('cascade');
            $table->index(['username', 'site_id']);
            $table->index('uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};