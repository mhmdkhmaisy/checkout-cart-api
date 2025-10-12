<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up(): void
    {
        try {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropUnique('unique_enabled_per_os');
            });
        } catch (\Exception $e) {
            // Log the error message
            Log::error("Error dropping unique index 'unique_enabled_per_os' from 'clients' table: " . $e->getMessage());

            // Optionally, you can also output to the console
            echo "\033[31m" . "Error: " . $e->getMessage() . "\033[0m\n"; // Red text for error
        }

        // MySQL/MariaDB doesn't support partial indexes with WHERE clause
        // The constraint will be enforced at the application level instead
        // See the Client model for the business logic implementation
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unique(['os', 'enabled'], 'unique_enabled_per_os');
        });
    }
};
