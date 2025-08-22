<?php

namespace BillingService;

use BillingService\Models\Invoice;

class BillingService {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create a new invoice
     * @param array $invoiceData
     * @return array
     */
    public function createInvoice($invoiceData) {
        // Validate input data
        if (!$this->validateInvoiceData($invoiceData)) {
            return ['success' => false, 'message' => 'Invalid invoice data'];
        }
        
        // Create invoice record
        $query = "INSERT INTO invoices (patient_id, appointment_id, amount, status, issued_date, due_date) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            $invoiceData['patient_id'],
            $invoiceData['appointment_id'],
            $invoiceData['amount'],
            'pending',
            date('Y-m-d'),
            date('Y-m-d', strtotime('+30 days'))
        ]);
        
        if ($result) {
            $invoiceId = $this->db->lastInsertId();
            return ['success' => true, 'invoice_id' => $invoiceId];
        }
        
        return ['success' => false, 'message' => 'Failed to create invoice'];
    }
    
    /**
     * Get invoices for a patient
     * @param int $patientId
     * @return array
     */
    public function getPatientInvoices($patientId) {
        $query = "SELECT * FROM invoices WHERE patient_id = ? ORDER BY issued_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$patientId]);
        $invoices = $stmt->fetchAll();
        
        return ['success' => true, 'invoices' => $invoices];
    }
    
    /**
     * Get invoice by ID
     * @param int $invoiceId
     * @return array
     */
    public function getInvoice($invoiceId) {
        $query = "SELECT * FROM invoices WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$invoiceId]);
        $invoice = $stmt->fetch();
        
        if ($invoice) {
            return ['success' => true, 'invoice' => $invoice];
        }
        
        return ['success' => false, 'message' => 'Invoice not found'];
    }
    
    /**
     * Update invoice status
     * @param int $invoiceId
     * @param string $status
     * @return array
     */
    public function updateInvoiceStatus($invoiceId, $status) {
        $validStatuses = ['pending', 'paid', 'overdue', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        $query = "UPDATE invoices SET status = ?, paid_date = IF(? = 'paid', NOW(), paid_date), updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([$status, $status, $invoiceId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Invoice status updated'];
        }
        
        return ['success' => false, 'message' => 'Failed to update invoice status'];
    }
    
    /**
     * Process payment for an invoice
     * @param int $invoiceId
     * @param array $paymentData
     * @return array
     */
    public function processPayment($invoiceId, $paymentData) {
        // In a real implementation, this would integrate with a payment gateway
        // For now, we'll just update the invoice status to 'paid'
        
        $result = $this->updateInvoiceStatus($invoiceId, 'paid');
        
        if ($result['success']) {
            // Log payment transaction
            $this->logPaymentTransaction($invoiceId, $paymentData);
            
            return ['success' => true, 'message' => 'Payment processed successfully'];
        }
        
        return ['success' => false, 'message' => $result['message']];
    }
    
    /**
     * Validate invoice data
     * @param array $invoiceData
     * @return bool
     */
    private function validateInvoiceData($invoiceData) {
        // Check required fields
        $requiredFields = ['patient_id', 'appointment_id', 'amount'];
        foreach ($requiredFields as $field) {
            if (!isset($invoiceData[$field]) || empty($invoiceData[$field])) {
                return false;
            }
        }
        
        // Validate amount
        if (!is_numeric($invoiceData['amount']) || $invoiceData['amount'] <= 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Log payment transaction
     * @param int $invoiceId
     * @param array $paymentData
     * @return void
     */
    private function logPaymentTransaction($invoiceId, $paymentData) {
        // In a real implementation, this would log the payment transaction
        // For now, we'll just return
        return;
    }
}