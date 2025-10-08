<?php
require_once 'config.php';

// Check if DomPDF library exists first
$use_dompdf = false;
if (file_exists('dompdf/autoload.inc.php')) {
    require_once 'dompdf/autoload.inc.php';
    $use_dompdf = true;
}

// Check if TCPDF library exists
$use_tcpdf = false;
if (file_exists('tcpdf/tcpdf.php')) {
    require_once('tcpdf/tcpdf.php');
    $use_tcpdf = true;
}

// If neither library is available, show error
if (!$use_dompdf && !$use_tcpdf) {
    die(json_encode(['success' => false, 'message' => 'PDF library not found. Please install DomPDF or TCPDF library.']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $appointment_id = $_GET['appointment_id'] ?? '';
    
    if (empty($appointment_id)) {
        echo json_encode(['success' => false, 'message' => 'Appointment ID is required']);
        exit;
    }
    
    // Fetch appointment details with doctor charges
    $sql = "SELECT a.*, d.consultation_fee, d.specialty as doctor_specialty 
            FROM appointments a 
            LEFT JOIN doctors d ON a.doctor_name = d.name 
            WHERE a.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Appointment not found']);
        exit;
    }
    
    $appointment = $result->fetch_assoc();
    
    // Default consultation fee if not found in database
    $consultation_fee = $appointment['consultation_fee'] ?? 5000; // Default fee in LKR
    $service_charge = 500; // Service charge
    $total_amount = $consultation_fee + $service_charge;
    
    // Generate PDF using available library
    if ($use_dompdf) {
        generatePDFWithDomPDF($appointment, $consultation_fee, $service_charge, $total_amount);
    } else if ($use_tcpdf) {
        generatePDFWithTCPDF($appointment, $consultation_fee, $service_charge, $total_amount);
    } else {
        // Fallback to simple HTML output
        generateSimpleReceipt($appointment, $consultation_fee, $service_charge, $total_amount);
    }
    
} catch (Exception $e) {
    error_log('PDF generation error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error generating PDF receipt']);
}

function generatePDFWithDomPDF($appointment, $consultation_fee, $service_charge, $total_amount) {
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isRemoteEnabled', true);
    
    $dompdf = new Dompdf($options);
    
    $html = generateReceiptHTML($appointment, $consultation_fee, $service_charge, $total_amount);
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    $filename = 'appointment_receipt_' . $appointment['id'] . '.pdf';
    
    // Output PDF to browser
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    echo $dompdf->output();
    exit;
}

function generatePDFWithTCPDF($appointment, $consultation_fee, $service_charge, $total_amount) {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('MedCare Hospital');
    $pdf->SetAuthor('MedCare Hospital');
    $pdf->SetTitle('Appointment Receipt');
    $pdf->SetSubject('Medical Appointment Receipt');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 12);
    
    $html = generateReceiptHTML($appointment, $consultation_fee, $service_charge, $total_amount);
    
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $filename = 'appointment_receipt_' . $appointment['id'] . '.pdf';
    
    // Output PDF to browser
    $pdf->Output($filename, 'D');
    exit;
}

function generateReceiptHTML($appointment, $consultation_fee, $service_charge, $total_amount) {
    $receipt_date = date('Y-m-d H:i:s');
    $appointment_date = date('F j, Y', strtotime($appointment['appointment_date']));
    
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
            .header { text-align: center; border-bottom: 2px solid #2c5aa0; padding-bottom: 20px; margin-bottom: 30px; }
            .logo { font-size: 28px; font-weight: bold; color: #2c5aa0; margin-bottom: 5px; }
            .hospital-info { font-size: 14px; color: #666; }
            .receipt-title { text-align: center; font-size: 24px; font-weight: bold; color: #2c5aa0; margin-bottom: 30px; }
            .info-section { margin-bottom: 25px; }
            .info-title { font-size: 16px; font-weight: bold; color: #2c5aa0; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
            .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
            .info-label { font-weight: bold; width: 150px; }
            .info-value { flex: 1; }
            .charges-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            .charges-table th, .charges-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            .charges-table th { background-color: #f8f9fa; font-weight: bold; color: #2c5aa0; }
            .total-row { background-color: #f0f8ff; font-weight: bold; }
            .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
            .receipt-note { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="logo">🏥 MedCare Hospital</div>
            <div class="hospital-info">
                📞 +94 11 5 777 777 | ✉️ info@medicare.com<br>
                Quality Healthcare Services
            </div>
        </div>
        
        <div class="receipt-title">APPOINTMENT RECEIPT</div>
        
        <div class="info-section">
            <div class="info-title">Receipt Information</div>
            <div class="info-row">
                <span class="info-label">Receipt No:</span>
                <span class="info-value">RCP-' . str_pad($appointment['id'], 6, '0', STR_PAD_LEFT) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Generated Date:</span>
                <span class="info-value">' . $receipt_date . '</span>
            </div>
        </div>
        
        <div class="info-section">
            <div class="info-title">Patient Information</div>
            <div class="info-row">
                <span class="info-label">Patient Name:</span>
                <span class="info-value">' . htmlspecialchars($appointment['patient_name']) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">' . htmlspecialchars($appointment['email']) . '</span>
            </div>
        </div>
        
        <div class="info-section">
            <div class="info-title">Appointment Details</div>
            <div class="info-row">
                <span class="info-label">Appointment Date:</span>
                <span class="info-value">' . $appointment_date . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Time Slot:</span>
                <span class="info-value">' . htmlspecialchars($appointment['appointment_time']) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Doctor:</span>
                <span class="info-value">Dr. ' . htmlspecialchars($appointment['doctor_name']) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Specialty:</span>
                <span class="info-value">' . htmlspecialchars($appointment['specialty']) . '</span>
            </div>
        </div>
        
        <table class="charges-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount (LKR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Consultation Fee - Dr. ' . htmlspecialchars($appointment['doctor_name']) . '</td>
                    <td>' . number_format($consultation_fee, 2) . '</td>
                </tr>
                <tr>
                    <td>Service Charge</td>
                    <td>' . number_format($service_charge, 2) . '</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Total Amount</strong></td>
                    <td><strong>' . number_format($total_amount, 2) . '</strong></td>
                </tr>
            </tbody>
        </table>
        
        <div class="receipt-note">
            <strong>Important Notes:</strong><br>
            • Please bring this receipt on your appointment date<br>
            • Payment is due at the time of consultation<br>
            • For any changes or cancellations, please contact us at least 24 hours in advance<br>
            • This receipt is valid only for the scheduled appointment date and time
        </div>
        
        <div class="footer">
            Thank you for choosing MedCare Hospital<br>
            This is a computer-generated receipt and does not require a signature
        </div>
    </body>
    </html>';
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>