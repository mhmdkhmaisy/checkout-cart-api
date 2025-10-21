# Bundle System & Checkout Error Fixes

## Issues Found

### 1. Foreign Key Constraint Violation
The `order_items` table had a strict foreign key constraint on `product_id` that required products to exist in the `products` table. When:
- A product was deleted after being added to cart
- A product_id didn't match an existing product
- Bundle products had invalid references

This caused the checkout process to fail with a foreign key constraint violation error.

### 2. CheckoutController Not Handling Missing Products
The CheckoutController had fallback logic for missing products (lines 64-65) but still tried to insert the product_id even when the product didn't exist, causing constraint violations.

## Fixes Applied

### 1. Migration: Make product_id Nullable
**File**: `database/migrations/2025_10_21_174524_make_product_id_nullable_in_order_items_table.php`

- Drops the existing foreign key constraint on `product_id`
- Makes `product_id` nullable
- Re-adds the constraint with `onDelete('set null')` behavior
- This allows order items to exist even if the product is deleted

### 2. CheckoutController: Handle Missing Products
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

### 3. OrderItem Model: Default Null Value
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

## Testing Instructions

1. Run the migration:
   ```bash
   php artisan migrate
   ```

2. Test scenarios:
   - Checkout with existing products
   - Checkout with bundle products
   - Try to checkout with a product that was deleted
   - Verify that claims still work correctly

## Additional Notes

- The `ClaimController` already has proper null checks (line 43) so it will skip order items without products
- The WebhookService doesn't interact with order items directly, so no changes needed there
- All order history is preserved with product names even if products are deleted
