<?php
/**
 * Gender Desk App System - Report Incident Form
 * FR-02: Secure Incident Submission Form
 * Implements file upload, validation, and CSRF protection
 */

require_once 'config.php';

$confirmation_message = '';
$error_message = '';
$tracking_id = '';

// Ensure uploads directory exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = 'Invalid security token. Please try again.';
    } else {
        try {
            $incident_type = isset($_POST['incident_type']) ? trim($_POST['incident_type']) : '';
            $incident_date = isset($_POST['incident_date']) ? $_POST['incident_date'] : '';
            $incident_location = isset($_POST['incident_location']) ? trim($_POST['incident_location']) : '';
            $narrative = isset($_POST['narrative']) ? trim($_POST['narrative']) : '';
            $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;

            if (empty($incident_type) || empty($incident_date) || empty($incident_location) || empty($narrative)) {
                $error_message = 'All fields are required.';
            } else {
                $incident_type = htmlspecialchars($incident_type, ENT_QUOTES, 'UTF-8');
                $incident_location = htmlspecialchars($incident_location, ENT_QUOTES, 'UTF-8');
                $narrative = htmlspecialchars($narrative, ENT_QUOTES, 'UTF-8');

                $tracking_id = strtoupper(bin2hex(random_bytes(8)));

                $stmt = $pdo->prepare('INSERT INTO incidents (tracking_id, incident_type, incident_date, incident_location, narrative_description, is_anonymous, status) VALUES (?, ?, ?, ?, ?, ?, "Pending")');
                $stmt->execute([$tracking_id, $incident_type, $incident_date, $incident_location, $narrative, $is_anonymous]);
                $incident_id = $pdo->lastInsertId();

                if (isset($_FILES['evidence']) && is_array($_FILES['evidence']['name'])) {
                    $file_count = count($_FILES['evidence']['name']);
                    for ($i = 0; $i < $file_count; $i++) {
                        if ($_FILES['evidence']['error'][$i] === UPLOAD_ERR_OK) {
                            $tmp_name = $_FILES['evidence']['tmp_name'][$i];
                            $original_name = basename($_FILES['evidence']['name'][$i]);
                            $mime_type = mime_content_type($tmp_name);
                            $file_size = $_FILES['evidence']['size'][$i];

                            if (!in_array($mime_type, ALLOWED_MIME_TYPES)) {
                                continue;
                            }

                            if ($file_size > MAX_FILE_SIZE) {
                                continue;
                            }

                            $ext = pathinfo($original_name, PATHINFO_EXTENSION);
                            $stored_filename = bin2hex(random_bytes(16)) . '.' . $ext;
                            $file_path = UPLOAD_DIR . $stored_filename;

                            if (move_uploaded_file($tmp_name, $file_path)) {
                                $stmt = $pdo->prepare('INSERT INTO files (incident_id, original_filename, stored_filename, file_path, mime_type, file_size) VALUES (?, ?, ?, ?, ?, ?)');
                                $stmt->execute([$incident_id, $original_name, $stored_filename, $file_path, $mime_type, $file_size]);
                            }
                        }
                    }
                }

                $stmt = $pdo->prepare('INSERT INTO case_management (incident_id, current_status) VALUES (?, "Pending")');
                $stmt->execute([$incident_id]);

                $confirmation_message = 'Incident submitted successfully! Your tracking ID is: ' . $tracking_id;
            }
        } catch (PDOException $e) {
            $error_message = 'Error submitting report: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Report Incident - Gender Desk App System</title>
    <link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body>
    <div class="container">
        <header>
            <h1>Gender Desk App System</h1>
            <p class="subtitle">Report a GBV Incident</p>
        </header>

        <nav class="navbar">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="report.php">Report Incident</a></li>
                <li><a href="track.php">Track Case</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>

        <main class="form-container">
            <?php if (!empty($confirmation_message)): ?>
                <div class="alert alert-success">
                    <h3>Success!</h3>
                    <p><?php echo htmlspecialchars($confirmation_message, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Please save this ID for future reference.</strong></p>
                    <p><a href="track.php">Track Your Case</a></p>
                </div>
            <?php else: ?>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="report.php" enctype="multipart/form-data" class="incident-form">
                    <h2>Submit Incident Report</h2>

                    <div class="form-group">
                        <label for="incident_type">Incident Type:</label>
                        <select id="incident_type" name="incident_type" required="required">
                            <option value="">-- Select Type --</option>
                            <option value="Physical Assault">Physical Assault</option>
                            <option value="Sexual Harassment">Sexual Harassment</option>
                            <option value="Sexual Assault">Sexual Assault</option>
                            <option value="Domestic Violence">Domestic Violence</option>
                            <option value="Psychological Abuse">Psychological Abuse</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="incident_date">Date and Time of Incident:</label>
                        <input type="datetime-local" id="incident_date" name="incident_date" required="required" />
                    </div>

                    <div class="form-group">
                        <label for="incident_location">Location of Incident:</label>
                        <input type="text" id="incident_location" name="incident_location" placeholder="Where did this occur?" required="required" />
                    </div>

                    <div class="form-group">
                        <label for="narrative">Incident Description:</label>
                        <textarea id="narrative" name="narrative" rows="8" placeholder="Please describe what happened in detail..." required="required"></textarea>
                    </div>

                    <div class="form-group checkbox">
                        <input type="checkbox" id="is_anonymous" name="is_anonymous" checked="checked" />
                        <label for="is_anonymous">Report Anonymously (Recommended for your safety)</label>
                    </div>

                    <div class="form-group">
                        <label for="evidence">Upload Evidence (Images/Audio - Optional):</label>
                        <input type="file" id="evidence" name="evidence[]" multiple="multiple" accept="image/jpeg,image/png,audio/mpeg,audio/wav" />
                        <small>Maximum file size: 10MB. Supported formats: JPG, PNG, MP3, WAV</small>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>" />

                    <button type="submit" class="btn btn-primary">Submit Report</button>
                    <p><a href="index.php">Cancel</a></p>
                </form>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; 2026 Gender Desk App System. All rights reserved. Confidentiality assured.</p>
        </footer>
    </div>

    <script type="text/javascript" src="js/form-validation.js"></script>
</body>
</html>
