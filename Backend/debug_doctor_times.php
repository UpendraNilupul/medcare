<?php
// Debug script to check doctor availability data
require_once 'config.php';

echo "<h2>Current Doctor Availability Data:</h2>";

$sql = "SELECT doctor_id, full_name, specialization, available_time FROM doctors";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'>";
    echo "<th>Doctor ID</th>";
    echo "<th>Full Name</th>";
    echo "<th>Specialization</th>";
    echo "<th>Available Time</th>";
    echo "<th>Time (Lowercase)</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['doctor_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['specialization']) . "</td>";
        echo "<td style='background-color: #ffffcc;'><strong>" . htmlspecialchars($row['available_time']) . "</strong></td>";
        echo "<td style='color: #666;'>" . htmlspecialchars(strtolower(trim($row['available_time']))) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr><h3>Time Validation Test:</h3>";
    echo "<p><strong>Available Time Formats to Match:</strong></p>";
    echo "<ul>";
    echo "<li>9AM-12PM (Morning slot)</li>";
    echo "<li>5PM-9PM (Evening slot)</li>";
    echo "</ul>";
    
} else {
    echo "<p style='color: red;'>No doctors found in the database or connection error: " . $conn->error . "</p>";
    
    echo "<h3>Let's check if the doctors table exists:</h3>";
    $tables = $conn->query("SHOW TABLES LIKE 'doctors'");
    if ($tables && $tables->num_rows > 0) {
        echo "<p>✅ Doctors table exists</p>";
        
        echo "<h4>Table Structure:</h4>";
        $structure = $conn->query("DESCRIBE doctors");
        if ($structure) {
            echo "<table border='1'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            while ($field = $structure->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $field['Field'] . "</td>";
                echo "<td>" . $field['Type'] . "</td>";
                echo "<td>" . $field['Null'] . "</td>";
                echo "<td>" . $field['Key'] . "</td>";
                echo "<td>" . $field['Default'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>❌ Doctors table does not exist</p>";
    }
}

$conn->close();
?>

<style>
table {
    font-family: Arial, sans-serif;
    font-size: 14px;
}
th, td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
}
th {
    background-color: #f2f2f2;
    font-weight: bold;
}
</style>