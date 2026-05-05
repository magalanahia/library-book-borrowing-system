-- ==============================================================
-- LIBRARY BOOK BORROWING SYSTEM - DATABASE TEST SCRIPT
-- ==============================================================
-- This script tests all database functionality

-- ==============================================================
-- PART 1: VERIFY DATABASE STRUCTURE
-- ==============================================================

-- Check database exists
SHOW DATABASES LIKE 'library_system';

-- Use the database
USE library_system;

-- Show all tables
SHOW TABLES;

-- Check users table structure
DESCRIBE users;

-- Check books table structure
DESCRIBE books;

-- Check borrowing_records table structure
DESCRIBE borrowing_records;

-- ==============================================================
-- PART 2: TEST 1 - USER REGISTRATION DATA
-- ==============================================================
-- Requirement: User Registration and Login

-- View sample users
SELECT user_id, username, email, role, full_name, is_active FROM users;

-- Test: Check admin account exists
SELECT username, role, is_active FROM users WHERE username = 'admin';

-- Test: Insert new student (simulates registration)
INSERT INTO users (username, email, password, full_name, role, enrollment_id, phone, is_active) 
VALUES ('student001', 'student001@example.com', SHA2('password123', 256), 'John Doe', 'student', 'S20240001', '555-1234', TRUE);

-- Verify registration
SELECT username, email, role, full_name FROM users WHERE username = 'student001';

-- ==============================================================
-- PART 3: TEST 2 - SESSION & LOGOUT
-- ==============================================================
-- Requirement: User Logout and Session Control

-- Check user is_active status (simulates logout)
SELECT user_id, username, is_active FROM users WHERE username = 'admin';

-- Update is_active to FALSE (logout effect)
UPDATE users SET is_active = FALSE WHERE username = 'student001';

-- Verify logout
SELECT username, is_active FROM users WHERE username = 'student001';

-- Restore is_active
UPDATE users SET is_active = TRUE WHERE username = 'student001';

-- ==============================================================
-- PART 4: TEST 3 & 4 - DATA ENTRY & VALIDATION
-- ==============================================================
-- Requirement: Data Entry through Forms & Input Validation

-- View all books in database
SELECT book_id, title, author, total_copies, available_copies FROM books;

-- Count books
SELECT COUNT(*) as total_books FROM books;

-- Test: Add new book (simulates form submission)
INSERT INTO books (title, author, isbn, publisher, publication_year, category, total_copies, available_copies, description)
VALUES ('Clean Code', 'Robert Martin', '978-0132350884', 'Prentice Hall', 2008, 'Programming', 2, 2, 'A Handbook of Agile Software Craftsmanship');

-- Verify new book
SELECT * FROM books WHERE title = 'Clean Code';

-- ==============================================================
-- PART 5: TEST 5 - DYNAMIC DATA DISPLAY
-- ==============================================================
-- Requirement: Dynamic Data Display

-- Get all books with availability
SELECT 
    book_id, 
    title, 
    author, 
    category,
    total_copies,
    available_copies,
    CASE 
        WHEN available_copies > 0 THEN 'Available'
        ELSE 'Unavailable'
    END as status
FROM books
ORDER BY title ASC;

-- Get book details by ID
SELECT * FROM books WHERE book_id = 1;

-- ==============================================================
-- PART 6: TEST 6 - SEARCH & FILTER
-- ==============================================================
-- Requirement: Search or Filter

-- Search books by title
SELECT book_id, title, author, category FROM books WHERE title LIKE '%Great%';

-- Search books by author
SELECT book_id, title, author, category FROM books WHERE author LIKE '%Gatsby%';

-- Filter books by category
SELECT book_id, title, author, category FROM books WHERE category = 'Fiction';

-- ==============================================================
-- PART 7: TEST 7 - BORROW BOOK (CREATE RECORD)
-- ==============================================================
-- Requirement: Data Entry through Forms

-- Get student ID
SELECT user_id, username, full_name FROM users WHERE username = 'student001';

-- Get book ID
SELECT book_id, title FROM books WHERE title = 'The Great Gatsby';

-- Simulate borrowing a book (INSERT)
SET @user_id = (SELECT user_id FROM users WHERE username = 'student001');
SET @book_id = (SELECT book_id FROM books WHERE title = 'The Great Gatsby');
SET @due_date = DATE_ADD(CURDATE(), INTERVAL 14 DAY);

INSERT INTO borrowing_records (user_id, book_id, due_date, status)
VALUES (@user_id, @book_id, @due_date, 'active');

-- Verify borrowing record created
SELECT br.record_id, u.username, b.title, br.borrow_date, br.due_date, br.status
FROM borrowing_records br
JOIN users u ON br.user_id = u.user_id
JOIN books b ON br.book_id = b.book_id
WHERE u.username = 'student001' AND b.title = 'The Great Gatsby';

-- Update available copies
UPDATE books SET available_copies = available_copies - 1 WHERE book_id = @book_id;

-- Verify availability updated
SELECT book_id, title, total_copies, available_copies FROM books WHERE book_id = @book_id;

-- ==============================================================
-- PART 8: TEST 8 - VIEW BORROWING HISTORY
-- ==============================================================
-- Requirement: Dynamic Data Display

-- Get complete borrowing history for a student
SELECT 
    br.record_id,
    u.username,
    u.full_name,
    b.title,
    b.author,
    br.borrow_date,
    br.due_date,
    br.return_date,
    br.status,
    br.fine_amount
FROM borrowing_records br
JOIN users u ON br.user_id = u.user_id
JOIN books b ON br.book_id = b.book_id
WHERE u.username = 'student001'
ORDER BY br.borrow_date DESC;

-- Get active borrows for a student
SELECT 
    br.record_id,
    b.title,
    b.author,
    br.borrow_date,
    br.due_date,
    DATEDIFF(br.due_date, CURDATE()) as days_remaining
FROM borrowing_records br
JOIN books b ON br.book_id = b.book_id
WHERE br.user_id = @user_id AND br.status = 'active';

-- ==============================================================
-- PART 9: TEST 9 - RETURN BOOK & FINE CALCULATION
-- ==============================================================
-- Requirement: Editing Records

-- Get active borrow
SET @record_id = (SELECT record_id FROM borrowing_records 
                  WHERE user_id = @user_id AND status = 'active' LIMIT 1);

-- Simulate returning book
UPDATE borrowing_records 
SET return_date = CURDATE(), status = 'returned'
WHERE record_id = @record_id;

-- Restore available copies
UPDATE books SET available_copies = available_copies + 1 
WHERE book_id = (SELECT book_id FROM borrowing_records WHERE record_id = @record_id);

-- Verify return
SELECT record_id, status, return_date FROM borrowing_records WHERE record_id = @record_id;

-- ==============================================================
-- PART 10: TEST - OVERDUE CALCULATION
-- ==============================================================

-- Simulate overdue book (for testing fine calculation)
-- Insert book borrow with past due date
INSERT INTO borrowing_records (user_id, book_id, due_date, status)
VALUES (@user_id, @book_id, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'active');

-- Get overdue books
SELECT 
    br.record_id,
    u.username,
    b.title,
    br.due_date,
    DATEDIFF(CURDATE(), br.due_date) as days_overdue,
    DATEDIFF(CURDATE(), br.due_date) * 10 as calculated_fine
FROM borrowing_records br
JOIN users u ON br.user_id = u.user_id
JOIN books b ON br.book_id = b.book_id
WHERE br.status = 'active' AND br.due_date < CURDATE();

-- ==============================================================
-- PART 11: TEST 11 - EDIT BOOK (ADMIN)
-- ==============================================================
-- Requirement: Editing Existing Records

-- Get a book
SET @edit_book_id = (SELECT book_id FROM books LIMIT 1);

-- Update book details
UPDATE books 
SET 
    title = 'Updated Book Title',
    publisher = 'Updated Publisher',
    category = 'Updated Category'
WHERE book_id = @edit_book_id;

-- Verify update
SELECT book_id, title, publisher, category FROM books WHERE book_id = @edit_book_id;

-- ==============================================================
-- PART 12: TEST 12 - DELETE BOOK (ADMIN)
-- ==============================================================
-- Requirement: Deleting Records

-- Get book ID to delete (use our added book)
SET @delete_book_id = (SELECT book_id FROM books WHERE title = 'Clean Code' LIMIT 1);

-- Delete book
DELETE FROM books WHERE book_id = @delete_book_id;

-- Verify deletion
SELECT COUNT(*) FROM books WHERE book_id = @delete_book_id;
SELECT * FROM books WHERE title = 'Clean Code';

-- ==============================================================
-- PART 13: TEST - ROLE-BASED ACCESS
-- ==============================================================
-- Requirement: Role-Based Access

-- Count users by role
SELECT role, COUNT(*) as count FROM users GROUP BY role;

-- Get all admin accounts
SELECT user_id, username, role FROM users WHERE role = 'admin';

-- Get all student accounts
SELECT user_id, username, role FROM users WHERE role = 'student';

-- ==============================================================
-- PART 14: TEST - FOREIGN KEY CONSTRAINTS
-- ==============================================================
-- Requirement: Persistent Data Storage (Integrity)

-- Show borrowing records with all related data
SELECT 
    br.record_id,
    br.user_id,
    u.username as user,
    br.book_id,
    b.title as book,
    br.status
FROM borrowing_records br
JOIN users u ON br.user_id = u.user_id
JOIN books b ON br.book_id = b.book_id
LIMIT 10;

-- ==============================================================
-- PART 15: SUMMARY STATISTICS
-- ==============================================================

-- Total users
SELECT COUNT(*) as total_users FROM users;

-- Total books
SELECT COUNT(*) as total_books FROM books;

-- Total borrowing records
SELECT COUNT(*) as total_records FROM borrowing_records;

-- Active borrows
SELECT COUNT(*) as active_borrows FROM borrowing_records WHERE status = 'active';

-- Returned books
SELECT COUNT(*) as returned_books FROM borrowing_records WHERE status = 'returned';

-- Overdue books
SELECT COUNT(*) as overdue_books FROM borrowing_records 
WHERE status = 'active' AND due_date < CURDATE();

-- Books by status (Availability)
SELECT 
    SUM(total_copies) as total_copies,
    SUM(available_copies) as available_copies,
    SUM(total_copies) - SUM(available_copies) as borrowed_copies
FROM books;

-- ==============================================================
-- CLEANUP (Optional - Reset for fresh testing)
-- ==============================================================
-- Note: Only run these if you want to reset to initial state

-- DELETE FROM borrowing_records WHERE user_id > 1;
-- DELETE FROM users WHERE user_id > 1;
-- DELETE FROM books WHERE book_id > 5;

-- ==============================================================
-- END OF TEST SCRIPT
-- ==============================================================
