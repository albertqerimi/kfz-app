-- Create database and use it
CREATE DATABASE IF NOT EXISTS kfz_db;
USE kfz_db;

-- Drop tables if they exist (for development purposes)
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS clients;

-- Create clients table
CREATE TABLE IF NOT EXISTS clients (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    street VARCHAR(255),
    house_number VARCHAR(50),
    postal_code VARCHAR(20),
    city VARCHAR(255),
    country VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(255),
    kundennummer VARCHAR(50) NOT NULL UNIQUE
);

-- Create vehicles table
CREATE TABLE IF NOT EXISTS vehicles (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    client_id INT(11),
    license_plate VARCHAR(50) NOT NULL UNIQUE,
    brand VARCHAR(50),
    model VARCHAR(100),
    year INT(11),
    vin VARCHAR(50) NOT NULL UNIQUE,
    hsn VARCHAR(10),    
    tsn VARCHAR(10),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    tuv_date DATE
);

-- Create invoices table
CREATE TABLE IF NOT EXISTS invoices (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    client_id INT(11) NOT NULL,
    date DATE NOT NULL,
    due_date DATE,
    discount DECIMAL(10,2),
    vehicle_id INT(11) NULL, -- Allow NULL values
    sub_total DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2),
    total_amount DECIMAL(10,2) NOT NULL,
    payment_form VARCHAR(50), 
    km_stand DECIMAL(10, 2),
    currency VARCHAR(10) DEFAULT 'EUR',
    status ENUM('Paid', 'Unpaid', 'Cancelled') DEFAULT 'Unpaid',
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

-- Create invoice_items table
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_description TEXT,
    quantity DECIMAL(10,2) NOT NULL,
    quantity_type ENUM('Stk', 'Liter', 'Stunde', 'Tag(e)', 'Kilogram', 'Meter', 'Paket') NOT NULL DEFAULT 'Stk',
    price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


INSERT INTO users (username, password) VALUES ('albert', '$2y$10$e0MY5pGGeLYo3szxTAPyW.u/dDhd4cDYoWh1O/J6pN62HpYY1nm4i'); 
