-- =====================================================================
-- PrimeBurger Inventory Management System
-- Database Schema
-- Engine: MySQL 8+ / MariaDB 10.4+
-- =====================================================================

CREATE DATABASE IF NOT EXISTS primeburger_ims
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE primeburger_ims;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  role ENUM('owner','staff') NOT NULL DEFAULT 'staff',
  status ENUM('active','disabled') NOT NULL DEFAULT 'active',
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role (role),
  INDEX idx_users_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- categories
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- suppliers
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS suppliers;
CREATE TABLE suppliers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  contact_person VARCHAR(100) NULL,
  phone VARCHAR(30) NULL,
  email VARCHAR(100) NULL,
  address VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_suppliers_name (name)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- products
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS products;
CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_code VARCHAR(30) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  category_id INT UNSIGNED NULL,
  supplier_id INT UNSIGNED NULL,
  unit VARCHAR(20) NOT NULL DEFAULT 'pcs',
  quantity DECIMAL(12,2) NOT NULL DEFAULT 0,
  minimum_stock_level DECIMAL(12,2) NOT NULL DEFAULT 0,
  cost_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  selling_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  date_received DATE NULL,
  expiration_date DATE NULL,
  qr_code VARCHAR(64) NOT NULL UNIQUE,
  status ENUM('active','archived') NOT NULL DEFAULT 'active',
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  CONSTRAINT fk_products_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
  CONSTRAINT fk_products_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_products_name (name),
  INDEX idx_products_expiration (expiration_date),
  INDEX idx_products_status (status),
  INDEX idx_products_qr (qr_code)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- inventory_transactions  (stock in / stock out ledger)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS inventory_transactions;
CREATE TABLE inventory_transactions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  type ENUM('in','out') NOT NULL,
  quantity DECIMAL(12,2) NOT NULL,
  reason VARCHAR(255) NULL,
  transaction_date DATE NOT NULL,
  user_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_txn_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_txn_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_txn_product (product_id),
  INDEX idx_txn_type (type),
  INDEX idx_txn_date (transaction_date)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- stock_history  (snapshot audit trail of quantity changes)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS stock_history;
CREATE TABLE stock_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  old_quantity DECIMAL(12,2) NOT NULL,
  new_quantity DECIMAL(12,2) NOT NULL,
  change_type ENUM('in','out','adjustment') NOT NULL,
  changed_by INT UNSIGNED NULL,
  changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_hist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_hist_user FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_hist_product (product_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- notifications
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('low_stock','expiring','expired') NOT NULL,
  product_id INT UNSIGNED NULL,
  message VARCHAR(255) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notif_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_notif_type (type),
  INDEX idx_notif_read (is_read)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- activity_logs
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS activity_logs;
CREATE TABLE activity_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  action VARCHAR(100) NOT NULL,
  description VARCHAR(255) NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_log_user (user_id),
  INDEX idx_log_action (action)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- SAMPLE DATA
-- =====================================================================

-- Default accounts. Password for BOTH accounts below is: Password123!
-- Hash generated with PHP password_hash() using PASSWORD_DEFAULT (bcrypt).
INSERT INTO users (username, email, password_hash, full_name, role, status) VALUES
('admin', 'owner@primeburger.test', '$2y$10$Yj3XtD4IxCAFieCEfuZ1xO2sNzSkTldy9pnEjGOuyk/0R7I.l6NLu', 'Dondee Lasig', 'owner', 'active'),
('staff1', 'staff1@primeburger.test', '$2y$10$Yj3XtD4IxCAFieCEfuZ1xO2sNzSkTldy9pnEjGOuyk/0R7I.l6NLu', 'Inventory Staff One', 'staff', 'active');

INSERT INTO categories (name, description) VALUES
('Buns & Bread', 'Burger buns, bread, and bakery items'),
('Meat & Patties', 'Beef, chicken, and other protein patties'),
('Vegetables', 'Lettuce, tomato, onion, and other fresh produce'),
('Condiments & Sauces', 'Ketchup, mayo, mustard, dressings'),
('Beverages', 'Soft drinks, juices, bottled water'),
('Packaging', 'Wrappers, boxes, cups, bags');

INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES
('Nueva Ecija Meat Supply', 'Ramon Cruz', '09171234567', 'sales@nemeatsupply.test', 'Science City of Muñoz, Nueva Ecija'),
('Fresh Farm Produce Co.', 'Liza Santos', '09181234567', 'orders@freshfarm.test', 'San Jose City, Nueva Ecija'),
('GoldenWheat Bakery Supplies', 'Mark Reyes', '09201234567', 'contact@goldenwheat.test', 'Cabanatuan City, Nueva Ecija'),
('CoolDrinks Distribution', 'Anna Lim', '09991234567', 'info@cooldrinks.test', 'San Jose City, Nueva Ecija');

INSERT INTO products
(product_code, name, category_id, supplier_id, unit, quantity, minimum_stock_level, cost_price, selling_price, date_received, expiration_date, qr_code, status, created_by)
VALUES
('PB-0001', 'Sesame Burger Buns', 1, 3, 'pack', 40, 15, 45.00, 65.00, CURDATE() - INTERVAL 2 DAY, CURDATE() + INTERVAL 4 DAY,  'QR-PB0001', 'active', 1),
('PB-0002', 'Beef Patty 120g', 2, 1, 'pcs', 8, 20, 28.00, 55.00, CURDATE() - INTERVAL 1 DAY, CURDATE() + INTERVAL 20 DAY, 'QR-PB0002', 'active', 1),
('PB-0003', 'Chicken Patty 100g', 2, 1, 'pcs', 30, 20, 22.00, 48.00, CURDATE() - INTERVAL 1 DAY, CURDATE() + INTERVAL 25 DAY, 'QR-PB0003', 'active', 1),
('PB-0004', 'Iceberg Lettuce', 3, 2, 'kg', 5, 5, 60.00, 0.00, CURDATE(), CURDATE() + INTERVAL 3 DAY, 'QR-PB0004', 'active', 1),
('PB-0005', 'Tomato', 3, 2, 'kg', 6, 5, 55.00, 0.00, CURDATE(), CURDATE() - INTERVAL 1 DAY, 'QR-PB0005', 'active', 1),
('PB-0006', 'Cheese Slice', 4, 3, 'pack', 25, 10, 90.00, 0.00, CURDATE() - INTERVAL 3 DAY, CURDATE() + INTERVAL 45 DAY, 'QR-PB0006', 'active', 1),
('PB-0007', 'Mayonnaise 1L', 4, 3, 'bottle', 12, 5, 120.00, 0.00, CURDATE() - INTERVAL 5 DAY, CURDATE() + INTERVAL 90 DAY, 'QR-PB0007', 'active', 1),
('PB-0008', 'Bottled Soda 1.5L', 5, 4, 'pcs', 48, 24, 35.00, 60.00, CURDATE() - INTERVAL 2 DAY, CURDATE() + INTERVAL 180 DAY, 'QR-PB0008', 'active', 1),
('PB-0009', 'Burger Wrap Paper', 6, 3, 'pack', 100, 30, 15.00, 0.00, CURDATE() - INTERVAL 10 DAY, NULL, 'QR-PB0009', 'active', 1),
('PB-0010', 'Take-out Box Medium', 6, 3, 'pack', 3, 20, 18.00, 0.00, CURDATE() - INTERVAL 10 DAY, NULL, 'QR-PB0010', 'active', 1);

INSERT INTO inventory_transactions (product_id, type, quantity, reason, transaction_date, user_id) VALUES
(1, 'in', 40, 'Weekly delivery', CURDATE() - INTERVAL 2 DAY, 1),
(2, 'in', 20, 'Weekly delivery', CURDATE() - INTERVAL 1 DAY, 1),
(2, 'out', 12, 'Daily sales usage', CURDATE(), 2),
(8, 'in', 48, 'Beverage restock', CURDATE() - INTERVAL 2 DAY, 1),
(10, 'out', 17, 'Daily sales usage', CURDATE() - INTERVAL 1 DAY, 2);

INSERT INTO notifications (type, product_id, message, is_read) VALUES
('low_stock', 2, 'Beef Patty 120g is below the minimum stock level.', 0),
('low_stock', 10, 'Take-out Box Medium is below the minimum stock level.', 0),
('expiring', 4, 'Iceberg Lettuce is nearing its expiration date.', 0),
('expired', 5, 'Tomato has expired.', 0);

INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES
(1, 'login', 'Owner logged in', '127.0.0.1'),
(2, 'stock_out', 'Recorded stock-out for Beef Patty 120g', '127.0.0.1');
