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
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM patients WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password); // Use hashed password in production!
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Successful login
        //header("Location: index.html");
        echo "<script>alert('Successfully logged in!'); window.location.href='index.html';</script>";
        exit();
    } else {
        echo "Invalid email or password.";
    }
    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>