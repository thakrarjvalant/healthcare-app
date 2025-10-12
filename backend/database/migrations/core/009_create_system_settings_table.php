<?php

use Database\DatabaseConnection;

/**
 * Migration to create system settings and audit logs tables
 */
class CreateSystemSettingsTable
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
        // System Settings
        $sql = "CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            key_name VARCHAR(100) NOT NULL UNIQUE,
            value TEXT,
            data_type ENUM('string', 'integer', 'float', 'boolean', 'json') NOT NULL DEFAULT 'string',
            description TEXT,
            is_editable BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);

        // Audit Logs
        $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            table_name VARCHAR(100),
            record_id INT,
            old_values JSON,
            new_values JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        $this->db->exec($sql);

        // RBAC Audit Logs
        $sql = "CREATE TABLE IF NOT EXISTS rbac_audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            role_id INT,
            permission_id INT,
            action VARCHAR(50) NOT NULL,
            details JSON,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (role_id) REFERENCES dynamic_roles(id) ON DELETE SET NULL,
            FOREIGN KEY (permission_id) REFERENCES dynamic_permissions(id) ON DELETE SET NULL
        )";
        $this->db->exec($sql);
    }

    public function down()
    {
        $this->db->exec("DROP TABLE IF EXISTS rbac_audit_logs");
        $this->db->exec("DROP TABLE IF EXISTS audit_logs");
        $this->db->exec("DROP TABLE IF EXISTS system_settings");
    }
}