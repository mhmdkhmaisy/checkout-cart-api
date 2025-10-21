# Bundle System & Checkout Error Fixes

## Issues Found

### 1. **CRITICAL: Table Engine Issue (MyISAM vs InnoDB)**
The `orders`, `order_items`, and `products` tables were using the MyISAM engine instead of InnoDB. MyISAM does NOT support:
- **Transactions** (causing order creation to succeed but order items to fail)
- **Foreign key constraints** (leading to data integrity issues)
- **Row-level locking** (causing concurrency problems)

**This was the PRIMARY cause of the error**: The order was being created successfully (ID 36990 logged), but when creating order items within the same transaction, MySQL couldn't find the order because MyISAM doesn't support proper transaction isolation.

### 2. Foreign Key Constraint on product_id
The `order_items` table had a strict foreign key constraint on `product_id` that required products to exist in the `products` table. When:
- A product was deleted after being added to cart
- A product_id didn't match an existing product
- Bundle products had invalid references

This could cause the checkout process to fail.

### 3. Auto-Increment Corruption
The `orders` table had an auto-increment value of 36990, but the actual max order_id was only 72. This caused:
- Foreign key constraint violations (trying to reference non-existent order_id 36990)
- Order creation appearing to succeed (logged) but order_items failing

This happens when records are deleted but auto-increment is never reset.

### 4. CheckoutController Not Handling Missing Products
The CheckoutController had fallback logic for missing products but still tried to insert invalid product_ids, causing constraint violations.

## Fixes Applied

### 1. **CRITICAL FIX: Convert Tables to InnoDB**
**File**: `database/migrations/2025_10_21_175525_fix_table_engines_for_transactions.php`

Converts all tables to InnoDB engine:
```sql
ALTER TABLE orders ENGINE = InnoDB;
ALTER TABLE order_items ENGINE = InnoDB;
ALTER TABLE products ENGINE = InnoDB;
```

This ensures proper transaction support and foreign key constraints work correctly.

### 2. Migration: Make product_id Nullable
**File**: `database/migrations/2025_10_21_174524_make_product_id_nullable_in_order_items_table.php`

- Drops the existing foreign key constraint on `product_id`
- Makes `product_id` nullable
- Re-adds the constraint with `onDelete('set null')` behavior
- This allows order items to exist even if the product is deleted later

### 3. CheckoutController: Handle Missing Products
**File**: `app/Http/Controllers/Api/CheckoutController.php` (Line 71)

Changed from:
```php
'product_id' => $item['product_id'],
```

To:
```php
'product_id' => $product ? $product->id : null,
```

This ensures that if a product doesn't exist, we set `product_id` to `null` instead of trying to insert an invalid foreign key.

### 4. OrderItem Model: Default Null Value
**File**: `app/Models/OrderItem.php`

Added default attribute to support nullable product_id:
```php
protected $attributes = [
    'product_id' => null
];
```

## How Bundle System Works

The current bundle system:
1. Stores bundle items in the `product_items` table
2. Each bundle product can have multiple `ProductItem` records
3. When checking out, bundles are NOT expanded - they're stored as a single order item
4. The `qty_unit` field is used to calculate `total_qty` (quantity × qty_unit)
5. The `ClaimController` already handles null products correctly

## Fix Auto-Increment Corruption

If you're seeing order IDs that don't match your actual data (e.g., trying to create order 36990 when max is 72), you have two options:

### Option 1: Reset All Store Data (Recommended for Testing/Development)

This command will **DELETE ALL orders, order_items, and optionally products**, and reset auto-increment to 1:

```bash
# Reset orders and order_items only
php artisan store:reset

# Also reset products table
php artisan store:reset --products
```

⚠️ **WARNING**: This deletes ALL store data! Only use in development/testing.

### Option 2: Fix Auto-Increment Without Deleting Data

Run this SQL to reset auto-increment counters to match your current data:

```sql
SET @max_order_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM orders);
SET @max_order_item_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM order_items);
SET @max_product_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM products);

ALTER TABLE orders AUTO_INCREMENT = @max_order_id;
ALTER TABLE order_items AUTO_INCREMENT = @max_order_item_id;
ALTER TABLE products AUTO_INCREMENT = @max_product_id;
```

Or use the pre-made SQL file: `fix_auto_increment.sql`

## Testing Instructions

**IMPORTANT: Run migrations AND fix auto-increment:**

```bash
# 1. First, convert tables to InnoDB (CRITICAL!)
php artisan migrate

# 2. Fix auto-increment (choose one option above)
php artisan store:reset  # OR run the SQL manually

# 3. Check that tables are now InnoDB
mysql -u your_user -p your_database -e "SHOW TABLE STATUS WHERE Name IN ('orders', 'order_items', 'products');"
```

Test scenarios:
- ✅ Checkout with existing products
- ✅ Checkout with bundle products
- ✅ Try to checkout with a product that was deleted
- ✅ Verify that claims still work correctly
- ✅ Test concurrent checkouts (with InnoDB row-level locking)

## Why This Happened

Your tables were likely created with MyISAM as the default engine (common in older XAMPP installations). When Laravel's DB transaction wraps the order creation:

1. **MyISAM behavior**: Auto-commits immediately, but changes aren't visible within the same transaction
2. **Result**: Order gets created (ID 36990), but when trying to create order_items in the same transaction, it can't find order_id 36990
3. **Error**: Foreign key constraint violation on `order_id`

With InnoDB, the transaction properly isolates the changes and the order_id is immediately visible to subsequent queries within the same transaction.

## Additional Notes

- The `ClaimController` already has proper null checks (line 43) so it will skip order items without products
- The WebhookService doesn't interact with order items directly, so no changes needed there
- All order history is preserved with product names even if products are deleted
- InnoDB provides better data integrity and concurrent access
