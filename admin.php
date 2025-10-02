<?php
header("Content-Type: application/json");

// Database connection
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "Medcare";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Fetch patient users from patients table
$sql = "SELECT id, full_name, email, phone_number as phone, blood_group, gender, age  
        FROM patients 
        ORDER BY id DESC";

$result = $conn->query($sql);

$users = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

echo json_encode($users);
$conn->close();
?>
