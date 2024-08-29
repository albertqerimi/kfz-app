-- Create database
CREATE DATABASE IF NOT EXISTS kfz_db;
USE kfz_db;

-- Drop tables if they exist (for development purposes)
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS autos;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS products;

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
    email VARCHAR(255) NOT NULL,
    vin_number VARCHAR(50) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    license_plate VARCHAR(20) NOT NULL,
    tuv_date DATE NOT NULL,
    kundennummer VARCHAR(50) NOT NULL UNIQUE
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
INSERT INTO clients (name, street, house_number, postal_code, city, state, country, phone, email, vin_number, brand, model, license_plate, tuv_date, kundennummer) VALUES
('John Doe', '123 Elm St', '10A', '12345', 'Springfield', 'IL', 'USA', '123-456-7890', 'john.doe@example.com', '1HGBH41JXMN109186', 'Toyota', 'Camry', 'XYZ123', '2025-05-20', 'KdNR00001'),
('Jane Smith', '456 Oak St', '20B', '23456', 'Shelbyville', 'IL', 'USA', '098-765-4321', 'jane.smith@example.com', '2HGBH41JXMN109187', 'Honda', 'Civic', 'ABC456', '2026-06-15', 'KdNR00002'),
('Alice Johnson', '789 Pine St', '30C', '34567', 'Capital City', 'IL', 'USA', '234-567-8901', 'alice.johnson@example.com', '3HGBH41JXMN109188', 'Ford', 'Focus', 'LMN789', '2027-07-10', 'KdNR00003'),
('Bob Brown', '135 Maple St', '40D', '45678', 'Eastwood', 'IL', 'USA', '345-678-9012', 'bob.brown@example.com', '4HGBH41JXMN109189', 'Chevrolet', 'Malibu', 'OPQ012', '2028-08-25', 'KdNR00004'),
('Carol White', '246 Cedar St', '50E', '56789', 'Westfield', 'IL', 'USA', '456-789-0123', 'carol.white@example.com', '5HGBH41JXMN109190', 'Nissan', 'Altima', 'RST345', '2029-09-30', 'KdNR00005');


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

