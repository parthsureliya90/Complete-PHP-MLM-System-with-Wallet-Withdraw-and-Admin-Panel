<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mlm_system');

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");

// Session start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper function to generate referral code
function generateReferralCode($length = 8) {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, $length));
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper function to sanitize input
function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}
?>