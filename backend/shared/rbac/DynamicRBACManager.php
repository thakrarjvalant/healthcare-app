<?php

namespace Shared\RBAC;

use Database\DatabaseConnection;

/**
 * Enhanced Dynamic RBAC Manager
 * Handles dynamic role-based access control with configurable permissions
 */
class DynamicRBACManager
{
    private $db;
    private $cache = [];
    
    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($userId, $permission, $resource = null)
    {
        $cacheKey = "user_{$userId}_permission_{$permission}" . ($resource ? "_resource_{$resource}" : "");
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $sql = "SELECT COUNT(*) FROM user_dynamic_roles udr
                JOIN dynamic_role_permissions drp ON udr.role_id = drp.role_id
                JOIN dynamic_permissions dp ON drp.permission_id = dp.id
                WHERE udr.user_id = ? AND udr.is_active = 1 
                AND drp.is_active = 1 AND dp.name = ?";
        
        $params = [$userId, $permission];
        
        if ($resource) {
            $sql .= " AND (dp.resource IS NULL OR dp.resource = ?)";
            $params[] = $resource;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $hasPermission = $stmt->fetchColumn() > 0;
        
        $this->cache[$cacheKey] = $hasPermission;
        return $hasPermission;
    }

    /**
     * Get user roles with permissions
     */
    public function getUserRoles($userId)
    {
        $sql = "SELECT dr.*, 
                       GROUP_CONCAT(dp.name SEPARATOR ',') as permissions
                FROM user_dynamic_roles udr
                JOIN dynamic_roles dr ON udr.role_id = dr.id
                LEFT JOIN dynamic_role_permissions drp ON dr.id = drp.role_id AND drp.is_active = 1
                LEFT JOIN dynamic_permissions dp ON drp.permission_id = dp.id
                WHERE udr.user_id = ? AND udr.is_active = 1 AND dr.is_active = 1
                GROUP BY dr.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        $roles = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['permissions'] = $row['permissions'] ? explode(',', $row['permissions']) : [];
            $roles[] = $row;
        }
        
        return $roles;
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole($userId, $roleNames)
    {
        if (!is_array($roleNames)) {
            $roleNames = [$roleNames];
        }
        
        $placeholders = str_repeat('?,', count($roleNames) - 1) . '?';
        
        $sql = "SELECT COUNT(*) FROM user_dynamic_roles udr
                JOIN dynamic_roles dr ON udr.role_id = dr.id
                WHERE udr.user_id = ? AND udr.is_active = 1 
                AND dr.name IN ($placeholders)";
        
        $params = array_merge([$userId], $roleNames);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get role feature access
     */
    public function getRoleFeatureAccess($roleId)
    {
        // This method is no longer used as we've removed the role_feature_access table
        return [];
    }

    /**
     * Check if user can access patient information
     */
    public function canAccessPatient($userId, $patientId, $accessType = 'read')
    {
        // Super admin has full access
        if ($this->hasAnyRole($userId, ['super_admin'])) {
            return true;
        }

        // Patient can access their own records
        if ($userId == $patientId && $accessType === 'read') {
            return true;
        }

        // Doctor can access assigned patients
        if ($this->hasAnyRole($userId, ['doctor'])) {
            $sql = "SELECT COUNT(*) FROM appointments 
                    WHERE patient_id = ? AND doctor_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$patientId, $userId]);
            return $stmt->fetchColumn() > 0;
        }

        // Receptionist has basic access
        if ($this->hasAnyRole($userId, ['receptionist'])) {
            return $this->hasPermission($userId, 'patients.basic_read');
        }

        return false;
    }

    /**
     * Log RBAC audit event
     */
    public function logRBACEvent($action, $entityType, $entityId, $oldValues = null, $newValues = null, $performedBy = null)
    {
        // RBAC audit logging is no longer used
    }

    /**
     * Create new dynamic role
     */
    public function createRole($roleData, $createdBy)
    {
        $sql = "INSERT INTO dynamic_roles 
                (name, display_name, description, color, icon, created_by)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $roleData['name'],
            $roleData['display_name'],
            $roleData['description'] ?? null,
            $roleData['color'] ?? '#666666',
            $roleData['icon'] ?? 'user',
            $createdBy
        ]);

        if ($result) {
            $roleId = $this->db->lastInsertId();
            $this->logRBACEvent('create', 'role', $roleId, null, $roleData, $createdBy);
            return $roleId;
        }

        return false;
    }

    /**
     * Assign role to user
     */
    public function assignRoleToUser($userId, $roleId, $assignedBy, $context = null)
    {
        // Check if assignment already exists
        $sql = "SELECT id FROM user_dynamic_roles 
                WHERE user_id = ? AND role_id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $roleId]);
        
        if ($stmt->fetchColumn()) {
            return false; // Already assigned
        }

        $sql = "INSERT INTO user_dynamic_roles 
                (user_id, role_id, context, assigned_by)
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $userId,
            $roleId,
            $context ? json_encode($context) : null,
            $assignedBy
        ]);

        if ($result) {
            $assignmentId = $this->db->lastInsertId();
            $this->logRBACEvent('assign', 'assignment', $assignmentId, null, [
                'user_id' => $userId,
                'role_id' => $roleId,
                'context' => $context
            ], $assignedBy);
            
            // Clear cache
            $this->clearUserCache($userId);
            return $assignmentId;
        }

        return false;
    }

    /**
     * Get all permissions
     */
    public function getAllPermissions()
    {
        $sql = "SELECT * FROM dynamic_permissions WHERE is_active = 1 ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get permissions for a specific role
     */
    public function getRolePermissions($roleId)
    {
        $sql = "SELECT dp.* FROM dynamic_role_permissions drp
                JOIN dynamic_permissions dp ON drp.permission_id = dp.id
                WHERE drp.role_id = ? AND drp.is_active = 1 AND dp.is_active = 1
                ORDER BY dp.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Assign permission to role
     */
    public function assignPermissionToRole($roleId, $permissionId, $assignedBy)
    {
        // Check if assignment already exists
        $sql = "SELECT id FROM dynamic_role_permissions 
                WHERE role_id = ? AND permission_id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId, $permissionId]);
        
        if ($stmt->fetchColumn()) {
            return false; // Already assigned
        }

        $sql = "INSERT INTO dynamic_role_permissions 
                (role_id, permission_id, granted_by)
                VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$roleId, $permissionId, $assignedBy]);

        if ($result) {
            $assignmentId = $this->db->lastInsertId();
            $this->logRBACEvent('assign', 'permission', $assignmentId, null, [
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ], $assignedBy);
            
            // Clear cache for users with this role
            $this->clearRoleCache($roleId);
            return $assignmentId;
        }

        return false;
    }

    /**
     * Remove permission from role
     */
    public function removePermissionFromRole($roleId, $permissionId, $removedBy)
    {
        $sql = "UPDATE dynamic_role_permissions 
                SET is_active = 0, revoked_at = NOW()
                WHERE role_id = ? AND permission_id = ? AND is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$roleId, $permissionId]);

        if ($result) {
            $this->logRBACEvent('revoke', 'permission', 0, [
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ], null, $removedBy);
            
            // Clear cache for users with this role
            $this->clearRoleCache($roleId);
            return true;
        }

        return false;
    }

    /**
     * Clear user permission cache
     */
    private function clearUserCache($userId)
    {
        foreach ($this->cache as $key => $value) {
            if (strpos($key, "user_{$userId}_") === 0) {
                unset($this->cache[$key]);
            }
        }
    }

    /**
     * Clear role permission cache
     */
    private function clearRoleCache($roleId)
    {
        // Clear cache for all users with this role
        $sql = "SELECT user_id FROM user_dynamic_roles WHERE role_id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId]);
        
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->clearUserCache($row['user_id']);
        }
    }
}