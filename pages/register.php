<?php
require_once '../config/config.php';
require_once '../includes/User.php';

if (User::isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $enrollment_id = trim($_POST['enrollment_id']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($enrollment_id)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $user = new User();
        // User::register handles password_hash before the user is saved.
        $result = $user->register($username, $email, $password, $full_name, $enrollment_id, $phone, $address);
        
        if ($result['success']) {
            header('Location: login.php?success=registered');
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
    <title>Register - Library System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h1 class="logo">Library System</h1>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="form-container">
            <h2>Student Registration</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="enrollment_id">Enrollment ID *</label>
                    <input type="text" id="enrollment_id" name="enrollment_id" required value="<?php echo htmlspecialchars($_POST['enrollment_id'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-primary">Register</button>
            </form>

            <p class="text-center" style="margin-top: 20px;">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Library System. All rights reserved.</p>
    </footer>
</body>
</html>
