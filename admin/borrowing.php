<?php
require_once '../config/config.php';
require_once '../includes/User.php';
require_once '../includes/Borrowing.php';

if (!User::isLoggedIn() || !User::isAdmin()) {
    header('Location: ../pages/login.php');
    exit;
}

$borrowing = new Borrowing();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'return' && isset($_POST['record_id'])) {
    $message = $borrowing->returnBook(intval($_POST['record_id']));
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$records = [];

if ($filter === 'active') {
    $records = $borrowing->getActiveBorrows();
} elseif ($filter === 'overdue') {
    $records = $borrowing->getOverdueBooks();
} else {
    $records = $borrowing->getAllBorrowingRecords();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowing Activity - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h1 class="logo">Library System - Admin Panel</h1>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="books.php">Manage Books</a></li>
                    <li><a href="borrowing.php" class="active">Borrowing Activity</a></li>
                    <li><a href="../pages/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="container">
        <h2>Borrowing Activity</h2>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $message['success'] ? 'alert-success' : 'alert-error'; ?>">
                <?php echo htmlspecialchars($message['message']); ?>
            </div>
        <?php endif; ?>

        <div style="margin-bottom: 20px;">
            <a href="borrowing.php?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
            <a href="borrowing.php?filter=active" class="btn <?php echo $filter === 'active' ? 'btn-primary' : 'btn-secondary'; ?>">Active Borrows</a>
            <a href="borrowing.php?filter=overdue" class="btn <?php echo $filter === 'overdue' ? 'btn-primary' : 'btn-secondary'; ?>">Overdue Books</a>
        </div>

        <?php if (empty($records)): ?>
            <p>No borrowing records found.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Fine</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['email']); ?></td>
                            <td><?php echo htmlspecialchars($record['title']); ?></td>
                            <td><?php echo htmlspecialchars($record['author']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($record['borrow_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($record['due_date'])); ?></td>
                            <td><?php echo $record['return_date'] ? date('M d, Y', strtotime($record['return_date'])) : '-'; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $record['status']; ?>">
                                    <?php echo ucfirst($record['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $record['fine_amount'] > 0 ? '$' . number_format($record['fine_amount'], 2) : '-'; ?></td>
                            <td>
                                <?php if ($record['status'] === 'active'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="return">
                                        <input type="hidden" name="record_id" value="<?php echo $record['record_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Mark Returned</button>
                                    </form>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
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
