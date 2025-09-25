<?php
// Database connection settings
$servername = "localhost";
$username_db = "root"; 
$password_db = "";     
$dbname = "Medcare";   

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

    // Fetch the hashed password from DB
    $stmt = $conn->prepare("SELECT id, password FROM patients WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $user_id;
            echo "<script>alert('Login successful!'); window.location.href='index.html';</script>";
            exit();
        } else {
            echo "❌ Invalid email or password.";
        }
    } else {
        echo "❌ Invalid email or password.";
    }
    $stmt->close();
}
$conn->close();
?>
