-- Logistics Database Schema
-- Run this SQL to create the database and tables

CREATE DATABASE IF NOT EXISTS logistics_db;
USE logistics_db;

-- Companies table (customers)
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    billing_address TEXT,
    vat_number VARCHAR(50),
    payment_terms INT DEFAULT 30,
    rate_per_pallet DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Load sheets table
CREATE TABLE load_sheets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    pickup_location VARCHAR(255),
    delivery_location VARCHAR(255),
    cargo_description TEXT,
    special_instructions TEXT,
    pallet_quantity INT NOT NULL,
    cargo_weight DECIMAL(10,2),
    delivery_method ENUM('own', 'contractor') DEFAULT 'own',
    contractor_name VARCHAR(255),
    contractor_cost DECIMAL(10,2) DEFAULT 0.00,
    rate_per_pallet DECIMAL(10,2),
    rate_override DECIMAL(10,2) DEFAULT 0.00,
    rate_override_reason TEXT,
    final_rate DECIMAL(10,2) NOT NULL,
    requested_date DATE,
    status ENUM('draft', 'confirmed', 'completed') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Invoices table
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    load_sheet_id INT NOT NULL,
    company_id INT NOT NULL,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    vat_rate DECIMAL(5,2) DEFAULT 15.00,
    vat_amount DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
    payment_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (load_sheet_id) REFERENCES load_sheets(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Statements table
CREATE TABLE statements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    statement_period VARCHAR(20) NOT NULL,
    statement_date DATE NOT NULL,
    opening_balance DECIMAL(10,2) DEFAULT 0.00,
    total_charges DECIMAL(10,2) NOT NULL,
    total_payments DECIMAL(10,2) NOT NULL,
    closing_balance DECIMAL(10,2) NOT NULL,
    invoice_count INT DEFAULT 0,
    email_sent BOOLEAN DEFAULT FALSE,
    email_sent_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Statement items (for detailed breakdown)
CREATE TABLE statement_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    statement_id INT NOT NULL,
    invoice_id INT NOT NULL,
    invoice_date DATE NOT NULL,
    invoice_number VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'paid') NOT NULL,
    payment_date DATE NULL,
    FOREIGN KEY (statement_id) REFERENCES statements(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- Contractors table
CREATE TABLE contractors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    phone VARCHAR(50),
    email VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO companies (name, contact_person, email, phone, billing_address, vat_number, payment_terms, rate_per_pallet) VALUES
('Spar Distribution', 'John Smith', 'john@spar.co.za', '011-123-4567', '123 Main Street, Johannesburg, 2000', 'VAT123456789', 30, 450.00),
('Pick n Pay Supply', 'Sarah Johnson', 'sarah@picknpay.co.za', '011-234-5678', '456 Oak Avenue, Cape Town, 8000', 'VAT987654321', 30, 520.00),
('Woolworths Logistics', 'Mike Brown', 'mike@woolworths.co.za', '011-345-6789', '789 Pine Road, Durban, 4000', 'VAT456789123', 30, 480.00);

-- Insert sample contractors
INSERT INTO contractors (name, contact_person, phone, email, status) VALUES
('Around The Clock Transport', 'Mike Johnson', '011-555-0101', 'mike@aroundtheclock.co.za', 'active'),
('Rapid Delivery Services', 'Sarah Martinez', '011-555-0202', 'sarah@rapiddelivery.co.za', 'active'),
('Cross Country Logistics', 'Tom Anderson', '011-555-0303', 'tom@crosscountry.co.za', 'active'),
('Premier Freight Solutions', 'Jennifer Brown', '011-555-0404', 'jennifer@premierfreight.co.za', 'active'),
('Express Cargo Movers', 'Robert Wilson', '011-555-0505', 'robert@expresscargo.co.za', 'active'),
('Safe & Sound Transport', 'Linda Davis', '011-555-0606', 'linda@safesound.co.za', 'active'),
('Mile High Logistics', 'James Miller', '011-555-0707', 'james@milehigh.co.za', 'active'),
('Reliable Routes Transport', 'Patricia Garcia', '011-555-0808', 'patricia@reliableroutes.co.za', 'active');

-- Insert sample load sheets
INSERT INTO load_sheets (company_id, pickup_location, delivery_location, cargo_description, special_instructions, pallet_quantity, cargo_weight, delivery_method, contractor_name, contractor_cost, rate_per_pallet, final_rate, requested_date, status) VALUES
(1, 'Spar Warehouse, Johannesburg', 'Spar Store, Pretoria', 'Grocery items and household goods', 'Handle with care - fragile items', 5, 1250.50, 'own', NULL, 0.00, 450.00, 2250.00, '2024-01-15', 'completed'),
(2, 'Pick n Pay DC, Cape Town', 'Pick n Pay Store, Stellenbosch', 'Fresh produce and dairy products', 'Temperature controlled transport required', 8, 2100.75, 'contractor', 'FastTrack Logistics', 1800.00, 520.00, 4160.00, '2024-01-16', 'completed'),
(1, 'Spar Warehouse, Johannesburg', 'Spar Store, Sandton', 'Beverages and snacks', 'Standard handling', 3, 750.25, 'own', NULL, 0.00, 450.00, 1350.00, '2024-01-17', 'confirmed');

-- Insert sample invoices
INSERT INTO invoices (load_sheet_id, company_id, invoice_number, invoice_date, due_date, subtotal, vat_amount, total_amount, payment_status, payment_date) VALUES
(1, 1, 'INV202401001', '2024-01-15', '2024-02-14', 2250.00, 337.50, 2587.50, 'paid', '2024-01-20'),
(2, 2, 'INV202401002', '2024-01-16', '2024-02-15', 4160.00, 624.00, 4784.00, 'pending', NULL),
(3, 1, 'INV202401003', '2024-01-17', '2024-02-16', 1350.00, 202.50, 1552.50, 'pending', NULL);
