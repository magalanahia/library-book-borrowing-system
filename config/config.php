<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Change if you have a password
define('DB_NAME', 'library_system');

// Application Settings
define('APP_NAME', 'Library Book Borrowing System');
define('APP_URL', 'http://localhost/library_system');
define('BORROW_LIMIT', 5); // Maximum books a student can borrow
define('BORROW_DAYS', 14); // Number of days for book loan

// Session Configuration
session_start();

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
