-- USB Store Database Setup
-- Run this in phpMyAdmin to create the database and tables

CREATE DATABASE IF NOT EXISTS usb_store;
USE usb_store;

-- Users table for customer accounts
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    secondary_mobile VARCHAR(15),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    pin_code VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    quantity_available INT DEFAULT 0,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    customer_name VARCHAR(100),
    customer_mobile VARCHAR(15),
    customer_secondary_mobile VARCHAR(15),
    delivery_address TEXT,
    delivery_city VARCHAR(50),
    delivery_state VARCHAR(50),
    delivery_pin_code VARCHAR(10),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Cart table for session-based cart
CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Insert default admin user
INSERT INTO admin_users (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample products
INSERT INTO products (name, description, price, quantity_available, image_url) VALUES
('SanDisk Ultra 32GB', 'High-speed USB 3.0 flash drive with 32GB storage capacity', 899.00, 50, 'https://images.pexels.com/photos/4792728/pexels-photo-4792728.jpeg'),
('Kingston DataTraveler 64GB', 'Reliable USB 3.0 flash drive with 64GB storage', 1299.00, 30, 'https://images.pexels.com/photos/4792728/pexels-photo-4792728.jpeg'),
('SanDisk Cruzer Blade 16GB', 'Compact and portable USB 2.0 flash drive', 499.00, 100, 'https://images.pexels.com/photos/4792728/pexels-photo-4792728.jpeg'),
('HP v236w 128GB', 'Premium metal USB 2.0 flash drive with large capacity', 2199.00, 20, 'https://images.pexels.com/photos/4792728/pexels-photo-4792728.jpeg'),
('Transcend JetFlash 790 32GB', 'Durable USB 3.1 flash drive with sleek design', 1099.00, 40, 'https://images.pexels.com/photos/4792728/pexels-photo-4792728.jpeg');