<?php
// enhanced_book_appointment.php - Enhanced appointment booking with doctor availability matching
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get POST data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $department = $_POST['department'] ?? '';
    $doctor_id = $_POST['doctor_id'] ?? '';

    // Validate required fields
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }
    
    if (empty($date)) {
        $errors[] = 'Appointment date is required';
    } else {
        $appointmentDate = DateTime::createFromFormat('Y-m-d', $date);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        if (!$appointmentDate || $appointmentDate < $today) {
            $errors[] = 'Please select a valid future date';
        }
    }
    
    if (empty($time)) {
        $errors[] = 'Please select a preferred time slot';
    }
    
    if (empty($department)) {
        $errors[] = 'Please select a medical specialty';
    }
    
    if (empty($doctor_id)) {
        $errors[] = 'Please select a preferred doctor';
    }

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Get doctor information
    $doctorSql = "SELECT doctor_id, full_name, specialization, available_time FROM doctors WHERE doctor_id = ?";
    $doctorStmt = $conn->prepare($doctorSql);
    $doctorStmt->bind_param("i", $doctor_id);
    $doctorStmt->execute();
    $doctorResult = $doctorStmt->get_result();
    
    if ($doctorResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Selected doctor not found']);
        exit;
    }
    
    $doctor = $doctorResult->fetch_assoc();
    $doctorName = $doctor['full_name'];
    $doctorAvailableTime = $doctor['available_time'];

    // Check if the requested time matches doctor's availability
    $isTimeAvailable = checkDoctorTimeAvailability($doctorAvailableTime, $time);
    
    if (!$isTimeAvailable) {
        echo json_encode([
            'success' => false, 
            'message' => 'Selected time slot is not available for this doctor. Please choose from their available times.',
            'doctor_available_time' => $doctorAvailableTime
        ]);
        exit;
    }

    // Convert time slot to TIME format for database
    $timeMapping = [
        '9AM-12PM' => '09:00:00',
        '5PM-9PM' => '17:00:00'
        
    ];
    
    $appointmentTime = $timeMapping[$time] ?? '10:00:00';

    // Check if the appointment slot is already booked
    $checkSql = "SELECT COUNT(*) as count FROM appointments 
                 WHERE appointment_date = ? AND appointment_time = ? AND doctor_name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("sss", $date, $appointmentTime, $doctorName);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'This time slot is already booked. Please select a different time.'
        ]);
        exit;
    }

    // Book the appointment
    $sql = "INSERT INTO appointments (patient_name, email, phone, appointment_date, appointment_time, specialty, doctor_name, doctor_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("sssssssi", $name, $email, $phone, $date, $appointmentTime, $department, $doctorName, $doctor_id);
    
    if ($stmt->execute()) {
        $appointmentId = $conn->insert_id;
        
        // Update doctor's available time by removing the booked slot
        updateDoctorAvailability($conn, $doctor_id, $time, $date);
        
        echo json_encode([
            'success' => true,
            'message' => 'Appointment booked successfully!',
            'appointment_id' => $appointmentId,
            'details' => [
                'name' => $name,
                'email' => $email,
                'date' => $date,
                'time' => $time,
                'specialty' => $department,
                'doctor' => $doctorName
            ]
        ]);
    } else {
        throw new Exception('Failed to book appointment: ' . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log('Enhanced appointment booking error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while booking your appointment. Please try again.'
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

/**
 * Check if requested time matches doctor's available time
 */
function checkDoctorTimeAvailability($doctorAvailableTime, $requestedTime) {
    // If no specific availability set, allow all times
    if (empty($doctorAvailableTime)) {
        return true;
    }
    
    $timeStr = strtolower(trim($doctorAvailableTime));
    $requestedStr = strtolower(trim($requestedTime));
    
    // Check direct matches (case insensitive)
    if (strpos($timeStr, $requestedStr) !== false) {
        return true;
    }
    
    // Check specific time slot matches with multiple formats
    switch ($requestedStr) {
        case '9am-12pm':
            return (strpos($timeStr, '9am-12pm') !== false || 
                   strpos($timeStr, '9:00am-12:00pm') !== false ||
                   strpos($timeStr, '09:00-12:00') !== false || 
                   strpos($timeStr, '9-12pm') !== false ||
                   strpos($timeStr, 'morning') !== false ||
                   $timeStr === '9am-12pm,5pm-9pm' ||
                   $timeStr === '9am-12pm' ||
                   strpos($timeStr, 'all') !== false);
                   
        case '5pm-9pm':
            return (strpos($timeStr, '5pm-9pm') !== false || 
                   strpos($timeStr, '5:00pm-9:00pm') !== false ||
                   strpos($timeStr, '17:00-21:00') !== false || 
                   strpos($timeStr, '5-9pm') !== false ||
                   strpos($timeStr, 'evening') !== false || 
                   strpos($timeStr, 'night') !== false ||
                   $timeStr === '9am-12pm,5pm-9pm' ||
                   $timeStr === '5pm-9pm' ||
                   strpos($timeStr, 'all') !== false);
                   
        default:
            // For any other format, be more permissive
            return true;
    }
}

/**
 * Update doctor availability after booking (optional feature)
 * This can be used to track daily availability
 */
function updateDoctorAvailability($conn, $doctor_id, $bookedTime, $date) {
    // This is optional - you might want to create a separate table for daily availability
    // For now, we'll just log the booking
    
    $logSql = "INSERT INTO doctor_daily_bookings (doctor_id, booking_date, booked_time, created_at) 
               VALUES (?, ?, ?, NOW())";
    
    // Check if table exists first
    $tableCheck = $conn->query("SHOW TABLES LIKE 'doctor_daily_bookings'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $logStmt = $conn->prepare($logSql);
        if ($logStmt) {
            $logStmt->bind_param("iss", $doctor_id, $date, $bookedTime);
            $logStmt->execute();
            $logStmt->close();
        }
    }
}
?>