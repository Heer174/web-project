-- ==========================================================
-- Campus Lost and Found System - Database Schema
-- Database Name: campus_lost_found
-- ==========================================================

CREATE DATABASE IF NOT EXISTS campus_lost_found CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_lost_found;

-- ----------------------------------------------------------
-- Table: users
-- ----------------------------------------------------------
DROP TABLE IF EXISTS claims;
DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- Table: items
-- ----------------------------------------------------------
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('lost', 'found') NOT NULL,
    title VARCHAR(150) NOT NULL,
    category ENUM('Electronics', 'Student ID & Cards', 'Books & Stationery', 'Wallets & Bags', 'Keys', 'Clothing & Accessories', 'Others') NOT NULL,
    location VARCHAR(150) NOT NULL,
    item_date DATE NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    status ENUM('active', 'claimed', 'resolved') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- Table: claims
-- ----------------------------------------------------------
CREATE TABLE claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    claimer_id INT NOT NULL,
    proof_description TEXT NOT NULL,
    contact_info VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (claimer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------
-- Sample Data Insertion
-- Passwords below are hashed for 'password123'
-- ----------------------------------------------------------
INSERT INTO users (student_id, full_name, email, phone, password, role) VALUES
('ADMIN001', 'Campus Security Admin', 'admin@campus.edu', '555-0199', '$2y$10$wE0i7Xf2FqG6x.rK6ZfGceYqH1U7pM7MhC9D9vM2P3O4Q5R6S7T8u', 'admin'),
('STU202401', 'Alex Johnson', 'alex.j@campus.edu', '555-0142', '$2y$10$wE0i7Xf2FqG6x.rK6ZfGceYqH1U7pM7MhC9D9vM2P3O4Q5R6S7T8u', 'student'),
('STU202402', 'Sarah Williams', 'sarah.w@campus.edu', '555-0188', '$2y$10$wE0i7Xf2FqG6x.rK6ZfGceYqH1U7pM7MhC9D9vM2P3O4Q5R6S7T8u', 'student'),
('STU202403', 'David Lee', 'david.l@campus.edu', '555-0155', '$2y$10$wE0i7Xf2FqG6x.rK6ZfGceYqH1U7pM7MhC9D9vM2P3O4Q5R6S7T8u', 'student');

-- Sample Items
INSERT INTO items (user_id, type, title, category, location, item_date, description, image_path, contact_phone, status) VALUES
(2, 'lost', 'Black HP Spectre Laptop Charger (65W)', 'Electronics', 'Central Library - 2nd Floor Study Cubicles', '2026-10-09', 'Left my black 65W USB-C charger plugged under desk #14 on 2nd floor library near the science section.', NULL, '555-0142', 'active'),
(3, 'found', 'Blue Water Bottle & Graphing Calculator (Casio fx-991EX)', 'Electronics', 'Science Block B, Room 204', '2026-10-10', 'Found an insulated blue flask along with a Casio calculator on the back row bench after calculus class.', NULL, '555-0188', 'active'),
(4, 'lost', 'Brown Leather Wallet with ID Cards', 'Wallets & Bags', 'Campus Canteen / Food Court', '2026-10-08', 'Brown Fossil wallet lost during lunchtime near juice counter. Contains my driver license and student card.', NULL, '555-0155', 'active'),
(2, 'found', 'Set of 3 Keys with a Red Ferrari Keychain', 'Keys', 'Sports Complex / Basketball Court', '2026-10-10', 'Picked up keys with silver ring and red keychain lying near the bleachers. Handed to security reception.', NULL, '555-0142', 'active'),
(3, 'lost', 'Data Structures & Algorithms Textbook (CLRS 3rd Ed)', 'Books & Stationery', 'Computer Science Lab 3', '2026-10-07', 'Hardcover CLRS algorithms book with yellow sticky bookmarks inside chapter 4 and my initials on page 1.', NULL, '555-0188', 'resolved');

-- Sample Claim
INSERT INTO claims (item_id, claimer_id, proof_description, contact_info, status) VALUES
(2, 4, 'The calculator has a small silver scratch on the solar panel and my name initials DL written on the inside cover.', 'david.l@campus.edu / 555-0155', 'pending');
