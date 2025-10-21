<?php

/**
 * Check Migration Status
 * Run this to see which migrations have been applied
 * 
 * Usage: php check_migration_status.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=================================================\n";
echo "MIGRATION STATUS CHECK\n";
echo "=================================================\n\n";

try {
    echo "Recent migrations in database:\n";
    $migrations = DB::table('migrations')
        ->orderBy('id', 'desc')
        ->limit(10)
        ->get(['migration', 'batch']);
    
    foreach ($migrations as $migration) {
        echo "  ✓ {$migration->migration} (batch {$migration->batch})\n";
    }
    
    echo "\n";
    
    echo "Critical migrations to check:\n";
    $critical = [
        '2025_10_21_174524_make_product_id_nullable_in_order_items_table',
        '2025_10_21_175525_fix_table_engines_for_transactions'
    ];
    
    foreach ($critical as $migrationName) {
        $exists = DB::table('migrations')
            ->where('migration', $migrationName)
            ->exists();
        
        if ($exists) {
            echo "  ✓ {$migrationName} - APPLIED\n";
        } else {
            echo "  ❌ {$migrationName} - NOT APPLIED\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
