CREATE DATABASE IF NOT EXISTS campusfind
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE campusfind;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    type ENUM('lost','found') NOT NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(160) NOT NULL,
    item_date DATE NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('active','returned') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_items_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_items_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_items_type (type),
    INDEX idx_items_status (status),
    INDEX idx_items_category (category_id),
    INDEX idx_items_location (location),
    INDEX idx_items_date (item_date),
    INDEX idx_items_created_at (created_at)
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (name) VALUES
('Electronics'),
('ID Cards & Documents'),
('Books & Notes'),
('Bags & Accessories'),
('Clothing'),
('Keys'),
('Jewellery'),
('Stationery'),
('Sports'),
('Other');
