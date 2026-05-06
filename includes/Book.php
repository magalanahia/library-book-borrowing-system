<?php
/*
 * Book class.
 * Handles catalogue display, searching, and admin CRUD operations for book records.
 */

require_once __DIR__ . '/Database.php';

class Book {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    private function normalizeOptional($value) {
        // Convert empty optional form fields into NULL-friendly values.
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    private function getBorrowedCopies($book_id) {
        // Active borrow count is needed when recalculating available copies.
        $sql = "SELECT COUNT(*) as borrowed_count FROM borrowing_records WHERE book_id = ? AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $book_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? intval($result['borrowed_count']) : 0;
    }

    // Get all books
    public function getAllBooks() {
        $sql = "SELECT * FROM books ORDER BY title ASC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Search books by title or author
    public function searchBooks($keyword) {
        // Wildcards allow partial title or author searches.
        $keyword = '%' . $this->db->escape($keyword) . '%';
        $sql = "SELECT * FROM books WHERE title LIKE ? OR author LIKE ? ORDER BY title ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $keyword, $keyword);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get book by ID
    public function getBookById($book_id) {
        $sql = "SELECT * FROM books WHERE book_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $book_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Add new book (Admin only)
    public function addBook($title, $author, $isbn, $publisher, $publication_year, $category, $total_copies, $description) {
        // Clean optional inputs before they are saved.
        $isbn = $this->normalizeOptional($isbn);
        $publisher = $this->normalizeOptional($publisher);
        $publication_year = $this->normalizeOptional($publication_year);
        $publication_year = $publication_year === null ? null : intval($publication_year);
        $category = $this->normalizeOptional($category);
        $total_copies = max(1, intval($total_copies));

        $sql = "INSERT INTO books (title, author, isbn, publisher, publication_year, category, total_copies, available_copies, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ssssisiis', $title, $author, $isbn, $publisher, $publication_year, $category, $total_copies, $total_copies, $description);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Book added successfully', 'book_id' => $this->db->lastInsertId()];
        }
        return ['success' => false, 'message' => 'Failed to add book'];
    }

    // Update book (Admin only)
    public function updateBook($book_id, $title, $author, $isbn, $publisher, $publication_year, $category, $total_copies, $description) {
        $book_id = intval($book_id);
        $total_copies = max(1, intval($total_copies));
        $borrowed_copies = $this->getBorrowedCopies($book_id);

        // Prevent admins from setting total copies below the number already on loan.
        if ($borrowed_copies > $total_copies) {
            return ['success' => false, 'message' => 'Total copies cannot be less than active borrowed copies'];
        }

        $isbn = $this->normalizeOptional($isbn);
        $publisher = $this->normalizeOptional($publisher);
        $publication_year = $this->normalizeOptional($publication_year);
        $publication_year = $publication_year === null ? null : intval($publication_year);
        $category = $this->normalizeOptional($category);
        // Availability is derived from total copies minus currently borrowed copies.
        $available_copies = $total_copies - $borrowed_copies;

        $sql = "UPDATE books SET title = ?, author = ?, isbn = ?, publisher = ?, publication_year = ?, category = ?, total_copies = ?, available_copies = ?, description = ? WHERE book_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ssssisiisi', $title, $author, $isbn, $publisher, $publication_year, $category, $total_copies, $available_copies, $description, $book_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Book updated successfully'];
        }
        return ['success' => false, 'message' => 'Failed to update book'];
    }

    // Delete book (Admin only)
    public function deleteBook($book_id) {
        // Keep borrowing history intact by blocking deletion of books with records.
        $historySql = "SELECT COUNT(*) as history_count FROM borrowing_records WHERE book_id = ?";
        $stmt = $this->db->prepare($historySql);
        $stmt->bind_param('i', $book_id);
        $stmt->execute();
        $history = $stmt->get_result()->fetch_assoc();

        if ($history && intval($history['history_count']) > 0) {
            return ['success' => false, 'message' => 'Cannot delete a book that has borrowing history'];
        }

        $sql = "DELETE FROM books WHERE book_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $book_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Book deleted successfully'];
        }
        return ['success' => false, 'message' => 'Failed to delete book'];
    }

    // Check book availability
    public function isAvailable($book_id) {
        $sql = "SELECT available_copies FROM books WHERE book_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $book_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result && $result['available_copies'] > 0;
    }
}
?>
