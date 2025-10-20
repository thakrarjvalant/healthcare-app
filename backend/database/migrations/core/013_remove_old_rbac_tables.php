<?php

use Database\DatabaseConnection;

/**
 * Migration to remove old RBAC tables that are no longer used
 */
class RemoveOldRbacTables
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
        // Drop old RBAC tables that are no longer used
        $this->db->exec("DROP TABLE IF EXISTS rbac_audit_logs");
        // Note: role_feature_access is still used and should not be dropped
    }

    public function down()
    {
        // We don't need to recreate these tables as they're obsolete
        // This is a one-way migration to clean up legacy tables
    }
}