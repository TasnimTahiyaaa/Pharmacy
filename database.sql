-- Noor Pharmacy Database Schema
-- Import this file in phpMyAdmin or run: mysql -u root -p < database.sql

CREATE DATABASE IF NOT EXISTS noor_pharmacy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE noor_pharmacy;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    phone VARCHAR(50),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    generic_name VARCHAR(255),
    description TEXT,
    category_id INT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock_quantity INT NOT NULL DEFAULT 0,
    low_stock_threshold INT NOT NULL DEFAULT 10,
    unit VARCHAR(50) DEFAULT 'tablet',
    manufacturer VARCHAR(255),
    requires_prescription TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart (user_id, medicine_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50),
    type ENUM('online','offline') NOT NULL DEFAULT 'online',
    status ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
    payment_method ENUM('bkash','cash') NOT NULL DEFAULT 'cash',
    payment_status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    medicine_id INT,
    medicine_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('bkash','cash') NOT NULL,
    status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
    transaction_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(100) NOT NULL DEFAULT 'low_stock',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    medicine_id INT,
    medicine_name VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE SET NULL
);

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin: admin@noorpharmacy.com / admin123
-- Customer: customer@test.com / customer123
-- Passwords hashed with SHA256 + salt 'noor_pharmacy_salt_2024'
INSERT INTO users (name, email, password_hash, role, phone, address) VALUES
('Admin User', 'admin@noorpharmacy.com', 'fd332b47dddbc6c0a86eb50be6d8b491d55e5e39c4e24c08b8a210d4c0f5b937', 'admin', '+880 1700-000001', '123 Health Street, Dhaka'),
('Test Customer', 'customer@test.com', 'cde03d257ac2320bf6d544db10816161f7cf26811e94b5a01a1c080276b8dd3c', 'customer', '+880 1700-000002', '456 Main Road, Dhaka');

INSERT INTO categories (name, description) VALUES
('Pain Relief', 'Analgesics and pain management'),
('Antibiotics', 'Bacterial infection treatment'),
('Vitamins & Supplements', 'Nutritional supplements'),
('Diabetes', 'Diabetic care medicines'),
('Gastroenterology', 'Digestive system medicines'),
('Allergy & Cold', 'Antihistamines and cold medicines'),
('Cardiac', 'Heart and blood pressure'),
('Dermatology', 'Skin care medicines');

INSERT INTO medicines (name, generic_name, description, category_id, price, stock_quantity, low_stock_threshold, unit, manufacturer, requires_prescription, is_active) VALUES
('Paracetamol 500mg','Acetaminophen','Fever and mild-to-moderate pain relief',1,12.00,500,50,'tablet','Square Pharma',0,1),
('Ibuprofen 400mg','Ibuprofen','Anti-inflammatory and pain reliever',1,18.50,300,30,'tablet','Beximco Pharma',0,1),
('Aspirin 75mg','Acetylsalicylic Acid','Pain relief and blood thinner',1,8.00,200,20,'tablet','ACI Limited',0,1),
('Diclofenac 50mg','Diclofenac Sodium','Anti-inflammatory pain relief',1,22.00,150,20,'tablet','Incepta Pharma',1,1),
('Amoxicillin 500mg','Amoxicillin','Broad-spectrum antibiotic for bacterial infections',2,45.00,200,25,'capsule','Square Pharma',1,1),
('Azithromycin 500mg','Azithromycin','Antibiotic for respiratory infections',2,65.00,8,15,'tablet','Beximco Pharma',1,1),
('Ciprofloxacin 500mg','Ciprofloxacin','Fluoroquinolone antibiotic',2,55.00,100,20,'tablet','Opsonin Pharma',1,1),
('Vitamin C 500mg','Ascorbic Acid','Antioxidant vitamin for immune system',3,30.00,400,50,'tablet','Healthcare Pharma',0,1),
('Vitamin D3 1000IU','Cholecalciferol','Bone health and immunity vitamin',3,40.00,300,30,'capsule','ACI Limited',0,1),
('Zinc 20mg','Zinc Sulfate','Mineral supplement for immune support',3,25.00,250,30,'tablet','Popular Pharma',0,1),
('Omega-3 Fish Oil','Fish Oil','Cardiovascular and brain health',3,85.00,7,15,'capsule','Renata Ltd',0,1),
('Metformin 500mg','Metformin HCl','Type 2 diabetes management',4,85.00,150,20,'tablet','Square Pharma',1,1),
('Glibenclamide 5mg','Glyburide','Oral hypoglycemic agent',4,60.00,100,15,'tablet','Incepta Pharma',1,1),
('Omeprazole 20mg','Omeprazole','Proton pump inhibitor for acid reflux',5,55.00,200,25,'capsule','Beximco Pharma',0,1),
('Antacid Suspension','Aluminium Hydroxide','Fast-acting acid neutralizer',5,35.00,120,20,'bottle','Eskayef Pharma',0,1),
('Cetirizine 10mg','Cetirizine HCl','Non-drowsy antihistamine for allergies',6,18.00,350,40,'tablet','Incepta Pharma',0,1),
('Fexofenadine 120mg','Fexofenadine HCl','Second-generation antihistamine',6,28.00,5,20,'tablet','Renata Ltd',0,1),
('Loratadine 10mg','Loratadine','Long-acting antihistamine',6,20.00,200,25,'tablet','ACI Limited',0,1),
('Amlodipine 5mg','Amlodipine Besylate','Calcium channel blocker for hypertension',7,75.00,120,15,'tablet','Square Pharma',1,1),
('Atorvastatin 20mg','Atorvastatin Calcium','Statin for cholesterol management',7,90.00,100,15,'tablet','Beximco Pharma',1,1),
('Clotrimazole Cream','Clotrimazole 1%','Antifungal cream for skin infections',8,45.00,80,10,'tube','Opsonin Pharma',0,1),
('Hydrocortisone Cream','Hydrocortisone 1%','Anti-inflammatory cream for skin rashes',8,38.00,9,10,'tube','Popular Pharma',0,1);

INSERT INTO notifications (type, title, message, medicine_id, medicine_name) VALUES
('low_stock','Low Stock Alert','Azithromycin 500mg has only 8 units left (threshold: 15)',6,'Azithromycin 500mg'),
('low_stock','Low Stock Alert','Omega-3 Fish Oil has only 7 units left (threshold: 15)',11,'Omega-3 Fish Oil'),
('low_stock','Low Stock Alert','Fexofenadine 120mg has only 5 units left (threshold: 20)',17,'Fexofenadine 120mg'),
('low_stock','Low Stock Alert','Hydrocortisone Cream has only 9 units left (threshold: 10)',22,'Hydrocortisone Cream');
