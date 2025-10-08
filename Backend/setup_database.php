<?php
require_once 'config.php';

// SQL to create doctors table if not exists with consultation fees
$create_doctors_table = "
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    specialty VARCHAR(255) NOT NULL,
    consultation_fee DECIMAL(10,2) DEFAULT 3500.00,
    experience_years INT DEFAULT 0,
    qualification VARCHAR(500),
    image_path VARCHAR(255),
    availability JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

// SQL to update appointments table to include charges
$alter_appointments_table = "
ALTER TABLE appointments 
ADD COLUMN IF NOT EXISTS consultation_fee DECIMAL(10,2) DEFAULT 3500.00,
ADD COLUMN IF NOT EXISTS service_charge DECIMAL(10,2) DEFAULT 500.00,
ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,2) DEFAULT 4000.00
";

try {
    // Create doctors table
    if ($conn->query($create_doctors_table) === TRUE) {
        echo "Doctors table created successfully or already exists.\n";
    } else {
        echo "Error creating doctors table: " . $conn->error . "\n";
    }
    
    // Modify appointments table
    if ($conn->query($alter_appointments_table) === TRUE) {
        echo "Appointments table updated successfully.\n";
    } else {
        echo "Error updating appointments table: " . $conn->error . "\n";
    }
    
    // Insert sample doctors with consultation fees
    $sample_doctors = [
        ['Dr. Aravind Rajkumar', 'Cardiology', 5000.00, 15, 'MBBS, MD (Cardiology), FRCP'],
        ['Dr. David Carter', 'Neurology', 5500.00, 20, 'MBBS, MD (Neurology), DM'],
        ['Dr. Navin Yogeswaran', 'Orthopedics', 4500.00, 12, 'MBBS, MS (Orthopedics)'],
        ['Dr. Pradeepan Selvakumar', 'General Medicine', 3000.00, 8, 'MBBS, MD (Internal Medicine)'],
        ['Dr. Sarah Johnson', 'Pediatrics', 3500.00, 10, 'MBBS, MD (Pediatrics)'],
        ['Dr. Michael Brown', 'Dermatology', 4000.00, 14, 'MBBS, MD (Dermatology)'],
        ['Dr. Emily Davis', 'Gynecology', 4000.00, 16, 'MBBS, MS (Gynecology & Obstetrics)'],
        ['Dr. James Wilson', 'Emergency Medicine', 6000.00, 18, 'MBBS, MD (Emergency Medicine)']
    ];
    
    // Check if doctors already exist
    $check_doctors = "SELECT COUNT(*) as count FROM doctors";
    $result = $conn->query($check_doctors);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $insert_doctor = "INSERT INTO doctors (name, specialty, consultation_fee, experience_years, qualification) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_doctor);
        
        foreach ($sample_doctors as $doctor) {
            $stmt->bind_param("ssdis", $doctor[0], $doctor[1], $doctor[2], $doctor[3], $doctor[4]);
            if ($stmt->execute()) {
                echo "Inserted doctor: " . $doctor[0] . "\n";
            } else {
                echo "Error inserting doctor " . $doctor[0] . ": " . $stmt->error . "\n";
            }
        }
        $stmt->close();
    } else {
        echo "Doctors already exist in database. Skipping insertion.\n";
    }
    
    echo "\nDatabase setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>