# Employee Self-Registration System - Implementation Guide

## Overview
The system has been updated to allow employees to register themselves instead of using default login credentials. This makes the system more user-friendly and realistic.

## What's New

### 1. Employee Registration Page (`employee_register.php`)
- **Location:** `http://localhost/Attendance_mgmt/employee_register.php`
- **Features:**
  - Self-service registration form
  - Required fields: Name, Email, Password, Department, Designation, Joining Date
  - Optional fields: Phone Number
  - Password validation (minimum 6 characters)
  - Password confirmation
  - Email uniqueness check
  - Automatic account activation
  - Secure password hashing

### 2. Updated Homepage (`index.html`)
- Added "Register Your Account" link
- Updated credentials section to promote self-registration
- Modern blue gradient design with professional look
- Mobile-responsive layout

### 3. Updated Employee Login (`employee_login.php`)
- Added "Register here" link for new users
- Removed default credential display
- Clean, professional interface

## How It Works

### For Employees:
1. **Registration:**
   - Visit the homepage
   - Click "Employee Portal" or "Register Your Account"
   - Fill in the registration form with personal details
   - Choose department and designation
   - Set a secure password
   - Submit the form
   - Get confirmation message
   - Redirected to login page

2. **Login:**
   - Use registered email and password
   - Access personal dashboard
   - Mark attendance, view history, apply for leaves

### For Admin:
1. **View All Employees:**
   - Login to admin panel
   - Navigate to "Employee Management"
   - View all registered employees
   - Search and filter by department or name

2. **Manage Employees:**
   - Edit employee details
   - Reset passwords (to default: employee123)
   - Activate/Deactivate accounts
   - Delete employees if needed
   - Add employees manually (admin can still do this)

## Database Changes
No database schema changes required! The existing `employees` table already supports all required fields.

## Files Modified

### New Files:
- `employee_register.php` - Employee self-registration page

### Modified Files:
- `index.html` - Added registration link and updated design
- `employee_login.php` - Added registration link
- `admin_login.php` - Fixed status column issue
- `README.md` - Updated documentation

### Removed Files:
- `fix_passwords.php` - No longer needed (was temporary)

## Security Features

1. **Password Security:**
   - All passwords are hashed using PHP's `password_hash()` function
   - Uses bcrypt algorithm (PASSWORD_DEFAULT)
   - Passwords are never stored in plain text

2. **Input Validation:**
   - Email format validation
   - Password strength requirements (minimum 6 characters)
   - SQL injection prevention using sanitization
   - XSS protection

3. **Email Uniqueness:**
   - System checks if email already exists
   - Prevents duplicate registrations

4. **Activity Logging:**
   - All registrations are logged in system_logs table
   - Admin can track when employees register

## Testing the System

### Test Employee Registration:
1. Go to: `http://localhost/Attendance_mgmt/`
2. Click "Employee Portal" or "Register Your Account"
3. Fill in the form:
   - Name: Test Employee
   - Email: test@company.com
   - Phone: 1234567890
   - Department: Select any
   - Designation: Test Position
   - Joining Date: Today's date
   - Password: test123
   - Confirm Password: test123
4. Click "Register"
5. You should see success message
6. Try logging in with test@company.com / test123

### Test Admin Access:
1. Login as admin: admin@company.com / admin123
2. Go to "Employee Management"
3. You should see the newly registered employee
4. Try editing, resetting password, or deleting

## Troubleshooting

### Issue: "Email already registered"
- **Solution:** The email is already in the database. Use a different email or login with existing credentials.

### Issue: "Passwords do not match"
- **Solution:** Make sure password and confirm password fields have the same value.

### Issue: "Registration failed"
- **Solution:** Check if all required fields are filled. Check database connection in config.php.

### Issue: Can't login after registration
- **Solution:** 
  1. Make sure you're using the correct email and password
  2. Check if the account is active (status = 'active')
  3. Run `update_passwords.sql` if you have old accounts with incorrect password hashes

## Admin Controls

Admin has full control over employee accounts:

1. **Manual Registration:**
   - Admin can still add employees manually
   - Default password will be: employee123
   - Employee can change it later

2. **Password Reset:**
   - Admin can reset any employee password to "employee123"
   - Useful when employee forgets password

3. **Account Management:**
   - Activate/Deactivate accounts
   - Edit all employee details
   - Delete accounts if needed

4. **View Registration History:**
   - Check system_logs table for registration activities
   - Track when employees joined

## Benefits of Self-Registration

1. **User-Friendly:** Employees can register themselves without admin intervention
2. **Secure:** Each employee sets their own password
3. **Scalable:** No need to create default accounts for testing
4. **Realistic:** Mimics real-world employee onboarding
5. **Flexible:** Admin can still manage all accounts

## Next Steps

### Optional Enhancements:
1. **Email Verification:**
   - Send verification email with link
   - Activate account only after email confirmation

2. **Admin Approval:**
   - Set new registrations as "pending"
   - Admin approves before activation

3. **Profile Pictures:**
   - Allow employees to upload photos during registration

4. **Department Restrictions:**
   - Limit which departments employees can choose

5. **Employee ID Generation:**
   - Auto-generate unique employee IDs

## Conclusion

The system is now fully functional with employee self-registration. Employees can register themselves, and admin maintains full control over all accounts. The system is production-ready and secure.

---

**Last Updated:** November 5, 2025
**Version:** 2.0 with Self-Registration
