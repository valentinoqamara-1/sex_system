<?php
/**
 * Admin - User Management
 * System Administrator Role Only
 */

require_once '../config.php';

// Session validation - Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Fetch all users with roles
try {
    $stmt = $pdo->prepare('
        SELECT 
            u.user_id,
            u.username,
            u.email,
            u.is_active,
            u.created_at,
            r.role_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.role_id
        ORDER BY u.created_at DESC
    ');
    $stmt->execute();
    $users = $stmt->fetchAll();

    // Fetch roles for dropdown
    $stmt = $pdo->prepare('SELECT role_id, role_name FROM roles');
    $stmt->execute();
    $roles = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
    $roles = [];
}

$message = '';

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role_id = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 0;

    if (empty($username) || empty($email) || empty($password) || $role_id <= 0) {
        $message = 'All fields are required.';
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $email, $hashed_password, $role_id]);
            $message = 'User created successfully!';
            
            // Log audit
            $stmt = $pdo->prepare('
                INSERT INTO audit_logs (user_id, action, target_table, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $_SESSION['user_id'],
                'User Account Created',
                'users',
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            // Refresh users list
            header('Location: users.php');
            exit;
        } catch (PDOException $e) {
            $message = 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Management - Gender Desk App System</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css" />
</head>
<body>
    <div class="container">
        <header>
            <h1>Gender Desk App System</h1>
            <p class="subtitle">User Management</p>
        </header>

        <nav class="navbar">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="audit.php">Audit Logs</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>

        <main>
            <?php if (!empty($message)): ?>
                <div class="alert alert-info">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <h2>Manage Staff Users</h2>

            <form method="post" action="users.php" class="user-form">
                <h3>Create New User</h3>

                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required="required" />
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required="required" />
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required="required" />
                </div>

                <div class="form-group">
                    <label for="role_id">Role:</label>
                    <select id="role_id" name="role_id" required="required">
                        <option value="">-- Select Role --</option>
                        <?php foreach ($roles as $role): ?>
                            <?php if ($role['role_id'] !== 3): // Don't allow creating End User accounts ?>
                                <option value="<?php echo htmlspecialchars($role['role_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($role['role_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <input type="hidden" name="action" value="create" />
                <button type="submit" class="btn btn-primary">Create User</button>
            </form>

            <h3 style="margin-top: 40px;">Existing Users</h3>

            <?php if (empty($users)): ?>
                <p>No users found.</p>
            <?php else: ?>
                <table border="1" class="users-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($user['role_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></td>
                                <td><?php echo htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; 2026 Gender Desk App System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
