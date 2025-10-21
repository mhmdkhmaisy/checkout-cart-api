-- Check table engines and auto-increment values
SELECT 
    TABLE_NAME,
    ENGINE,
    AUTO_INCREMENT,
    TABLE_ROWS
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN ('orders', 'order_items', 'products');

-- Check current max IDs
SELECT 'orders' as table_name, MAX(id) as max_id FROM orders
UNION ALL
SELECT 'order_items', MAX(id) FROM order_items
UNION ALL
SELECT 'products', MAX(id) FROM products;
