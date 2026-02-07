-- Create database (if not already created)
CREATE DATABASE IF NOT EXISTS dera_shop
 CHARACTER SET utf8mb4
 COLLATE utf8mb4_unicode_ci;

USE dera_shop;

-- =========================
-- 1. USERS (admins/customers) - NO CHANGES
-- =========================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
  phone VARCHAR(15),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin account:
-- Email: admin@dera-shop.test | Password: admin123
INSERT INTO users (name, email, password, role, phone) VALUES
('Admin', 'admin@dera-shop.test', SHA2('admin123',256), 'admin', '0712345678');

-- =========================
-- 2. PRODUCTS - ADDED CATEGORY & SIZE
-- =========================
DROP TABLE IF EXISTS products;
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  category VARCHAR(50) DEFAULT 'fashion', -- NEW: Women's Fashion, Accessories, etc.
  size VARCHAR(20), -- NEW: S, M, L, XL
  image VARCHAR(255),
  stock INT NOT NULL DEFAULT 0,
  featured TINYINT(1) DEFAULT 0, -- NEW: For featured products
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- REAL WOMEN'S FASHION PRODUCTS (Replace sample data)
INSERT INTO products (name, description, price, category, size, image, stock, featured) VALUES
('Floral Maxi Dress', 'Beautiful floral print maxi dress perfect for summer evenings. Soft cotton fabric with comfortable fit.', 4500.00, 'fashion', 'M,L', 'dress1.jpg', 8, 1),
('White Crop Top', 'Trendy white crop top perfect for casual outings. Pair with jeans or skirts.', 1800.00, 'fashion', 'S,M,L', 'crop1.jpg', 12, 0),
('Black High Waist Jeans', 'Slim fit high waist jeans with stretch fabric. Perfect for everyday wear.', 3200.00, 'fashion', 'S,M,L,XL', 'jeans1.jpg', 15, 1),
('Golden Hoop Earrings', 'Stylish 2-inch golden hoop earrings. Lightweight and comfortable.', 1200.00, 'accessories', 'One Size', 'earrings1.jpg', 20, 0),
('Red Handbag', 'Elegant red leather handbag with gold hardware. Perfect for parties.', 5800.00, 'accessories', 'One Size', 'bag1.jpg', 6, 1),
('Off-Shoulder Blouse', 'Romantic off-shoulder blouse in soft pastel pink. Flowy fit.', 2400.00, 'fashion', 'S,M,L', 'blouse1.jpg', 10, 0);

-- =========================
-- 3. ORDERS - ADDED PHONE
-- =========================
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL, -- Link to users table
  customer_name VARCHAR(150) NOT NULL,
  customer_email VARCHAR(150) NOT NULL,
  customer_phone VARCHAR(15) NOT NULL, -- REQUIRED for MPesa
  customer_address TEXT,
  total DECIMAL(10,2) NOT NULL,
  mpesa_code VARCHAR(50), -- NEW: MPesa transaction code
  status ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =========================
-- 4. ORDER ITEMS - NO CHANGES
-- =========================
DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- =========================
-- 5. CART SESSIONS (Optional backup)
-- =========================
CREATE TABLE IF NOT EXISTS cart_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255) NOT NULL,
  user_id INT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- SAMPLE ORDER
-- =========================
INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total, status)
VALUES ('Jane Doe', 'jane@example.com', '0712345678', 'Westlands, Nairobi', 8700.00, 'pending');

INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 4500.00),
(1, 3, 1, 3200.00);

-- =========================
-- INDEXES FOR PERFORMANCE
-- =========================
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_stock ON products(stock);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_customer ON orders(customer_phone);

