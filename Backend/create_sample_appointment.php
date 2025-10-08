<?php
require_once 'config.php';

// Create a sample appointment for testing
$sample_appointment = [
    'patient_name' => 'John Doe',
    'email' => 'john.doe@example.com',
    'appointment_date' => date('Y-m-d', strtotime('+1 day')),
    'appointment_time' => '09:00:00',
    'specialty' => 'General Medicine',
    'doctor_name' => 'Dr. Pradeepan Selvakumar',
    'consultation_fee' => 3000.00,
    'service_charge' => 500.00,
    'total_amount' => 3500.00
];

$sql = "INSERT INTO appointments (patient_name, email, appointment_date, appointment_time, specialty, doctor_name, consultation_fee, service_charge, total_amount) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssddd", 
    $sample_appointment['patient_name'],
    $sample_appointment['email'],
    $sample_appointment['appointment_date'],
    $sample_appointment['appointment_time'],
    $sample_appointment['specialty'],
    $sample_appointment['doctor_name'],
    $sample_appointment['consultation_fee'],
    $sample_appointment['service_charge'],
    $sample_appointment['total_amount']
);

if ($stmt->execute()) {
    $appointment_id = $conn->insert_id;
    echo "Sample appointment created successfully!\n";
    echo "Appointment ID: " . $appointment_id . "\n";
    echo "Patient: " . $sample_appointment['patient_name'] . "\n";
    echo "Doctor: " . $sample_appointment['doctor_name'] . "\n";
    echo "Date: " . $sample_appointment['appointment_date'] . "\n";
    echo "Total Amount: LKR " . number_format($sample_appointment['total_amount'], 2) . "\n\n";
    echo "Receipt URL: http://localhost/medcare/Backend/generate_receipt.php?appointment_id=" . $appointment_id . "\n";
} else {
    echo "Error creating sample appointment: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();
?>