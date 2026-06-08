# Gender Desk App System - Test Data & Credentials

## Default Test Accounts

### System Administrator
- **Username:** `admin`
- **Password:** `Admin@123`
- **Role:** System Administrator
- **Access:** admin/dashboard.php
- **⚠️ IMPORTANT:** Change this password immediately after first login!

### Sample Gender Desk Officer (Create via Admin Panel)
- **Username:** `officer1`
- **Password:** (Set during creation)
- **Role:** Gender Desk Officer
- **Access:** officer/dashboard.php

---

## How to Create Test Accounts

1. Login as Admin at `/login.php` with credentials above
2. Navigate to `/admin/users.php`
3. Fill out the user creation form:
   - Username: `officer1`
   - Email: `officer@genderdesk.local`
   - Password: `Officer@123`
   - Role: Gender Desk Officer
4. Click "Create User"

---

## Test Scenarios

### Test 1: Anonymous Report Submission
**Steps:**
1. Go to http://localhost/sex_system/report.php
2. Fill out form:
   - Incident Type: Sexual Harassment
   - Date/Time: (Any past date)
   - Location: Campus Library
   - Description: Detailed incident description
   - Check "Report Anonymously"
3. Click Submit
4. Note the Tracking ID displayed

**Expected Result:**
- ✅ Tracking ID generated
- ✅ Case created with status "Pending"
- ✅ Entry in audit_logs

---

### Test 2: Track Case Anonymously
**Steps:**
1. Go to http://localhost/sex_system/track.php
2. Enter the Tracking ID from Test 1
3. Click Search

**Expected Result:**
- ✅ Case details displayed (type, date, location)
- ✅ Current status shown
- ✅ Narrative NOT visible (privacy protection)

---

### Test 3: Officer Case Management
**Steps:**
1. Login as officer at http://localhost/sex_system/login.php
2. View dashboard at officer/dashboard.php
3. Click on a case row
4. Change status to "Under Investigation"
5. Add remarks
6. Click "Update Case"

**Expected Result:**
- ✅ Case updated without page reload
- ✅ Status changed in database
- ✅ Action logged in audit_logs
- ✅ Officer assigned to case

---

### Test 4: Admin Audit Review
**Steps:**
1. Login as admin at http://localhost/sex_system/login.php
2. Go to admin/dashboard.php
3. Review Recent Audit Activity
4. Click on "Audit Logs" in navbar
5. Browse all transactions

**Expected Result:**
- ✅ All actions visible with timestamps
- ✅ User actions properly logged
- ✅ IP addresses recorded
- ✅ Narrative descriptions NOT visible in logs (privacy)

---

## File Upload Testing

### Supported Formats
- **Images:** JPG, PNG
- **Audio:** MP3, WAV
- **Max Size:** 10MB per file

### Test Upload
1. Go to /report.php
2. Fill incident details
3. Click "Choose Files" for evidence
4. Select a JPG or PNG image
5. Submit form

**Expected Result:**
- ✅ File cryptographically renamed
- ✅ Stored outside web root
- ✅ Metadata saved to `files` table
- ✅ Original filename preserved

---

## Security Testing

### Test: SQL Injection Prevention
**Attempt:**
1. Go to /track.php
2. In Tracking ID field, enter: `' OR '1'='1`

**Expected Result:**
- ✅ No cases returned (injection blocked)
- ✅ Error message displayed

### Test: XSS Prevention
**Attempt:**
1. Create a report with narrative: `<script>alert('XSS')</script>`
2. Track the case
3. View in officer dashboard

**Expected Result:**
- ✅ No alert displayed
- ✅ Script tags visible as text (encoded)

### Test: CSRF Protection
**Attempt:**
1. Login as officer
2. Open browser console (F12)
3. Try to submit form without CSRF token

**Expected Result:**
- ✅ Form rejection (invalid token)
- ✅ No database update

---

## Database Testing

### View All Tables
```sql
USE gender_desk_db;

-- View all incidents
SELECT * FROM incidents;

-- View all cases
SELECT * FROM case_management;

-- View audit logs
SELECT * FROM audit_logs;

-- View users
SELECT username, role_id, is_active FROM users;

-- View files
SELECT * FROM files;
```

### Check Data Integrity
```sql
-- Count incidents
SELECT COUNT(*) FROM incidents;

-- Count audit entries
SELECT COUNT(*) FROM audit_logs;

-- View recent incidents
SELECT tracking_id, status, created_at FROM incidents ORDER BY created_at DESC LIMIT 10;
```

---

## Performance Testing

### Load Test Guidelines
- 100+ concurrent reports
- 1000+ audit log entries
- Database response time < 500ms

### Check Database Size
```sql
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) as 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'gender_desk_db'
ORDER BY (data_length + index_length) DESC;
```

---

## Cleanup & Reset

### Reset Database
```bash
# Drop and recreate database
mysql -u root -p < database_schema.sql
```

### Clear Audit Logs
```sql
DELETE FROM audit_logs;
```

### Delete All Cases
```sql
DELETE FROM incidents;
-- Cascading delete removes related files and cases automatically
```

---

## Troubleshooting

### Issue: Cannot login
- **Check:** Username and password are correct
- **Check:** User account is active (is_active = 1)
- **Check:** Role is assigned (role_id between 1-3)

### Issue: Upload fails
- **Check:** uploads/ directory exists and is writable
- **Check:** File size < 10MB
- **Check:** File format is JPG, PNG, MP3, or WAV

### Issue: Tracking not working
- **Check:** Tracking ID exactly matches format (16 alphanumeric)
- **Check:** Case exists in database
- **Check:** Incident is public (visible to tracking)

### Issue: AJAX updates fail
- **Check:** Browser console for JavaScript errors
- **Check:** Network tab shows POST request
- **Check:** Server returns valid JSON response

---

## Last Updated
2026-06-08

## Version
1.0.0