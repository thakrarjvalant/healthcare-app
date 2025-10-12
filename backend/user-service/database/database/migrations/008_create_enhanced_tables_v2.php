<?php

use Database\DatabaseConnection;

/**
 * Migration to create additional tables for enhanced dashboard functionalities (Part 2)
 */
class CreateEnhancedTablesV2
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
        // Doctor Schedules
        $sql = "CREATE TABLE IF NOT EXISTS doctor_schedules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            doctor_id INT NOT NULL,
            day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            break_start_time TIME,
            break_end_time TIME,
            is_available BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_doctor_day (doctor_id, day_of_week)
        )";
        $this->db->exec($sql);

        // System Settings
        $sql = "CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT NOT NULL,
            setting_type ENUM('string', 'integer', 'boolean', 'json') NOT NULL DEFAULT 'string',
            category VARCHAR(50) NOT NULL,
            description TEXT,
            is_encrypted BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);

        // Audit Logs
        $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            table_name VARCHAR(50),
            record_id INT,
            old_values JSON,
            new_values JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            session_id VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_action (action),
            INDEX idx_created_at (created_at)
        )";
        $this->db->exec($sql);

        // Payment Records
        $sql = "CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            invoice_id INT,
            amount DECIMAL(10, 2) NOT NULL,
            payment_method ENUM('cash', 'credit_card', 'debit_card', 'insurance', 'bank_transfer') NOT NULL,
            payment_status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
            transaction_id VARCHAR(255),
            insurance_provider VARCHAR(255),
            insurance_policy_number VARCHAR(100),
            copay_amount DECIMAL(10, 2),
            covered_amount DECIMAL(10, 2),
            payment_date TIMESTAMP,
            processed_by INT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
            FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
        )";
        $this->db->exec($sql);

        // Document Categories
        $sql = "CREATE TABLE IF NOT EXISTS document_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            icon VARCHAR(50),
            color VARCHAR(7),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);

        // Update documents table to include category
        $sql = "ALTER TABLE documents 
                ADD COLUMN category_id INT AFTER user_id,
                ADD COLUMN description TEXT AFTER original_filename,
                ADD COLUMN is_shared BOOLEAN DEFAULT FALSE AFTER file_type,
                ADD COLUMN shared_with JSON AFTER is_shared,
                ADD FOREIGN KEY (category_id) REFERENCES document_categories(id) ON DELETE SET NULL";
        
        try {
            $this->db->exec($sql);
        } catch (PDOException $e) {
            // Ignore if columns already exist
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                throw $e;
            }
        }

        // Patient Check-in Queue
        $sql = "CREATE TABLE IF NOT EXISTS check_in_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            appointment_id INT,
            check_in_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('waiting', 'in_progress', 'completed', 'no_show') NOT NULL DEFAULT 'waiting',
            priority ENUM('low', 'medium', 'high', 'emergency') NOT NULL DEFAULT 'medium',
            estimated_wait_time INT, -- in minutes
            notes TEXT,
            called_time TIMESTAMP NULL,
            completed_time TIMESTAMP NULL,
            processed_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
            FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
        )";
        $this->db->exec($sql);
    }

    public function down()
    {
        $this->db->exec("DROP TABLE IF EXISTS check_in_queue");
        $this->db->exec("ALTER TABLE documents DROP FOREIGN KEY documents_ibfk_2");
        $this->db->exec("ALTER TABLE documents DROP COLUMN shared_with, DROP COLUMN is_shared, DROP COLUMN description, DROP COLUMN category_id");
        $this->db->exec("DROP TABLE IF EXISTS document_categories");
        $this->db->exec("DROP TABLE IF EXISTS payments");
        $this->db->exec("DROP TABLE IF EXISTS audit_logs");
        $this->db->exec("DROP TABLE IF EXISTS system_settings");
        $this->db->exec("DROP TABLE IF EXISTS doctor_schedules");
    }
}