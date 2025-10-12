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
        Schema::table('cache_files', function (Blueprint $table) {
            $table->string('relative_path')->nullable()->after('filename'); // Relative path within archive
            $table->string('file_type', 50)->nullable()->after('relative_path'); // file, directory, etc.
            $table->string('mime_type')->nullable()->after('file_type'); // MIME type for files
            $table->text('metadata')->nullable()->after('mime_type'); // JSON metadata for additional info
            
            // Update indexes
            $table->dropIndex(['filename', 'hash']);
            $table->index(['filename', 'relative_path', 'hash']);
            $table->index(['file_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cache_files', function (Blueprint $table) {
            $table->dropIndex(['filename', 'relative_path', 'hash']);
            $table->dropIndex(['file_type']);
            
            $table->dropColumn(['relative_path', 'file_type', 'mime_type', 'metadata']);
            
            $table->index(['filename', 'hash']);
        });
    }
};