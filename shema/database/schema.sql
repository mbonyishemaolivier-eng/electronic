-- neresStore Database Schema
-- Electronics E-Commerce Platform

CREATE DATABASE IF NOT EXISTS neresstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE neresstore;

-- Categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255) DEFAULT 'default.jpg',
    stock INT NOT NULL DEFAULT 0,
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Customers
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL DEFAULT 'Kigali',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



-- Orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    shipping DECIMAL(10, 2) NOT NULL DEFAULT 2000.00,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Order Items
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Admins
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Activity Logs
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    entity_type VARCHAR(50) DEFAULT NULL,
    entity_id INT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Admin (password: admin123)
INSERT INTO admins (username, password, full_name) VALUES
('admin', '$2y$10$kH6Jvy73jgd5ofI3XhWHret82KtpI3tTsUe6lfwzkyB.3UA97r4x2', 'Store Administrator');

-- Seed Categories
INSERT INTO categories (name, slug, description) VALUES
('Smartphones', 'smartphones', 'Latest smartphones and mobile devices'),
('Laptops', 'laptops', 'Laptops and notebooks for work and gaming'),
('Audio', 'audio', 'Headphones, speakers, and audio accessories'),
('Accessories', 'accessories', 'Chargers, cables, cases, and more'),
('TV & Monitors', 'tv-monitors', 'Televisions and computer monitors');

-- Seed Products
INSERT INTO products (category_id, name, slug, description, price, image, stock, featured) VALUES
(1, 'Samsung Galaxy A54', 'samsung-galaxy-a54', '6.4" Super AMOLED display, 50MP camera, 5000mAh battery. Perfect for everyday use in Rwanda.', 385000, 'galaxy-a54.jpg', 25, 1),
(1, 'iPhone 13', 'iphone-13', 'A15 Bionic chip, dual-camera system, Ceramic Shield. Premium Apple experience.', 890000, 'iphone-13.jpg', 15, 1),
(1, 'Tecno Spark 20', 'tecno-spark-20', 'Affordable smartphone with 50MP camera and 5000mAh battery. Great value for money.', 195000, 'tecno-spark.jpg', 40, 0),
(2, 'HP Pavilion 15', 'hp-pavilion-15', 'Intel Core i5, 8GB RAM, 512GB SSD. Reliable laptop for students and professionals.', 750000, 'hp-pavilion.jpg', 12, 1),
(2, 'Lenovo IdeaPad 3', 'lenovo-ideapad-3', 'AMD Ryzen 5, 8GB RAM, 256GB SSD. Lightweight and portable design.', 620000, 'lenovo-ideapad.jpg', 18, 0),
(2, 'MacBook Air M2', 'macbook-air-m2', 'Apple M2 chip, 8GB RAM, 256GB SSD. Ultra-thin design with all-day battery life.', 1450000, 'macbook-air.jpg', 8, 1),
(3, 'Sony WH-1000XM5', 'sony-wh1000xm5', 'Industry-leading noise cancellation, 30-hour battery, premium sound quality.', 420000, 'sony-headphones.jpg', 20, 1),
(3, 'JBL Flip 6', 'jbl-flip-6', 'Portable Bluetooth speaker with IP67 waterproof rating. Perfect for outdoor use.', 145000, 'jbl-flip.jpg', 35, 0),
(3, 'AirPods Pro 2', 'airpods-pro-2', 'Active noise cancellation, spatial audio, MagSafe charging case.', 310000, 'airpods-pro.jpg', 22, 1),
(4, 'Anker PowerCore 20000', 'anker-powercore', '20000mAh portable charger with fast charging. Keep your devices powered on the go.', 55000, 'anker-powerbank.jpg', 50, 0),
(4, 'USB-C Hub 7-in-1', 'usb-c-hub', 'HDMI, USB 3.0, SD card reader, and PD charging. Essential laptop accessory.', 35000, 'usb-hub.jpg', 60, 0),
(4, 'Phone Case Universal', 'phone-case', 'Durable silicone case with shock absorption. Fits most smartphone models.', 15000, 'phone-case.jpg', 100, 0),
(5, 'Samsung 55" 4K Smart TV', 'samsung-55-tv', 'Crystal UHD 4K, Tizen OS, HDR support. Transform your living room entertainment.', 980000, 'samsung-tv.jpg', 10, 1),
(5, 'Dell 24" Monitor', 'dell-24-monitor', 'Full HD IPS display, thin bezels, adjustable stand. Ideal for office and home.', 285000, 'dell-monitor.jpg', 15, 0),
(5, 'LG 32" Gaming Monitor', 'lg-32-gaming', '165Hz refresh rate, 1ms response time, AMD FreeSync. Built for gamers.', 520000, 'lg-monitor.jpg', 8, 1);
