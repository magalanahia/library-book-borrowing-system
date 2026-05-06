<?php
/*
 * Login page for both student and admin users.
 * The User class verifies credentials and stores session data after a successful login.
 */

require_once '../config/config.php';
require_once '../includes/User.php';

// If a user is already logged in, send them back to the home page.
if (User::isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = isset($_GET['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read submitted credentials from the login form.
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Basic validation before checking the database.
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $user = new User();
        $result = $user->login($username, $password);
        
        if ($result['success']) {
            header('Location: ../index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h1 class="logo">Library System</h1>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="form-container">
            <h2>Account Login</h2>
            
            <?php if ($success): ?>
                <!-- Displayed after a new student successfully registers. -->
                <div class="alert alert-success">Registration successful! Please log in with your credentials.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <p class="text-center" style="margin-top: 20px;">
                Don't have an account? <a href="register.php">Register here</a>
            </p>

            <div style="margin-top: 30px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
                <!-- Demo accounts make presentation and marking easier. -->
                <p><strong>Demo Credentials:</strong></p>
                <p>Admin - Username: <code>admin</code>, Password: <code>admin123</code></p>
                <p>Student - Username: <code>student</code>, Password: <code>student123</code></p>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Library System. All rights reserved.</p>
    </footer>
</body>
</html>
