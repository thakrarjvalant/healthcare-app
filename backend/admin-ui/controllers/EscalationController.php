<?php

namespace AdminUI\Controllers;

use UserService\Middleware\AuthMiddleware;
use Database\DatabaseConnection;
use Shared\RBAC\DynamicRBACManager;

class EscalationController {
    private $db;
    private $rbacManager;

    public function __construct() {
        $this->db = DatabaseConnection::getInstance();
        $this->rbacManager = new DynamicRBACManager();
    }
    
    /**
     * Get all escalations with optional filters
     * @param array $request
     * @return array
     */
    public function getAllEscalations($request) {
        // Require admin authentication and escalation handling permission
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        // Check permission
        $userId = $request['user']['user_id'] ?? $request['user']['id'] ?? null;
        if (!$this->rbacManager->hasPermission($userId, 'escalations.handle')) {
            return [
                'status' => 403,
                'message' => 'Permission denied: Cannot access escalations'
            ];
        }

        try {
            // Build query with optional filters
            $sql = "SELECT e.*, 
                           ec.display_name as category_name,
                           es.display_name as status_name,
                           u1.name as reporter_name,
                           u2.name as assigned_to_name
                    FROM escalations e
                    JOIN escalation_categories ec ON e.category_id = ec.id
                    JOIN escalation_statuses es ON e.status_id = es.id
                    JOIN users u1 ON e.reporter_id = u1.id
                    LEFT JOIN users u2 ON e.assigned_to = u2.id
                    WHERE 1=1";
            
            $params = [];
            
            // Apply filters if provided
            if (!empty($request['query']['category_id'])) {
                $sql .= " AND e.category_id = ?";
                $params[] = $request['query']['category_id'];
            }
            
            if (!empty($request['query']['status_id'])) {
                $sql .= " AND e.status_id = ?";
                $params[] = $request['query']['status_id'];
            }
            
            if (!empty($request['query']['priority'])) {
                $sql .= " AND e.priority = ?";
                $params[] = $request['query']['priority'];
            }
            
            if (!empty($request['query']['assigned_to'])) {
                $sql .= " AND e.assigned_to = ?";
                $params[] = $request['query']['assigned_to'];
            }
            
            // Order by creation date (newest first)
            $sql .= " ORDER BY e.created_at DESC";
            
            // Limit results
            $sql .= " LIMIT 100";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $escalations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => 200,
                'data' => [
                    'escalations' => $escalations
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to fetch escalations: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get escalation by ID
     * @param array $request
     * @return array
     */
    public function getEscalation($request) {
        // Require admin authentication and escalation handling permission
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        // Check permission
        $userId = $request['user']['user_id'] ?? $request['user']['id'] ?? null;
        if (!$this->rbacManager->hasPermission($userId, 'escalations.handle')) {
            return [
                'status' => 403,
                'message' => 'Permission denied: Cannot access escalations'
            ];
        }

        $escalationId = $request['params']['id'] ?? null;
        
        if (!$escalationId) {
            return [
                'status' => 400,
                'message' => 'Escalation ID is required'
            ];
        }

        try {
            $sql = "SELECT e.*, 
                           ec.display_name as category_name,
                           es.display_name as status_name,
                           u1.name as reporter_name,
                           u1.email as reporter_email,
                           u2.name as assigned_to_name,
                           u2.email as assigned_to_email
                    FROM escalations e
                    JOIN escalation_categories ec ON e.category_id = ec.id
                    JOIN escalation_statuses es ON e.status_id = es.id
                    JOIN users u1 ON e.reporter_id = u1.id
                    LEFT JOIN users u2 ON e.assigned_to = u2.id
                    WHERE e.id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$escalationId]);
            $escalation = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$escalation) {
                return [
                    'status' => 404,
                    'message' => 'Escalation not found'
                ];
            }

            // Get comments
            $commentsSql = "SELECT ec.*, u.name as author_name, u.email as author_email
                           FROM escalation_comments ec
                           JOIN users u ON ec.user_id = u.id
                           WHERE ec.escalation_id = ?
                           ORDER BY ec.created_at ASC";
            $commentsStmt = $this->db->prepare($commentsSql);
            $commentsStmt->execute([$escalationId]);
            $comments = $commentsStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $escalation['comments'] = $comments;

            return [
                'status' => 200,
                'data' => [
                    'escalation' => $escalation
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to fetch escalation: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a new escalation
     * @param array $request
     * @return array
     */
    public function createEscalation($request) {
        // Require admin authentication and escalation handling permission
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        // Check permission
        $userId = $request['user']['user_id'] ?? $request['user']['id'] ?? null;
        if (!$this->rbacManager->hasPermission($userId, 'escalations.handle')) {
            return [
                'status' => 403,
                'message' => 'Permission denied: Cannot create escalations'
            ];
        }

        $data = $request['body'] ?? [];
        
        // Validate required fields
        if (empty($data['title']) || empty($data['description']) || empty($data['category_id'])) {
            return [
                'status' => 400,
                'message' => 'Title, description, and category are required'
            ];
        }

        try {
            // Check if category exists
            $categoryStmt = $this->db->prepare("SELECT id FROM escalation_categories WHERE id = ? AND is_active = 1");
            $categoryStmt->execute([$data['category_id']]);
            if (!$categoryStmt->fetch()) {
                return [
                    'status' => 400,
                    'message' => 'Invalid category'
                ];
            }

            // Insert new escalation
            $sql = "INSERT INTO escalations 
                    (title, description, category_id, priority, reporter_id, assigned_to, due_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['title'],
                $data['description'],
                $data['category_id'],
                $data['priority'] ?? 'medium',
                $userId, // Reporter is the current user
                $data['assigned_to'] ?? null,
                !empty($data['due_date']) ? $data['due_date'] : null
            ]);

            if ($result) {
                $escalationId = $this->db->lastInsertId();
                
                // Log the creation
                $this->logEscalationEvent($escalationId, 'create', null, $data, $userId);
                
                // Fetch the created escalation
                return $this->getEscalation(['params' => ['id' => $escalationId], 'user' => $request['user']]);

            } else {
                return [
                    'status' => 500,
                    'message' => 'Failed to create escalation'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to create escalation: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing escalation
     * @param array $request
     * @return array
     */
    public function updateEscalation($request) {
        // Require admin authentication and escalation handling permission
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        // Check permission
        $userId = $request['user']['user_id'] ?? $request['user']['id'] ?? null;
        if (!$this->rbacManager->hasPermission($userId, 'escalations.handle')) {
            return [
                'status' => 403,
                'message' => 'Permission denied: Cannot update escalations'
            ];
        }

        $escalationId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        if (!$escalationId) {
            return [
                'status' => 400,
                'message' => 'Escalation ID is required'
            ];
        }

        try {
            // Check if escalation exists
            $stmt = $this->db->prepare("SELECT * FROM escalations WHERE id = ?");
            $stmt->execute([$escalationId]);
            $existingEscalation = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$existingEscalation) {
                return [
                    'status' => 404,
                    'message' => 'Escalation not found'
                ];
            }

            // Build update query dynamically
            $fields = [];
            $values = [];
            
            if (isset($data['title'])) {
                $fields[] = "title = ?";
                $values[] = $data['title'];
            }
            
            if (isset($data['description'])) {
                $fields[] = "description = ?";
                $values[] = $data['description'];
            }
            
            if (isset($data['category_id'])) {
                // Validate category
                $categoryStmt = $this->db->prepare("SELECT id FROM escalation_categories WHERE id = ? AND is_active = 1");
                $categoryStmt->execute([$data['category_id']]);
                if (!$categoryStmt->fetch()) {
                    return [
                        'status' => 400,
                        'message' => 'Invalid category'
                    ];
                }
                $fields[] = "category_id = ?";
                $values[] = $data['category_id'];
            }
            
            if (isset($data['status_id'])) {
                // Validate status
                $statusStmt = $this->db->prepare("SELECT id FROM escalation_statuses WHERE id = ? AND is_active = 1");
                $statusStmt->execute([$data['status_id']]);
                if (!$statusStmt->fetch()) {
                    return [
                        'status' => 400,
                        'message' => 'Invalid status'
                    ];
                }
                $fields[] = "status_id = ?";
                $values[] = $data['status_id'];
                
                // If status is resolved or closed, set resolved_at
                $finalStatusStmt = $this->db->prepare("SELECT is_final FROM escalation_statuses WHERE id = ?");
                $finalStatusStmt->execute([$data['status_id']]);
                $status = $finalStatusStmt->fetch(\PDO::FETCH_ASSOC);
                if ($status && $status['is_final']) {
                    $fields[] = "resolved_at = NOW()";
                }
            }
            
            if (isset($data['priority'])) {
                $fields[] = "priority = ?";
                $values[] = $data['priority'];
            }
            
            if (isset($data['assigned_to'])) {
                $fields[] = "assigned_to = ?";
                $values[] = $data['assigned_to'];
            }
            
            if (isset($data['due_date'])) {
                $fields[] = "due_date = ?";
                $values[] = $data['due_date'];
            }
            
            if (isset($data['resolution_notes'])) {
                $fields[] = "resolution_notes = ?";
                $values[] = $data['resolution_notes'];
            }
            
            if (empty($fields)) {
                return [
                    'status' => 400,
                    'message' => 'No valid fields to update'
                ];
            }
            
            // Add updated_at
            $fields[] = "updated_at = NOW()";
            
            $values[] = $escalationId;
            
            $sql = "UPDATE escalations SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($values);

            if ($result) {
                // Log the update
                $this->logEscalationEvent($escalationId, 'update', $existingEscalation, $data, $userId);
                
                // Fetch the updated escalation
                return $this->getEscalation(['params' => ['id' => $escalationId], 'user' => $request['user']]);
            } else {
                return [
                    'status' => 500,
                    'message' => 'Failed to update escalation'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to update escalation: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete an escalation
     * @param array $request
     * @return array
     */
    public function deleteEscalation($request) {
        // Require admin authentication and escalation handling permission
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        // Check permission
        $userId = $request['user']['user_id'] ?? $request['user']['id'] ?? null;
        if (!$this->rbacManager->hasPermission($userId, 'escalations.handle')) {
            return [
                'status' => 403,
                'message' => 'Permission denied: Cannot delete escalations'
            ];
        }

        $escalationId = $request['params']['id'] ?? null;
        
        if (!$escalationId) {
            return [
                'status' => 400,
                'message' => 'Escalation ID is required'
            ];
        }

        try {
            // Check if escalation exists
            $stmt = $this->db->prepare("SELECT * FROM escalations WHERE id = ?");
            $stmt->execute([$escalationId]);
            $escalation = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$escalation) {
                return [
                    'status' => 404,
                    'message' => 'Escalation not found'
                ];
            }

            // Delete the escalation (will cascade to comments, attachments, and logs)
            $stmt = $this->db->prepare("DELETE FROM escalations WHERE id = ?");
            $result = $stmt->execute([$escalationId]);

            if ($result) {
                // Log the deletion
                $this->logEscalationEvent($escalationId, 'delete', $escalation, null, $userId);
                
                return [
                    'status' => 200,
                    'message' => 'Escalation deleted successfully'
                ];
            } else {
                return [
                    'status' => 500,
                    'message' => 'Failed to delete escalation'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to delete escalation: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Add a comment to an escalation
     * @param array $request
     * @return array
     */
    public function addComment($request) {
        // Require admin authentication and escalation handling permission
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        // Check permission
        $userId = $request['user']['user_id'] ?? $request['user']['id'] ?? null;
        if (!$this->rbacManager->hasPermission($userId, 'escalations.handle')) {
            return [
                'status' => 403,
                'message' => 'Permission denied: Cannot add comments to escalations'
            ];
        }

        $escalationId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];
        
        if (!$escalationId) {
            return [
                'status' => 400,
                'message' => 'Escalation ID is required'
            ];
        }
        
        if (empty($data['comment'])) {
            return [
                'status' => 400,
                'message' => 'Comment is required'
            ];
        }

        try {
            // Check if escalation exists
            $stmt = $this->db->prepare("SELECT id FROM escalations WHERE id = ?");
            $stmt->execute([$escalationId]);
            if (!$stmt->fetch()) {
                return [
                    'status' => 404,
                    'message' => 'Escalation not found'
                ];
            }

            // Insert comment
            $sql = "INSERT INTO escalation_comments (escalation_id, user_id, comment, is_internal) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $escalationId,
                $userId,
                $data['comment'],
                $data['is_internal'] ?? false
            ]);

            if ($result) {
                $commentId = $this->db->lastInsertId();
                
                // Fetch the created comment
                $commentSql = "SELECT ec.*, u.name as author_name, u.email as author_email
                              FROM escalation_comments ec
                              JOIN users u ON ec.user_id = u.id
                              WHERE ec.id = ?";
                $commentStmt = $this->db->prepare($commentSql);
                $commentStmt->execute([$commentId]);
                $comment = $commentStmt->fetch(\PDO::FETCH_ASSOC);
                
                return [
                    'status' => 201,
                    'message' => 'Comment added successfully',
                    'data' => [
                        'comment' => $comment
                    ]
                ];
            } else {
                return [
                    'status' => 500,
                    'message' => 'Failed to add comment'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to add comment: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get escalation categories
     * @param array $request
     * @return array
     */
    public function getCategories($request) {
        // Require admin authentication and escalation handling permission
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        // Check permission
        $userId = $request['user']['user_id'] ?? $request['user']['id'] ?? null;
        if (!$this->rbacManager->hasPermission($userId, 'escalations.handle')) {
            return [
                'status' => 403,
                'message' => 'Permission denied: Cannot access escalation categories'
            ];
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM escalation_categories WHERE is_active = 1 ORDER BY name");
            $stmt->execute();
            $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => 200,
                'data' => [
                    'categories' => $categories
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to fetch categories: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get escalation statuses
     * @param array $request
     * @return array
     */
    public function getStatuses($request) {
        // Require admin authentication and escalation handling permission
        $authResult = AuthMiddleware::requireRole($request, ['admin', 'super_admin']);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        // Check permission
        $userId = $request['user']['user_id'] ?? $request['user']['id'] ?? null;
        if (!$this->rbacManager->hasPermission($userId, 'escalations.handle')) {
            return [
                'status' => 403,
                'message' => 'Permission denied: Cannot access escalation statuses'
            ];
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM escalation_statuses WHERE is_active = 1 ORDER BY sort_order");
            $stmt->execute();
            $statuses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return [
                'status' => 200,
                'data' => [
                    'statuses' => $statuses
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => 'Failed to fetch statuses: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Log escalation events for audit trail
     * @param int $escalationId
     * @param string $action
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param int|null $performedBy
     */
    private function logEscalationEvent($escalationId, $action, $oldValues = null, $newValues = null, $performedBy = null) {
        try {
            $sql = "INSERT INTO escalation_audit_logs 
                    (escalation_id, action, old_values, new_values, performed_by, ip_address, user_agent)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $escalationId,
                $action,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $performedBy,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            error_log("Failed to log escalation event: " . $e->getMessage());
        }
    }
}