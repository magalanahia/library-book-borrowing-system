# Library Book Borrowing System

A PHP and MySQL web application for managing library book borrowing and returning. The system supports two roles:

- Students can register, log in, search the catalogue, borrow available books, return books, and view borrowing history.
- Admins can manage book records and monitor or update borrowing activity.

## Assessment Option

This project implements Option E: Library Book Borrowing System.

## Main Features

- User registration and login
- Logout and session-protected pages
- Student and admin role-based access
- Book catalogue loaded dynamically from MySQL
- Search catalogue by title or author
- Borrow and return books
- Borrowing history with due dates and fines
- Admin add, edit, and delete book records
- Admin view/filter borrowing activity and mark active loans as returned
- Persistent MySQL database storage

## Requirements

- PHP 7.0 or newer
- MySQL 5.7 or newer
- Apache, XAMPP, WAMP, or another PHP web server
- Modern web browser

## Project Structure

```text
library_system/
  admin/
    books.php
    borrowing.php
    dashboard.php
  config/
    config.php
  css/
    style.css
  database/
    schema.sql
  includes/
    Book.php
    Borrowing.php
    Database.php
    User.php
  pages/
    borrow.php
    borrowing_history.php
    catalogue.php
    login.php
    logout.php
    my_borrows.php
    register.php
  index.php
  project_db.sql
  README.md
```

## Database Setup

Use either `project_db.sql` at the project root or `database/schema.sql`.

### Option 1: phpMyAdmin

1. Open phpMyAdmin.
2. Go to the Import tab.
3. Choose `project_db.sql`.
4. Click Go.

### Option 2: MySQL command line

```bash
mysql -u root -p < project_db.sql
```

If your MySQL root account has no password in XAMPP, use:

```bash
mysql -u root < project_db.sql
```

## Application Configuration

Open `config/config.php` and update the database settings if your local MySQL credentials are different:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_system');
```

The default XAMPP configuration usually works with `DB_USER` as `root` and an empty `DB_PASS`.

## Running the System

1. Copy the `library_system` folder into your web server document root, for example:
   - XAMPP: `C:\xampp\htdocs\library_system`
   - WAMP: `C:\wamp64\www\library_system`
2. Start Apache and MySQL.
3. Import `project_db.sql`.
4. Visit:

```text
http://localhost/library_system
```

## Default Login Credentials

### Admin Account

- Username: `admin`
- Password: `admin123`

### Student Account

- Username: `student`
- Password: `student123`

You can also register new student accounts through the registration page.

## Assessment Requirements Checklist

| No. | Requirement | Where It Is Implemented |
| --- | --- | --- |
| 1 | User registration and login | `pages/register.php`, `pages/login.php`, `includes/User.php` |
| 2 | Logout and session control | `pages/logout.php`, `includes/User.php`, protected page redirects |
| 3 | Data entry through forms | Registration form, catalogue borrow form, admin book forms |
| 4 | Input validation with feedback | Registration validation, borrow availability checks, admin copy validation |
| 5 | Dynamic data display | Catalogue, borrowing history, admin dashboard, admin borrowing activity |
| 6 | Search or filter | Catalogue search and admin borrowing filters |
| 7 | Editing existing records | Admin edit book form in `admin/books.php` |
| 8 | Deleting records | Admin delete book action in `admin/books.php` |
| 9 | Role-based access | `student` and `admin` roles in `users`; admin pages require `User::isAdmin()` |
| 10 | Persistent data storage | MySQL tables in `project_db.sql` and `database/schema.sql` |

## Database Tables

### users

Stores student and admin accounts.

- `user_id`
- `username`
- `email`
- `password`
- `full_name`
- `role`
- `enrollment_id`
- `phone`
- `address`
- `created_at`
- `updated_at`
- `is_active`

### books

Stores catalogue records.

- `book_id`
- `title`
- `author`
- `isbn`
- `publisher`
- `publication_year`
- `category`
- `total_copies`
- `available_copies`
- `description`
- `created_at`
- `updated_at`

### borrowing_records

Stores borrowing and return activity.

- `record_id`
- `user_id`
- `book_id`
- `borrow_date`
- `due_date`
- `return_date`
- `status`
- `fine_amount`
- `created_at`
- `updated_at`

## Testing

Run the project validator:

```bash
python validate_project.py
```

Check PHP syntax:

```bash
php -l index.php
```

To lint all PHP files in PowerShell:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

## Submission Contents

The repository should include:

- All PHP source files and folders
- `project_db.sql` database dump
- `README.md`
- `PROJECT_REPORT.pdf`

Set the GitHub repository visibility to Public, then submit the repository URL on Moodle.
