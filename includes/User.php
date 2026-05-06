<?php
/*
 * User class.
 * Handles student registration, login, logout, and role checks for protected pages.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class User {
    private $db;

    public function __construct() {
        // Each model class creates its own database helper.
        $this->db = new Database();
    }

    // Register a new user
    public function register($username, $email, $password, $full_name, $enrollment_id, $phone = '', $address = '') {
        $conn = $this->db->getConnection();
        
        // Check if username or email already exists
        $checkSql = "SELECT user_id FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($checkSql);
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }

        // Store only the hashed password, never the plain text password.
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // New accounts created from the public registration form are students.
        $sql = "INSERT INTO users (username, email, password, full_name, role, enrollment_id, phone, address) 
                VALUES (?, ?, ?, ?, 'student', ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssssss', $username, $email, $hashedPassword, $full_name, $enrollment_id, $phone, $address);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Registration successful'];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    // Login user
    public function login($username, $password) {
        $sql = "SELECT user_id, username, email, password, role, full_name FROM users WHERE username = ? AND is_active = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Store the minimum user information needed by protected pages.
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                return ['success' => true, 'message' => 'Login successful'];
            }
        }

        return ['success' => false, 'message' => 'Invalid username or password'];
    }

    // Check if user is logged in
    public static function isLoggedIn() {
        // Protected pages call this before showing private content.
        return isset($_SESSION['user_id']);
    }

    // Check if user is admin
    public static function isAdmin() {
        // Admin pages use this to enforce role-based access.
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    // Get user by ID
    public function getUserById($user_id) {
        $sql = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Logout user
    public static function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logout successful'];
    }
}
?>
