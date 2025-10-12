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
        Schema::create('cache_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('bundle_key', 32)->unique(); // MD5 of file list & mode
            $table->json('file_list'); // Array of filenames included
            $table->string('path'); // Path to .tar.gz archive
            $table->bigInteger('size'); // File size in bytes
            $table->datetime('expires_at'); // Expiry timestamp
            $table->timestamps();
            
            $table->index(['bundle_key', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_bundles');
    }
};