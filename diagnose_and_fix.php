<?php

/**
 * All-in-One Database Diagnostic and Fix Script
 * 
 * This script will:
 * 1. Diagnose your database issues
 * 2. Show you what needs to be fixed
 * 3. Ask if you want to fix it automatically
 * 4. Verify the fix worked
 * 
 * Usage: php diagnose_and_fix.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// ANSI color codes for better output
define('RED', "\033[31m");
define('GREEN', "\033[32m");
define('YELLOW', "\033[33m");
define('BLUE', "\033[34m");
define('RESET', "\033[0m");

function success($msg) { echo GREEN . "✓ " . $msg . RESET . "\n"; }
function error($msg) { echo RED . "✗ " . $msg . RESET . "\n"; }
function warning($msg) { echo YELLOW . "⚠ " . $msg . RESET . "\n"; }
function info($msg) { echo BLUE . "ℹ " . $msg . RESET . "\n"; }

echo "\n";
echo "=================================================================\n";
echo "           DATABASE DIAGNOSTIC & FIX TOOL\n";
echo "=================================================================\n\n";

try {
    $issues = [];
    $tables = ['orders', 'order_items', 'products'];
    
    // STEP 1: DIAGNOSE
    echo BLUE . "STEP 1: DIAGNOSING DATABASE\n" . RESET;
    echo str_repeat("-", 65) . "\n\n";
    
    foreach ($tables as $table) {
        info("Checking table: {$table}");
        
        $result = DB::selectOne("
            SELECT ENGINE, AUTO_INCREMENT, TABLE_ROWS
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
        ", [$table]);
        
        if (!$result) {
            error("Table '{$table}' not found!");
            continue;
        }
        
        $maxId = DB::selectOne("SELECT MAX(id) as max_id FROM {$table}");
        $maxId = $maxId->max_id ?? 0;
        $expectedAutoIncrement = $maxId + 1;
        $count = DB::table($table)->count();
        
        echo "  Engine: {$result->ENGINE}\n";
        echo "  Rows: {$count}\n";
        echo "  Max ID: {$maxId}\n";
        echo "  Current Auto-Increment: {$result->AUTO_INCREMENT}\n";
        echo "  Expected Auto-Increment: {$expectedAutoIncrement}\n";
        
        // Check for issues
        $tableIssues = [];
        
        if ($result->ENGINE !== 'InnoDB') {
            error("  CRITICAL: Table is {$result->ENGINE}, should be InnoDB!");
            $tableIssues[] = 'engine';
            $issues[] = [
                'table' => $table,
                'type' => 'engine',
                'current' => $result->ENGINE,
                'expected' => 'InnoDB',
                'severity' => 'CRITICAL'
            ];
        } else {
            success("  Engine is correct (InnoDB)");
        }
        
        if ($result->AUTO_INCREMENT && $result->AUTO_INCREMENT != $expectedAutoIncrement) {
            warning("  Auto-increment mismatch: {$result->AUTO_INCREMENT} (should be {$expectedAutoIncrement})");
            $tableIssues[] = 'auto_increment';
            $issues[] = [
                'table' => $table,
                'type' => 'auto_increment',
                'current' => $result->AUTO_INCREMENT,
                'expected' => $expectedAutoIncrement,
                'severity' => 'WARNING'
            ];
        } else {
            success("  Auto-increment is correct");
        }
        
        echo "\n";
    }
    
    // Check foreign keys
    info("Checking foreign key constraints...");
    $fks = DB::select("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'order_items'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    foreach ($fks as $fk) {
        echo "  ✓ {$fk->CONSTRAINT_NAME}: {$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
    }
    echo "\n";
    
    // STEP 2: REPORT ISSUES
    echo BLUE . "STEP 2: ISSUE SUMMARY\n" . RESET;
    echo str_repeat("-", 65) . "\n\n";
    
    if (empty($issues)) {
        success("No issues found! Your database is healthy.");
        echo "\n";
        exit(0);
    }
    
    $criticalCount = count(array_filter($issues, fn($i) => $i['severity'] === 'CRITICAL'));
    $warningCount = count(array_filter($issues, fn($i) => $i['severity'] === 'WARNING'));
    
    echo "Found " . count($issues) . " issue(s):\n";
    echo "  - {$criticalCount} CRITICAL (must fix)\n";
    echo "  - {$warningCount} WARNING (should fix)\n\n";
    
    foreach ($issues as $issue) {
        $color = $issue['severity'] === 'CRITICAL' ? RED : YELLOW;
        echo $color . "[{$issue['severity']}]" . RESET . " {$issue['table']}: ";
        
        if ($issue['type'] === 'engine') {
            echo "Using {$issue['current']}, should be {$issue['expected']}\n";
            echo "    Impact: Transactions don't work, checkout will fail\n";
        } else {
            echo "Auto-increment is {$issue['current']}, should be {$issue['expected']}\n";
            echo "    Impact: Order IDs will be incorrect\n";
        }
    }
    
    echo "\n";
    
    // STEP 3: ASK TO FIX
    echo BLUE . "STEP 3: APPLY FIXES?\n" . RESET;
    echo str_repeat("-", 65) . "\n\n";
    
    echo "Would you like to automatically fix these issues? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($response) !== 'yes') {
        warning("Fix cancelled. Run this script again when ready.");
        echo "\nTo fix manually, see: CHECKOUT_ERROR_FIX.md\n\n";
        exit(0);
    }
    
    echo "\n";
    echo BLUE . "STEP 4: APPLYING FIXES\n" . RESET;
    echo str_repeat("-", 65) . "\n\n";
    
    foreach ($issues as $issue) {
        $table = $issue['table'];
        
        if ($issue['type'] === 'engine') {
            info("Converting {$table} to InnoDB...");
            try {
                DB::statement("ALTER TABLE {$table} ENGINE = InnoDB");
                success("Converted {$table} to InnoDB");
            } catch (Exception $e) {
                error("Failed to convert {$table}: " . $e->getMessage());
            }
        }
        
        if ($issue['type'] === 'auto_increment') {
            info("Fixing auto-increment for {$table}...");
            try {
                $expected = $issue['expected'];
                DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = {$expected}");
                success("Set {$table} auto-increment to {$expected}");
            } catch (Exception $e) {
                error("Failed to fix auto-increment for {$table}: " . $e->getMessage());
            }
        }
    }
    
    echo "\n";
    
    // STEP 5: VERIFY
    echo BLUE . "STEP 5: VERIFICATION\n" . RESET;
    echo str_repeat("-", 65) . "\n\n";
    
    $allGood = true;
    foreach ($tables as $table) {
        $result = DB::selectOne("
            SELECT ENGINE, AUTO_INCREMENT
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
        ", [$table]);
        
        $maxId = DB::selectOne("SELECT MAX(id) as max_id FROM {$table}");
        $maxId = $maxId->max_id ?? 0;
        $expectedAutoIncrement = $maxId + 1;
        
        echo "{$table}:\n";
        
        if ($result->ENGINE === 'InnoDB') {
            success("  Engine: InnoDB");
        } else {
            error("  Engine: {$result->ENGINE} (STILL WRONG!)");
            $allGood = false;
        }
        
        if ($result->AUTO_INCREMENT == $expectedAutoIncrement) {
            success("  Auto-increment: {$result->AUTO_INCREMENT}");
        } else {
            warning("  Auto-increment: {$result->AUTO_INCREMENT} (expected {$expectedAutoIncrement})");
            $allGood = false;
        }
        
        echo "\n";
    }
    
    echo "\n";
    echo "=================================================================\n";
    if ($allGood) {
        echo GREEN . "           ✓ ALL FIXES APPLIED SUCCESSFULLY!\n" . RESET;
    } else {
        echo YELLOW . "           ⚠ SOME ISSUES REMAIN\n" . RESET;
    }
    echo "=================================================================\n\n";
    
    if ($allGood) {
        echo "Next steps:\n";
        echo "  1. Test your checkout endpoint\n";
        echo "  2. New orders should now work correctly\n";
        echo "  3. Order IDs will be sequential\n\n";
    } else {
        echo "Some issues could not be fixed automatically.\n";
        echo "Please check the errors above and fix manually.\n";
        echo "See CHECKOUT_ERROR_FIX.md for detailed instructions.\n\n";
    }
    
} catch (Exception $e) {
    echo "\n";
    error("ERROR: " . $e->getMessage());
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n\n";
    exit(1);
}
