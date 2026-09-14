CREATE DATABASE IF NOT EXISTS primeburger_inventory
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE primeburger_inventory;


CREATE TABLE products (
    product_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,

    category VARCHAR(100) NULL,

    quantity INT UNSIGNED NOT NULL DEFAULT 0,

    unit VARCHAR(50) NOT NULL DEFAULT 'pcs',

    expiry_date DATE NULL,

    reorder_level INT UNSIGNED NOT NULL DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE inventory_logs (
    log_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    product_id INT UNSIGNED NOT NULL,

    log_type ENUM(
        'stock_in',
        'stock_out',
        'adjustment'
    ) NOT NULL,

    quantity INT UNSIGNED NOT NULL,

    description VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_inventory_logs_product
        FOREIGN KEY (product_id)
        REFERENCES products(product_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


CREATE INDEX idx_products_expiry
ON products(expiry_date);


CREATE INDEX idx_inventory_logs_created
ON inventory_logs(created_at);