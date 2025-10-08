<!DOCTYPE html>
<html>
<head>
    <title>Receipt Test Page</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .test-button { background: #2c5aa0; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 10px; text-decoration: none; display: inline-block; }
        .test-button:hover { background: #1e3f73; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>MedCare Receipt System Test</h1>
        
        <h2>System Status</h2>
        <?php
        require_once 'Backend/config.php';
        
        // Test database connection
        if ($conn->connect_error) {
            echo '<p class="error">❌ Database connection failed: ' . $conn->connect_error . '</p>';
        } else {
            echo '<p class="success">✅ Database connection successful</p>';
            
            // Check if appointments exist
            $result = $conn->query("SELECT COUNT(*) as count FROM appointments");
            if ($result) {
                $count = $result->fetch_assoc()['count'];
                echo '<p class="success">✅ Found ' . $count . ' appointments in database</p>';
                
                if ($count > 0) {
                    // Get the latest appointment
                    $latest = $conn->query("SELECT appointment_id, patient_name, doctor_name FROM appointments ORDER BY appointment_id DESC LIMIT 1");
                    if ($latest && $latest->num_rows > 0) {
                        $appointment = $latest->fetch_assoc();
                        echo '<p class="success">✅ Latest appointment: ID #' . $appointment['appointment_id'] . ' - ' . htmlspecialchars($appointment['patient_name']) . ' with ' . htmlspecialchars($appointment['doctor_name']) . '</p>';
                        
                        echo '<h2>Test Receipt Generation</h2>';
                        echo '<a href="Backend/generate_receipt.php?appointment_id=' . $appointment['appointment_id'] . '" target="_blank" class="test-button">🧾 View Receipt for Appointment #' . $appointment['appointment_id'] . '</a>';
                    }
                }
            }
        }
        
        $conn->close();
        ?>
        
        <h2>Manual Test</h2>
        <p>Enter an appointment ID to test:</p>
        <input type="number" id="appointmentId" placeholder="Appointment ID" style="padding: 8px; margin: 5px;">
        <button onclick="testReceipt()" class="test-button">📄 Test Receipt</button>
        
        <h2>Create Test Data</h2>
        <a href="Backend/create_sample_appointment.php" target="_blank" class="test-button">➕ Create Sample Appointment</a>
        
        <script>
            function testReceipt() {
                const id = document.getElementById('appointmentId').value;
                if (id) {
                    window.open('Backend/generate_receipt.php?appointment_id=' + id, '_blank');
                } else {
                    alert('Please enter an appointment ID');
                }
            }
        </script>
    </div>
</body>
</html>