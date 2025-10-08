<?php
// Simulate the GET parameter and test the receipt
$_GET['appointment_id'] = '7';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Suppress the content-type header for testing
$suppress_headers = true;

echo "Testing receipt generation for appointment ID: 7\n";
echo "URL would be: generate_receipt.php?appointment_id=7\n\n";
echo "Generated HTML output:\n";
echo "======================\n";

// Include the receipt generator
include 'generate_receipt.php';
?>