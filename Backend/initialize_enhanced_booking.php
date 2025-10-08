<?php
// initialize_enhanced_booking.php - Setup script for enhanced booking system
require_once 'config.php';

echo "<h2>Enhanced Booking System Setup</h2>";

try {
    // 1. Check if doctors table exists
    $result = $conn->query("SHOW TABLES LIKE 'doctors'");
    if ($result->num_rows == 0) {
        echo "<p>Creating doctors table...</p>";
        $createDoctors = "
        CREATE TABLE doctors (
            doctor_id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(20),
            specialization VARCHAR(100),
            qualifications TEXT,
            available_time VARCHAR(255) DEFAULT '10-12PM,12-2PM,2-5PM',
            gender ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($createDoctors)) {
            echo "<p style='color: green;'>✓ Doctors table created successfully</p>";
        } else {
            echo "<p style='color: red;'>✗ Error creating doctors table: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>✓ Doctors table already exists</p>";
        
        // Add available_time column if it doesn't exist
        $result = $conn->query("SHOW COLUMNS FROM doctors LIKE 'available_time'");
        if ($result->num_rows == 0) {
            echo "<p>Adding available_time column to doctors table...</p>";
            if ($conn->query("ALTER TABLE doctors ADD COLUMN available_time VARCHAR(255) DEFAULT '10-12PM,12-2PM,2-5PM'")) {
                echo "<p style='color: green;'>✓ Available_time column added</p>";
            } else {
                echo "<p style='color: red;'>✗ Error adding available_time column: " . $conn->error . "</p>";
            }
        }
    }

    // 2. Check if appointments table exists and add missing columns
    $result = $conn->query("SHOW TABLES LIKE 'appointments'");
    if ($result->num_rows > 0) {
        // Add phone column if it doesn't exist
        $result = $conn->query("SHOW COLUMNS FROM appointments LIKE 'phone'");
        if ($result->num_rows == 0) {
            echo "<p>Adding phone column to appointments table...</p>";
            if ($conn->query("ALTER TABLE appointments ADD COLUMN phone VARCHAR(20)")) {
                echo "<p style='color: green;'>✓ Phone column added to appointments</p>";
            } else {
                echo "<p style='color: red;'>✗ Error adding phone column: " . $conn->error . "</p>";
            }
        }
        
        // Add doctor_id column if it doesn't exist
        $result = $conn->query("SHOW COLUMNS FROM appointments LIKE 'doctor_id'");
        if ($result->num_rows == 0) {
            echo "<p>Adding doctor_id column to appointments table...</p>";
            if ($conn->query("ALTER TABLE appointments ADD COLUMN doctor_id INT")) {
                echo "<p style='color: green;'>✓ Doctor_id column added to appointments</p>";
            } else {
                echo "<p style='color: red;'>✗ Error adding doctor_id column: " . $conn->error . "</p>";
            }
        }
    }

    // 3. Insert sample doctors if table is empty
    $result = $conn->query("SELECT COUNT(*) as count FROM doctors");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        echo "<p>Inserting sample doctors...</p>";
        
        $sampleDoctors = [
            [1, 'Dr. Nuwan Perera', 'nuwan.perera@medcare.com', '+94771234567', 'Cardiology', 'MBBS, MD (Cardiology)', '10-12PM,2-5PM', 'Male'],
            [2, 'Dr. David Carter', 'david.carter@medcare.com', '+94771234568', 'Psychiatry', 'MBBS, MD (Psychiatry)', '12-2PM,2-5PM', 'Male'],
            [3, 'Dr. Ishara Jayasinghe', 'ishara.j@medcare.com', '+94771234569', 'Pediatrics', 'MBBS, MD (Pediatrics)', '10-12PM,12-2PM', 'Female'],
            [4, 'Dr. Harini Wijesinghe', 'harini.w@medcare.com', '+94771234570', 'Dermatology', 'MBBS, MD (Dermatology)', '10-12PM,12-2PM,2-5PM', 'Female'],
            [5, 'Dr. Chaminda de Silva', 'chaminda.silva@medcare.com', '+94771234571', 'Orthopedics', 'MBBS, MS (Orthopedics)', '12-2PM,2-5PM', 'Male'],
            [6, 'Dr. Pradeepan Selvakumar', 'pradeepan.s@medcare.com', '+94771234572', 'Orthopedics', 'MBBS, MS (Orthopedics)', '10-12PM,2-5PM', 'Male'],
            [7, 'Dr. Malith Fernando', 'malith.f@medcare.com', '+94771234573', 'Neurology', 'MBBS, MD (Neurology)', '10-12PM,2-5PM', 'Male'],
            [8, 'Dr. Navin Yogeswaran', 'navin.y@medcare.com', '+94771234574', 'Neurology', 'MBBS, MD (Neurology)', '12-2PM,2-5PM', 'Male'],
            [9, 'Dr. Sanduni Karunarathne', 'sanduni.k@medcare.com', '+94771234575', 'Gynecology', 'MBBS, MD (Gynecology)', '10-12PM,2-5PM', 'Female'],
            [10, 'Dr. Aravind Rajkumar', 'aravind.r@medcare.com', '+94771234576', 'Surgery', 'MBBS, MS (Surgery)', '2-5PM', 'Male']
        ];
        
        $insertSql = "INSERT INTO doctors (doctor_id, full_name, email, phone, specialization, qualifications, available_time, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSql);
        
        $successCount = 0;
        foreach ($sampleDoctors as $doctor) {
            $stmt->bind_param("isssssss", ...$doctor);
            if ($stmt->execute()) {
                $successCount++;
            }
        }
        
        echo "<p style='color: green;'>✓ Inserted $successCount sample doctors</p>";
    } else {
        echo "<p style='color: blue;'>✓ Doctors table already has data ({$row['count']} doctors)</p>";
    }

    // 4. Create daily bookings tracking table
    $result = $conn->query("SHOW TABLES LIKE 'doctor_daily_bookings'");
    if ($result->num_rows == 0) {
        echo "<p>Creating doctor_daily_bookings table...</p>";
        $createBookings = "
        CREATE TABLE doctor_daily_bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            doctor_id INT NOT NULL,
            booking_date DATE NOT NULL,
            booked_time VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_doctor_date (doctor_id, booking_date)
        )";
        
        if ($conn->query($createBookings)) {
            echo "<p style='color: green;'>✓ Doctor_daily_bookings table created successfully</p>";
        } else {
            echo "<p style='color: red;'>✗ Error creating doctor_daily_bookings table: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>✓ Doctor_daily_bookings table already exists</p>";
    }

    echo "<hr><h3>Current System Status:</h3>";
    
    // Show current doctors and their availability
    $result = $conn->query("SELECT doctor_id, full_name, specialization, available_time FROM doctors ORDER BY doctor_id");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Doctor Name</th><th>Specialization</th><th>Available Times</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['doctor_id'] . "</td>";
            echo "<td>" . $row['full_name'] . "</td>";
            echo "<td>" . $row['specialization'] . "</td>";
            echo "<td><strong>" . $row['available_time'] . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "<br><p style='color: green; font-weight: bold;'>✓ Enhanced Booking System is ready!</p>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>Book appointments with time matching based on doctor availability</li>";
    echo "<li>View only available time slots for selected doctors</li>";
    echo "<li>Prevent booking conflicts with existing appointments</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
} finally {
    $conn->close();
}
?>