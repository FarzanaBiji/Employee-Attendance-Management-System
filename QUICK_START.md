# Quick Start Guide - Employee Attendance Management System

## 🚀 Getting Started in 3 Steps

### Step 1: Start XAMPP
```
1. Open XAMPP Control Panel
2. Click "Start" for Apache
3. Click "Start" for MySQL
4. Wait for green indicators
```

### Step 2: Setup Database (First Time Only)
```
1. Open browser: http://localhost/phpmyadmin
2. Click "New" to create database
3. Name it: attendance_system
4. Click "Import" tab
5. Choose file: database.sql (from project folder)
6. Click "Go"
7. Done! ✓
```

### Step 3: Access the System
```
Open browser and go to:
http://localhost/Attendance_mgmt/
```

---

## 👤 For Employees

### First Time? Register Now!
```
1. Click "Employee Portal" or "Register Your Account"
2. Fill in your details:
   ✓ Full Name
   ✓ Email (use your real email)
   ✓ Phone Number
   ✓ Choose Department
   ✓ Your Designation
   ✓ Joining Date
   ✓ Create Password (min 6 characters)
3. Click "Register"
4. Done! You can now login
```

### Already Registered? Login Here
```
URL: http://localhost/Attendance_mgmt/employee_login.php

Enter:
- Your Email
- Your Password

Click "Login"
```

### What Can You Do?
- ✓ Mark attendance (Check-in/Check-out)
- ✓ View your attendance history
- ✓ Apply for leaves
- ✓ Check leave status
- ✓ Update your profile
- ✓ Change password

---

## 👨‍💼 For Admin

### Admin Login
```
URL: http://localhost/Attendance_mgmt/admin_login.php

Credentials:
Email: admin@company.com
Password: admin123
```

### What Can You Do?
- ✓ View all employees
- ✓ Manage employee accounts
- ✓ View daily attendance
- ✓ Generate reports
- ✓ Approve/reject leave requests
- ✓ Manage departments
- ✓ Reset employee passwords

### Quick Actions
```
Employee Management:
- Add new employee manually
- Edit employee details
- Reset password (sets to "employee123")
- Activate/Deactivate accounts
- Delete employees

Attendance Management:
- View daily attendance
- Mark attendance manually
- Edit attendance records
- Generate reports

Leave Management:
- View pending requests
- Approve/Reject leaves
- Add remarks
- View leave history
```

---

## 🔑 Important URLs

| Page | URL |
|------|-----|
| Homepage | http://localhost/Attendance_mgmt/ |
| Employee Registration | http://localhost/Attendance_mgmt/employee_register.php |
| Employee Login | http://localhost/Attendance_mgmt/employee_login.php |
| Admin Login | http://localhost/Attendance_mgmt/admin_login.php |
| phpMyAdmin | http://localhost/phpmyadmin |

---

## ❓ Common Questions

### Q: I forgot my password. What do I do?
**Employee:** Contact admin to reset your password to "employee123", then change it after login.
**Admin:** Check config.php for database credentials, or reinstall the system.

### Q: Can I change my password?
**Yes!** After login, go to Profile Settings → Change Password

### Q: How do I mark attendance?
1. Login to employee dashboard
2. Click "Mark Attendance"
3. Click "Check In" when you arrive
4. Click "Check Out" when you leave

### Q: What if I'm late?
The system automatically marks you as "Late" if you check in after 9:15 AM (shift start + 15 min grace period)

### Q: How do I apply for leave?
1. Login to employee dashboard
2. Click "Leave Management"
3. Click "Apply Leave"
4. Fill form and submit
5. Wait for admin approval

### Q: Can admin see all my details?
**Yes.** Admin has full access to view all employee information, attendance records, and leave history.

---

## 🛠️ Troubleshooting

### Issue: "Cannot connect to database"
```
Solution:
1. Check if MySQL is running in XAMPP
2. Verify database name is "attendance_system"
3. Check config.php for correct credentials
```

### Issue: "Email already registered"
```
Solution:
- Email is already in use
- Try a different email
- Or login with existing account
```

### Issue: "Invalid email or password"
```
Solution:
1. Check if email is correct
2. Check if password is correct (case-sensitive)
3. For old accounts, ask admin to reset password
```

### Issue: Page not loading
```
Solution:
1. Check if Apache is running
2. Check URL spelling
3. Clear browser cache
4. Try different browser
```

### Issue: Can't upload profile picture
```
Solution:
1. Check if 'uploads' folder exists
2. Check folder permissions (should be writable)
3. File size should be under 2MB
4. Use JPG, PNG, or GIF format
```

---

## 📱 Mobile Access

The system is mobile-friendly! Access from your phone:
```
1. Connect phone to same WiFi as computer
2. Find computer's IP address
3. Use: http://[YOUR-IP]/Attendance_mgmt/

Example: http://192.168.1.100/Attendance_mgmt/
```

---

## 🎯 Best Practices

### For Employees:
- ✓ Register with your official email
- ✓ Use a strong password
- ✓ Mark attendance daily
- ✓ Apply for leaves in advance
- ✓ Update profile information
- ✓ Check attendance history regularly

### For Admin:
- ✓ Review registrations regularly
- ✓ Process leave requests promptly
- ✓ Generate monthly reports
- ✓ Backup database regularly
- ✓ Monitor attendance patterns
- ✓ Keep department list updated

---

## 📞 Need Help?

1. Check `REGISTRATION_GUIDE.md` for detailed instructions
2. Check `README.md` for full documentation
3. Check `UPDATE_SUMMARY.md` for recent changes
4. Check PHP error logs for technical issues

---

## ✅ System Requirements

- Apache 2.4+
- PHP 7.4+
- MySQL 5.7+
- Modern web browser (Chrome, Firefox, Edge, Safari)
- Minimum 2GB RAM
- Active internet (for Font Awesome icons)

---

**Happy Attendance Tracking! 🎉**

---

*Last Updated: November 5, 2025*
