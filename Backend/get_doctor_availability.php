<?php
// get_doctor_availability.php - API to get doctor available times
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $doctor_id = $_GET['doctor_id'] ?? '';
    $date = $_GET['date'] ?? '';
    
    if (empty($doctor_id)) {
        echo json_encode(['success' => false, 'message' => 'Doctor ID is required']);
        exit;
    }

    // Get doctor's available time from database
    $sql = "SELECT full_name, available_time, specialization FROM doctors WHERE doctor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Doctor not found']);
        exit;
    }
    
    $doctor = $result->fetch_assoc();
    $doctorName = $doctor['full_name'];
    $availableTime = $doctor['available_time'];
    
    // Parse doctor's available time (assuming format like "10:00-12:00,14:00-17:00")
    $availableSlots = parseAvailableTime($availableTime);
    
    // If date is provided, check which slots are already booked
    $bookedSlots = [];
    if (!empty($date)) {
        $bookedSql = "SELECT appointment_time FROM appointments 
                      WHERE doctor_name = ? AND appointment_date = ?";
        $bookedStmt = $conn->prepare($bookedSql);
        $bookedStmt->bind_param("ss", $doctorName, $date);
        $bookedStmt->execute();
        $bookedResult = $bookedStmt->get_result();
        
        while ($bookedRow = $bookedResult->fetch_assoc()) {
            $bookedSlots[] = $bookedRow['appointment_time'];
        }
    }
    
    // Filter out booked slots and return available ones
    $finalAvailableSlots = [];
    foreach ($availableSlots as $slot) {
        $timeKey = convertSlotToTimeKey($slot);
        if (!in_array($timeKey, $bookedSlots)) {
            $finalAvailableSlots[] = $slot;
        }
    }
    
    echo json_encode([
        'success' => true,
        'doctor_name' => $doctorName,
        'available_slots' => $finalAvailableSlots,
        'raw_available_time' => $availableTime
    ]);
    
} catch (Exception $e) {
    error_log('Doctor availability error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching doctor availability'
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

/**
 * Parse doctor's available time string into slot array
 * Expected formats: 
 * - "10:00-12:00,14:00-17:00" (24-hour format)
 * - "10-12PM,2-5PM" (12-hour format)
 * - "Morning,Evening" (general terms)
 */
function parseAvailableTime($availableTime) {
    $slots = [];
    
    if (empty($availableTime)) {
        return getDefaultSlots();
    }
    
    // Handle different formats
    $timeStr = strtolower(trim($availableTime));
    
    // If it contains standard slot names, return them directly
    if (strpos($timeStr, '9am-12pm') !== false || strpos($timeStr, '09:00-12:00') !== false) {
        $slots[] = ['value' => '9AM-12PM', 'label' => '9:00 AM - 12:00 PM', 'time_key' => '09:00:00'];
    }
    if (strpos($timeStr, '5pm-9pm') !== false || strpos($timeStr, '17:00-21:00') !== false) {
        $slots[] = ['value' => '5PM-9PM', 'label' => '5:00 PM - 9:00 PM', 'time_key' => '17:00:00'];
    }
    
    // Handle general terms
    if (strpos($timeStr, 'morning') !== false) {
        $slots[] = ['value' => '9AM-12PM', 'label' => '9:00 AM - 12:00 PM', 'time_key' => '09:00:00'];
    }
    if (strpos($timeStr, 'afternoon') !== false || strpos($timeStr, 'evening') !== false) {
        $slots[] = ['value' => '5PM-9PM', 'label' => '5:00 PM - 9:00 PM', 'time_key' => '17:00:00'];
    }
    
    // If no slots found, return default slots
    return empty($slots) ? getDefaultSlots() : $slots;
}

/**
 * Get default time slots if doctor availability is not specific
 */
function getDefaultSlots() {
    return [
        ['value' => '9AM-12PM', 'label' => '9:00 AM - 12:00 PM', 'time_key' => '09:00:00'],
        ['value' => '5PM-9PM', 'label' => '5:00 PM - 9:00 PM', 'time_key' => '17:00:00']
    ];
}

/**
 * Convert slot to time key for database comparison
 */
function convertSlotToTimeKey($slot) {
    return $slot['time_key'];
}
?>