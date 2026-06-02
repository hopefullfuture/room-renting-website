-- 1. Create the Database
CREATE DATABASE IF NOT EXISTS room_rent_db;
USE room_rent_db;

-- 2. Create Users Table (Admin, Landlord, Tenant)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'landlord', 'tenant') NOT NULL
);

-- 3. Create Rooms Table
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    landlord_id INT,
    title VARCHAR(255) NOT NULL,
    location VARCHAR(100) NOT NULL,
    price INT NOT NULL,
    image_path VARCHAR(255),
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. Insert Dummy Data for Testing
-- Use 'password123' for all these users for now
INSERT INTO users (full_name, email, password, role) VALUES 
('Admin User', 'admin@gmail.com', 'password123', 'admin'),
('Hari Sharma', 'hari@gmail.com', 'password123', 'landlord'),
('Sita Thapa', 'sita@gmail.com', 'password123', 'tenant');

INSERT INTO rooms (landlord_id, title, location, price, image_path) VALUES 
(2, 'Single Room with Balcony', 'Kathmandu', 6500, 'room1.jpg'),
(2, 'Flat for Rent', 'Lalitpur', 15000, 'room2.jpg'),
(2, 'Budget Room', 'Bhaktapur', 4000, 'room3.jpg');