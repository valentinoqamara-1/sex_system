# Gender Desk App System - Project Documentation

## Project Overview

The Gender Desk App System is a secure, web-based platform designed to facilitate the anonymous or authenticated reporting, management, and tracking of Gender-Based Violence (GBV) incidents within the university community.

**Key Objective:** Provide survivors with a confidential, accessible channel to report incidents while enabling Gender Desk officers to efficiently manage cases and generate institutional insights.

---

## Core Features

### 1. Anonymous Incident Reporting (FR-02)
- Users can submit GBV incident reports without authentication
- Support for text descriptions and multimedia evidence (images, audio)
- Automatic generation of unique 16-character tracking IDs
- CSRF protection on all forms

### 2. Secure Case Tracking (FR-03)
- Users can check their case status without logging in
- Query using only the tracking ID
- Read-only access to non-sensitive case information
- View current status updates from Gender Desk officers

### 3. Role-Based Access Control (RBAC)
Three distinct user roles with granular permissions:

**System Administrator (IT Role)**
- User account creation and management
- Role assignment
- System health and maintenance
- View audit logs (CANNOT access case narratives)

**Gender Desk Officer (Case Management Role)**
- Full read/write access to incident database
- Case status updates
- Evidence file management
- Generation of campus reports
- Case note documentation

**End User (Reporter Role)**
- Submit anonymous or identified reports
- Track cases using tracking ID
- No login required for reporting

### 4. Asynchronous Case Management (FR-04)
- Officers can update case status without full page reload
- AJAX-based dashboard for efficient workflow
- Real-time status transitions
- Internal notes documentation
- Case assignment to specific officers

### 5. Immutable Audit Logging (FR-05)
- Complete transaction history for all sensitive operations
- Cryptographically irreversible audit trail
- Tracks user actions, timestamps, and IP addresses
- Supports legal compliance and forensic analysis
- Administrator-only access to logs

---

## Security Architecture

### Authentication & Sessions
- ✅ BCRYPT password hashing (Cost 12)
- ✅ Session regeneration after login (prevents session fixation)
- ✅ Session timeout protection
- ✅ Secure cookie handling

### Data Protection
- ✅ SQL Injection Prevention: PDO Prepared Statements
- ✅ XSS Mitigation: HTML entity encoding on all outputs
- ✅ CSRF Protection: Anti-CSRF tokens on all state-changing forms
- ✅ File Upload Security: MIME type validation, cryptographic filename generation
- ✅ Access Control: Request-level RBAC enforcement

### Database Security
- ✅ InnoDB engine with ACID compliance
- ✅ Third Normal Form (3NF) normalization
- ✅ Referential integrity via foreign keys
- ✅ Encrypted credential storage
- ✅ UTF-8MB4 character set support

---

## System Architecture

### Stack
- **Web Server:** Apache 2.4.x (XAMPP)
- **Database:** MySQL 8.x / MariaDB 10.x
- **Server-Side:** PHP 8.x with PDO
- **Client-Side:** Vanilla JavaScript (ES6), XHTML 1.0 Strict, CSS3
- **No External Dependencies:** All functionality built with native technologies

### Technology Rationale
- **PDO Database Layer:** Eliminates SQL injection vulnerabilities
- **Vanilla JavaScript:** No external framework dependencies; complete control over DOM
- **XHTML Strict:** Rigorous markup validation ensures cross-browser consistency
- **Local XAMPP:** Total operational isolation; functions entirely offline

---

## Database Schema

### Core Tables

**users**
- Stores user accounts with BCRYPT hashed passwords
- Links users to roles via role_id
- Tracks active/inactive status

**incidents**
- Primary table for GBV reports
- Includes tracking_id for anonymous tracking
- Stores narrative_description (victim's account)
- References uploaded evidence via files table

**case_management**
- One-to-one relationship with incidents
- Stores case status, assigned officer, investigation notes
- Tracks status transitions for audit purposes

**files**
- Stores metadata for uploaded evidence
- Contains only file paths (actual files outside web root)
- Prevents directory traversal attacks

**audit_logs**
- Immutable log of all sensitive transactions
- Includes user_id, action, timestamp, IP address
- Maintains old_value/new_value for change tracking
- Supports forensic analysis and legal discovery

---

## API Endpoints

### Public Routes (No Authentication Required)
- `GET /` - Homepage
- `GET /report.php` - Incident submission form
- `POST /report.php` - Submit incident (with CSRF token)
- `GET /track.php` - Case tracking form
- `POST /track.php` - Query case status (with CSRF token)
- `GET /login.php` - Staff login form
- `POST /login.php` - Authenticate user (with CSRF token)

### Protected Routes (Authentication Required)
- `GET /dashboard.php` - Role-based router
- `GET /officer/dashboard.php` - Officer case list
- `GET /officer/case.php?id=X` - Individual case details
- `POST /officer/case.php?id=X` - Update case status
- `GET /admin/dashboard.php` - System overview
- `GET /admin/audit.php` - Audit log viewer
- `GET /logout.php` - Destroy session

---

## Installation & Deployment

See `INSTALLATION_GUIDE.md` for step-by-step setup instructions.

**Quick Start:**
1. Clone repository to XAMPP htdocs
2. Import database schema
3. Configure `config.php` with DB credentials
4. Create uploads directory outside web root
5. Access http://localhost/sex_system/
6. Login with admin/Admin@123 (change password immediately)

---

## Compliance & Standards

- **IEEE 830-1998:** Software Requirements Specification standard
- **OWASP Top 10:** Web application security risk mitigation
- **W3C XHTML 1.0 Strict:** Valid markup validation
- **ACID Compliance:** Transactional database integrity

---

## File Structure

```
sex_system/
├── index.php                 # Homepage
├── login.php                 # Authentication
├── report.php                # GBV incident form (FR-02)
├── track.php                 # Case tracking (FR-03)
├── logout.php                # Session termination
├── dashboard.php             # RBAC router
├── config.php                # Database configuration
├── database_schema.sql       # MySQL schema
├── README.md                 # Project documentation
├── INSTALLATION_GUIDE.md     # Setup instructions
├── .gitignore                # Sensitive files exclusion
├── css/
│   └── style.css             # Responsive stylesheet
├── js/
│   ├── form-validation.js    # Client-side validation
│   └── officer-dashboard.js  # AJAX case updates
├── officer/
│   ├── dashboard.php         # Case management (FR-04)
│   └── case.php              # Individual case view
└── admin/
    ├── dashboard.php         # System overview (FR-05)
    ├── audit.php             # Audit log viewer
    └── users.php             # User management
```

---

## Testing Scenarios

### Scenario 1: Anonymous Report Submission
1. Navigate to http://localhost/sex_system/report.php
2. Fill out incident details
3. Upload evidence (optional)
4. Submit with "Report Anonymously" checked
5. Receive tracking ID
6. Verify database entry created

### Scenario 2: Case Tracking
1. Navigate to http://localhost/sex_system/track.php
2. Enter tracking ID from previous report
3. View case details (status, date, location only)
4. Verify narrative NOT visible in tracking view

### Scenario 3: Officer Case Management
1. Login as Gender Desk Officer
2. View case list in dashboard
3. Click on individual case
4. Update status to "Under Investigation"
5. Add internal notes
6. Submit and verify AJAX update
7. Check audit logs confirm action

### Scenario 4: Admin Audit Review
1. Login as System Administrator
2. Access admin/dashboard.php
3. View system statistics
4. Review recent audit activity
5. Verify case narratives NOT visible in admin interface

---

## Future Enhancements

- Multi-language support
- SMS notifications for case updates
- Integration with campus card system
- Advanced analytics dashboard
- Email notifications to officers
- Mobile-responsive web app
- Two-factor authentication

---

## Contact & Support

For technical issues or questions, contact the development team.

**Student:** Valentino Augustino Qamara  
**ID:** 14320048/T.24  
**Assignment:** ICTM - Gender Desk App System

**Last Updated:** 2026-06-08  
**Version:** 1.0.0  
**License:** University Proprietary
