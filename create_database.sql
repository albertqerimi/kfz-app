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
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    tuv_date DATE
);

-- Create invoices table
CREATE TABLE IF NOT EXISTS invoices (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    client_id INT(11) NOT NULL,
    date DATETIME NOT NULL,
    due_date DATE,
    discount DECIMAL(10,2),
    vehicle_id INT(11) NULL, -- Allow NULL values
    sub_total DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2),
    total_amount DECIMAL(10,2) NOT NULL,
    payment_form VARCHAR(50), 
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
    quantity_type ENUM('stk', 'litre', 'hour', 'pauschal', 'days') NOT NULL DEFAULT 'stk',
    price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO clients (name, street, house_number, postal_code, city, country, phone, email, kundennummer) VALUES
('John Doe', '123 Elm St', '10A', '12345', 'Springfield', 'USA', '123-456-7890', 'john.doe@example.com', '00001'),
('Jane Smith', '456 Oak St', '20B', '23456', 'Shelbyville', 'USA', '098-765-4321', 'jane.smith@example.com', '00002'),
('Alice Johnson', '789 Pine St', '30C', '34567', 'Capital City', 'USA', '234-567-8901', 'alice.johnson@example.com', '00003'),
('Bob Brown', '135 Maple St', '40D', '45678', 'Eastwood', 'USA', '345-678-9012', 'bob.brown@example.com', '00004'),
('Carol White', '246 Cedar St', '50E', '56789', 'Westfield', 'USA', '456-789-0123', 'carol.white@example.com', '00005');

INSERT INTO vehicles (client_id, license_plate, brand, model, year, vin) VALUES
(1, 'ABC123', 'Toyota', 'Corolla', 2020, '1HGBH41JXMN109186'),
(1, 'XYZ789', 'Honda', 'Civic', 2021, '2HGBH41JXMN109187');

INSERT INTO products (name) VALUES
('Oil Change'),
('Tire Rotation');

-- Example of adding an invoice
INSERT INTO invoices ( client_id, date, sub_total, discount, tax, vehicle_id, total_amount) VALUES
(, 1, NOW(), 100.00, 10.00, 17.10, NULL, 107.10);

INSERT INTO invoice_items (invoice_id, product_id, quantity, price, total_price) VALUES
(1, 1, 1, 30.00, 30.00),
(1, 2, 1, 70.00, 70.00);
