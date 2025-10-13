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
            $table->string('relative_path')->nullable();
            $table->bigInteger('file_size');
            $table->bigInteger('uploaded_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->string('tus_id')->nullable();
            $table->string('status')->default('uploading'); // uploading, processing, completed, failed
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('upload_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_session_id')->constrained('upload_sessions')->onDelete('cascade');
            $table->integer('chunk_number');
            $table->bigInteger('chunk_size');
            $table->string('chunk_hash')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
            
            $table->unique(['upload_session_id', 'chunk_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_chunks');
        Schema::dropIfExists('upload_sessions');
    }
};
