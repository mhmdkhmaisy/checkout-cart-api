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
            // Remove old unique constraint on filename only
            $table->dropUnique(['filename']);

            // Add composite unique constraint for filename + relative_path
            // This allows same filename in different paths and optimizes upsert operations
            $table->unique(['filename', 'relative_path'], 'cache_files_filename_path_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cache_files', function (Blueprint $table) {
            // Drop composite unique constraint
            $table->dropUnique('cache_files_filename_path_unique');

            // Restore original unique constraint on filename
            $table->unique('filename');
        });
    }
};
