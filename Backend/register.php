<?php
// Database connection settings
$servername = "localhost";
$username_db = "root"; // Change if your MySQL username is different
$password_db = "";     // Change if your MySQL password is set
$dbname = "Medcare";   // Change to your actual database name

// Create connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $age = $_POST['age'] ?? '';
    $nationalId = $_POST['nationalId'] ?? '';
    $phoneNumber = $_POST['phoneNumber'] ?? '';
    $bloodGroup = $_POST['bloodGroup'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute insert query
    $stmt = $conn->prepare("INSERT INTO patients (full_name, gender, age, national_id, phone_number, blood_group, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssss", $fullName, $gender, $age, $nationalId, $phoneNumber, $bloodGroup, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!'); window.location.href='../login.html';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>