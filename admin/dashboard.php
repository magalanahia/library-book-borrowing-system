<?php
require_once '../config/config.php';
require_once '../includes/User.php';
require_once '../includes/Book.php';
require_once '../includes/Borrowing.php';

if (!User::isLoggedIn() || !User::isAdmin()) {
    header('Location: ../pages/login.php');
    exit;
}

$book = new Book();
$borrowing = new Borrowing();

$total_books = count($book->getAllBooks());
$active_borrows = count($borrowing->getActiveBorrows());
$overdue_books = count($borrowing->getOverdueBooks());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h1 class="logo">Library System - Admin Panel</h1>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="books.php">Manage Books</a></li>
                    <li><a href="borrowing.php">Borrowing Activity</a></li>
                    <li><a href="../pages/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="container">
        <h2>Admin Dashboard</h2>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Total Books</h3>
                <p class="big-number"><?php echo $total_books; ?></p>
            </div>

            <div class="dashboard-card">
                <h3>Active Borrows</h3>
                <p class="big-number"><?php echo $active_borrows; ?></p>
                <a href="borrowing.php">View Details</a>
            </div>

            <div class="dashboard-card">
                <h3>Overdue Books</h3>
                <p class="big-number overdue"><?php echo $overdue_books; ?></p>
                <a href="borrowing.php">View Details</a>
            </div>
        </div>

        <div style="margin-top: 30px;">
            <h3>Quick Actions</h3>
            <ul>
                <li><a href="books.php">Manage Books</a></li>
                <li><a href="books.php?action=add">Add New Book</a></li>
                <li><a href="borrowing.php">View All Borrowing Activity</a></li>
            </ul>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Library System. All rights reserved.</p>
    </footer>
</body>
</html>
