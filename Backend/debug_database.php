<?php
require_once 'config.php';

echo "Testing database connection and structure...\n\n";

// Test database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "✓ Database connection successful\n";

// Check if appointments table exists
$result = $conn->query("SHOW TABLES LIKE 'appointments'");
if ($result->num_rows > 0) {
    echo "✓ Appointments table exists\n";
    
    // Get table structure
    $columns = $conn->query("DESCRIBE appointments");
    echo "\nAppointments table structure:\n";
    echo "Column Name | Type | Null | Key | Default | Extra\n";
    echo "--------------------------------------------------------\n";
    while ($row = $columns->fetch_assoc()) {
        printf("%-15s | %-20s | %-4s | %-3s | %-10s | %s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Key'], 
            $row['Default'] ?? 'NULL', 
            $row['Extra']
        );
    }
    
    // Check if we have any appointments
    $count_result = $conn->query("SELECT COUNT(*) as count FROM appointments");
    $count = $count_result->fetch_assoc()['count'];
    echo "\nTotal appointments in database: " . $count . "\n";
    
    if ($count > 0) {
        $sample = $conn->query("SELECT * FROM appointments ORDER BY id DESC LIMIT 1");
        $appointment = $sample->fetch_assoc();
        echo "\nLatest appointment data:\n";
        foreach ($appointment as $key => $value) {
            echo "$key: " . ($value ?? 'NULL') . "\n";
        }
    }
    
} else {
    echo "✗ Appointments table does not exist\n";
}

// Check if doctors table exists
$result = $conn->query("SHOW TABLES LIKE 'doctors'");
if ($result->num_rows > 0) {
    echo "\n✓ Doctors table exists\n";
} else {
    echo "\n✗ Doctors table does not exist\n";
}

$conn->close();
?>