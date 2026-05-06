<?php
/*
 * Borrow action page.
 * Receives a book_id from the catalogue and attempts to create a borrowing record.
 */

require_once '../config/config.php';
require_once '../includes/User.php';
require_once '../includes/Book.php';
require_once '../includes/Borrowing.php';

// Only logged-in users can borrow books.
if (!User::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Borrowing must come from a POST request with a valid book_id.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['book_id'])) {
    header('Location: catalogue.php');
    exit;
}

$book_id = intval($_POST['book_id']);
$user_id = $_SESSION['user_id'];

// The Borrowing class handles availability, limits, and database updates.
$borrowing = new Borrowing();
$result = $borrowing->borrowBook($user_id, $book_id);

// Get book details for display
$book = new Book();
$book_info = $book->getBookById($book_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Book - Library System</title>
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
                    <li><a href="borrowing_history.php">History</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="container">
        <h2>Borrow Book</h2>

        <?php if ($result['success']): ?>
            <div class="alert alert-success">
                <h3>Book Borrowed Successfully!</h3>
                <p><strong>Book:</strong> <?php echo htmlspecialchars($book_info['title']); ?></p>
                <p><strong>Due Date:</strong> <?php echo date('F d, Y', strtotime($result['due_date'])); ?></p>
                <p>You must return this book on or before the due date to avoid fines.</p>
            </div>
        <?php else: ?>
            <div class="alert alert-error">
                <h3>Error</h3>
                <p><?php echo htmlspecialchars($result['message']); ?></p>
            </div>
        <?php endif; ?>

        <p>
            <a href="catalogue.php" class="btn btn-primary">Back to Catalogue</a>
            <a href="my_borrows.php" class="btn btn-secondary">View My Borrows</a>
        </p>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Library System. All rights reserved.</p>
    </footer>
</body>
</html>
