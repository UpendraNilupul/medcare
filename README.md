# 🏥 MedCare Hospital Management System

![HTML](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

## 📋 Project Description
MedCare is a comprehensive Hospital Management System that provides a complete digital platform for healthcare services. The website features user authentication, doctor consultations, appointment booking with PDF receipt generation, and an admin management system for efficient hospital operations. Built with modern web technologies and a focus on user experience and administrative efficiency.

## ✨ Features

### 🔐 Patient Features
- **User Registration & Login** - Secure authentication system with form validation
- **Doctor Directory** - Browse available doctors by specialty
- **Appointment Booking** - Schedule medical appointments with real-time availability
- **PDF Receipt Generation** - Automated receipt system with detailed charge breakdown
- **Service Information** - Comprehensive healthcare services catalog
- **Contact Support** - Multi-channel customer service integration
- **Responsive Interface** - Optimized for mobile and desktop devices

### 👨‍💼 Admin Features
- **Admin Dashboard** - Complete hospital management interface
- **Doctor Management** - Add, edit, and manage doctor profiles and schedules
- **Appointment Oversight** - View, manage, and track all patient appointments
- **User Management** - Monitor registered patients and their information
- **Receipt System** - Generate and manage PDF receipts for all transactions
- **Database Operations** - Comprehensive backend data management tools

### 🎨 Technical Features
- **Responsive Design** - Mobile-first approach with cross-device compatibility
- **Interactive UI** - Dynamic JavaScript functionality with form validation
- **PDF Generation** - Professional receipt system with hospital branding
- **Database Integration** - MySQL backend with PHP server-side processing
- **Security Features** - Input validation and secure user authentication
- **Clean Architecture** - Well-organized MVC-style file structure

## 🚀 Getting Started

### Prerequisites
- **Web Server**: XAMPP, WAMP, or similar local development environment
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **Web Browser**: Modern browser (Chrome, Firefox, Safari, Edge)
- **Text Editor/IDE**: VS Code, PhpStorm, or similar for development

### Installation
1. **Clone the repository**
   ```bash
   git clone https://github.com/UpendraNilupul/medcare.git
   ```

2. **Navigate to project directory**
   ```bash
   cd medcare
   ```

3. **Set up local server**
   - Place the project folder in your web server directory (e.g., `htdocs` for XAMPP)
   - Start Apache and MySQL services in XAMPP/WAMP

4. **Database Setup**
   - Create a MySQL database named `medcare`
   - Run the database setup script: `Backend/setup_database.php`
   - Initialize sample data: `Backend/initialize_doctors.php`

5. **Configure Database Connection**
   - Update `Backend/config.php` with your database credentials
   - Ensure proper permissions for PDF generation

6. **Access the website**
   ```
   http://localhost/medcare/
   ```

## 📁 Project Structure
```
medcare/
├── 📄 index.html              # Homepage with hero section
├── 📄 about.html              # About hospital page
├── 📄 service.html            # Medical services page
├── 📄 doctors.html            # Doctor directory
├── 📄 booking.html            # Appointment booking system
├── 📄 ContactUs.html          # Contact and support page
├── 📄 login.html              # Patient login portal
├── 📄 register.html           # Patient registration
├── 📄 adminLogin.html         # Admin authentication
├── 📄 admin.html              # Admin dashboard
├── 📄 test_receipt_system.php # Receipt system testing
├── 🎨 style.css               # Main responsive stylesheet
├── 🎨 doctors.css             # Doctor page specific styles
├── 🎨 Contactus.css           # Contact page styles
├── ⚡ style.js                # Interactive JavaScript features
├── 📁 image/                  # Hospital images and assets
│   ├── 📁 Doctors/            # Doctor profile pictures
│   └── 📁 hos1_files/         # Additional hospital images
├── 📁 Backend/                # PHP backend functionality
│   ├── � config.php          # Database configuration
│   ├── 📄 setup_database.php  # Database initialization
│   ├── 📄 book_appointment.php # Appointment booking logic
│   ├── 📄 generate_receipt.php # PDF receipt generation
│   ├── 📄 admin.php           # Admin operations
│   ├── 📄 login.php           # User authentication
│   ├── 📄 register.php        # User registration logic
│   └── 📄 doctor_operations.php # Doctor management
├── 📄 IMPLEMENTATION_STATUS.md # Detailed development progress
├── 📄 PDF_RECEIPT_FEATURE_README.md # Receipt system documentation
└── 📄 README.md               # This file
```

## 🖥️ Usage

### For Patients
1. **Visit Homepage** - Access the main portal at `index.html`
2. **Register Account** - Create new patient account via `register.html`
3. **Login** - Access your account through `login.html`
4. **Browse Doctors** - View available doctors and their specialties
5. **Book Appointments** - Schedule consultations with preferred doctors
6. **Download Receipts** - Get PDF receipts for all appointments
7. **Contact Support** - Reach out via the integrated contact form

### For Administrators
1. **Admin Access** - Secure login through `adminLogin.html`
2. **Dashboard Overview** - Monitor system operations via `admin.html`
3. **Doctor Management** - Add, edit, and manage doctor profiles
4. **Appointment Management** - View and manage all patient bookings
5. **User Administration** - Monitor registered patients and their activities
6. **Receipt Management** - Generate and track all PDF receipts
7. **System Maintenance** - Database operations and system health checks

## 🔧 Technologies Used

### Frontend
- **HTML5** - Semantic markup with modern web standards
- **CSS3** - Custom responsive design with Flexbox and Grid
- **JavaScript** - Interactive UI elements and form validation
- **Responsive Design** - Mobile-first approach for all devices

### Backend
- **PHP** - Server-side logic and database operations
- **MySQL** - Relational database for data storage
- **Session Management** - Secure user authentication system
- **PDF Generation** - Automated receipt creation with TCPDF/similar

### Features
- **Form Validation** - Client and server-side input validation
- **Database Integration** - CRUD operations for all entities
- **Receipt System** - Professional PDF generation with branding
- **Admin Panel** - Comprehensive management interface
- **Security** - Input sanitization and SQL injection prevention

## 📊 Current Status

### ✅ Completed Features
- ✅ Patient registration and authentication system
- ✅ Doctor directory with specialty-based browsing
- ✅ Appointment booking with real-time scheduling
- ✅ PDF receipt generation with detailed charge breakdown
- ✅ Admin dashboard with comprehensive management tools
- ✅ Responsive design for all major devices
- ✅ Database integration with proper schema design

### 🚧 In Development
- 🔄 Enhanced security features
- 🔄 Email notifications for appointments
- 🔄 Advanced reporting and analytics
- 🔄 Payment gateway integration

For detailed development progress, see `IMPLEMENTATION_STATUS.md` and `PDF_RECEIPT_FEATURE_README.md`.

## 🐛 Known Issues & Troubleshooting

### Common Issues
1. **PDF Generation Errors**: Ensure proper file permissions for the Backend directory
2. **Database Connection**: Verify MySQL service is running and credentials in `config.php`
3. **Appointment Booking**: Check if doctor initialization script has been run
4. **Receipt Download**: Ensure browser allows PDF downloads

### Debugging Tools
- `Backend/debug_database.php` - Database connection testing
- `Backend/debug_doctor_times.php` - Doctor availability debugging
- `test_receipt_system.php` - Receipt generation testing

## 🤝 Contributing

We welcome contributions to improve MedCare! Here's how you can help:

### Getting Started
1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Make your changes following our coding standards
4. Test your changes thoroughly
5. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
6. Push to the branch (`git push origin feature/AmazingFeature`)
7. Open a Pull Request

### Contribution Guidelines
- Follow existing code style and structure
- Add comments for complex functionality
- Test all new features before submitting
- Update documentation for new features
- Ensure responsive design compatibility

## 📞 Support

### Getting Help
- **Documentation**: Check `IMPLEMENTATION_STATUS.md` for technical details
- **Issues**: Report bugs via GitHub Issues
- **Contact**: Use the website contact form for general inquiries
- **Development**: Review code comments and inline documentation

### Reporting Bugs
When reporting issues, please include:
- Browser and version
- Server environment (XAMPP/WAMP version)
- Steps to reproduce the issue
- Error messages or screenshots

## 📈 Future Enhancements

### Planned Features
- 🎯 Email notification system for appointments
- 💳 Payment gateway integration
- 📱 Mobile application development
- 🔔 SMS reminder system
- 📊 Advanced analytics and reporting
- 🌐 Multi-language support
- 🔐 Two-factor authentication

---

**Made with ❤️ for better healthcare accessibility**

*Last updated: October 2025*
