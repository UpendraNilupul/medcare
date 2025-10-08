<?php
// filepath: c:\Users\LENOVO\Desktop\Web development project\Website\medcare\Backend\doctor_operations.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Medcare";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get the action parameter
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_all_doctors':
            getAllDoctors($conn);
            break;
            
        case 'get_doctor':
            $id = $_GET['id'] ?? '';
            if ($id) {
                getDoctor($conn, $id);
            } else {
                echo json_encode(['error' => 'Doctor ID is required']);
            }
            break;
            
        case 'add_doctor':
            $input = json_decode(file_get_contents('php://input'), true);
            addDoctor($conn, $input);
            break;
            
        case 'update_doctor':
            $input = json_decode(file_get_contents('php://input'), true);
            updateDoctor($conn, $input);
            break;
            
        case 'delete_doctor':
            $input = json_decode(file_get_contents('php://input'), true);
            deleteDoctor($conn, $input);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
}

// Function to get all doctors
function getAllDoctors($conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM doctors ORDER BY doctor_id DESC");
        $stmt->execute();
        $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($doctors);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to fetch doctors: ' . $e->getMessage()]);
    }
}

// Function to get a single doctor
function getDoctor($conn, $id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
        $stmt->execute([$id]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($doctor) {
            echo json_encode($doctor);
        } else {
            echo json_encode(['error' => 'Doctor not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to fetch doctor: ' . $e->getMessage()]);
    }
}

// Function to add a new doctor
function addDoctor($conn, $data) {
    try {
        // Validate required fields
        $required_fields = ['full_name', 'email', 'phone', 'specialization', 'qualifications', 'available_time', 'gender'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                echo json_encode(['error' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
                return;
            }
        }
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            echo json_encode(['error' => 'Email already exists']);
            return;
        }
        
        // Insert new doctor
        $stmt = $conn->prepare("INSERT INTO doctors (full_name, email, phone, specialization, qualifications, available_time, gender) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $result = $stmt->execute([
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['specialization'],
            $data['qualifications'],
            $data['available_time'],
            $data['gender']
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Doctor added successfully', 'id' => $conn->lastInsertId()]);
        } else {
            echo json_encode(['error' => 'Failed to add doctor']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to update a doctor
function updateDoctor($conn, $data) {
    try {
        // Validate required fields
        if (empty($data['id'])) {
            echo json_encode(['error' => 'Doctor ID is required']);
            return;
        }
        
        $required_fields = ['full_name', 'email', 'phone', 'specialization', 'qualifications', 'available_time', 'gender'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                echo json_encode(['error' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
                return;
            }
        }
        
        // Check if email already exists for another doctor
        $stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE email = ? AND doctor_id != ?");
        $stmt->execute([$data['email'], $data['id']]);
        if ($stmt->fetch()) {
            echo json_encode(['error' => 'Email already exists for another doctor']);
            return;
        }
        
        // Update doctor
        $stmt = $conn->prepare("UPDATE doctors SET full_name = ?, email = ?, phone = ?, specialization = ?, qualifications = ?, available_time = ?, gender = ? WHERE doctor_id = ?");
        
        $result = $stmt->execute([
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['specialization'],
            $data['qualifications'],
            $data['available_time'],
            $data['gender'],
            $data['id']
        ]);
        
        if ($result) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Doctor updated successfully']);
            } else {
                echo json_encode(['error' => 'No changes made or doctor not found']);
            }
        } else {
            echo json_encode(['error' => 'Failed to update doctor']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to delete a doctor
function deleteDoctor($conn, $data) {
    try {
        if (empty($data['id'])) {
            echo json_encode(['error' => 'Doctor ID is required']);
            return;
        }
        
        // Check if doctor exists
        $stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ?");
        $stmt->execute([$data['id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Doctor not found']);
            return;
        }
        
        // Delete doctor
        $stmt = $conn->prepare("DELETE FROM doctors WHERE doctor_id = ?");
        $result = $stmt->execute([$data['id']]);
        
        if ($result) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Doctor deleted successfully']);
            } else {
                echo json_encode(['error' => 'Doctor not found']);
            }
        } else {
            echo json_encode(['error' => 'Failed to delete doctor']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>