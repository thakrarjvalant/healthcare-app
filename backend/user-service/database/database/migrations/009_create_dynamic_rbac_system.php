<?php

use Database\DatabaseConnection;

/**
 * Migration to create enhanced dynamic RBAC system
 * This supports configurable roles, permissions, and feature allocation
 */
class CreateDynamicRBACSystem
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
        // Enhanced roles table with metadata
        $sql = "CREATE TABLE IF NOT EXISTS dynamic_roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            display_name VARCHAR(100) NOT NULL,
            description TEXT,
            color VARCHAR(7) DEFAULT '#666666',
            icon VARCHAR(50) DEFAULT 'user',
            is_system_role BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE,
            created_by INT,
            updated_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
        )";
        $this->db->exec($sql);

        // Enhanced permissions with feature modules
        $sql = "CREATE TABLE IF NOT EXISTS dynamic_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            display_name VARCHAR(150) NOT NULL,
            description TEXT,
            module VARCHAR(50) NOT NULL,
            feature VARCHAR(50) NOT NULL,
            action VARCHAR(50) NOT NULL,
            resource VARCHAR(50),
            is_system_permission BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_module_feature (module, feature),
            INDEX idx_resource (resource)
        )";
        $this->db->exec($sql);

        // Role-Permission mappings with conditions
        $sql = "CREATE TABLE IF NOT EXISTS dynamic_role_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_id INT NOT NULL,
            permission_id INT NOT NULL,
            conditions JSON,
            granted_by INT,
            granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            revoked_at TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (role_id) REFERENCES dynamic_roles(id) ON DELETE CASCADE,
            FOREIGN KEY (permission_id) REFERENCES dynamic_permissions(id) ON DELETE CASCADE,
            FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL,
            UNIQUE KEY unique_active_role_permission (role_id, permission_id, is_active)
        )";
        $this->db->exec($sql);

        // User-Role assignments with contexts
        $sql = "CREATE TABLE IF NOT EXISTS user_dynamic_roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            role_id INT NOT NULL,
            context JSON,
            assigned_by INT,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (role_id) REFERENCES dynamic_roles(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user_active (user_id, is_active)
        )";
        $this->db->exec($sql);

        // Feature modules configuration
        $sql = "CREATE TABLE IF NOT EXISTS feature_modules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            display_name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50),
            color VARCHAR(7) DEFAULT '#007bff',
            is_core_module BOOLEAN DEFAULT FALSE,
            is_enabled BOOLEAN DEFAULT TRUE,
            configuration JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);

        echo "✅ Dynamic RBAC system tables created successfully!\n";
    }

    public function down()
    {
        // Drop tables in reverse order of creation to respect foreign key constraints
        $this->db->exec("DROP TABLE IF EXISTS feature_modules");
        $this->db->exec("DROP TABLE IF EXISTS user_dynamic_roles");
        $this->db->exec("DROP TABLE IF EXISTS dynamic_role_permissions");
        $this->db->exec("DROP TABLE IF EXISTS dynamic_permissions");
        $this->db->exec("DROP TABLE IF EXISTS dynamic_roles");
        echo "🗑️ Dynamic RBAC system tables dropped successfully!\n";
    }
}