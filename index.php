<?php
/**
 * Gender Desk App System - Home Page
 * IEEE 830-1998 Compliant
 * Entry point for the web application
 */

require_once 'config.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gender Desk App System - Report GBV Incidents</title>
    <link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body>
    <div class="container">
        <header>
            <h1>Gender Desk App System</h1>
            <p class="subtitle">A Secure Platform for Reporting Gender-Based Violence</p>
        </header>

        <nav class="navbar">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="report.php">Report Incident</a></li>
                <li><a href="track.php">Track Case</a></li>
                <?php if ($is_logged_in): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <main>
            <section class="welcome">
                <h2>Welcome</h2>
                <p>
                    The Gender Desk App System provides a secure and confidential platform for reporting 
                    incidents of Gender-Based Violence within the university community. Your safety and privacy 
                    are our highest priorities.
                </p>
            </section>

            <section class="features">
                <h2>Our Services</h2>
                <div class="feature-box">
                    <h3>Anonymous Reporting</h3>
                    <p>Report incidents without revealing your identity. Receive a confidential tracking ID to monitor case progress.</p>
                </div>
                <div class="feature-box">
                    <h3>Secure Case Tracking</h3>
                    <p>Check the status of your report at any time using your tracking ID.</p>
                </div>
                <div class="feature-box">
                    <h3>Professional Case Management</h3>
                    <p>Our trained Gender Desk officers handle every case with care and confidentiality.</p>
                </div>
            </section>

            <section class="cta">
                <h2>Get Started</h2>
                <p>Ready to report an incident? Choose one of the options below:</p>
                <ul>
                    <li><a href="report.php" class="btn btn-primary">Submit New Report</a></li>
                    <li><a href="track.php" class="btn btn-secondary">Track Existing Case</a></li>
                </ul>
            </section>
        </main>

        <footer>
            <p>&copy; 2026 Gender Desk App System. All rights reserved. Confidentiality assured.</p>
        </footer>
    </div>
</body>
</html>