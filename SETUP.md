# Project Setup Instructions

## Quick Start Guide

### Prerequisites
- PHP 7.0+
- MySQL 5.7+
- Web Server (Apache/Nginx)

### Step 1: Create Database
1. Open MySQL client
2. Run the SQL from `database/schema.sql`
3. Verify tables are created

### Step 2: Configure
1. Check `config/config.php`
2. Update database credentials if needed

### Step 3: Access Application
1. Place project in web server root
2. Navigate to: `http://localhost/library_system`

### Step 4: Login
- Use demo admin: `admin` / `admin123`
- Or create new student account

## Testing Checklist

- [ ] Database connection works
- [ ] Student registration successful
- [ ] Student login successful
- [ ] Book search functionality
- [ ] Borrow book feature
- [ ] View active borrows
- [ ] Return book
- [ ] Admin dashboard accessible
- [ ] Add/Edit/Delete books (admin)
- [ ] View borrowing activity (admin)

## Common Issues & Solutions

### Issue: Can't connect to database
**Solution**: 
- Check MySQL is running
- Verify credentials in config.php
- Ensure database exists

### Issue: Blank page
**Solution**:
- Check PHP error logs
- Enable error reporting in config.php
- Verify all includes are correct

### Issue: 404 on pages
**Solution**:
- Check file paths
- Verify web server configuration
- Ensure files are in correct directories

## Deployment Notes

Before going live:
1. Change default admin password
2. Update database credentials
3. Disable error display in production
4. Set up backups
5. Configure web server security
6. Use HTTPS

---

Happy coding! 📚
