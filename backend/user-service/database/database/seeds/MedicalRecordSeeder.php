<?php

use Database\DatabaseConnection;

/**
 * 🏥 Seed Medical Records with comprehensive clinical data
 * This seeder creates detailed medical records, clinical notes, and prescriptions
 */
class MedicalRecordSeeder
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
        
        if (empty($users['patients']) || empty($users['doctors'])) {
            echo "⚠️ Warning: No patients or doctors found. Please run UserSeeder first.\n";
            return;
        }

        // 📋 Seed Clinical Note Templates
        $this->seedClinicalNoteTemplates($users['doctors'][0]);
        
        // 🏥 Seed Medical Records
        $this->seedMedicalRecords($users, $appointments);
        
        // 💊 Seed Prescriptions
        $this->seedPrescriptions($users);
        
        // 📊 Seed Patient Health Records
        $this->seedPatientHealthRecords($users);

        echo "✅ Medical record data seeded successfully!\n";
    }

    private function seedClinicalNoteTemplates($doctorId)
    {
        $templates = [
            [
                'name' => 'General Consultation Template',
                'template_type' => 'general',
                'template_content' => json_encode([
                    'chief_complaint' => 'Patient presents with...',
                    'history_present_illness' => 'Patient reports...',
                    'physical_examination' => 'Vital signs: BP [BP], HR [HR], Temp [TEMP]\nGeneral appearance: [APPEARANCE]\nHEENT: [HEENT]\nCardiovascular: [CV]\nRespiratory: [RESP]\nAbdomen: [ABD]\nNeurological: [NEURO]',
                    'assessment' => 'Clinical impression: [DIAGNOSIS]',
                    'plan' => 'Treatment plan:\n1. [MEDICATION]\n2. [FOLLOW_UP]\n3. [LIFESTYLE_MODIFICATIONS]'
                ]),
                'created_by' => $doctorId
            ],
            [
                'name' => 'Follow-up Visit Template',
                'template_type' => 'followup',
                'template_content' => json_encode([
                    'chief_complaint' => 'Follow-up visit for...',
                    'history_present_illness' => 'Since last visit patient reports...',
                    'physical_examination' => 'Interval examination reveals...',
                    'assessment' => 'Condition status: [IMPROVING/STABLE/WORSENING]',
                    'plan' => 'Continue current treatment:\n1. [CONTINUE_MEDS]\n2. [SCHEDULE_NEXT_VISIT]\n3. [ADDITIONAL_INSTRUCTIONS]'
                ]),
                'created_by' => $doctorId
            ],
            [
                'name' => 'Emergency Consultation Template',
                'template_type' => 'emergency',
                'template_content' => json_encode([
                    'chief_complaint' => 'Emergency presentation: [EMERGENCY_REASON]',
                    'history_present_illness' => 'Acute onset of symptoms...',
                    'physical_examination' => 'Emergency assessment:\nABC status: [AIRWAY/BREATHING/CIRCULATION]\nVital signs: [VITALS]\nFocused exam: [FOCUSED_FINDINGS]',
                    'assessment' => 'Emergency diagnosis: [EMERGENCY_DIAGNOSIS]',
                    'plan' => 'Immediate management:\n1. [IMMEDIATE_TREATMENT]\n2. [MONITORING]\n3. [DISPOSITION]'
                ]),
                'created_by' => $doctorId
            ]
        ];

        foreach ($templates as $template) {
            $stmt = $this->db->prepare("
                INSERT INTO clinical_note_templates (name, template_type, template_content, created_by) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $template['name'],
                $template['template_type'],
                $template['template_content'],
                $template['created_by']
            ]);
        }
    }

    private function seedMedicalRecords($users, $appointments)
    {
        $medicalRecords = [
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'appointment_id' => !empty($appointments) ? $appointments[0] : null,
                'diagnosis' => 'Hypertension (Essential), Type 2 Diabetes Mellitus',
                'prescription' => 'Lisinopril 10mg daily, Metformin 500mg twice daily',
                'notes' => 'Patient presents with well-controlled hypertension and diabetes. Continuing current medications. Advised dietary modifications and regular exercise. Follow-up in 3 months.'
            ],
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'appointment_id' => !empty($appointments) && count($appointments) > 1 ? $appointments[1] : null,
                'diagnosis' => 'Upper Respiratory Tract Infection',
                'prescription' => 'Amoxicillin 500mg three times daily for 7 days',
                'notes' => 'Patient presents with cough, congestion, and mild fever for 3 days. Physical examination reveals mild pharyngeal erythema. Prescribed antibiotics and symptomatic treatment.'
            ],
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'appointment_id' => !empty($appointments) && count($appointments) > 2 ? $appointments[2] : null,
                'diagnosis' => 'Annual Physical Examination - Normal',
                'prescription' => 'Multivitamin daily, Continue current medications',
                'notes' => 'Routine annual physical examination. All systems reviewed and found to be normal. Laboratory results within normal limits. Continue current health maintenance plan.'
            ]
        ];

        foreach ($medicalRecords as $record) {
            $stmt = $this->db->prepare("
                INSERT INTO medical_records (patient_id, doctor_id, appointment_id, diagnosis, prescription, notes) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $record['patient_id'],
                $record['doctor_id'],
                $record['appointment_id'],
                $record['diagnosis'],
                $record['prescription'],
                $record['notes']
            ]);
        }

        // Seed Clinical Notes
        $clinicalNotes = [
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'appointment_id' => !empty($appointments) ? $appointments[0] : null,
                'note_type' => 'general',
                'chief_complaint' => 'Follow-up for hypertension and diabetes',
                'history_present_illness' => 'Patient reports good adherence to medications. No episodes of hypoglycemia. Blood pressure readings at home averaging 130/80.',
                'physical_examination' => 'BP: 132/78, HR: 72, Temp: 98.6°F. General appearance: Well-appearing, no acute distress. HEENT: Normal. CV: Regular rate and rhythm. Lungs: Clear bilaterally.',
                'assessment' => 'Hypertension - well controlled, Type 2 DM - well controlled',
                'plan' => 'Continue current medications. HbA1c in 3 months. Return visit in 3 months.',
                'additional_notes' => 'Patient counseled on importance of medication compliance and lifestyle modifications.'
            ]
        ];

        foreach ($clinicalNotes as $note) {
            $stmt = $this->db->prepare("
                INSERT INTO clinical_notes (patient_id, doctor_id, appointment_id, note_type, chief_complaint, history_present_illness, physical_examination, assessment, plan, additional_notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $note['patient_id'],
                $note['doctor_id'],
                $note['appointment_id'],
                $note['note_type'],
                $note['chief_complaint'],
                $note['history_present_illness'],
                $note['physical_examination'],
                $note['assessment'],
                $note['plan'],
                $note['additional_notes']
            ]);
        }
    }

    private function seedPrescriptions($users)
    {
        $prescriptions = [
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'medication_name' => 'Lisinopril',
                'dosage' => '10mg',
                'frequency' => 'Once daily',
                'duration_days' => 90,
                'instructions' => 'Take in the morning with or without food. Monitor blood pressure regularly.',
                'status' => 'active',
                'prescribed_date' => date('Y-m-d'),
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+90 days')),
                'refills_remaining' => 3,
                'total_refills' => 3
            ],
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'medication_name' => 'Metformin',
                'dosage' => '500mg',
                'frequency' => 'Twice daily',
                'duration_days' => 90,
                'instructions' => 'Take with meals to reduce stomach upset. Monitor for signs of lactic acidosis.',
                'status' => 'active',
                'prescribed_date' => date('Y-m-d'),
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+90 days')),
                'refills_remaining' => 2,
                'total_refills' => 3
            ],
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'medication_name' => 'Amoxicillin',
                'dosage' => '500mg',
                'frequency' => 'Three times daily',
                'duration_days' => 7,
                'instructions' => 'Take with food. Complete the full course even if feeling better.',
                'status' => 'completed',
                'prescribed_date' => date('Y-m-d', strtotime('-14 days')),
                'start_date' => date('Y-m-d', strtotime('-14 days')),
                'end_date' => date('Y-m-d', strtotime('-7 days')),
                'refills_remaining' => 0,
                'total_refills' => 0
            ],
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'medication_name' => 'Vitamin D3',
                'dosage' => '2000 IU',
                'frequency' => 'Once daily',
                'duration_days' => 365,
                'instructions' => 'Take with a meal containing fat for better absorption.',
                'status' => 'active',
                'prescribed_date' => date('Y-m-d', strtotime('-30 days')),
                'start_date' => date('Y-m-d', strtotime('-30 days')),
                'end_date' => date('Y-m-d', strtotime('+335 days')),
                'refills_remaining' => 1,
                'total_refills' => 2
            ]
        ];

        foreach ($prescriptions as $prescription) {
            $stmt = $this->db->prepare("
                INSERT INTO prescriptions (patient_id, doctor_id, medication_name, dosage, frequency, duration_days, instructions, status, prescribed_date, start_date, end_date, refills_remaining, total_refills) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $prescription['patient_id'],
                $prescription['doctor_id'],
                $prescription['medication_name'],
                $prescription['dosage'],
                $prescription['frequency'],
                $prescription['duration_days'],
                $prescription['instructions'],
                $prescription['status'],
                $prescription['prescribed_date'],
                $prescription['start_date'],
                $prescription['end_date'],
                $prescription['refills_remaining'],
                $prescription['total_refills']
            ]);
        }
    }

    private function seedPatientHealthRecords($users)
    {
        $healthRecords = [
            // Vital Signs
            [
                'patient_id' => $users['patients'][0],
                'record_type' => 'vital_signs',
                'data' => json_encode([
                    'blood_pressure_systolic' => 132,
                    'blood_pressure_diastolic' => 78,
                    'heart_rate' => 72,
                    'temperature' => 98.6,
                    'weight' => 180,
                    'height' => 70,
                    'bmi' => 25.8,
                    'oxygen_saturation' => 98
                ]),
                'recorded_date' => date('Y-m-d'),
                'recorded_by' => $users['doctors'][0],
                'notes' => 'Routine vital signs - stable'
            ],
            [
                'patient_id' => $users['patients'][0],
                'record_type' => 'vital_signs',
                'data' => json_encode([
                    'blood_pressure_systolic' => 128,
                    'blood_pressure_diastolic' => 75,
                    'heart_rate' => 68,
                    'temperature' => 98.4,
                    'weight' => 178,
                    'height' => 70,
                    'bmi' => 25.5,
                    'oxygen_saturation' => 99
                ]),
                'recorded_date' => date('Y-m-d', strtotime('-7 days')),
                'recorded_by' => $users['doctors'][0],
                'notes' => 'Improvement in BP control'
            ],
            
            // Lab Results
            [
                'patient_id' => $users['patients'][0],
                'record_type' => 'lab_results',
                'data' => json_encode([
                    'test_name' => 'Comprehensive Metabolic Panel',
                    'glucose' => 95,
                    'bun' => 18,
                    'creatinine' => 1.0,
                    'egfr' => 85,
                    'sodium' => 140,
                    'potassium' => 4.2,
                    'chloride' => 102,
                    'co2' => 24,
                    'hemoglobin_a1c' => 6.8
                ]),
                'recorded_date' => date('Y-m-d', strtotime('-30 days')),
                'recorded_by' => $users['doctors'][0],
                'notes' => 'HbA1c improved from previous 7.2%'
            ],
            
            // Allergies
            [
                'patient_id' => $users['patients'][0],
                'record_type' => 'allergies',
                'data' => json_encode([
                    'allergen' => 'Penicillin',
                    'reaction' => 'Rash, hives',
                    'severity' => 'Moderate',
                    'onset_date' => '2018-05-15'
                ]),
                'recorded_date' => date('Y-m-d', strtotime('-365 days')),
                'recorded_by' => $users['doctors'][0],
                'notes' => 'Patient reports rash and hives with penicillin exposure'
            ],
            
            // Immunizations
            [
                'patient_id' => $users['patients'][0],
                'record_type' => 'immunizations',
                'data' => json_encode([
                    'vaccine' => 'Influenza (seasonal)',
                    'lot_number' => 'FL2023-001',
                    'manufacturer' => 'Sanofi Pasteur',
                    'site' => 'Left deltoid',
                    'route' => 'Intramuscular'
                ]),
                'recorded_date' => date('Y-m-d', strtotime('-60 days')),
                'recorded_by' => $users['doctors'][0],
                'notes' => 'Annual flu vaccination - no adverse reactions'
            ]
        ];

        foreach ($healthRecords as $record) {
            $stmt = $this->db->prepare("
                INSERT INTO patient_health_records (patient_id, record_type, data, recorded_date, recorded_by, notes) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $record['patient_id'],
                $record['record_type'],
                $record['data'],
                $record['recorded_date'],
                $record['recorded_by'],
                $record['notes']
            ]);
        }
    }

    private function getUsers()
    {
        $users = ['patients' => [], 'doctors' => []];
        
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'patient' LIMIT 5");
        $stmt->execute();
        $users['patients'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'doctor' LIMIT 3");
        $stmt->execute();
        $users['doctors'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
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
        $this->db->exec("DELETE FROM patient_health_records");
        $this->db->exec("DELETE FROM prescriptions");
        $this->db->exec("DELETE FROM clinical_notes");
        $this->db->exec("DELETE FROM clinical_note_templates");
        $this->db->exec("DELETE FROM medical_records WHERE id > 0");
        echo "🗑️ Medical record data unseeded successfully!\n";
    }
}