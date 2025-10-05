<?php
session_start();

// Database connection
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "Medcare";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["admin_username"]);
    $password = trim($_POST["admin_password"]);

    // Check if fields are empty
    if (empty($username) || empty($password)) {
        header("Location: login.html?error=empty_fields");
        exit;
    }

    // Fetch admin by username and password (plain text comparison)
    $stmt = $conn->prepare("SELECT id, username FROM admins WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Set session variables
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_username"] = $admin["username"];
        $_SESSION["admin_logged_in"] = true;

        // Redirect to admin dashboard
        echo "<script>alert('Admin login successful!'); window.location.href='../admin.html';</script>";
        exit();
        
    } else {
        // Invalid credentials - redirect back to login with error
        echo "❌ Invalid email or password.";
        exit;
    }
    
    $stmt->close();
} else {
    // If not POST request, redirect to login
    header("Location: login.html");
    exit;
}

$conn->close();
?>
