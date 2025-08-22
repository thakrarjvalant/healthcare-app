<?php

namespace UserService;

class UserService {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
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
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Generate JWT token
            $token = $this->generateJWT($user);
            return ['success' => true, 'token' => $token, 'user' => $user];
        }
        
        return ['success' => false, 'message' => 'Invalid credentials'];
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
        // Implementation would generate a proper JWT
        // This is a placeholder
        return "jwt_token_for_user_" . $user['id'];
    }
}