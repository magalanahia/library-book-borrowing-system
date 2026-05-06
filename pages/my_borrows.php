<?php
/*
 * Active borrows page.
 * Shows the logged-in user's active loans and lets them return their own books.
 */

require_once '../config/config.php';
require_once '../includes/User.php';
require_once '../includes/Borrowing.php';

// The page is protected because borrowing records belong to a user account.
if (!User::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$borrowing = new Borrowing();
$records = $borrowing->getUserBorrowingHistory($_SESSION['user_id']);

// Filter only active records
$active_records = array_filter($records, function($record) {
    return $record['status'] === 'active';
});

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_id'])) {
    // Pass the session user_id to prevent returning another user's record.
    $record_id = intval($_POST['record_id']);
    $result = $borrowing->returnBook($record_id, $_SESSION['user_id']);
    $message = $result;
    
    // Refresh the page after 2 seconds
    if ($result['success']) {
        header('Refresh: 2; url=my_borrows.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Active Borrows - Library System</title>
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
                    <li><a href="my_borrows.php" class="active">My Borrows</a></li>
                    <li><a href="borrowing_history.php">History</a></li>
                    <?php if (User::isAdmin()): ?>
                        <li><a href="../admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="container">
        <h2>My Active Borrows</h2>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $message['success'] ? 'alert-success' : 'alert-error'; ?>">
                <?php echo htmlspecialchars($message['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($active_records)): ?>
            <p class="text-center">You have no active borrows.</p>
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
                        <th>Days Left</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_records as $record): 
                        // Calculate due-date status for warning and overdue labels.
                        $days_left = ceil((strtotime($record['due_date']) - time()) / (60 * 60 * 24));
                        $status_class = $days_left <= 0 ? 'overdue' : ($days_left <= 3 ? 'warning' : 'normal');
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['title']); ?></td>
                            <td><?php echo htmlspecialchars($record['author']); ?></td>
                            <td><?php echo htmlspecialchars($record['isbn']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($record['borrow_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($record['due_date'])); ?></td>
                            <td class="<?php echo $status_class; ?>">
                                <?php 
                                if ($days_left <= 0) {
                                    echo 'OVERDUE (' . abs($days_left) . ' days)';
                                } else {
                                    echo $days_left . ' days';
                                }
                                ?>
                            </td>
                            <td>
                                <span class="badge badge-active">Active</span>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="record_id" value="<?php echo $record['record_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Return</button>
                                </form>
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
