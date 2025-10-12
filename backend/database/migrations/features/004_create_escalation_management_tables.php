<?php

use Database\DatabaseConnection;

/**
 * Migration to create escalation management tables
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
        // Escalation Categories
        $sql = "CREATE TABLE IF NOT EXISTS escalation_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            priority ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);

        // Escalation Statuses
        $sql = "CREATE TABLE IF NOT EXISTS escalation_statuses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            display_name VARCHAR(100) NOT NULL,
            description TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);

        // Escalations
        $sql = "CREATE TABLE IF NOT EXISTS escalations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            category_id INT NOT NULL,
            status_id INT NOT NULL DEFAULT 1,
            priority ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
            reported_by INT NOT NULL,
            assigned_to INT,
            due_date DATETIME,
            resolved_at DATETIME,
            resolution_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES escalation_categories(id) ON DELETE CASCADE,
            FOREIGN KEY (status_id) REFERENCES escalation_statuses(id) ON DELETE CASCADE,
            FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
        )";
        $this->db->exec($sql);

        // Escalation Comments
        $sql = "CREATE TABLE IF NOT EXISTS escalation_comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            escalation_id INT NOT NULL,
            user_id INT NOT NULL,
            comment TEXT NOT NULL,
            is_internal BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (escalation_id) REFERENCES escalations(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $this->db->exec($sql);

        // Escalation Attachments
        $sql = "CREATE TABLE IF NOT EXISTS escalation_attachments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            escalation_id INT NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            mime_type VARCHAR(100),
            size INT,
            uploaded_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (escalation_id) REFERENCES escalations(id) ON DELETE CASCADE,
            FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
        )";
        $this->db->exec($sql);

        // Escalation Audit Logs
        $sql = "CREATE TABLE IF NOT EXISTS escalation_audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            escalation_id INT NOT NULL,
            user_id INT NOT NULL,
            action VARCHAR(100) NOT NULL,
            details JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (escalation_id) REFERENCES escalations(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $this->db->exec($sql);
    }

    public function down()
    {
        $this->db->exec("DROP TABLE IF EXISTS escalation_audit_logs");
        $this->db->exec("DROP TABLE IF EXISTS escalation_attachments");
        $this->db->exec("DROP TABLE IF EXISTS escalation_comments");
        $this->db->exec("DROP TABLE IF EXISTS escalations");
        $this->db->exec("DROP TABLE IF EXISTS escalation_statuses");
        $this->db->exec("DROP TABLE IF EXISTS escalation_categories");
    }
}