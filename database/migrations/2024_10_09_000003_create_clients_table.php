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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->enum('os', ['windows', 'macos', 'linux', 'standalone'])->index();
            $table->string('version', 20)->index();
            $table->string('file_path', 255);
            $table->string('original_filename', 255);
            $table->bigInteger('size');
            $table->string('hash', 128);
            $table->boolean('enabled')->default(true)->index();
            $table->text('changelog')->nullable();
            $table->timestamps();
            
            // Ensure only one enabled version per OS
            $table->unique(['os', 'enabled'], 'unique_enabled_per_os');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};