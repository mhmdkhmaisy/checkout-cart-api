-- Fix auto-increment values to match current data
-- Run this AFTER checking the current state

SET @max_order_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM orders);
SET @max_order_item_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM order_items);
SET @max_product_id = (SELECT IFNULL(MAX(id), 0) + 1 FROM products);

SET @sql1 = CONCAT('ALTER TABLE orders AUTO_INCREMENT = ', @max_order_id);
SET @sql2 = CONCAT('ALTER TABLE order_items AUTO_INCREMENT = ', @max_order_item_id);
SET @sql3 = CONCAT('ALTER TABLE products AUTO_INCREMENT = ', @max_product_id);

PREPARE stmt1 FROM @sql1;
PREPARE stmt2 FROM @sql2;
PREPARE stmt3 FROM @sql3;

EXECUTE stmt1;
EXECUTE stmt2;
EXECUTE stmt3;

DEALLOCATE PREPARE stmt1;
DEALLOCATE PREPARE stmt2;
DEALLOCATE PREPARE stmt3;

-- Verify the fix
SELECT 
    TABLE_NAME,
    AUTO_INCREMENT
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN ('orders', 'order_items', 'products');
