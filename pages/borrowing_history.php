<?php
require_once '../config/config.php';
require_once '../includes/User.php';
require_once '../includes/Borrowing.php';

if (!User::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$borrowing = new Borrowing();
$records = $borrowing->getUserBorrowingHistory($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowing History - Library System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h1 class="logo">Library System</h1>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="catalogue.php">Catalogue</a></li>
                    <li><a href="my_borrows.php">My Borrows</a></li>
                    <li><a href="borrowing_history.php" class="active">History</a></li>
                    <?php if (User::isAdmin()): ?>
                        <li><a href="../admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="container">
        <h2>My Borrowing History</h2>

        <?php if (empty($records)): ?>
            <p class="text-center">You have no borrowing history yet.</p>
            <p class="text-center">
                <a href="catalogue.php" class="btn btn-primary">Browse Catalogue</a>
            </p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Borrowed Date</th>
                        <th>Due Date</th>
                        <th>Returned Date</th>
                        <th>Status</th>
                        <th>Fine</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['title']); ?></td>
                            <td><?php echo htmlspecialchars($record['author']); ?></td>
                            <td><?php echo htmlspecialchars($record['isbn']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($record['borrow_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($record['due_date'])); ?></td>
                            <td><?php echo $record['return_date'] ? date('M d, Y', strtotime($record['return_date'])) : '-'; ?></td>
                            <td>
                                <span class="badge <?php echo 'badge-' . $record['status']; ?>">
                                    <?php echo ucfirst($record['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $record['fine_amount'] > 0 ? '$' . number_format($record['fine_amount'], 2) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Library System. All rights reserved.</p>
    </footer>
</body>
</html>
