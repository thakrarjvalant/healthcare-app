<?php

namespace UserService;

// Add JWT library import
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserService {
    private $db;
    // Add JWT secret key
    private $jwtSecret;
    
    public function __construct($database) {
        $this->db = $database;
        // Use environment variable for JWT secret or fallback to default
        $this->jwtSecret = getenv('JWT_SECRET') ?: 'healthcare_app_secret_key_2023';
    }
    
    /**
     * Register a new user
     * @param array $userData
     * @return array
     */
    public function register($userData) {
        // Validate input data
        if (!$this->validateUserData($userData)) {
            return ['success' => false, 'message' => 'Invalid user data'];
        }
        
        // Hash password
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Insert user into database
        $query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            $userData['name'],
            $userData['email'],
            $hashedPassword,
            $userData['role'] ?? 'patient'
        ]);
        
        if ($result) {
            $userId = $this->db->lastInsertId();
            // Send verification email
            $this->sendVerificationEmail($userData['email'], $userId);
            return ['success' => true, 'user_id' => $userId];
        }
        
        return ['success' => false, 'message' => 'Registration failed'];
    }
    
    /**
     * Authenticate a user
     * @param string $email
     * @param string $password
     * @return array
     */
    public function login($email, $password) {
        $query = "SELECT id, name, email, password, role, verified, created_at, updated_at FROM users WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Remove password from user data before returning
            unset($user['password']);
            
            // Generate JWT token
            $token = $this->generateJWT($user);
            return ['success' => true, 'token' => $token, 'user' => $user];
        }
        
        return ['success' => false, 'message' => 'Invalid credentials'];
    }
    
    /**
     * Get all users
     * @return array
     */
    public function getAllUsers() {
        try {
            $query = "SELECT id, name, email, role, verified, created_at, updated_at FROM users ORDER BY id";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return ['success' => true, 'users' => $users];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to fetch users: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get user by ID
     * @param int $userId
     * @return array
     */
    public function getUserById($userId) {
        try {
            $query = "SELECT id, name, email, role, verified, created_at, updated_at FROM users WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user) {
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'User not found'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to fetch user: ' . $e->getMessage()];
        }
    }
    
    /**
     * Validate user data
     * @param array $userData
     * @return bool
     */
    private function validateUserData($userData) {
        // Check required fields
        $requiredFields = ['name', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (!isset($userData[$field]) || empty($userData[$field])) {
                return false;
            }
        }
        
        // Validate email format
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Check password strength
        if (strlen($userData['password']) < 8) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Send verification email
     * @param string $email
     * @param int $userId
     * @return void
     */
    private function sendVerificationEmail($email, $userId) {
        // Implementation would integrate with Notification Service
        // This is a placeholder
    }
    
    /**
     * Generate JWT token
     * @param array $user
     * @return string
     */
    private function generateJWT($user) {
        // Create token payload
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'exp' => time() + (60 * 60 * 24), // Token expires in 24 hours
            'iat' => time(), // Issued at
            'iss' => 'healthcare-app' // Issuer
        ];
        
        // Generate JWT token
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
    
    /**
     * Validate JWT token
     * @param string $token
     * @return array|null
     */
    public function validateJWT($token) {
        try {
            // Decode JWT token
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Convert stdClass to array
            $payload = json_decode(json_encode($decoded), true);
            
            // Verify user exists in database
            $query = "SELECT id, name, email, role, verified, created_at, updated_at FROM users WHERE id = ? AND email = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$payload['user_id'], $payload['email']]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user) {
                return [
                    'user_id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
            }
            
            return null;
        } catch (\Exception $e) {
            error_log('JWT validation error: ' . $e->getMessage());
            return null;
        }
    }
}