-- Healthcare Management System Database Setup
-- Create database
CREATE DATABASE IF NOT EXISTS healthcare_db;
USE healthcare_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'doctor', 'receptionist', 'admin', 'medical_coordinator') NOT NULL DEFAULT 'patient',
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create dynamic roles table
CREATE TABLE IF NOT EXISTS dynamic_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(7),
    icon VARCHAR(50),
    is_system_role BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create feature modules table
CREATE TABLE IF NOT EXISTS feature_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(7),
    is_core_module BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create dynamic permissions table
CREATE TABLE IF NOT EXISTS dynamic_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    module VARCHAR(50),
    feature VARCHAR(50),
    action VARCHAR(50),
    resource VARCHAR(50) NULL,
    is_system_permission BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create role-permissions relationship table
CREATE TABLE IF NOT EXISTS dynamic_role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id)
);

-- Create role-feature access table
-- This table is no longer used

-- Insert users with proper password hashes (password123)
INSERT IGNORE INTO users (name, email, password, role, verified) VALUES 
('John Doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', TRUE),
('Dr. Jane Smith', 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', TRUE),
('Receptionist Bob', 'bob.receptionist@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receptionist', TRUE),
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE),
('Medical Coordinator', 'medical.coordinator@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'medical_coordinator', TRUE);

-- Insert roles
INSERT IGNORE INTO dynamic_roles (name, display_name, description, color, icon, is_system_role) VALUES
('super_admin', 'Super Administrator', 'System super administrator with full access and dynamic role configuration', '#dc3545', 'crown', TRUE),
('admin', 'Administrator', 'System administrator with user management and audit oversight', '#007bff', 'shield-alt', TRUE),
('doctor', 'Doctor', 'Medical professional with clinical duties and patient care', '#17a2b8', 'user-md', TRUE),
('receptionist', 'Receptionist', 'Front desk operations and patient registration', '#ffc107', 'concierge-bell', TRUE),
('patient', 'Patient', 'Healthcare recipient with personal health record access', '#6c757d', 'user', TRUE),
('medical_coordinator', 'Medical Coordinator', 'Manages all appointment scheduling, rescheduling, and cancellations system-wide, resolves slot conflicts, oversees patient assignment to clinicians, and acts as liaison between clinical and administrative teams with limited audited access to patient histories', '#20c997', 'user-clock', TRUE);

-- Verify data
SELECT COUNT(*) as user_count FROM users;
SELECT id, name, email, role FROM users ORDER BY id;