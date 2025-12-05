-- Buat database
CREATE DATABASE IF NOT EXISTS danautoba_ticketing;
USE danautoba_ticketing;

-- Tabel users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel hotels
CREATE TABLE hotels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    location VARCHAR(200),
    price_per_night DECIMAL(10,2),
    amenities TEXT,
    image_url VARCHAR(255),
    is_recommended BOOLEAN DEFAULT FALSE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Tabel bookings
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    hotel_id INT,
    check_in DATE,
    check_out DATE,
    guests INT,
    total_price DECIMAL(10,2),
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

-- Tabel feedback
CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    subject VARCHAR(200),
    message TEXT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'admin@danautoba.com', '$2y$10$YourHashedPasswordHere', 'admin');

-- Insert sample hotels
INSERT INTO hotels (name, description, location, price_per_night, amenities, image_url, is_recommended) VALUES
('Hotel Danau Toba Indah', 'Hotel dengan pemandangan langsung ke Danau Toba', 'Parapat, Sumatera Utara', 450000, '["WiFi Gratis", "Kolam Renang", "Restoran", "Parkir Gratis"]', 'hotel1.jpg', 1),
('Resort Samosir Luxury', 'Resort mewah di pulau Samosir', 'Pulau Samosir', 750000, '["WiFi Gratis", "Spa", "Restoran", "Fitness Center", "Parkir Gratis"]', 'hotel2.jpg', 1),
('Hotel Silimalombu', 'Hotel nyaman dengan harga terjangkau', 'Silimalombu', 250000, '["WiFi Gratis", "Restoran", "Parkir Gratis"]', 'hotel3.jpg', 0),
('Toba Village Resort', 'Resort dengan konsep tradisional Batak', 'Balige', 550000, '["WiFi Gratis", "Kolam Renang", "Restoran", "Parkir Gratis", "Tour Guide"]', 'hotel4.jpg', 1);