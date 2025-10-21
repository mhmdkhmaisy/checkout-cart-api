<?php

/**
 * Emergency Database Fix Script
 * Run this directly to fix table engines and auto-increment issues
 * 
 * Usage: php fix_database_now.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=================================================\n";
echo "EMERGENCY DATABASE FIX SCRIPT\n";
echo "=================================================\n\n";

try {
    echo "Step 1: Checking current table engines...\n";
    $tables = ['orders', 'order_items', 'products'];
    
    foreach ($tables as $table) {
        $result = DB::selectOne("
            SELECT ENGINE, AUTO_INCREMENT
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
        ", [$table]);
        
        if ($result) {
            echo "  {$table}: Engine={$result->ENGINE}, Auto-Increment={$result->AUTO_INCREMENT}\n";
        }
    }
    
    echo "\n";
    
    // Step 2: Convert to InnoDB
    echo "Step 2: Converting tables to InnoDB...\n";
    foreach ($tables as $table) {
        echo "  Converting {$table}...";
        DB::statement("ALTER TABLE {$table} ENGINE = InnoDB");
        echo " ✓ Done\n";
    }
    
    echo "\n";
    
    // Step 3: Fix auto-increment
    echo "Step 3: Fixing auto-increment values...\n";
    foreach ($tables as $table) {
        $maxId = DB::selectOne("SELECT MAX(id) as max_id FROM {$table}");
        $maxId = $maxId->max_id ?? 0;
        $expectedAutoIncrement = $maxId + 1;
        
        echo "  {$table}: Setting auto-increment to {$expectedAutoIncrement}...";
        DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = {$expectedAutoIncrement}");
        echo " ✓ Done\n";
    }
    
    echo "\n";
    
    // Step 4: Verify foreign keys
    echo "Step 4: Verifying foreign key constraints...\n";
    $fks = DB::select("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'order_items'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    foreach ($fks as $fk) {
        echo "  ✓ {$fk->CONSTRAINT_NAME}: {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
    }
    
    echo "\n";
    
    // Step 5: Final verification
    echo "Step 5: Final verification...\n";
    foreach ($tables as $table) {
        $result = DB::selectOne("
            SELECT ENGINE, AUTO_INCREMENT
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
        ", [$table]);
        
        $maxId = DB::selectOne("SELECT MAX(id) as max_id FROM {$table}");
        $maxId = $maxId->max_id ?? 0;
        $count = DB::table($table)->count();
        
        echo "  {$table}:\n";
        echo "    Engine: {$result->ENGINE}\n";
        echo "    Auto-Increment: {$result->AUTO_INCREMENT}\n";
        echo "    Max ID: {$maxId}\n";
        echo "    Row Count: {$count}\n";
        
        if ($result->ENGINE !== 'InnoDB') {
            echo "    ❌ WARNING: Still not InnoDB!\n";
        } else {
            echo "    ✓ Correct engine\n";
        }
    }
    
    echo "\n=================================================\n";
    echo "✅ FIX COMPLETE!\n";
    echo "=================================================\n\n";
    echo "Next steps:\n";
    echo "1. Try the checkout again\n";
    echo "2. New orders should now start from the next available ID\n";
    echo "3. All transactions should work properly\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
