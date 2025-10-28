<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('upload_key')->unique()->index();
            $table->string('filename');
            $table->bigInteger('total_size');
            $table->bigInteger('uploaded_size')->default(0);
            $table->integer('total_chunks');
            $table->json('received_chunks')->nullable();
            $table->string('relative_path')->nullable();
            $table->string('temp_dir');
            $table->string('status')->default('uploading')->index();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Add index for cleanup of old sessions
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_sessions');
    }
};
