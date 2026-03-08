-- Event Management System Database Schema

CREATE DATABASE IF NOT EXISTS event_management;
USE event_management;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    user_type ENUM('admin', 'vendor', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Halls Table
CREATE TABLE IF NOT EXISTS halls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    capacity INT NOT NULL,
    location VARCHAR(255) NOT NULL,
    price_per_day DECIMAL(10, 2) NOT NULL,
    amenities TEXT,
    image VARCHAR(255),
    status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vendors Table
CREATE TABLE IF NOT EXISTS vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    business_name VARCHAR(150) NOT NULL,
    service_type ENUM('catering', 'decoration', 'photography', 'music', 'other') NOT NULL,
    description TEXT,
    contact_number VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    price_range VARCHAR(50),
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hall_id INT NOT NULL,
    event_name VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    event_type VARCHAR(100),
    guests_count INT,
    special_requirements TEXT,
    total_amount DECIMAL(10, 2),
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hall_id) REFERENCES halls(id) ON DELETE CASCADE
);

-- Booking Vendors (Many-to-Many relationship)
CREATE TABLE IF NOT EXISTS booking_vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    vendor_id INT NOT NULL,
    status ENUM('requested', 'confirmed', 'declined') DEFAULT 'requested',
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, user_type) VALUES 
('Admin', 'admin@eventmanager.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample Halls Data
INSERT INTO halls (name, description, capacity, location, price_per_day, amenities, image) VALUES
('Grand Ballroom', 'Elegant ballroom with crystal chandeliers and spacious dance floor', 500, '123 Main Street, Downtown', 5000.00, 'AC, Stage, Sound System, Parking, Catering Kitchen', 'hall1.jpg'),
('Garden Paradise', 'Beautiful outdoor venue with lush gardens and fountain', 300, '456 Park Avenue, Westside', 3500.00, 'Outdoor Seating, Garden, Parking, Lighting', 'hall2.jpg'),
('Royal Palace Hall', 'Luxurious hall with traditional architecture and modern amenities', 400, '789 Royal Road, Uptown', 6000.00, 'AC, VIP Rooms, Stage, Premium Sound, Valet Parking', 'hall3.jpg'),
('Sunset Terrace', 'Rooftop venue with stunning city views', 200, '321 Sky Tower, Central', 4000.00, 'Open Air, Bar Counter, Lounge, City View', 'hall4.jpg');
