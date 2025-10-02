<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "Medcare";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch($method) {
    case 'GET':
        if ($action === 'get_user' && isset($_GET['id'])) {
            getUserById($_GET['id'], $conn);
        }
        break;
        
    case 'POST':
        if ($action === 'update_user') {
            updateUser($conn);
        } elseif ($action === 'delete_user') {
            deleteUser($conn);
        }
        break;
}

function getUserById($id, $conn) {
    $stmt = $conn->prepare("SELECT id, full_name, email, phone_number, blood_group, gender, age, national_id FROM patients WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["error" => "User not found"]);
    }
    $stmt->close();
}

function updateUser($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = $input['id'] ?? '';
    $fullName = $input['full_name'] ?? '';
    $email = $input['email'] ?? '';
    $phone = $input['phone_number'] ?? '';
    $bloodGroup = $input['blood_group'] ?? '';
    $gender = $input['gender'] ?? '';
    $age = $input['age'] ?? '';
    $nationalId = $input['national_id'] ?? '';
    
    if (empty($id) || empty($fullName) || empty($email)) {
        echo json_encode(["error" => "Missing required fields"]);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE patients SET full_name=?, email=?, phone_number=?, blood_group=?, gender=?, age=?, national_id=? WHERE id=?");
    $stmt->bind_param("sssssssi", $fullName, $email, $phone, $bloodGroup, $gender, $age, $nationalId, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "User updated successfully"]);
        } else {
            echo json_encode(["error" => "No changes made or user not found"]);
        }
    } else {
        echo json_encode(["error" => "Failed to update user: " . $stmt->error]);
    }
    $stmt->close();
}

function deleteUser($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(["error" => "User ID is required"]);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM patients WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "User deleted successfully"]);
        } else {
            echo json_encode(["error" => "User not found"]);
        }
    } else {
        echo json_encode(["error" => "Failed to delete user: " . $stmt->error]);
    }
    $stmt->close();
}

$conn->close();
?>