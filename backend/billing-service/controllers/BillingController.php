<?php

namespace BillingService\Controllers;

use BillingService\BillingService;
use UserService\Middleware\AuthMiddleware;

class BillingController {
    private $billingService;
    
    public function __construct(BillingService $billingService) {
        $this->billingService = $billingService;
    }
    
    /**
     * Get invoices for the authenticated user
     * @param array $request
     * @return array
     */
    public function getUserInvoices($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userId = $authResult['data']['user']['id'];
        $userRole = $authResult['data']['user']['role'];
        
        // Only patients and admins can view invoices
        if ($userRole !== 'patient' && $userRole !== 'admin') {
            return [
                'status' => 403,
                'data' => [
                    'message' => 'Insufficient permissions'
                ]
            ];
        }
        
        $result = $this->billingService->getPatientInvoices($userId);
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'invoices' => $result['invoices']
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
     * Get a specific invoice
     * @param array $request
     * @return array
     */
    public function getInvoice($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $invoiceId = $request['params']['id'] ?? '';
        
        if (empty($invoiceId)) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Invoice ID is required'
                ]
            ];
        }
        
        $result = $this->billingService->getInvoice($invoiceId);
        
        if ($result['success']) {
            // Check if user has permission to view this invoice
            $userId = $authResult['data']['user']['id'];
            $userRole = $authResult['data']['user']['role'];
            
            if ($userRole !== 'admin' && $result['invoice']['patient_id'] != $userId) {
                return [
                    'status' => 403,
                    'data' => [
                        'message' => 'Insufficient permissions'
                    ]
                ];
            }
            
            return [
                'status' => 200,
                'data' => [
                    'invoice' => $result['invoice']
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
     * Process payment for an invoice
     * @param array $request
     * @return array
     */
    public function processPayment($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $invoiceId = $request['params']['id'] ?? '';
        $paymentData = $request['body'] ?? [];
        
        if (empty($invoiceId)) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Invoice ID is required'
                ]
            ];
        }
        
        // Get invoice to verify ownership
        $invoiceResult = $this->billingService->getInvoice($invoiceId);
        
        if (!$invoiceResult['success']) {
            return [
                'status' => 404,
                'data' => [
                    'message' => 'Invoice not found'
                ]
            ];
        }
        
        // Check if user has permission to pay this invoice
        $userId = $authResult['data']['user']['id'];
        $userRole = $authResult['data']['user']['role'];
        
        if ($userRole !== 'admin' && $invoiceResult['invoice']['patient_id'] != $userId) {
            return [
                'status' => 403,
                'data' => [
                    'message' => 'Insufficient permissions'
                ]
            ];
        }
        
        $result = $this->billingService->processPayment($invoiceId, $paymentData);
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'message' => $result['message']
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
}