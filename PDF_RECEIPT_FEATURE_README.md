# MedCare Hospital - PDF Receipt Feature

## Overview
This feature allows patients to download PDF receipts for their appointments after successful booking. The receipt includes detailed information about the appointment, doctor charges, and payment breakdown.

## Features Added

### 1. **Receipt Generation System**
- Professional HTML receipt with detailed appointment information
- Doctor consultation fees based on medical specialty
- Service charges and total amount calculation
- Printable format optimized for A4 paper
- Mobile-responsive design

### 2. **Database Enhancements**
- Added doctor charges to appointments table
- Created doctors table with consultation fees
- Specialty-based fee structure
- Sample doctors with different consultation rates

### 3. **Booking Process Updates**
- Enhanced booking confirmation with charges display
- Download and View receipt buttons after successful booking
- Real-time fee calculation based on selected specialty

## File Structure

```
Backend/
├── generate_receipt.php          # Main receipt generation script
├── setup_database.php           # Database setup and doctor data
├── book_appointment.php         # Updated booking with charges
├── create_sample_appointment.php # Testing utility
└── test_receipt.php             # Testing utility

booking.html                     # Updated with PDF download buttons
```

## Installation & Setup

### 1. Database Setup
Run the database setup script to create necessary tables:
```bash
php Backend/setup_database.php
```

This will:
- Create doctors table with consultation fees
- Add charge-related columns to appointments table
- Insert sample doctors with different specialties and fees

### 2. Doctor Consultation Fees
The system includes the following fee structure:

| Specialty | Consultation Fee (LKR) |
|-----------|------------------------|
| General Medicine | 3,000 |
| Cardiology | 5,000 |
| Neurology | 5,500 |
| Orthopedics | 4,500 |
| Pediatrics | 3,500 |
| Dermatology | 4,000 |
| Gynecology | 4,000 |
| Emergency Medicine | 6,000 |

**Service Charge:** LKR 500 (fixed for all appointments)

### 3. Testing the Feature
Create a sample appointment for testing:
```bash
php Backend/create_sample_appointment.php
```

## How It Works

### Booking Process
1. Patient fills out the appointment booking form
2. Upon successful booking, the system displays:
   - Appointment confirmation details
   - Doctor consultation fee
   - Service charge
   - Total amount
   - Download Receipt button
   - View Receipt button

### Receipt Generation
1. **Download Receipt**: Opens the receipt in a new tab for saving/printing
2. **View Receipt**: Opens the receipt in a popup window for immediate viewing
3. The receipt includes:
   - Hospital branding and contact information
   - Unique receipt number (RCP-XXXXXX format)
   - Patient information
   - Appointment details
   - Detailed charge breakdown
   - Important notes and instructions

### Receipt Features
- **Professional Design**: Modern, clean layout with hospital branding
- **Print Optimized**: Proper formatting for A4 paper printing
- **Mobile Responsive**: Works on all device sizes
- **Browser Compatible**: Works in all modern browsers
- **Print-to-PDF**: Can be saved as PDF using browser's print function

## Usage Instructions

### For Patients
1. Book an appointment through the booking form
2. After successful booking, you'll see the confirmation with charges
3. Click "📄 Download Receipt" to open and save the receipt
4. Click "👁️ View Receipt" to view the receipt in a popup
5. Use your browser's print function to save as PDF

### For Administrators
1. Monitor appointments through the admin panel
2. Check consultation fees in the doctors table
3. Generate receipts for any appointment using the appointment ID
4. Modify consultation fees by updating the doctors table

## Customization Options

### Updating Consultation Fees
Edit the fees in `Backend/book_appointment.php`:
```php
$consultation_fees = [
    'General Medicine' => 3000,
    'Cardiology' => 5000,
    // Add or modify specialties and fees
];
```

### Modifying Receipt Design
Edit the CSS in `Backend/generate_receipt.php` to customize:
- Colors and branding
- Layout and spacing
- Typography
- Print styles

### Adding New Doctors
Use the admin panel or add directly to the database:
```sql
INSERT INTO doctors (name, specialty, consultation_fee, experience_years, qualification) 
VALUES ('Dr. Name', 'Specialty', 4000.00, 10, 'MBBS, MD');
```

## Technical Details

### Security Features
- SQL injection protection using prepared statements
- Input validation and sanitization
- XSS protection with htmlspecialchars
- Appointment ID validation

### Error Handling
- Database connection error handling
- Missing appointment ID handling
- Invalid appointment ID handling
- User-friendly error messages

### Browser Compatibility
- Chrome, Firefox, Safari, Edge
- Mobile browsers (iOS Safari, Chrome Mobile)
- Print functionality across all browsers

## Troubleshooting

### Common Issues

1. **Receipt not loading**
   - Check if appointment ID exists in database
   - Verify database connection in config.php
   - Check for PHP errors in server logs

2. **Print button not working**
   - Ensure browser allows popups
   - Try using the download option instead
   - Check browser's print settings

3. **Charges not displaying correctly**
   - Verify consultation fees in booking script
   - Check if appointments table has charge columns
   - Run database setup script again if needed

### Database Queries for Debugging

Check appointments with charges:
```sql
SELECT id, patient_name, doctor_name, consultation_fee, service_charge, total_amount 
FROM appointments 
ORDER BY id DESC LIMIT 10;
```

Check doctors and their fees:
```sql
SELECT name, specialty, consultation_fee 
FROM doctors 
ORDER BY specialty;
```

## Future Enhancements

Possible improvements for the receipt system:
- Email receipt to patient automatically
- SMS notifications with receipt link
- Receipt history for patients
- Multiple language support
- Digital signature integration
- Payment gateway integration
- Receipt templates for different specialties

## Support

For technical support or questions about this feature:
- Check the troubleshooting section above
- Review the PHP error logs
- Test with sample appointments
- Verify database setup completion

---

**Version:** 1.0  
**Last Updated:** October 2025  
**Compatibility:** PHP 7.4+, MySQL 5.7+, Modern Browsers