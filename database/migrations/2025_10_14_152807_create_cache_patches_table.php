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
        Schema::create('cache_patches', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->string('base_version')->nullable();
            $table->string('path');
            $table->json('file_manifest');
            $table->integer('file_count')->default(0);
            $table->bigInteger('size')->default(0);
            $table->boolean('is_base')->default(false);
            $table->timestamps();
            
            $table->index(['version', 'is_base']);
            $table->index('base_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_patches');
    }
};
