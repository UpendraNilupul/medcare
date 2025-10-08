<?php
require_once 'config.php';

if (!isset($suppress_headers)) {
    header('Content-Type: text/html');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    echo '<html><body><h1>Method not allowed</h1></body></html>';
    exit;
}

try {
    $appointment_id = $_GET['appointment_id'] ?? '';
    
    if (empty($appointment_id)) {
        echo '<html><body><h1>Error: Appointment ID is required</h1></body></html>';
        exit;
    }
    
    // Fetch appointment details
    $sql = "SELECT * FROM appointments WHERE appointment_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo '<html><body><h1>Database Error</h1><p>Error preparing statement: ' . htmlspecialchars($conn->error) . '</p></body></html>';
        exit;
    }
    
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo '<html><body><h1>Error: Appointment not found</h1></body></html>';
        exit;
    }
    
    $appointment = $result->fetch_assoc();
    
    // Default consultation fees based on specialty
    $consultation_fees = [
        'General Medicine' => 3000,
        'Cardiology' => 5000,
        'Neurology' => 5500,
        'Orthopedics' => 4500,
        'Pediatrics' => 3500,
        'Dermatology' => 4000,
        'Gynecology' => 4000,
        'Emergency Medicine' => 6000
    ];
    
    $consultation_fee = $consultation_fees[$appointment['specialty']] ?? 3500;
    $service_charge = 500;
    $total_amount = $consultation_fee + $service_charge;
    
    $receipt_date = date('Y-m-d H:i:s');
    $appointment_date = date('F j, Y', strtotime($appointment['appointment_date']));
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Appointment Receipt - MedCare Hospital</title>
        <style>
            @media print {
                .no-print { display: none !important; }
                body { margin: 0; }
            }
            
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                margin: 20px; 
                color: #333; 
                background-color: #f8f9fa;
            }
            
            .receipt-container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            
            .header { 
                background: linear-gradient(135deg, #2c5aa0 0%, #1e3f73 100%);
                color: white;
                text-align: center; 
                padding: 30px 20px;
            }
            
            .logo { 
                font-size: 32px; 
                font-weight: bold; 
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }
            
            .hospital-info { 
                font-size: 16px; 
                opacity: 0.9;
                line-height: 1.6;
            }
            
            .content {
                padding: 40px;
            }
            
            .receipt-title { 
                text-align: center; 
                font-size: 28px; 
                font-weight: bold; 
                color: #2c5aa0; 
                margin-bottom: 40px;
                text-transform: uppercase;
                letter-spacing: 2px;
            }
            
            .info-section { 
                margin-bottom: 30px; 
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                border-left: 4px solid #2c5aa0;
            }
            
            .info-title { 
                font-size: 18px; 
                font-weight: bold; 
                color: #2c5aa0; 
                margin-bottom: 15px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            .info-row { 
                display: flex; 
                margin-bottom: 12px;
                align-items: center;
            }
            
            .info-label { 
                font-weight: 600; 
                min-width: 200px;
                color: #555;
            }
            
            .info-value { 
                flex: 1;
                font-weight: 500;
            }
            
            .charges-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 30px 0;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 0 0 1px #ddd;
            }
            
            .charges-table th, .charges-table td { 
                padding: 15px 20px; 
                text-align: left;
                border-bottom: 1px solid #eee;
            }
            
            .charges-table th { 
                background: #2c5aa0;
                color: white;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            .charges-table td {
                background: white;
            }
            
            .total-row { 
                background: #e3f2fd !important;
                font-weight: bold;
                font-size: 18px;
                color: #2c5aa0;
            }
            
            .receipt-note { 
                background: #fff8e1;
                border: 1px solid #ffcc02;
                border-left: 4px solid #ff9800;
                padding: 20px;
                border-radius: 8px;
                margin: 30px 0;
            }
            
            .receipt-note h4 {
                margin-top: 0;
                color: #f57c00;
            }
            
            .receipt-note ul {
                margin: 10px 0;
                padding-left: 20px;
            }
            
            .receipt-note li {
                margin: 8px 0;
                line-height: 1.4;
            }
            
            .footer { 
                text-align: center; 
                margin-top: 40px; 
                padding-top: 20px; 
                border-top: 2px solid #e0e0e0;
                color: #666;
                line-height: 1.6;
            }
            
            .print-btn {
                background: #2c5aa0;
                color: white;
                border: none;
                padding: 12px 30px;
                font-size: 16px;
                border-radius: 5px;
                cursor: pointer;
                margin: 20px 10px;
                transition: background 0.3s;
            }
            
            .print-btn:hover {
                background: #1e3f73;
            }
            
            .download-btn {
                background: #28a745;
                color: white;
                border: none;
                padding: 12px 30px;
                font-size: 16px;
                border-radius: 5px;
                cursor: pointer;
                margin: 20px 10px;
                transition: background 0.3s;
            }
            
            .download-btn:hover {
                background: #1e7e34;
            }
            
            .button-container {
                text-align: center;
                margin: 30px 0;
            }
            
            .receipt-id {
                background: #2c5aa0;
                color: white;
                padding: 8px 15px;
                border-radius: 20px;
                font-weight: bold;
                font-size: 14px;
                display: inline-block;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        <div class="receipt-container">
            <div class="header">
                <div class="logo">
                    🏥 MedCare Hospital
                </div>
                <div class="hospital-info">
                    📞 +94 11 5 777 777 | ✉️ info@medicare.com<br>
                    Quality Healthcare Services | Trusted Medical Care
                </div>
            </div>
            
            <div class="content">
                <div class="receipt-title">Appointment Receipt</div>
                
                <div class="info-section">
                    <div class="info-title">Receipt Information</div>
                    <div class="receipt-id">Receipt No: RCP-<?php echo str_pad($appointment['appointment_id'], 6, '0', STR_PAD_LEFT); ?></div><br>
                    <div class="info-row">
                        <span class="info-label">Generated Date:</span>
                        <span class="info-value"><?php echo $receipt_date; ?></span>
                    </div>
                </div>
                
                <div class="info-section">
                    <div class="info-title">Patient Information</div>
                    <div class="info-row">
                        <span class="info-label">Patient Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($appointment['patient_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email Address:</span>
                        <span class="info-value"><?php echo htmlspecialchars($appointment['email']); ?></span>
                    </div>
                </div>
                
                <div class="info-section">
                    <div class="info-title">Appointment Details</div>
                    <div class="info-row">
                        <span class="info-label">Appointment Date:</span>
                        <span class="info-value"><?php echo $appointment_date; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Time Slot:</span>
                        <span class="info-value"><?php echo htmlspecialchars($appointment['appointment_time']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Doctor:</span>
                        <span class="info-value"><?php echo htmlspecialchars($appointment['doctor_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Medical Specialty:</span>
                        <span class="info-value"><?php echo htmlspecialchars($appointment['specialty']); ?></span>
                    </div>
                </div>
                
                <table class="charges-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="text-align: right;">Amount (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Consultation Fee - <?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                            <td style="text-align: right;"><?php echo number_format($consultation_fee, 2); ?></td>
                        </tr>
                        <tr>
                            <td>Hospital Service Charge</td>
                            <td style="text-align: right;"><?php echo number_format($service_charge, 2); ?></td>
                        </tr>
                        <tr class="total-row">
                            <td><strong>Total Amount Payable</strong></td>
                            <td style="text-align: right;"><strong><?php echo number_format($total_amount, 2); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="receipt-note">
                    <h4>Important Information</h4>
                    <ul>
                        <li>Please bring this receipt on your appointment date</li>
                        <li>Payment is due at the time of consultation</li>
                        <li>For any changes or cancellations, please contact us at least 24 hours in advance</li>
                        <li>This receipt is valid only for the scheduled appointment date and time</li>
                        <li>Consultation fee may vary based on additional services required</li>
                    </ul>
                </div>
                
                <div class="button-container no-print">
                    <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
                    <button class="download-btn" onclick="downloadAsPDF()">📄 Save as PDF</button>
                </div>
                
                <div class="footer">
                    Thank you for choosing MedCare Hospital<br>
                    This is a computer-generated receipt and does not require a signature<br>
                    For any queries, please contact us at +94 11 5 777 777
                </div>
            </div>
        </div>
        
        <script>
            function downloadAsPDF() {
                // Hide print buttons temporarily
                const noPrintElements = document.querySelectorAll('.no-print');
                noPrintElements.forEach(el => el.style.display = 'none');
                
                // Use browser's print to PDF functionality
                window.print();
                
                // Restore print buttons after a delay
                setTimeout(() => {
                    noPrintElements.forEach(el => el.style.display = 'block');
                }, 1000);
            }
        </script>
    </body>
    </html>
    
    <?php
    
} catch (Exception $e) {
    error_log('Receipt generation error: ' . $e->getMessage());
    echo '<html><body><h1>Error generating receipt</h1><p>' . htmlspecialchars($e->getMessage()) . '</p></body></html>';
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>