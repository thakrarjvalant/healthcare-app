<?php

use Database\DatabaseConnection;

/**
 * Migration to create the Dynamic RBAC system tables
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
        // Dynamic Roles table
        $sql = "CREATE TABLE IF NOT EXISTS dynamic_roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            display_name VARCHAR(150) NOT NULL,
            description TEXT,
            color VARCHAR(7) DEFAULT '#6c757d',
            icon VARCHAR(50) DEFAULT 'user',
            is_system_role BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);

        // Feature Modules table
        $sql = "CREATE TABLE IF NOT EXISTS feature_modules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            display_name VARCHAR(150) NOT NULL,
            description TEXT,
            icon VARCHAR(50) DEFAULT 'cog',
            color VARCHAR(7) DEFAULT '#6c757d',
            is_core_module BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);

        // Dynamic Permissions table
        $sql = "CREATE TABLE IF NOT EXISTS dynamic_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            display_name VARCHAR(150) NOT NULL,
            description TEXT,
            module VARCHAR(50) NOT NULL,
            feature VARCHAR(50) NOT NULL,
            action VARCHAR(50) NOT NULL,
            resource VARCHAR(50) NULL,
            is_system_permission BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);

        // Dynamic Role Permissions mapping table
        $sql = "CREATE TABLE IF NOT EXISTS dynamic_role_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_id INT NOT NULL,
            permission_id INT NOT NULL,
            conditions JSON NULL,
            granted_by INT NULL,
            granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            revoked_at TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (role_id) REFERENCES dynamic_roles(id) ON DELETE CASCADE,
            FOREIGN KEY (permission_id) REFERENCES dynamic_permissions(id) ON DELETE CASCADE,
            FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL,
            UNIQUE KEY unique_role_permission (role_id, permission_id, is_active)
        )";
        $this->db->exec($sql);

        // Role Feature Access matrix
        $sql = "CREATE TABLE IF NOT EXISTS role_feature_access (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_id INT NOT NULL,
            module_id INT NOT NULL,
            access_level ENUM('none', 'read', 'write', 'admin') NOT NULL DEFAULT 'none',
            conditions JSON NULL,
            granted_by INT NULL,
            granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            revoked_at TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (role_id) REFERENCES dynamic_roles(id) ON DELETE CASCADE,
            FOREIGN KEY (module_id) REFERENCES feature_modules(id) ON DELETE CASCADE,
            FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL,
            UNIQUE KEY unique_role_module (role_id, module_id, is_active)
        )";
        $this->db->exec($sql);

        // User Dynamic Roles mapping table
        $sql = "CREATE TABLE IF NOT EXISTS user_dynamic_roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            role_id INT NOT NULL,
            context JSON NULL,
            assigned_by INT NULL,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (role_id) REFERENCES dynamic_roles(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
            UNIQUE KEY unique_user_role (user_id, role_id, is_active)
        )";
        $this->db->exec($sql);
    }

    public function down()
    {
        $this->db->exec("DROP TABLE IF EXISTS user_dynamic_roles");
        $this->db->exec("DROP TABLE IF EXISTS role_feature_access");
        $this->db->exec("DROP TABLE IF EXISTS dynamic_role_permissions");
        $this->db->exec("DROP TABLE IF EXISTS dynamic_permissions");
        $this->db->exec("DROP TABLE IF EXISTS feature_modules");
        $this->db->exec("DROP TABLE IF EXISTS dynamic_roles");
    }
}