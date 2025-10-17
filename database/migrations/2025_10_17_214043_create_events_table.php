<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->text('description');
            $table->text('rewards');
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['upcoming', 'active', 'ended'])->default('upcoming');
            $table->timestamps();
            
            $table->index('status');
            $table->index('start_at');
            $table->index('end_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
