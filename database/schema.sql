-- Create Database
CREATE DATABASE IF NOT EXISTS library_system;
USE library_system;

-- Users Table (for both students and admins)
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    enrollment_id VARCHAR(50),
    phone VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Books Table
CREATE TABLE books (
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    publisher VARCHAR(100),
    publication_year YEAR,
    category VARCHAR(50),
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Borrowing Records Table
CREATE TABLE borrowing_records (
    record_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE NOT NULL,
    return_date DATE,
    status ENUM('active', 'returned', 'overdue') DEFAULT 'active',
    fine_amount DECIMAL(8, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE RESTRICT,
    INDEX (user_id),
    INDEX (book_id),
    INDEX (status)
);

-- Create Indexes for better performance
CREATE INDEX idx_username ON users(username);
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_book_title ON books(title);
CREATE INDEX idx_book_author ON books(author);
CREATE INDEX idx_borrow_user ON borrowing_records(user_id);
CREATE INDEX idx_borrow_book ON borrowing_records(book_id);

-- Sample Data (Optional)
INSERT INTO users (username, email, password, full_name, role, enrollment_id) VALUES
('admin', 'admin@library.com', '$2y$10$XuPmZuMjIgjPjivbN4LgKe6eCdTVZJryIHqu2VIxFJGEQ/Esc3rOO', 'Library Admin', 'admin', 'ADM001'),
('student', 'student@library.com', '$2y$10$rar5imDQmMYAHsShgCdqoe5VKbdBKffOnFpu.E6E6lPO0Mdgt3bIu', 'Demo Student', 'student', 'STU001')
ON DUPLICATE KEY UPDATE
password = VALUES(password),
role = VALUES(role),
full_name = VALUES(full_name),
enrollment_id = VALUES(enrollment_id);

INSERT INTO books (title, author, isbn, publisher, publication_year, category, total_copies, available_copies, description) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 'Scribner', 1925, 'Fiction', 3, 3, 'A classic American novel set in the Jazz Age.'),
('To Kill a Mockingbird', 'Harper Lee', '978-0061120084', 'J.B. Lippincott', 1960, 'Fiction', 2, 2, 'A gripping tale of racial injustice and childhood innocence.'),
('1984', 'George Orwell', '978-0451524935', 'Signet Classic', 1949, 'Dystopian', 2, 2, 'A dystopian social science fiction novel.'),
('Pride and Prejudice', 'Jane Austen', '978-0141439518', 'Penguin Classics', 1813, 'Romance', 4, 4, 'A romantic novel of manners.'),
('The Catcher in the Rye', 'J.D. Salinger', '978-0316769174', 'Little, Brown', 1951, 'Fiction', 2, 2, 'A story of teenage rebellion and alienation.');

CREATE OR REPLACE VIEW book_catalogue_view AS
SELECT book_id, title, author, isbn, category, total_copies, available_copies
FROM books;
