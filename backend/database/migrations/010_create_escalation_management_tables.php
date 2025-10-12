<?php

use Database\DatabaseConnection;

/**
 * Migration to create escalation management tables
 * This supports tracking, managing, and resolving system escalations
 */
class CreateEscalationManagementTables
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
        // Escalation categories/types
        $sql = "CREATE TABLE IF NOT EXISTS escalation_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            display_name VARCHAR(150) NOT NULL,
            description TEXT,
            priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);

        // Escalation statuses
        $sql = "CREATE TABLE IF NOT EXISTS escalation_statuses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            display_name VARCHAR(100) NOT NULL,
            description TEXT,
            is_final BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);

        // Main escalations table
        $sql = "CREATE TABLE IF NOT EXISTS escalations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            category_id INT NOT NULL,
            status_id INT NOT NULL DEFAULT 1,
            priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
            reporter_id INT NOT NULL,
            assigned_to INT NULL,
            due_date TIMESTAMP NULL,
            resolved_at TIMESTAMP NULL,
            resolution_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES escalation_categories(id) ON DELETE RESTRICT,
            FOREIGN KEY (status_id) REFERENCES escalation_statuses(id) ON DELETE RESTRICT,
            FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_category (category_id),
            INDEX idx_status (status_id),
            INDEX idx_priority (priority),
            INDEX idx_reporter (reporter_id),
            INDEX idx_assigned (assigned_to),
            INDEX idx_due_date (due_date),
            INDEX idx_created_at (created_at)
        )";
        $this->db->exec($sql);

        // Escalation comments/updates
        $sql = "CREATE TABLE IF NOT EXISTS escalation_comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            escalation_id INT NOT NULL,
            user_id INT NOT NULL,
            comment TEXT NOT NULL,
            is_internal BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (escalation_id) REFERENCES escalations(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_escalation (escalation_id),
            INDEX idx_user (user_id),
            INDEX idx_created_at (created_at)
        )";
        $this->db->exec($sql);

        // Escalation attachments
        $sql = "CREATE TABLE IF NOT EXISTS escalation_attachments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            escalation_id INT NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size INT,
            mime_type VARCHAR(100),
            uploaded_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (escalation_id) REFERENCES escalations(id) ON DELETE CASCADE,
            FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_escalation (escalation_id),
            INDEX idx_uploaded_by (uploaded_by)
        )";
        $this->db->exec($sql);

        // Escalation audit logs
        $sql = "CREATE TABLE IF NOT EXISTS escalation_audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            escalation_id INT NOT NULL,
            action VARCHAR(100) NOT NULL,
            old_values JSON,
            new_values JSON,
            performed_by INT,
            performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            user_agent TEXT,
            FOREIGN KEY (escalation_id) REFERENCES escalations(id) ON DELETE CASCADE,
            FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_escalation (escalation_id),
            INDEX idx_performed_by (performed_by),
            INDEX idx_performed_at (performed_at)
        )";
        $this->db->exec($sql);

        echo "✅ Escalation management tables created successfully!\n";
    }

    public function down()
    {
        // Drop tables in reverse order of creation to respect foreign key constraints
        $this->db->exec("DROP TABLE IF EXISTS escalation_audit_logs");
        $this->db->exec("DROP TABLE IF EXISTS escalation_attachments");
        $this->db->exec("DROP TABLE IF EXISTS escalation_comments");
        $this->db->exec("DROP TABLE IF EXISTS escalations");
        $this->db->exec("DROP TABLE IF EXISTS escalation_statuses");
        $this->db->exec("DROP TABLE IF EXISTS escalation_categories");
        echo "🗑️ Escalation management tables dropped successfully!\n";
    }
}