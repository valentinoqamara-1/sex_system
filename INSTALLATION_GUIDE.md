# Gender Desk App System - Installation Guide

## Requirements

- **Web Server:** Apache 2.4.x (via XAMPP)
- **Database:** MySQL 8.x or MariaDB 10.x
- **Server-Side Language:** PHP 8.x
- **Client Browser:** Any modern browser supporting XHTML 1.0, CSS3, and ES6 JavaScript

## Installation Steps

### 1. Install XAMPP

Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)

### 2. Start Apache and MySQL

Open XAMPP Control Panel and start:
- Apache
- MySQL

### 3. Clone Repository

```bash
cd C:\xampp\htdocs  # Windows
# or
cd /Applications/XAMPP/htdocs  # macOS
# or
cd /opt/lampp/htdocs  # Linux

git clone https://github.com/valentinoqamara-1/sex_system.git
cd sex_system
```

### 4. Configure Database

1. Open phpMyAdmin: http://localhost/phpmyadmin/
2. Create a new database named `gender_desk_db`
3. Import the database schema:
   - Go to Import tab
   - Select `database_schema.sql` from the repository
   - Click Import

Or run via command line:
```bash
mysql -u root -p < database_schema.sql
```

### 5. Configure Application

1. Rename or copy the `config.example.php` file to `config.php`
2. Edit `config.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Add your MySQL password if set
   define('DB_NAME', 'gender_desk_db');
   ```

### 6. Create Uploads Directory

Create a directory outside the web root for security:

```bash
# Windows
mkdir C:\xampp\uploads\gender_desk

# macOS/Linux
mkdir -p /Applications/XAMPP/uploads/gender_desk
```

Update the `UPLOAD_DIR` path in `config.php`:
```php
define('UPLOAD_DIR', 'C:/xampp/uploads/gender_desk/');
```

### 7. Set File Permissions

```bash
# Linux/macOS
chmod 755 sex_system/
chmod 777 sex_system/uploads/  # If uploads is inside htdocs
```

### 8. Access the Application

Open your browser and navigate to:
```
http://localhost/sex_system/
```

### 9. Login Credentials

**Default Admin Account:**
- Username: `admin`
- Password: `Admin@123`

**⚠️ Important:** Change this password immediately after first login!

## Database Schema

The application creates the following tables:

- `users` - User accounts and authentication
- `roles` - Role definitions for RBAC
- `incidents` - GBV incident reports
- `files` - Uploaded evidence files
- `case_management` - Case tracking and officer notes
- `audit_logs` - System audit trail
- `csrf_tokens` - CSRF protection tokens

## Role-Based Access Control (RBAC)

### 1. System Administrator
- Username: `admin`
- Permissions: System maintenance, user management, audit logs
- Dashboard: `/admin/dashboard.php`

### 2. Gender Desk Officer
- Can view and manage incidents
- Can update case status and add notes
- Can download evidence files
- Dashboard: `/officer/dashboard.php`

### 3. End User (Reporter)
- Can submit anonymous incident reports
- Can track case status using tracking ID
- Cannot login (uses anonymous tracking)

## Security Features

✅ **SQL Injection Prevention:** PDO Prepared Statements
✅ **XSS Protection:** HTML entity encoding
✅ **CSRF Protection:** Anti-CSRF tokens
✅ **Password Security:** BCRYPT hashing (Cost 12)
✅ **Session Security:** Regenerated IDs, timeout protection
✅ **File Upload Security:** MIME type validation, cryptographic file names
✅ **Access Control:** Role-Based Access Control (RBAC)
✅ **Audit Logging:** Immutable transaction records

## First Run Checklist

- [ ] Apache and MySQL are running
- [ ] Database imported successfully
- [ ] `config.php` configured with correct credentials
- [ ] Uploads directory created and writable
- [ ] Can access http://localhost/sex_system/
- [ ] Can login as admin
- [ ] Default password changed

## Troubleshooting

### Cannot connect to database
- Check MySQL is running
- Verify credentials in `config.php`
- Check database `gender_desk_db` exists

### White screen error
- Check `config.php` exists and is configured
- Check error logs: `C:\xampp\apache\logs\error.log`
- Ensure PHP display_errors is enabled (for development only)

### File uploads not working
- Check uploads directory exists and is writable
- Verify `UPLOAD_DIR` path in `config.php`
- Check PHP max_upload_size in `php.ini`

### Session errors
- Check PHP session.save_path is writable
- Verify cookies are enabled in browser
- Check for session timeout configuration

## Next Steps

1. Read the README.md for project overview
2. Review the SRS in README.md for full specifications
3. Explore the officer dashboard to manage incidents
4. Review audit logs for system activity

## Support

For issues or questions, please contact the development team or refer to the project documentation.

---

**Last Updated:** 2026-06-08
**Version:** 1.0.0
