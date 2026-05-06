<?php
/*
 * Logout page.
 * Ends the current session and shows a short confirmation before redirecting home.
 */

require_once '../config/config.php';
require_once '../includes/User.php';

// Only logged-in users need to run the logout process.
if (!User::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$result = User::logout();
// Destroy the session fully so protected pages are no longer accessible.
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
        // Send the user back to the home page after the confirmation message.
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
