<?php

/**
 * Quick diagnostic to check the order ID mismatch issue
 * Usage: php check_order_mismatch.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=================================================\n";
echo "ORDER ID MISMATCH DIAGNOSTIC\n";
echo "=================================================\n\n";

// Check auto-increment
echo "1. Checking AUTO_INCREMENT value...\n";
$result = DB::selectOne("
    SELECT AUTO_INCREMENT
    FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'orders'
");
echo "   Current AUTO_INCREMENT: " . ($result->AUTO_INCREMENT ?? 'NULL') . "\n\n";

// Check actual orders in database
echo "2. Last 5 orders in database:\n";
$orders = DB::select("SELECT id, username, amount, payment_id, created_at FROM orders ORDER BY id DESC LIMIT 5");
foreach ($orders as $order) {
    echo "   ID: {$order->id}, User: {$order->username}, Amount: {$order->amount}, PayPal ID: {$order->payment_id}, Created: {$order->created_at}\n";
}
echo "\n";

// Check max ID
echo "3. Database stats:\n";
$maxId = DB::selectOne("SELECT MAX(id) as max_id, COUNT(*) as total FROM orders");
echo "   Max ID: {$maxId->max_id}\n";
echo "   Total Orders: {$maxId->total}\n";
echo "   Expected Next ID: " . ($maxId->max_id + 1) . "\n\n";

// Check order_items for the problematic order
echo "4. Checking order_items table:\n";
$items = DB::select("SELECT order_id, COUNT(*) as count FROM order_items GROUP BY order_id ORDER BY order_id DESC LIMIT 5");
foreach ($items as $item) {
    echo "   Order ID {$item->order_id} has {$item->count} items\n";
}
echo "\n";

// Look for the specific PayPal order
echo "5. Searching for PayPal order ID '3M097096W56607933':\n";
$paypalOrder = DB::selectOne("SELECT id, username, amount, status FROM orders WHERE payment_id = '3M097096W56607933'");
if ($paypalOrder) {
    echo "   Found! Database ID: {$paypalOrder->id}\n";
    echo "   Username: {$paypalOrder->username}\n";
    echo "   Amount: {$paypalOrder->amount}\n";
    echo "   Status: {$paypalOrder->status}\n";
} else {
    echo "   NOT FOUND in orders table\n";
}

echo "\n=================================================\n";
echo "DIAGNOSIS COMPLETE\n";
echo "=================================================\n\n";

echo "ISSUE SUMMARY:\n";
if ($result && $result->AUTO_INCREMENT != ($maxId->max_id + 1)) {
    echo "❌ AUTO_INCREMENT is WRONG! It's {$result->AUTO_INCREMENT} but should be " . ($maxId->max_id + 1) . "\n";
    echo "   This causes Laravel to get wrong IDs when saving.\n\n";
    echo "FIX: Run 'php fix_database_now.php' to fix the auto-increment.\n";
} else {
    echo "✓ AUTO_INCREMENT looks correct\n";
}
