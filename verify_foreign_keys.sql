-- Run this SQL to verify and fix foreign key constraints
-- Usage: mysql -u root -p rsps_donations < verify_foreign_keys.sql

USE rsps_donations;

-- Show current foreign key constraints on order_items
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME,
    DELETE_RULE,
    UPDATE_RULE
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'order_items'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Show table engines
SELECT 
    TABLE_NAME,
    ENGINE,
    AUTO_INCREMENT,
    TABLE_ROWS
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN ('orders', 'order_items', 'products');

-- Show max IDs vs auto-increment
SELECT 'orders' as table_name, MAX(id) as max_id FROM orders
UNION ALL
SELECT 'order_items', MAX(id) FROM order_items
UNION ALL
SELECT 'products', MAX(id) FROM products;
