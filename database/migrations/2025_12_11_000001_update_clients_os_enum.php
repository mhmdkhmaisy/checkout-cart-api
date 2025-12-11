<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to modify the ENUM directly
        // First, delete any existing macos or linux clients (they shouldn't exist based on user's requirement)
        DB::table('clients')->whereIn('os', ['macos', 'linux'])->delete();
        
        // Modify the ENUM to new values: windows, minimal, standalone
        DB::statement("ALTER TABLE clients MODIFY COLUMN os ENUM('windows', 'minimal', 'standalone') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original ENUM values
        DB::statement("ALTER TABLE clients MODIFY COLUMN os ENUM('windows', 'macos', 'linux', 'standalone') NOT NULL");
    }
};
