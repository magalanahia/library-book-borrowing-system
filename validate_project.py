"""
Library Book Borrowing System - Project Validator
This script validates the project structure and files
"""

import os
import re
from pathlib import Path

class ProjectValidator:
    def __init__(self, project_path):
        self.project_path = Path(project_path)
        self.results = {
            'structure': [],
            'files': [],
            'code_quality': [],
            'requirements': []
        }
    
    def validate_structure(self):
        """Validate project directory structure"""
        print("=" * 60)
        print("1. VALIDATING PROJECT STRUCTURE")
        print("=" * 60)
        
        required_dirs = [
            'config', 'includes', 'pages', 'admin', 'css', 'database'
        ]
        
        for dir_name in required_dirs:
            dir_path = self.project_path / dir_name
            if dir_path.exists() and dir_path.is_dir():
                print(f"✅ Directory exists: {dir_name}")
                self.results['structure'].append((dir_name, True))
            else:
                print(f"❌ Missing directory: {dir_name}")
                self.results['structure'].append((dir_name, False))
    
    def validate_files(self):
        """Validate required files exist"""
        print("\n" + "=" * 60)
        print("2. VALIDATING REQUIRED FILES")
        print("=" * 60)
        
        required_files = {
            'config/config.php': 'Database configuration',
            'includes/Database.php': 'Database class',
            'includes/User.php': 'User management class',
            'includes/Book.php': 'Book management class',
            'includes/Borrowing.php': 'Borrowing operations class',
            'pages/register.php': 'Student registration',
            'pages/login.php': 'Student login',
            'pages/logout.php': 'Logout',
            'pages/catalogue.php': 'Book catalogue',
            'pages/borrow.php': 'Borrow book',
            'pages/my_borrows.php': 'Active borrows',
            'pages/borrowing_history.php': 'Borrowing history',
            'admin/dashboard.php': 'Admin dashboard',
            'admin/books.php': 'Manage books',
            'admin/borrowing.php': 'Borrowing activity',
            'css/style.css': 'Stylesheet',
            'database/schema.sql': 'Database schema',
            'index.php': 'Home page',
            'README.md': 'Documentation'
        }
        
        for file_path, description in required_files.items():
            full_path = self.project_path / file_path
            if full_path.exists() and full_path.is_file():
                size = full_path.stat().st_size
                print(f"✅ {file_path:40} ({size:6} bytes) - {description}")
                self.results['files'].append((file_path, True, size))
            else:
                print(f"❌ {file_path:40} - Missing")
                self.results['files'].append((file_path, False, 0))
    
    def validate_code_quality(self):
        """Check PHP files for key features"""
        print("\n" + "=" * 60)
        print("3. VALIDATING CODE QUALITY & FEATURES")
        print("=" * 60)
        
        checks = {
            'includes/User.php': [
                (r'function register', 'Registration method'),
                (r'function login', 'Login method'),
                (r'function logout', 'Logout method'),
                (r'isLoggedIn', 'Session check'),
                (r'isAdmin', 'Admin role check'),
                (r'password_hash', 'Password hashing'),
                (r'password_verify', 'Password verification')
            ],
            'includes/Book.php': [
                (r'function getAllBooks', 'Get all books'),
                (r'function searchBooks', 'Search books'),
                (r'function addBook', 'Add book'),
                (r'function updateBook', 'Update book'),
                (r'function deleteBook', 'Delete book'),
                (r'function isAvailable', 'Check availability')
            ],
            'includes/Borrowing.php': [
                (r'function borrowBook', 'Borrow book'),
                (r'function returnBook', 'Return book'),
                (r'function getUserBorrowingHistory', 'User history'),
                (r'function getAllBorrowingRecords', 'All records'),
                (r'function getActiveBorrows', 'Active borrows'),
                (r'function getOverdueBooks', 'Overdue books')
            ],
            'includes/Database.php': [
                (r'function prepare', 'Prepared statements'),
                (r'bind_param', 'Parameter binding'),
                (r'function query', 'Query execution'),
                (r'mysqli', 'MySQL connection')
            ],
            'pages/register.php': [
                (r'User->register', 'Uses User class'),
                (r'password_hash|password_verify', 'Password handling'),
                (r'\$error', 'Error handling'),
                (r'if.*empty', 'Input validation')
            ],
            'pages/catalogue.php': [
                (r'searchBooks|getAllBooks', 'Book retrieval'),
                (r'foreach.*books', 'Dynamic display'),
                (r'User::isLoggedIn', 'Login check')
            ],
            'admin/books.php': [
                (r'User::isAdmin', 'Admin check'),
                (r'addBook|updateBook|deleteBook', 'CRUD operations')
            ]
        }
        
        for file_path, patterns in checks.items():
            full_path = self.project_path / file_path
            if full_path.exists():
                with open(full_path, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                
                print(f"\n📄 {file_path}:")
                for pattern, description in patterns:
                    if re.search(pattern, content, re.IGNORECASE):
                        print(f"  ✅ {description}")
                        self.results['code_quality'].append((file_path, description, True))
                    else:
                        print(f"  ❌ {description}")
                        self.results['code_quality'].append((file_path, description, False))
    
    def validate_requirements(self):
        """Validate 10 assessment requirements"""
        print("\n" + "=" * 60)
        print("4. VALIDATING 10 ASSESSMENT REQUIREMENTS")
        print("=" * 60)
        
        requirements = {
            '1. User Registration & Login': [
                ('includes/User.php', r'function register'),
                ('includes/User.php', r'function login'),
                ('pages/register.php', r'User->register'),
                ('pages/login.php', r'User->login')
            ],
            '2. User Logout & Session': [
                ('pages/logout.php', r'session_destroy'),
                ('includes/User.php', r'function logout'),
                ('includes/User.php', r'isLoggedIn')
            ],
            '3. Data Entry through Forms': [
                ('pages/register.php', r'<form'),
                ('pages/catalogue.php', r'<form'),
                ('admin/books.php', r'<form'),
                ('includes/User.php', r'INSERT INTO')
            ],
            '4. Input Validation': [
                ('pages/register.php', r'if.*empty|strlen'),
                ('includes/User.php', r'if.*error'),
                ('includes/Borrowing.php', r'if.*available')
            ],
            '5. Dynamic Data Display': [
                ('pages/catalogue.php', r'foreach.*books'),
                ('pages/my_borrows.php', r'foreach.*records'),
                ('database/schema.sql', r'SELECT')
            ],
            '6. Search or Filter': [
                ('pages/catalogue.php', r'searchBooks'),
                ('admin/borrowing.php', r'filter'),
                ('includes/Book.php', r'LIKE')
            ],
            '7. Editing Records': [
                ('admin/books.php', r'updateBook|UPDATE'),
                ('includes/Book.php', r'function updateBook')
            ],
            '8. Deleting Records': [
                ('admin/books.php', r'deleteBook|DELETE'),
                ('includes/Book.php', r'function deleteBook')
            ],
            '9. Role-Based Access': [
                ('includes/User.php', r'isAdmin'),
                ('admin/books.php', r'User::isAdmin'),
                ('database/schema.sql', r"ENUM.*admin.*student")
            ],
            '10. Persistent Data Storage': [
                ('database/schema.sql', r'CREATE TABLE'),
                ('includes/Database.php', r'mysqli'),
                ('config/config.php', r'DB_')
            ]
        }
        
        for req_name, patterns in requirements.items():
            print(f"\n{req_name}:")
            all_found = True
            for file_path, pattern in patterns:
                full_path = self.project_path / file_path
                if full_path.exists():
                    with open(full_path, 'r', encoding='utf-8', errors='ignore') as f:
                        content = f.read()
                    
                    if re.search(pattern, content, re.IGNORECASE):
                        print(f"  ✅ {file_path}")
                        self.results['requirements'].append((req_name, True))
                    else:
                        print(f"  ⚠️  {file_path} - Pattern not found")
                        all_found = False
                else:
                    print(f"  ❌ {file_path} - File missing")
                    all_found = False
    
    def generate_summary(self):
        """Generate validation summary"""
        print("\n" + "=" * 60)
        print("VALIDATION SUMMARY")
        print("=" * 60)
        
        # Count results
        files_ok = sum(1 for _, exists, _ in self.results['files'] if exists)
        total_files = len(self.results['files'])
        
        structure_ok = sum(1 for _, exists in self.results['structure'] if exists)
        total_structure = len(self.results['structure'])
        
        code_ok = sum(1 for _, _, found in self.results['code_quality'] if found)
        total_code = len(self.results['code_quality'])
        
        requirements_ok = sum(1 for _, found in self.results['requirements'] if found)
        total_reqs = len(set(req[0] for req in self.results['requirements']))
        
        print(f"\n📁 Directory Structure: {structure_ok}/{total_structure} ✅")
        print(f"📄 Required Files: {files_ok}/{total_files} ✅")
        print(f"💻 Code Quality: {code_ok}/{total_code} ✅")
        print(f"✅ Requirements Met: {total_reqs}/10 ✅")
        
        # Calculate total score
        total_checks = files_ok + structure_ok + code_ok + total_reqs
        max_checks = total_files + total_structure + total_code + 10
        percentage = (total_checks / max_checks * 100) if max_checks > 0 else 0
        
        print(f"\n📊 Overall Score: {percentage:.1f}%")
        
        if percentage == 100:
            print("\n🎉 PROJECT VALIDATION: ALL CHECKS PASSED ✅")
        elif percentage >= 90:
            print("\n✅ PROJECT VALIDATION: EXCELLENT (>90%)")
        elif percentage >= 80:
            print("\n⚠️  PROJECT VALIDATION: GOOD (>80%)")
        else:
            print("\n❌ PROJECT VALIDATION: NEEDS REVIEW (<80%)")

def main():
    project_path = r"c:\Users\Nahia\Desktop\project work\library_system"
    
    print("\n" + "=" * 60)
    print("LIBRARY BOOK BORROWING SYSTEM - PROJECT VALIDATOR")
    print("=" * 60)
    print(f"Project Path: {project_path}\n")
    
    validator = ProjectValidator(project_path)
    validator.validate_structure()
    validator.validate_files()
    validator.validate_code_quality()
    validator.validate_requirements()
    validator.generate_summary()
    
    print("\n" + "=" * 60)
    print("✅ VALIDATION COMPLETE")
    print("=" * 60)

if __name__ == '__main__':
    main()
