# Employee Attendance Management System

A comprehensive web-based application for managing employee attendance, leaves, and performance tracking. Built with HTML, CSS, and PHP with MySQL database.

## Features

### Admin Features
1. **Admin Login & Dashboard**
   - Secure login with email and password
   - Overview of total employees, present/absent counts
   - Quick statistics and pending leave requests

2. **Employee Management**
   - Add, edit, and delete employee records
   - Manage employee details (name, email, phone, department, designation, shift timing)
   - Reset employee passwords
   - Search and filter employees

3. **Daily Attendance Management**
   - View daily attendance records
   - Manually mark/edit attendance
   - Filter by date
   - View department-wise attendance

4. **Attendance Reports**
   - Generate reports by date range
   - Monthly reports by department
   - Export and print functionality
   - Attendance percentage calculation

5. **Leave Management**
   - View all leave requests
   - Approve or reject leave applications
   - Add admin remarks
   - Filter by status and department

6. **Department Management**
   - Create and manage departments
   - Track employee count per department

7. **Profile Settings**
   - Update admin profile information
   - Change password
   - View account details

### Employee Features
1. **Employee Registration**
   - Self-registration with email verification
   - Choose department and designation
   - Set personal password
   - Automatic account activation

2. **Employee Login & Dashboard**
   - Secure employee portal
   - View personal attendance statistics
   - Monthly attendance summary

3. **Attendance Marking**
   - Check-in and check-out functionality
   - Real-time clock display
   - Automatic late status calculation
   - View today's attendance status

3. **Attendance History**
   - View personal attendance records
   - Monthly statistics
   - Working hours calculation
   - Filter by month

4. **Leave Management**
   - Apply for different types of leaves (sick, casual, annual, emergency, unpaid)
   - View leave request status
   - Cancel pending requests
   - Track leave history

5. **Profile Settings**
   - Update personal information
   - Change password
   - View employment details

## Installation Instructions

### Prerequisites
- XAMPP/WAMP/LAMP (Apache + PHP + MySQL)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

### Setup Steps

1. **Install XAMPP/WAMP**
   - Download and install from [https://www.apachefriends.org/](https://www.apachefriends.org/)

2. **Copy Project Files**
   - Copy the `Attendance_mgmt` folder to your web server directory:
     - XAMPP: `C:\xampp\htdocs\`
     - WAMP: `C:\wamp\www\`

3. **Create Database**
   - Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Create a new database named `attendance_system`
   - Import the `database.sql` file or run the SQL queries from it

4. **Configure Database Connection**
   - Open `config.php`
   - Update database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'attendance_system');
     ```

5. **Create Uploads Directory**
   - Create a folder named `uploads` in the project root
   - Ensure it has write permissions

6. **Access the Application**
   - Open your browser and go to: [http://localhost/Attendance_mgmt/](http://localhost/Attendance_mgmt/)

## Default Login Credentials

### Admin
- **Email:** admin@company.com
- **Password:** admin123

### Employee Registration
- New employees can **self-register** using the "Employee Registration" link on the homepage
- After registration, employees can login with their chosen email and password
- Admin can view all registered employees and manage their accounts

### Sample Employees (if using default database)
- **Email:** john.doe@company.com
- **Password:** employee123

**Note:** After first login, employees should change their default password from their profile settings.

## File Structure

```
Attendance_mgmt/
├── assets/
│   └── css/
│       └── style.css
├── includes/
│   ├── admin_sidebar.php
│   ├── admin_topbar.php
│   ├── employee_sidebar.php
│   └── employee_topbar.php
├── uploads/
├── admin_dashboard.php
├── admin_leave_management.php
├── admin_login.php
├── admin_logout.php
├── admin_profile.php
├── attendance_reports.php
├── config.php
├── daily_attendance.php
├── database.sql
├── department_management.php
├── employee_attendance_history.php
├── employee_dashboard.php
├── employee_leave_management.php
├── employee_login.php
├── employee_logout.php
├── employee_management.php
├── employee_profile.php
├── index.html
├── mark_attendance.php
└── README.md
```

## Key Technologies

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL
- **Icons:** Font Awesome 6.4.0
- **Design:** Responsive, Mobile-friendly

## Features Highlights

### Attendance System
- Automatic late detection with grace period (15 minutes)
- Check-in and check-out tracking
- Working hours calculation
- Manual attendance correction by admin

### Leave Management
- Multiple leave types (sick, casual, annual, emergency, unpaid)
- Approval workflow
- Automatic attendance marking for approved leaves
- Email notifications (can be implemented)

### Reporting
- Date range reports
- Monthly department-wise reports
- Attendance percentage calculation
- Print-friendly report generation

### Security
- Password hashing (bcrypt)
- Session management
- SQL injection prevention
- XSS protection
- Activity logging

## Customization

### Changing Timezone
Edit `config.php`:
```php
date_default_timezone_set('Asia/Kolkata'); // Change to your timezone
```

### Shift Timing
Default shift: 9:00 AM - 6:00 PM with 15-minute grace period
Can be customized per employee in Employee Management

### Grace Period
Edit `config.php` in the `calculate_late_status()` function:
```php
$grace_period = 15 * 60; // 15 minutes in seconds
```

## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Opera

## License

This project is open-source and available for educational and commercial use.

## Support

For issues or questions, please check the code comments or modify as per your requirements.

## Future Enhancements

- Email notifications
- SMS alerts
- Biometric integration
- Mobile app
- Advanced analytics
- Payroll integration
- Multi-language support
- API for external integrations

---

**Developed with ❤️ for efficient attendance management**
