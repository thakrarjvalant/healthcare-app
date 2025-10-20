<?php

namespace AdminUI\Controllers;

use Shared\RBAC\DynamicRBACManager;
use UserService\Middleware\AuthMiddleware;
use Database\DatabaseConnection;

class RoleController {
    private $rbacManager;
    private $db;

    public function __construct() {
        $this->rbacManager = new DynamicRBACManager();
        $this->db = DatabaseConnection::getInstance();
    }

    /**
     * Get all dynamic roles
     * @param array $request
     * @return array
     */
    public function getAllRoles($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM dynamic_roles WHERE is_active = 1 ORDER BY name");
            $stmt->execute();
            $roles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => 200,
                'data' => [
                    'roles' => $roles
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to fetch roles: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create a new dynamic role
     * @param array $request
     * @return array
     */
    public function createRole($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }

        $data = $request['body'] ?? [];
        
        // Validate required fields
        if (empty($data['name']) || empty($data['display_name'])) {
            return [
                'status' => 400,
                'message' => 'Name and display name are required'
            ];
        }

        try {
            // Check if role already exists
            $stmt = $this->db->prepare("SELECT id FROM dynamic_roles WHERE name = ?");
            $stmt->execute([$data['name']]);
            if ($stmt->fetch()) {
                return [
                    'status' => 400,
                    'message' => 'Role with this name already exists'
                ];
            }

            // Get user ID from auth token
            $userId = $request['user']['id'] ?? null;
            
            // Insert new role
            $stmt = $this->db->prepare("INSERT INTO dynamic_roles 
                (name, display_name, description, color, icon, is_system_role, created_by, updated_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            $result = $stmt->execute([
                $data['name'],
                $data['display_name'],
                $data['description'] ?? '',
                $data['color'] ?? '#666666',
                $data['icon'] ?? 'user',
                isset($data['is_system_role']) ? (int)$data['is_system_role'] : 0,
                $userId,
                $userId
            ]);

            if ($result) {
                $roleId = $this->db->lastInsertId();
                
                // Log the creation
                $this->rbacManager->logRBACEvent('create', 'role', $roleId, null, $data, $userId);
                
                // Fetch the created role
                $stmt = $this->db->prepare("SELECT * FROM dynamic_roles WHERE id = ?");
                $stmt->execute([$roleId]);
                $role = $stmt->fetch(\PDO::FETCH_ASSOC);

                return [
                    'status' => 201,
                    'message' => 'Role created successfully',
                    'data' => [
                        'role' => $role
                    ]
                ];
            } else {
                return [
                    'status' => 500,
                    'message' => 'Failed to create role'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to create role: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update an existing dynamic role
     * @param array $request
     * @return array
     */
    public function updateRole($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }

        $roleId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        if (!$roleId) {
            return [
                'status' => 400,
                'message' => 'Role ID is required'
            ];
        }

        try {
            // Check if role exists
            $stmt = $this->db->prepare("SELECT id FROM dynamic_roles WHERE id = ?");
            $stmt->execute([$roleId]);
            if (!$stmt->fetch()) {
                return [
                    'status' => 404,
                    'message' => 'Role not found'
                ];
            }

            // Build update query dynamically
            $fields = [];
            $values = [];
            
            if (isset($data['display_name'])) {
                $fields[] = "display_name = ?";
                $values[] = $data['display_name'];
            }
            
            if (isset($data['description'])) {
                $fields[] = "description = ?";
                $values[] = $data['description'];
            }
            
            if (isset($data['color'])) {
                $fields[] = "color = ?";
                $values[] = $data['color'];
            }
            
            if (isset($data['icon'])) {
                $fields[] = "icon = ?";
                $values[] = $data['icon'];
            }
            
            if (isset($data['is_system_role'])) {
                $fields[] = "is_system_role = ?";
                $values[] = (int)$data['is_system_role'];
            }
            
            // Add updated_by
            $fields[] = "updated_by = ?";
            $values[] = $request['user']['id'] ?? null;
            
            if (empty($fields)) {
                return [
                    'status' => 400,
                    'message' => 'No valid fields to update'
                ];
            }

            $values[] = $roleId; // For WHERE clause
            
            $sql = "UPDATE dynamic_roles SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($values);

            if ($result) {
                // Fetch the updated role
                $stmt = $this->db->prepare("SELECT * FROM dynamic_roles WHERE id = ?");
                $stmt->execute([$roleId]);
                $role = $stmt->fetch(\PDO::FETCH_ASSOC);

                // Log the update
                $userId = $request['user']['id'] ?? null;
                $this->rbacManager->logRBACEvent('update', 'role', $roleId, null, $data, $userId);

                return [
                    'status' => 200,
                    'message' => 'Role updated successfully',
                    'data' => [
                        'role' => $role
                    ]
                ];
            } else {
                return [
                    'status' => 500,
                    'message' => 'Failed to update role'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to update role: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete a dynamic role
     * @param array $request
     * @return array
     */
    public function deleteRole($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }

        $roleId = $request['params']['id'] ?? null;

        if (!$roleId) {
            return [
                'status' => 400,
                'message' => 'Role ID is required'
            ];
        }

        try {
            // Check if role exists
            $stmt = $this->db->prepare("SELECT id, name FROM dynamic_roles WHERE id = ?");
            $stmt->execute([$roleId]);
            $role = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$role) {
                return [
                    'status' => 404,
                    'message' => 'Role not found'
                ];
            }

            // Prevent deletion of system roles
            $stmt = $this->db->prepare("SELECT is_system_role FROM dynamic_roles WHERE id = ?");
            $stmt->execute([$roleId]);
            $isSystemRole = $stmt->fetchColumn();
            
            if ($isSystemRole) {
                return [
                    'status' => 400,
                    'message' => 'Cannot delete system roles'
                ];
            }

            // Check if role is assigned to any users
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM user_dynamic_roles WHERE role_id = ? AND is_active = 1");
            $stmt->execute([$roleId]);
            $userCount = $stmt->fetchColumn();
            
            if ($userCount > 0) {
                return [
                    'status' => 400,
                    'message' => 'Cannot delete role that is assigned to users. Unassign users first.'
                ];
            }

            // Soft delete - set is_active to 0
            $stmt = $this->db->prepare("UPDATE dynamic_roles SET is_active = 0 WHERE id = ?");
            $result = $stmt->execute([$roleId]);

            if ($result) {
                // Log the deletion
                $userId = $request['user']['id'] ?? null;
                $this->rbacManager->logRBACEvent('delete', 'role', $roleId, $role, null, $userId);

                return [
                    'status' => 200,
                    'message' => 'Role deleted successfully'
                ];
            } else {
                return [
                    'status' => 500,
                    'message' => 'Failed to delete role'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to delete role: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get permissions for a role
     * @param array $request
     * @return array
     */
    public function getRolePermissions($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }

        $roleId = $request['params']['id'] ?? null;

        if (!$roleId) {
            return [
                'status' => 400,
                'message' => 'Role ID is required'
            ];
        }

        try {
            $permissions = $this->rbacManager->getRolePermissions($roleId);

            return [
                'status' => 200,
                'data' => [
                    'permissions' => $permissions
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to fetch role permissions: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all permissions
     * @param array $request
     * @return array
     */
    public function getAllPermissions($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }

        try {
            $permissions = $this->rbacManager->getAllPermissions();

            return [
                'status' => 200,
                'data' => [
                    'permissions' => $permissions
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to fetch permissions: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get roles for a specific user
     * @param array $request
     * @return array
     */
    public function getUserRoles($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }

        // Extract user ID from request params
        $userId = null;
        if (isset($request['params']['id'])) {
            $userId = $request['params']['id'];
        }

        if (!$userId) {
            return [
                'status' => 400,
                'message' => 'User ID is required'
            ];
        }

        try {
            $roles = $this->rbacManager->getUserRoles($userId);

            return [
                'status' => 200,
                'data' => [
                    'roles' => $roles
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to fetch user roles: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Assign permission to role
     * @param array $request
     * @return array
     */
    public function assignPermissionToRole($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }

        $roleId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];
        $permissionId = $data['permission_id'] ?? null;

        if (!$roleId || !$permissionId) {
            return [
                'status' => 400,
                'message' => 'Role ID and Permission ID are required'
            ];
        }

        try {
            $userId = $request['user']['id'] ?? null;
            $result = $this->rbacManager->assignPermissionToRole($roleId, $permissionId, $userId);

            if ($result) {
                return [
                    'status' => 200,
                    'message' => 'Permission assigned to role successfully'
                ];
            } else {
                return [
                    'status' => 400,
                    'message' => 'Failed to assign permission to role'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to assign permission to role: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove permission from role
     * @param array $request
     * @return array
     */
    public function removePermissionFromRole($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }

        $roleId = $request['params']['id'] ?? null;
        $permissionId = $request['params']['permission_id'] ?? null;

        if (!$roleId || !$permissionId) {
            return [
                'status' => 400,
                'message' => 'Role ID and Permission ID are required'
            ];
        }

        try {
            $userId = $request['user']['id'] ?? null;
            $result = $this->rbacManager->removePermissionFromRole($roleId, $permissionId, $userId);

            if ($result) {
                return [
                    'status' => 200,
                    'message' => 'Permission removed from role successfully'
                ];
            } else {
                return [
                    'status' => 400,
                    'message' => 'Failed to remove permission from role'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to remove permission from role: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get feature access for a specific role
     * @param array $request
     * @return array
     */
    public function getRoleFeatureAccess($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }

        $roleId = $request['params']['id'] ?? null;

        if (!$roleId) {
            return [
                'status' => 400,
                'message' => 'Role ID is required'
            ];
        }

        try {
            $featureAccess = $this->rbacManager->getRoleFeatureAccess($roleId);

            return [
                'status' => 200,
                'data' => [
                    'feature_access' => $featureAccess
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to fetch role feature access: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update feature access for a specific role
     * @param array $request
     * @return array
     */
    public function updateRoleFeatureAccess($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }

        $roleId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];
        $moduleId = $data['module_id'] ?? null;
        $accessLevel = $data['access_level'] ?? null;

        if (!$roleId || !$moduleId || !$accessLevel) {
            return [
                'status' => 400,
                'message' => 'Role ID, Module ID, and Access Level are required'
            ];
        }

        try {
            $userId = $request['user']['id'] ?? null;
            $result = $this->rbacManager->assignFeatureAccessToRole($roleId, $moduleId, $accessLevel, $userId);

            if ($result) {
                return [
                    'status' => 200,
                    'message' => 'Feature access updated successfully'
                ];
            } else {
                return [
                    'status' => 400,
                    'message' => 'Failed to update feature access'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to update feature access: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove feature access from a specific role
     * @param array $request
     * @return array
     */
    public function removeRoleFeatureAccess($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }

        $roleId = $request['params']['id'] ?? null;
        $moduleId = $request['params']['module_id'] ?? null;

        if (!$roleId || !$moduleId) {
            return [
                'status' => 400,
                'message' => 'Role ID and Module ID are required'
            ];
        }

        try {
            $userId = $request['user']['id'] ?? null;
            $result = $this->rbacManager->removeFeatureAccessFromRole($roleId, $moduleId, $userId);

            if ($result) {
                return [
                    'status' => 200,
                    'message' => 'Feature access removed successfully'
                ];
            } else {
                return [
                    'status' => 400,
                    'message' => 'Failed to remove feature access'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to remove feature access: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all feature modules
     * @param array $request
     * @return array
     */
    public function getFeatureModules($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM feature_modules WHERE is_active = 1 ORDER BY name");
            $stmt->execute();
            $modules = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => 200,
                'data' => [
                    'modules' => $modules
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to fetch feature modules: ' . $e->getMessage()
            ];
        }
    }
}