<?php

use Database\DatabaseConnection;

/**
 * 💰 Seed Financial data including invoices, payments, and billing information
 * This seeder creates comprehensive financial test data for billing workflows
 */
class FinancialSeeder
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function seed()
    {
        $users = $this->getUsers();
        $appointments = $this->getAppointments();
        
        if (empty($users['patients'])) {
            echo "⚠️ Warning: No patients found. Please run UserSeeder first.\n";
            return;
        }

        // 💳 Seed Invoices
        $this->seedInvoices($users, $appointments);
        
        // 💰 Seed Payments
        $this->seedPayments($users);

        echo "✅ Financial data seeded successfully!\n";
    }

    private function seedInvoices($users, $appointments)
    {
        $invoices = [
            // Recent invoices
            [
                'patient_id' => $users['patients'][0],
                'appointment_id' => !empty($appointments) ? $appointments[0] : null,
                'amount' => 150.00,
                'status' => 'paid',
                'issued_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d', strtotime('+30 days')),
                'paid_date' => date('Y-m-d')
            ],
            [
                'patient_id' => $users['patients'][0],
                'appointment_id' => !empty($appointments) && count($appointments) > 1 ? $appointments[1] : null,
                'amount' => 75.00,
                'status' => 'pending',
                'issued_date' => date('Y-m-d', strtotime('-5 days')),
                'due_date' => date('Y-m-d', strtotime('+25 days')),
                'paid_date' => null
            ],
            [
                'patient_id' => $users['patients'][0],
                'appointment_id' => !empty($appointments) && count($appointments) > 2 ? $appointments[2] : null,
                'amount' => 200.00,
                'status' => 'pending',
                'issued_date' => date('Y-m-d', strtotime('-10 days')),
                'due_date' => date('Y-m-d', strtotime('+20 days')),
                'paid_date' => null
            ],
            [
                'patient_id' => $users['patients'][0],
                'appointment_id' => null,
                'amount' => 120.00,
                'status' => 'overdue',
                'issued_date' => date('Y-m-d', strtotime('-45 days')),
                'due_date' => date('Y-m-d', strtotime('-15 days')),
                'paid_date' => null
            ],
            [
                'patient_id' => $users['patients'][0],
                'appointment_id' => null,
                'amount' => 300.00,
                'status' => 'paid',
                'issued_date' => date('Y-m-d', strtotime('-30 days')),
                'due_date' => date('Y-m-d'),
                'paid_date' => date('Y-m-d', strtotime('-5 days'))
            ],
            
            // Historical invoices for analytics
            [
                'patient_id' => $users['patients'][0],
                'appointment_id' => null,
                'amount' => 180.00,
                'status' => 'paid',
                'issued_date' => date('Y-m-d', strtotime('-60 days')),
                'due_date' => date('Y-m-d', strtotime('-30 days')),
                'paid_date' => date('Y-m-d', strtotime('-35 days'))
            ],
            [
                'patient_id' => $users['patients'][0],
                'appointment_id' => null,
                'amount' => 95.00,
                'status' => 'paid',
                'issued_date' => date('Y-m-d', strtotime('-90 days')),
                'due_date' => date('Y-m-d', strtotime('-60 days')),
                'paid_date' => date('Y-m-d', strtotime('-65 days'))
            ],
            [
                'patient_id' => $users['patients'][0],
                'appointment_id' => null,
                'amount' => 220.00,
                'status' => 'cancelled',
                'issued_date' => date('Y-m-d', strtotime('-15 days')),
                'due_date' => date('Y-m-d', strtotime('+15 days')),
                'paid_date' => null
            ]
        ];

        foreach ($invoices as $invoice) {
            $stmt = $this->db->prepare("
                INSERT INTO invoices (patient_id, appointment_id, amount, status, issued_date, due_date, paid_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $invoice['patient_id'],
                $invoice['appointment_id'],
                $invoice['amount'],
                $invoice['status'],
                $invoice['issued_date'],
                $invoice['due_date'],
                $invoice['paid_date']
            ]);
        }
    }

    private function seedPayments($users)
    {
        // Get invoice IDs for payments
        $stmt = $this->db->prepare("SELECT id FROM invoices WHERE status IN ('paid') LIMIT 10");
        $stmt->execute();
        $invoiceIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Get receptionist for processed_by
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'receptionist' LIMIT 1");
        $stmt->execute();
        $receptionistId = $stmt->fetchColumn();

        $payments = [
            // Cash payments
            [
                'patient_id' => $users['patients'][0],
                'invoice_id' => !empty($invoiceIds) ? $invoiceIds[0] : null,
                'amount' => 150.00,
                'payment_method' => 'cash',
                'payment_status' => 'completed',
                'transaction_id' => 'CASH_' . time() . '_001',
                'insurance_provider' => null,
                'insurance_policy_number' => null,
                'copay_amount' => null,
                'covered_amount' => null,
                'payment_date' => date('Y-m-d H:i:s'),
                'processed_by' => $receptionistId,
                'notes' => 'Cash payment received at front desk'
            ],
            
            // Credit card payments
            [
                'patient_id' => $users['patients'][0],
                'invoice_id' => !empty($invoiceIds) && count($invoiceIds) > 1 ? $invoiceIds[1] : null,
                'amount' => 300.00,
                'payment_method' => 'credit_card',
                'payment_status' => 'completed',
                'transaction_id' => 'CC_4532_****_1234_' . time(),
                'insurance_provider' => null,
                'insurance_policy_number' => null,
                'copay_amount' => null,
                'covered_amount' => null,
                'payment_date' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'processed_by' => $receptionistId,
                'notes' => 'Credit card payment processed successfully'
            ],
            
            // Insurance payments
            [
                'patient_id' => $users['patients'][0],
                'invoice_id' => !empty($invoiceIds) && count($invoiceIds) > 2 ? $invoiceIds[2] : null,
                'amount' => 180.00,
                'payment_method' => 'insurance',
                'payment_status' => 'completed',
                'transaction_id' => 'INS_CLAIM_' . time() . '_001',
                'insurance_provider' => 'Blue Cross Blue Shield',
                'insurance_policy_number' => 'BCBS123456789',
                'copay_amount' => 20.00,
                'covered_amount' => 160.00,
                'payment_date' => date('Y-m-d H:i:s', strtotime('-35 days')),
                'processed_by' => $receptionistId,
                'notes' => 'Insurance claim processed - copay collected from patient'
            ],
            
            // Pending insurance payment
            [
                'patient_id' => $users['patients'][0],
                'invoice_id' => null,
                'amount' => 95.00,
                'payment_method' => 'insurance',
                'payment_status' => 'pending',
                'transaction_id' => 'INS_CLAIM_' . time() . '_002',
                'insurance_provider' => 'Aetna',
                'insurance_policy_number' => 'AETNA987654321',
                'copay_amount' => 15.00,
                'covered_amount' => 80.00,
                'payment_date' => null,
                'processed_by' => $receptionistId,
                'notes' => 'Insurance claim submitted - awaiting approval'
            ],
            
            // Bank transfer payment
            [
                'patient_id' => $users['patients'][0],
                'invoice_id' => !empty($invoiceIds) && count($invoiceIds) > 3 ? $invoiceIds[3] : null,
                'amount' => 220.00,
                'payment_method' => 'bank_transfer',
                'payment_status' => 'completed',
                'transaction_id' => 'WIRE_' . time() . '_001',
                'insurance_provider' => null,
                'insurance_policy_number' => null,
                'copay_amount' => null,
                'covered_amount' => null,
                'payment_date' => date('Y-m-d H:i:s', strtotime('-65 days')),
                'processed_by' => $receptionistId,
                'notes' => 'Bank transfer payment confirmed'
            ],
            
            // Failed payment
            [
                'patient_id' => $users['patients'][0],
                'invoice_id' => null,
                'amount' => 150.00,
                'payment_method' => 'credit_card',
                'payment_status' => 'failed',
                'transaction_id' => 'CC_FAILED_' . time(),
                'insurance_provider' => null,
                'insurance_policy_number' => null,
                'copay_amount' => null,
                'covered_amount' => null,
                'payment_date' => null,
                'processed_by' => $receptionistId,
                'notes' => 'Credit card payment failed - insufficient funds'
            ],
            
            // Refunded payment
            [
                'patient_id' => $users['patients'][0],
                'invoice_id' => null,
                'amount' => -75.00, // Negative amount for refund
                'payment_method' => 'credit_card',
                'payment_status' => 'refunded',
                'transaction_id' => 'REFUND_' . time() . '_001',
                'insurance_provider' => null,
                'insurance_policy_number' => null,
                'copay_amount' => null,
                'covered_amount' => null,
                'payment_date' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'processed_by' => $receptionistId,
                'notes' => 'Refund processed for cancelled appointment'
            ]
        ];

        foreach ($payments as $payment) {
            $stmt = $this->db->prepare("
                INSERT INTO payments (patient_id, invoice_id, amount, payment_method, payment_status, transaction_id, insurance_provider, insurance_policy_number, copay_amount, covered_amount, payment_date, processed_by, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $payment['patient_id'],
                $payment['invoice_id'],
                $payment['amount'],
                $payment['payment_method'],
                $payment['payment_status'],
                $payment['transaction_id'],
                $payment['insurance_provider'],
                $payment['insurance_policy_number'],
                $payment['copay_amount'],
                $payment['covered_amount'],
                $payment['payment_date'],
                $payment['processed_by'],
                $payment['notes']
            ]);
        }
    }

    private function getUsers()
    {
        $users = ['patients' => []];
        
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'patient' LIMIT 5");
        $stmt->execute();
        $users['patients'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $users;
    }

    private function getAppointments()
    {
        $stmt = $this->db->prepare("SELECT id FROM appointments LIMIT 10");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function unseed()
    {
        $this->db->exec("DELETE FROM payments");
        $this->db->exec("DELETE FROM invoices WHERE id > 0");
        echo "🗑️ Financial data unseeded successfully!\n";
    }
}