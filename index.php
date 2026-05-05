<?php
require_once 'config/config.php';
require_once 'includes/User.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h1 class="logo"><?php echo APP_NAME; ?></h1>
            <nav>
                <ul>
                    <?php if (User::isLoggedIn()): ?>
                        <li><a href="pages/catalogue.php">Catalogue</a></li>
                        <li><a href="pages/my_borrows.php">My Borrows</a></li>
                        <li><a href="pages/borrowing_history.php">History</a></li>
                        <?php if (User::isAdmin()): ?>
                            <li><a href="admin/dashboard.php">Admin Panel</a></li>
                        <?php endif; ?>
                        <li><a href="pages/logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a></li>
                    <?php else: ?>
                        <li><a href="pages/login.php">Login</a></li>
                        <li><a href="pages/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>

    <div class="hero">
        <div class="container">
            <h2>Welcome to <?php echo APP_NAME; ?></h2>
            <p>Manage your book borrowing experience</p>
            <?php if (!User::isLoggedIn()): ?>
                <p>
                    <a href="pages/login.php" class="btn btn-primary">Login</a>
                    <a href="pages/register.php" class="btn btn-secondary">Register</a>
                </p>
            <?php else: ?>
                <p>
                    <a href="pages/catalogue.php" class="btn btn-primary">Browse Catalogue</a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="container main-content">
        <section>
            <h3>About This System</h3>
            <p>Our Library Book Borrowing System helps students manage their book borrowing activities efficiently. Students can:</p>
            <ul>
                <li>Register and create a personal account</li>
                <li>Search for books by title or author</li>
                <li>Borrow and return books</li>
                <li>View their borrowing history</li>
                <li>Track active borrows and due dates</li>
            </ul>
        </section>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
    </footer>
</body>
</html>
