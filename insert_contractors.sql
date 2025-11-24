-- Insert Sample Contractors
-- Run this SQL script to add sample contractors to your existing database
-- Use phpMyAdmin or MySQL command line: mysql -u root -p logistics_db < insert_contractors.sql

USE logistics_db;

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

-- Show inserted contractors
SELECT * FROM contractors WHERE status = 'active' ORDER BY name;
