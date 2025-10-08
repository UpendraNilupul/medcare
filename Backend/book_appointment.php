<?php
// Include database configuration
require_once 'config.php';

// Set the content type to JSON for AJAX responses
header('Content-Type: application/json');

// Enable CORS if needed
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
    // Get POST data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $department = $_POST['department'] ?? '';
    $doctor = $_POST['doctor'] ?? '';

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
        // Validate date format and ensure it's not in the past
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
    
    if (empty($doctor)) {
        $errors[] = 'Preferred doctor is required';
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Convert time slot to TIME format for database
    $timeMapping = [
        '9AM-12PM' => '09:00:00',
        '5PM-9PM' => '17:00:00'
    ];
    
    $appointmentTime = $timeMapping[$time] ?? '09:00:00';

    // Get doctor name from the doctorsByDepartment mapping
    $doctorMapping = [
        'dr_smith' => 'Dr.Nuwan Perera',
        'dr_david' => 'Dr.David Carter',
        'dr_jones' => 'Dr.Ishara Jayasinghe',
        'dr_harini' => 'Dr.Harini Wijesinghe',
        'dr_chaminda' => 'Dr.Chaminda de Silva',
        'dr_pradeepan' => 'Dr.Pradeepan Selvakumar',
        'dr_kumar' => 'Dr.Malith Fernando',
        'dr_navin' => 'Dr.Navin Yogeswaran',
        'dr_sanduni' => 'Dr.Sanduni Karunarathne',
        'dr_aravind' => 'Dr.Aravind Rajkumar'
    ];
    
    $doctorName = $doctorMapping[$doctor] ?? $doctor;

    // Get consultation fee for the doctor
    $consultation_fees = [
        'General Medicine' => 3000,
        'Cardiology' => 5000,
        'Neurology' => 5500,
        'Orthopedics' => 4500,
        'Pediatrics' => 3500,
        'Dermatology' => 4000,
        'Gynecology' => 4000,
        'Emergency Medicine' => 6000
    ];
    
    $consultation_fee = $consultation_fees[$department] ?? 3500;
    $service_charge = 500;
    $total_amount = $consultation_fee + $service_charge;

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
            'message' => 'This time slot is already booked. Please select a different time or doctor.'
        ]);
        exit;
    }

    // Prepare the SQL statement with charges
    $sql = "INSERT INTO appointments (patient_name, email, appointment_date, appointment_time, specialty, doctor_name, consultation_fee, service_charge, total_amount) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }
    
    // Bind parameters
    $stmt->bind_param("ssssssddd", $name, $email, $date, $appointmentTime, $department, $doctorName, $consultation_fee, $service_charge, $total_amount);
    
    // Execute the statement
    if ($stmt->execute()) {
        $appointmentId = $conn->insert_id;
        
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
                'doctor' => $doctorName,
                'consultation_fee' => $consultation_fee,
                'service_charge' => $service_charge,
                'total_amount' => $total_amount
            ]
        ]);
    } else {
        throw new Exception('Failed to book appointment: ' . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log('Appointment booking error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while booking your appointment. Please try again.'
    ]);
} finally {
    // Close the database connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>