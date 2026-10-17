CREATE DATABASE IF NOT EXISTS Customer_complaint_management
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE Customer_complaint_management;

-- Customer account information
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    street_address VARCHAR(150) NOT NULL,
    city VARCHAR(75) NOT NULL,
    state CHAR(2) NOT NULL,
    zip_code VARCHAR(10) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Technician and administrator information
CREATE TABLE IF NOT EXISTS employees (
    employee_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone_extension VARCHAR(10),
    employee_level ENUM('Administrator', 'Technician') NOT NULL,
    password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Products and services offered
CREATE TABLE IF NOT EXISTS products (
    product_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255)
) ENGINE=InnoDB;

-- Available complaint categories
CREATE TABLE IF NOT EXISTS complaint_categories (
    category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(75) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Customer complaints and resolution information
CREATE TABLE IF NOT EXISTS complaints (
    complaint_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    technician_id INT UNSIGNED,
    description TEXT NOT NULL,
    image_path VARCHAR(255),
    status ENUM('Open', 'Closed') NOT NULL DEFAULT 'Open',
    resolution_date DATE,
    resolution_notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_complaint_customer
        FOREIGN KEY (customer_id)
        REFERENCES customers(customer_id),

    CONSTRAINT fk_complaint_product
        FOREIGN KEY (product_id)
        REFERENCES products(product_id),

    CONSTRAINT fk_complaint_category
        FOREIGN KEY (category_id)
        REFERENCES complaint_categories(category_id),

    CONSTRAINT fk_complaint_technician
        FOREIGN KEY (technician_id)
        REFERENCES employees(employee_id)
) ENGINE=InnoDB;

-- Technician notes associated with complaints
CREATE TABLE IF NOT EXISTS complaint_notes (
    note_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    note_text TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_note_complaint
        FOREIGN KEY (complaint_id)
        REFERENCES complaints(complaint_id),

    CONSTRAINT fk_note_employee
        FOREIGN KEY (employee_id)
        REFERENCES employees(employee_id)
) ENGINE=InnoDB;

-- Five required products/services
INSERT INTO products (product_name, description) VALUES
    ('Laptop Computer', 'Portable computers and related hardware.'),
    ('Desktop Computer', 'Desktop computers and workstations.'),
    ('Printer', 'Printers and printing equipment.'),
    ('Computer Repair', 'Hardware diagnosis and repair services.'),
    ('Network Setup', 'Home and small business network installation.')
ON DUPLICATE KEY UPDATE
    description = VALUES(description);

-- Three required complaint categories
INSERT INTO complaint_categories (category_name) VALUES
    ('Product Defect'),
    ('Warranty'),
    ('Service Issue')
ON DUPLICATE KEY UPDATE
    category_name = VALUES(category_name);