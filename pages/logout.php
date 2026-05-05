<?php
require_once '../config/config.php';
require_once '../includes/User.php';

if (!User::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$result = User::logout();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Library System</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        setTimeout(function() {
            window.location.href = '../index.php';
        }, 2000);
    </script>
</head>
<body>
    <div class="container">
        <div class="alert alert-success" style="margin-top: 50px;">
            <h2>You have been logged out successfully!</h2>
            <p>Redirecting to home page...</p>
        </div>
    </div>
</body>
</html>
