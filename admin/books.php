<?php
/*
 * Admin book management page.
 * Allows admin users to add, edit, and delete catalogue records.
 */

require_once '../config/config.php';
require_once '../includes/User.php';
require_once '../includes/Book.php';

// Role-based protection: students cannot access book management.
if (!User::isLoggedIn() || !User::isAdmin()) {
    header('Location: ../pages/login.php');
    exit;
}

$book = new Book();
$message = '';
$edit_book = null;

// Handle add/edit/delete actions submitted by the admin forms.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            // Add a brand-new book record.
            $result = $book->addBook(
                $_POST['title'],
                $_POST['author'],
                $_POST['isbn'],
                $_POST['publisher'],
                $_POST['publication_year'],
                $_POST['category'],
                intval($_POST['total_copies']),
                $_POST['description']
            );
            $message = $result;
            $books = $book->getAllBooks(); // Refresh list
        } elseif ($_POST['action'] === 'update') {
            // Save changes made through the edit form.
            $result = $book->updateBook(
                intval($_POST['book_id']),
                $_POST['title'],
                $_POST['author'],
                $_POST['isbn'],
                $_POST['publisher'],
                $_POST['publication_year'],
                $_POST['category'],
                intval($_POST['total_copies']),
                $_POST['description']
            );
            $message = $result;
            $books = $book->getAllBooks(); // Refresh list
        } elseif ($_POST['action'] === 'delete') {
            // Delete is blocked inside Book::deleteBook when borrowing history exists.
            $result = $book->deleteBook(intval($_POST['book_id']));
            $message = $result;
            $books = $book->getAllBooks(); // Refresh list
        }
    }
}

// If an edit id is provided, load that book into the edit form.
if (isset($_GET['edit'])) {
    $edit_book = $book->getBookById(intval($_GET['edit']));
    if (!$edit_book) {
        $message = ['success' => false, 'message' => 'Book not found'];
    }
}

// Always load the latest book list after any action.
$books = $book->getAllBooks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Admin Panel</title>
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
                    <li><a href="books.php" class="active">Manage Books</a></li>
                    <li><a href="borrowing.php">Borrowing Activity</a></li>
                    <li><a href="../pages/logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="container">
        <h2>Manage Books</h2>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $message['success'] ? 'alert-success' : 'alert-error'; ?>">
                <?php echo htmlspecialchars($message['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($edit_book)): ?>
            <div style="margin-bottom: 20px;">
                <button onclick="document.getElementById('addBookForm').style.display='block'" class="btn btn-primary">
                    + Add New Book
                </button>
            </div>
        <?php endif; ?>

        <!-- Add Book Form -->
        <div id="addBookForm" style="display: none; margin-bottom: 20px; padding: 20px; background: #f5f5f5; border-radius: 5px;">
            <h3>Add New Book</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required>
                </div>

                <div class="form-group">
                    <label>Author *</label>
                    <input type="text" name="author" required>
                </div>

                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn">
                </div>

                <div class="form-group">
                    <label>Publisher</label>
                    <input type="text" name="publisher">
                </div>

                <div class="form-group">
                    <label>Publication Year</label>
                    <input type="number" name="publication_year" min="1900" max="<?php echo date('Y'); ?>">
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" placeholder="e.g., Fiction, Science, History">
                </div>

                <div class="form-group">
                    <label>Total Copies *</label>
                    <input type="number" name="total_copies" value="1" min="1" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"></textarea>
                </div>

                <button type="submit" class="btn btn-success">Add Book</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addBookForm').style.display='none'">Cancel</button>
            </form>
        </div>

        <?php if (!empty($edit_book)): ?>
            <!-- Edit Book Form -->
            <div style="margin-bottom: 20px; padding: 20px; background: #f5f5f5; border-radius: 5px;">
                <h3>Edit Book</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="book_id" value="<?php echo $edit_book['book_id']; ?>">

                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" required value="<?php echo htmlspecialchars($edit_book['title']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Author *</label>
                        <input type="text" name="author" required value="<?php echo htmlspecialchars($edit_book['author']); ?>">
                    </div>

                    <div class="form-group">
                        <label>ISBN</label>
                        <input type="text" name="isbn" value="<?php echo htmlspecialchars($edit_book['isbn'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Publisher</label>
                        <input type="text" name="publisher" value="<?php echo htmlspecialchars($edit_book['publisher'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Publication Year</label>
                        <input type="number" name="publication_year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($edit_book['publication_year'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" name="category" value="<?php echo htmlspecialchars($edit_book['category'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Total Copies *</label>
                        <input type="number" name="total_copies" min="1" required value="<?php echo $edit_book['total_copies']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"><?php echo htmlspecialchars($edit_book['description'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <a href="books.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        <?php endif; ?>

        <!-- Books List -->
        <h3>Books List</h3>
        <?php if (empty($books)): ?>
            <p>No books found.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Category</th>
                        <th>Total</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $b): ?>
                        <!-- Each row is a database book record with admin actions. -->
                        <tr>
                            <td><?php echo htmlspecialchars($b['title']); ?></td>
                            <td><?php echo htmlspecialchars($b['author']); ?></td>
                            <td><?php echo htmlspecialchars($b['isbn'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($b['category'] ?? '-'); ?></td>
                            <td><?php echo $b['total_copies']; ?></td>
                            <td><?php echo $b['available_copies']; ?></td>
                            <td>
                                <a href="books.php?edit=<?php echo $b['book_id']; ?>" class="btn btn-sm">Edit</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this book?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="book_id" value="<?php echo $b['book_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
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
