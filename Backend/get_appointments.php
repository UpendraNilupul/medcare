<?php
// API endpoint to fetch appointments for admin panel
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Get appointments from database
    $sql = "SELECT 
                appointment_id,
                patient_name,
                email,
                appointment_date,
                appointment_time,
                specialty,
                doctor_name,
                created_at
            FROM appointments 
            ORDER BY appointment_date DESC, appointment_time ASC";
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception('Database query failed: ' . $conn->error);
    }
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        // Format the data for display
        $appointments[] = [
            'id' => $row['appointment_id'],
            'patient' => $row['patient_name'],
            'email' => $row['email'],
            'date' => date('M d, Y', strtotime($row['appointment_date'])),
            'time' => date('g:i A', strtotime($row['appointment_time'])),
            'specialty' => ucfirst($row['specialty']),
            'doctor' => $row['doctor_name'],
            'created' => date('M d, Y g:i A', strtotime($row['created_at'])),
            'status' => strtotime($row['appointment_date'] . ' ' . $row['appointment_time']) > time() ? 'Upcoming' : 'Past'
        ];
    }
    
    // Get statistics
    $totalCount = count($appointments);
    $upcomingCount = count(array_filter($appointments, function($apt) {
        return $apt['status'] === 'Upcoming';
    }));
    $todayCount = count(array_filter($appointments, function($apt) {
        return strpos($apt['date'], date('M d, Y')) !== false;
    }));
    
    echo json_encode([
        'success' => true,
        'appointments' => $appointments,
        'statistics' => [
            'total' => $totalCount,
            'upcoming' => $upcomingCount, 
            'today' => $todayCount
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Admin appointments fetch error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch appointments: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>