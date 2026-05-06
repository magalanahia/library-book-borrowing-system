<?php
/*
 * Borrowing class.
 * Coordinates borrowing, returning, due dates, fines, and borrowing reports.
 */

require_once __DIR__ . '/Database.php';

class Borrowing {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Borrow a book
    public function borrowBook($user_id, $book_id) {
        $conn = $this->db->getConnection();

        // Calculate due date based on the configured loan period.
        $due_date = date('Y-m-d', strtotime('+' . BORROW_DAYS . ' days'));

        // Start transaction so the record insert and copy decrement stay together.
        $conn->begin_transaction();

        try {
            // Lock the book row so two users cannot borrow the final copy at the same time.
            $checkSql = "SELECT available_copies FROM books WHERE book_id = ? FOR UPDATE";
            $stmt = $this->db->prepare($checkSql);
            $stmt->bind_param('i', $book_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if (!$result || $result['available_copies'] <= 0) {
                $conn->rollback();
                return ['success' => false, 'message' => 'Book is not available'];
            }

            // Check borrow limit before creating the new borrowing record.
            $limitSql = "SELECT COUNT(*) as active_count FROM borrowing_records WHERE user_id = ? AND status = 'active'";
            $stmt = $this->db->prepare($limitSql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $limitResult = $stmt->get_result()->fetch_assoc();

            if ($limitResult['active_count'] >= BORROW_LIMIT) {
                $conn->rollback();
                return ['success' => false, 'message' => 'You have reached the maximum borrow limit'];
            }

            // Check if user already has this book
            $checkDuplicateSql = "SELECT record_id FROM borrowing_records WHERE user_id = ? AND book_id = ? AND status = 'active'";
            $stmt = $this->db->prepare($checkDuplicateSql);
            $stmt->bind_param('ii', $user_id, $book_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $conn->rollback();
                return ['success' => false, 'message' => 'You already have this book'];
            }

            // Insert borrowing record with active status and due date.
            $borrowSql = "INSERT INTO borrowing_records (user_id, book_id, due_date, status) VALUES (?, ?, ?, 'active')";
            $stmt = $this->db->prepare($borrowSql);
            $stmt->bind_param('iis', $user_id, $book_id, $due_date);
            $stmt->execute();

            // Decrease available copies only if a copy is still available.
            $updateSql = "UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ? AND available_copies > 0";
            $stmt = $this->db->prepare($updateSql);
            $stmt->bind_param('i', $book_id);
            $stmt->execute();

            if ($this->db->affectedRows() !== 1) {
                $conn->rollback();
                return ['success' => false, 'message' => 'Book is not available'];
            }

            $conn->commit();
            return ['success' => true, 'message' => 'Book borrowed successfully', 'due_date' => $due_date];
        } catch (Exception $e) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Failed to borrow book'];
        }
    }

    // Return a book
    public function returnBook($record_id, $user_id = null) {
        $conn = $this->db->getConnection();

        // Students pass user_id so they can only return their own records.
        $recordSql = "SELECT * FROM borrowing_records WHERE record_id = ? AND status = 'active'";
        if ($user_id !== null) {
            $recordSql .= " AND user_id = ?";
        }
        $stmt = $this->db->prepare($recordSql);
        if ($user_id !== null) {
            $stmt->bind_param('ii', $record_id, $user_id);
        } else {
            $stmt->bind_param('i', $record_id);
        }
        $stmt->execute();
        $record = $stmt->get_result()->fetch_assoc();

        if (!$record) {
            return ['success' => false, 'message' => 'Record not found or already returned'];
        }

        $return_date = date('Y-m-d');
        $fine_amount = 0;

        // Calculate fine if overdue. The rate is 10 per day.
        if ($return_date > $record['due_date']) {
            $days_overdue = (strtotime($return_date) - strtotime($record['due_date'])) / (60 * 60 * 24);
            $fine_amount = $days_overdue * 10; // 10 per day
        }

        $conn->begin_transaction();

        try {
            // Update borrowing record
            $updateRecordSql = "UPDATE borrowing_records SET return_date = ?, status = 'returned', fine_amount = ? WHERE record_id = ?";
            $stmt = $this->db->prepare($updateRecordSql);
            $stmt->bind_param('sdi', $return_date, $fine_amount, $record_id);
            $stmt->execute();

            // Increase availability but never above total copies.
            $updateBookSql = "UPDATE books SET available_copies = LEAST(available_copies + 1, total_copies) WHERE book_id = ?";
            $stmt = $this->db->prepare($updateBookSql);
            $stmt->bind_param('i', $record['book_id']);
            $stmt->execute();

            $conn->commit();
            return ['success' => true, 'message' => 'Book returned successfully', 'fine_amount' => $fine_amount];
        } catch (Exception $e) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Failed to return book'];
        }
    }

    // Get user's borrowing history
    public function getUserBorrowingHistory($user_id) {
        $sql = "SELECT br.*, b.title, b.author, b.isbn FROM borrowing_records br 
                JOIN books b ON br.book_id = b.book_id 
                WHERE br.user_id = ? 
                ORDER BY br.borrow_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get all borrowing records (Admin)
    public function getAllBorrowingRecords() {
        $sql = "SELECT br.*, u.username, u.email, u.full_name, b.title, b.author FROM borrowing_records br 
                JOIN users u ON br.user_id = u.user_id 
                JOIN books b ON br.book_id = b.book_id 
                ORDER BY br.borrow_date DESC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get active borrows (Admin)
    public function getActiveBorrows() {
        $sql = "SELECT br.*, u.username, u.email, u.full_name, b.title, b.author FROM borrowing_records br 
                JOIN users u ON br.user_id = u.user_id 
                JOIN books b ON br.book_id = b.book_id 
                WHERE br.status = 'active' 
                ORDER BY br.due_date ASC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get overdue books
    public function getOverdueBooks() {
        $today = date('Y-m-d');
        $sql = "SELECT br.*, u.username, u.email, u.full_name, b.title, b.author FROM borrowing_records br 
                JOIN users u ON br.user_id = u.user_id 
                JOIN books b ON br.book_id = b.book_id 
                WHERE br.status = 'active' AND br.due_date < ? 
                ORDER BY br.due_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $today);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
