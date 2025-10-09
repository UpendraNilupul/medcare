<?php
// API endpoint to cancel appointments
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get the JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Get appointment ID from input
    $appointment_id = $input['appointment_id'] ?? '';
    $cancellation_reason = $input['reason'] ?? 'Cancelled by admin';
    
    // Validate appointment ID
    if (empty($appointment_id) || !is_numeric($appointment_id)) {
        throw new Exception('Valid appointment ID is required');
    }
    
    // First, check if the appointment exists and is not already cancelled
    $check_sql = "SELECT appointment_id, patient_name, appointment_date, appointment_time, status 
                  FROM appointments 
                  WHERE appointment_id = ?";
    
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $check_stmt->bind_param("i", $appointment_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Appointment not found');
    }
    
    $appointment = $result->fetch_assoc();
    $check_stmt->close();
    
    // Check if appointment is already cancelled
    if (isset($appointment['status']) && $appointment['status'] === 'cancelled') {
        throw new Exception('Appointment is already cancelled');
    }
    
    // Check if appointment is in the past (optional - you might want to allow cancelling past appointments)
    $appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
    if (strtotime($appointment_datetime) < time()) {
        // Still allow cancellation of past appointments for admin purposes
        // throw new Exception('Cannot cancel past appointments');
    }
    
    // Add status column if it doesn't exist
    $alter_sql = "ALTER TABLE appointments ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'confirmed'";
    $conn->query($alter_sql);
    
    // Add cancellation tracking columns if they don't exist
    $alter_cancellation_sql = "ALTER TABLE appointments 
                               ADD COLUMN IF NOT EXISTS cancellation_reason TEXT,
                               ADD COLUMN IF NOT EXISTS cancelled_at TIMESTAMP NULL";
    $conn->query($alter_cancellation_sql);
    
    // Update the appointment status to cancelled
    $cancel_sql = "UPDATE appointments 
                   SET status = 'cancelled', 
                       cancellation_reason = ?, 
                       cancelled_at = CURRENT_TIMESTAMP 
                   WHERE appointment_id = ?";
    
    $cancel_stmt = $conn->prepare($cancel_sql);
    if (!$cancel_stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $cancel_stmt->bind_param("si", $cancellation_reason, $appointment_id);
    
    if ($cancel_stmt->execute()) {
        if ($cancel_stmt->affected_rows > 0) {
            // Log the cancellation for audit purposes
            error_log("Appointment cancelled: ID {$appointment_id}, Patient: {$appointment['patient_name']}, Reason: {$cancellation_reason}");
            
            echo json_encode([
                'success' => true,
                'message' => 'Appointment cancelled successfully',
                'appointment_id' => $appointment_id,
                'patient_name' => $appointment['patient_name'],
                'cancelled_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            throw new Exception('No appointment was updated. Please check the appointment ID.');
        }
    } else {
        throw new Exception('Failed to cancel appointment: ' . $cancel_stmt->error);
    }
    
} catch (Exception $e) {
    error_log('Appointment cancellation error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($cancel_stmt)) {
        $cancel_stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>