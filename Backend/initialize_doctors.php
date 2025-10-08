<?php
// Initialize doctors with correct time slots
require_once 'config.php';

echo "<h2>Initializing Doctors with Correct Time Slots</h2>";

// First, let's see if we have any doctors
$checkSql = "SELECT COUNT(*) as count FROM doctors";
$result = $conn->query($checkSql);
$count = 0;

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'];
    echo "<p>Current number of doctors in database: <strong>$count</strong></p>";
}

// Update existing doctors or insert new ones
$doctors = [
    [
        'id' => 1,
        'name' => 'Dr. Nuwan Perera',
        'email' => 'nuwan.perera@medcare.com',
        'phone' => '+94771234567',
        'specialization' => 'Cardiology',
        'qualifications' => 'MBBS, MD (Cardiology)',
        'available_time' => '9AM-12PM,5PM-9PM',
        'gender' => 'Male'
    ],
    [
        'id' => 2,
        'name' => 'Dr. David Carter',
        'email' => 'david.carter@medcare.com',
        'phone' => '+94771234568',
        'specialization' => 'Psychiatry',
        'qualifications' => 'MBBS, MD (Psychiatry)',
        'available_time' => '5PM-9PM',
        'gender' => 'Male'
    ],
    [
        'id' => 3,
        'name' => 'Dr. Ishara Jayasinghe',
        'email' => 'ishara.j@medcare.com',
        'phone' => '+94771234569',
        'specialization' => 'Pediatrics',
        'qualifications' => 'MBBS, MD (Pediatrics)',
        'available_time' => '9AM-12PM',
        'gender' => 'Female'
    ],
    [
        'id' => 4,
        'name' => 'Dr. Harini Wijesinghe',
        'email' => 'harini.w@medcare.com',
        'phone' => '+94771234570',
        'specialization' => 'Dermatology',
        'qualifications' => 'MBBS, MD (Dermatology)',
        'available_time' => '9AM-12PM,5PM-9PM',
        'gender' => 'Female'
    ],
    [
        'id' => 5,
        'name' => 'Dr. Chaminda de Silva',
        'email' => 'chaminda.silva@medcare.com',
        'phone' => '+94771234571',
        'specialization' => 'Orthopedics',
        'qualifications' => 'MBBS, MS (Orthopedics)',
        'available_time' => '5PM-9PM',
        'gender' => 'Male'
    ],
    [
        'id' => 6,
        'name' => 'Dr. Pradeepan Selvakumar',
        'email' => 'pradeepan.s@medcare.com',
        'phone' => '+94771234572',
        'specialization' => 'Orthopedics',
        'qualifications' => 'MBBS, MS (Orthopedics)',
        'available_time' => '9AM-12PM',
        'gender' => 'Male'
    ],
    [
        'id' => 7,
        'name' => 'Dr. Malith Fernando',
        'email' => 'malith.f@medcare.com',
        'phone' => '+94771234573',
        'specialization' => 'Neurology',
        'qualifications' => 'MBBS, MD (Neurology)',
        'available_time' => '9AM-12PM',
        'gender' => 'Male'
    ],
    [
        'id' => 8,
        'name' => 'Dr. Navin Yogeswaran',
        'email' => 'navin.y@medcare.com',
        'phone' => '+94771234574',
        'specialization' => 'Neurology',
        'qualifications' => 'MBBS, MD (Neurology)',
        'available_time' => '5PM-9PM',
        'gender' => 'Male'
    ],
    [
        'id' => 9,
        'name' => 'Dr. Sanduni Karunarathne',
        'email' => 'sanduni.k@medcare.com',
        'phone' => '+94771234575',
        'specialization' => 'Gynecology',
        'qualifications' => 'MBBS, MD (Gynecology)',
        'available_time' => '9AM-12PM',
        'gender' => 'Female'
    ],
    [
        'id' => 10,
        'name' => 'Dr. Aravind Rajkumar',
        'email' => 'aravind.r@medcare.com',
        'phone' => '+94771234576',
        'specialization' => 'Surgery',
        'qualifications' => 'MBBS, MS (Surgery)',
        'available_time' => '5PM-9PM',
        'gender' => 'Male'
    ]
];

echo "<h3>Updating/Inserting Doctors:</h3>";

foreach ($doctors as $doctor) {
    // Check if doctor exists
    $checkStmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ?");
    $checkStmt->bind_param("i", $doctor['id']);
    $checkStmt->execute();
    $exists = $checkStmt->get_result()->num_rows > 0;
    
    if ($exists) {
        // Update existing doctor
        $updateStmt = $conn->prepare("UPDATE doctors SET full_name = ?, email = ?, phone = ?, specialization = ?, qualifications = ?, available_time = ?, gender = ? WHERE doctor_id = ?");
        $updateStmt->bind_param("sssssssi", 
            $doctor['name'], 
            $doctor['email'], 
            $doctor['phone'], 
            $doctor['specialization'], 
            $doctor['qualifications'], 
            $doctor['available_time'], 
            $doctor['gender'], 
            $doctor['id']
        );
        
        if ($updateStmt->execute()) {
            echo "<p>✅ Updated: {$doctor['name']} - Available: {$doctor['available_time']}</p>";
        } else {
            echo "<p>❌ Failed to update: {$doctor['name']} - Error: " . $updateStmt->error . "</p>";
        }
        $updateStmt->close();
    } else {
        // Insert new doctor
        $insertStmt = $conn->prepare("INSERT INTO doctors (doctor_id, full_name, email, phone, specialization, qualifications, available_time, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->bind_param("isssssss", 
            $doctor['id'],
            $doctor['name'], 
            $doctor['email'], 
            $doctor['phone'], 
            $doctor['specialization'], 
            $doctor['qualifications'], 
            $doctor['available_time'], 
            $doctor['gender']
        );
        
        if ($insertStmt->execute()) {
            echo "<p>✅ Inserted: {$doctor['name']} - Available: {$doctor['available_time']}</p>";
        } else {
            echo "<p>❌ Failed to insert: {$doctor['name']} - Error: " . $insertStmt->error . "</p>";
        }
        $insertStmt->close();
    }
    $checkStmt->close();
}

echo "<hr><h3>Final Doctor List:</h3>";
$finalResult = $conn->query("SELECT doctor_id, full_name, specialization, available_time FROM doctors ORDER BY doctor_id");

if ($finalResult && $finalResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f2f2f2;'><th>ID</th><th>Name</th><th>Specialization</th><th>Available Time</th></tr>";
    
    while ($row = $finalResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['doctor_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['specialization']) . "</td>";
        echo "<td style='background-color: #e8f5e8;'><strong>" . htmlspecialchars($row['available_time']) . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin-top: 10px; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
p { margin: 5px 0; }
</style>