<?php

use Database\DatabaseConnection;

/**
 * Migration to update users table role ENUM to include all roles
 */
class UpdateUsersRoleEnum
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
        // Update the role ENUM to include all possible roles
        $sql = "ALTER TABLE users MODIFY COLUMN role ENUM('patient', 'doctor', 'receptionist', 'admin', 'medical_coordinator', 'super_admin') NOT NULL DEFAULT 'patient'";
        $this->db->exec($sql);
    }

    public function down()
    {
        // Revert to previous ENUM values
        $sql = "ALTER TABLE users MODIFY COLUMN role ENUM('patient', 'doctor', 'receptionist', 'admin', 'medical_coordinator') NOT NULL DEFAULT 'patient'";
        $this->db->exec($sql);
    }
}