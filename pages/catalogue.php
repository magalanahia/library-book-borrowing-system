<?php
/*
 * Book catalogue page.
 * Displays all books or filtered search results and lets logged-in users borrow available copies.
 */

require_once '../config/config.php';
require_once '../includes/User.php';
require_once '../includes/Book.php';

// Only authenticated users can browse and borrow books.
if (!User::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$book = new Book();
$books = [];
$search_keyword = '';

// Search by title or author when the search form is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['search'])) {
    $search_keyword = trim($_POST['search']);
    $books = $book->searchBooks($search_keyword);
} else {
    $books = $book->getAllBooks();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Catalogue - Library System</title>
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
                    <?php if (User::isAdmin()): ?>
                        <li><a href="../admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="container">
        <h2>Book Catalogue</h2>

        <div class="search-container">
            <form method="POST">
                <input type="text" name="search" placeholder="Search by title or author..." value="<?php echo htmlspecialchars($search_keyword); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if (!empty($search_keyword)): ?>
                    <a href="catalogue.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($books)): ?>
            <p class="text-center">No books found.</p>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach ($books as $b): ?>
                    <!-- Each card is generated dynamically from the books table. -->
                    <div class="book-card">
                        <h3><?php echo htmlspecialchars($b['title']); ?></h3>
                        <p><strong>Author:</strong> <?php echo htmlspecialchars($b['author']); ?></p>
                        <p><strong>ISBN:</strong> <?php echo htmlspecialchars($b['isbn'] ?? 'N/A'); ?></p>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($b['category'] ?? 'N/A'); ?></p>
                        <p><strong>Available:</strong> <?php echo $b['available_copies']; ?> / <?php echo $b['total_copies']; ?></p>
                        <p class="description"><?php echo htmlspecialchars(substr($b['description'], 0, 100)); ?>...</p>
                        
                        <?php if ($b['available_copies'] > 0): ?>
                            <!-- Borrow action posts the selected book_id to borrow.php. -->
                            <form method="POST" action="borrow.php" style="display: inline;">
                                <input type="hidden" name="book_id" value="<?php echo $b['book_id']; ?>">
                                <button type="submit" class="btn btn-success">Borrow</button>
                            </form>
                        <?php else: ?>
                            <button disabled class="btn btn-disabled">Unavailable</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Library System. All rights reserved.</p>
    </footer>
</body>
</html>
