-- Create database
CREATE DATABASE IF NOT EXISTS kfz_db;
USE kfz_db;

-- Drop tables if they exist (for development purposes)
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS autos;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS products;

-- Create clients table
CREATE TABLE IF NOT EXISTS clients (

    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    street VARCHAR(255) NOT NULL,
    house_number VARCHAR(50) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    city VARCHAR(255) NOT NULL,
    state VARCHAR(255),
    country VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255) NOT NULL

);

-- Create autos table
CREATE TABLE autos (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    client_id INT(11) NOT NULL,
    license_plate VARCHAR(50) NOT NULL,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT(11) NOT NULL,
    vin VARCHAR(50) NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Create invoices table
CREATE TABLE IF NOT EXISTS invoices (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    client_id INT(11) NOT NULL,
    date DATETIME NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2),
    tax DECIMAL(10,2),
    auto_id INT(11),
    total_amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (auto_id) REFERENCES autos(id)
);
-- Create invoice_items table
CREATE TABLE IF NOT EXISTS products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL
);

-- Create products table
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT(11) NOT NULL,
    product_id INT(11),
    quantity INT(11) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);
-- Insert sample data (Optional)
INSERT INTO clients (name, email, phone) VALUES
('John Doe', 'john.doe@example.com', '123-456-7890'),
('Jane Smith', 'jane.smith@example.com', '098-765-4321');

INSERT INTO autos (client_id, license_plate, make, model, year, vin) VALUES
(1, 'ABC123', 'Toyota', 'Corolla', 2020, '1HGBH41JXMN109186'),
(1, 'XYZ789', 'Honda', 'Civic', 2021, '2HGBH41JXMN109187');

INSERT INTO products (name) VALUES
('Oil Change'),
('Tire Rotation');

-- Example of adding an invoice (just for demo purposes, ensure to adjust for your needs)
INSERT INTO invoices (invoice_number, client_id, date, total, discount, tax, auto_id, total_amount) VALUES
('000001', 1, NOW(), 100.00, 10.00, 17.10, 1, 107.10);

INSERT INTO invoice_items (invoice_id, product_id, quantity, price, total_price) VALUES
(1, 1, 1, 30.00, 30.00),
(1, 2, 1, 70.00, 70.00);

