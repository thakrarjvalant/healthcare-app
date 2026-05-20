<?php

namespace UserService\Controllers;

use UserService\UserService;
use UserService\Models\User;

class UserController {
    private $userService;
    
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }
    
    /**
     * Handle user registration request
     * @param array $request
     * @return array
     */
    public function register($request) {
        $userData = [
            'name' => $request['name'] ?? '',
            'email' => $request['email'] ?? '',
            'password' => $request['password'] ?? '',
            'role' => $request['role'] ?? 'patient'
        ];
        
        $result = $this->userService->register($userData);
        
        if ($result['success']) {
            return [
                'status' => 201,
                'data' => [
                    'message' => 'User registered successfully',
                    'user_id' => $result['user_id']
                ]
            ];
        } else {
            return [
                'status' => 400,
                'data' => [
                    'message' => $result['message']
                ]
            ];
        }
    }
    
    /**
     * Handle user login request
     * @param array $request
     * @return array
     */
    public function login($request) {
        error_log('Login request received');
        error_log('Request data: ' . json_encode($request));
        
        $email = $request['email'] ?? '';
        $password = $request['password'] ?? '';
        
        error_log('Email: ' . $email);
        error_log('Password: ' . ($password ? '****' : 'empty'));
        
        if (empty($email) || empty($password)) {
            error_log('Email or password is empty');
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Email and password are required'
                ]
            ];
        }
        
        error_log('Calling userService->login');
        $result = $this->userService->login($email, $password);
        error_log('Login result: ' . json_encode($result));
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'message' => 'Login successful',
                    'token' => $result['token'],
                    'user' => $result['user']
                ]
            ];
        } else {
            return [
                'status' => 401,
                'data' => [
                    'message' => $result['message']
                ]
            ];
        }
    }
    
    /**
     * Get all users
     * @param array $request
     * @return array
     */
    public function getAllUsers($request) {
        $result = $this->userService->getAllUsers();
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'users' => $result['users']
                ]
            ];
        } else {
            return [
                'status' => 500,
                'data' => [
                    'message' => $result['message']
                ]
            ];
        }
    }
    
    /**
     * Get user by ID
     * @param array $request
     * @param int $userId
     * @return array
     */
    public function getUserById($request, $userId) {
        $result = $this->userService->getUserById($userId);
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'user' => $result['user']
                ]
            ];
        } else {
            return [
                'status' => 404,
                'data' => [
                    'message' => $result['message']
                ]
            ];
        }
    }
    
    /**
     * Get user profile
     * @param array $request
     * @return array
     */
    public function getProfile($request) {
        // Extract user ID from token
        $userId = $this->getUserIdFromToken($request['token'] ?? '');
        
        if (!$userId) {
            return [
                'status' => 401,
                'data' => [
                    'message' => 'Unauthorized'
                ]
            ];
        }
        
        // Fetch user from database using UserService
        $result = $this->userService->getUserById($userId);
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'user' => $result['user']
                ]
            ];
        } else {
            return [
                'status' => 404,
                'data' => [
                    'message' => 'User not found'
                ]
            ];
        }
    }
    
    /**
     * Extract user ID from JWT token
     * @param string $token
     * @return int|null
     */
    private function getUserIdFromToken($token) {
        if (!$token) {
            return null;
        }
        
        // Remove "Bearer " prefix if present
        $token = str_replace('Bearer ', '', $token);
        
        // Validate JWT token using UserService
        $userData = $this->userService->validateJWT($token);
        
        if ($userData) {
            return $userData['user_id'];
        }
        
        return null;
    }
}