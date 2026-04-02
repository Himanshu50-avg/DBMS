CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    category_slug VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

CREATE TABLE cart_items (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    shipping_address TEXT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    order_status VARCHAR(50) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(order_id),
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    payment_method VARCHAR(50) NOT NULL,
    payment_status VARCHAR(50) NOT NULL,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE INDEX idx_products_name ON products(product_name);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_orders_user_status ON orders(user_id, order_status);
CREATE INDEX idx_orders_customer_email ON orders(customer_email);
CREATE INDEX idx_reviews_product_rating ON reviews(product_id, rating);

INSERT INTO categories (category_name, category_slug) VALUES
('Wearables', 'wearables'),
('Audio', 'audio'),
('Home', 'home'),
('Lifestyle', 'lifestyle'),
('Accessories', 'accessories');

INSERT INTO products (category_id, product_name, description, price, stock, image_url) VALUES
(1, 'AeroFit Smart Watch', 'Fitness-focused smart watch with heart-rate tracking and AMOLED display.', 6999.00, 12, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=900&q=80'),
(2, 'NovaSound Headphones', 'Wireless over-ear headphones with noise cancellation and 30-hour battery life.', 4599.00, 18, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=900&q=80'),
(2, 'PixelNest Speaker', 'Compact Bluetooth speaker with crisp bass and modern fabric finish.', 2499.00, 26, 'https://images.unsplash.com/photo-1545454675-3531b543be5d?auto=format&fit=crop&w=900&q=80'),
(3, 'LumaDesk Lamp', 'Minimal LED study lamp with touch brightness controls and USB charging.', 1799.00, 30, 'https://images.unsplash.com/photo-1517999144091-3d9dca6d1e43?auto=format&fit=crop&w=900&q=80'),
(4, 'TerraBottle Pro', 'Insulated stainless bottle designed for college, travel, and daily hydration.', 899.00, 40, 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&w=900&q=80'),
(5, 'CodePack Backpack', 'Laptop backpack with anti-theft zip design and ergonomic storage layout.', 3299.00, 14, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80');
