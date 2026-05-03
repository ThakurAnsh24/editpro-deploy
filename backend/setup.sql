-- EditPro Database Setup Script
-- Run this to create the database and tables

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS editpro;
USE editpro;

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    service_type VARCHAR(50) NOT NULL,
    sub_service VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    delivery_date DATE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_screenshot VARCHAR(255) DEFAULT NULL,
    description TEXT,
    
    -- New fields for editing preferences
    editing_style VARCHAR(255) DEFAULT '',
    aspect_ratio VARCHAR(50) DEFAULT '',
    duration_preference VARCHAR(50) DEFAULT '',
    special_instructions TEXT,
    
    -- Poster design preferences
    design_style VARCHAR(255),
    color_theme VARCHAR(255),
    text_style VARCHAR(100),
    
    -- Admin fields
    internal_notes TEXT,
    is_urgent TINYINT DEFAULT 0,
    customer_notes TEXT,
    payment_status VARCHAR(20) DEFAULT 'Pending',
    paid_amount DECIMAL(10,2) DEFAULT 0.00,
    assigned_to VARCHAR(100),
    customer_blacklist TINYINT DEFAULT 0,
    customer_rating INT DEFAULT 0,
    customer_feedback TEXT,
    
    -- Preview & Approval fields
    preview_file VARCHAR(255) DEFAULT NULL,
    preview_sent_at DATETIME DEFAULT NULL,
    client_approval VARCHAR(20) DEFAULT 'Pending',
    client_approval_at DATETIME DEFAULT NULL,
    client_feedback TEXT DEFAULT NULL,
    revision_count INT DEFAULT 0,
    
    status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for faster queries
CREATE INDEX idx_orders_phone ON orders(phone);
CREATE INDEX idx_orders_service ON orders(service_type);
CREATE INDEX idx_orders_status ON orders(status);

-- Insert sample data for testing (optional)
-- INSERT INTO orders (name, phone, service_type, sub_service, delivery_date, price, payment_method) VALUES 
-- ('Test User', '9015353021', 'edit', 'Reel Edit', '2025-01-20', 499, 'GPay');

