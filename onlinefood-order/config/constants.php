<?php
// Start Session
session_start();

// Enable errors (TURN OFF after testing)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Constants
define('SITEURL', 'https://student.heraldcollege.edu.np/~np03cs4a240269/onlinefood-order/');
define('DB_HOST', 'localhost');
define('DB_USER', 'np03cs4a240269');
define('DB_PASS', '7CZCTNnd88');
define('DB_NAME', 'np03cs4a240269');

// Database Connection 
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check Connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Optional but recommended (prevents weird character bugs)
mysqli_set_charset($conn, 'utf8');
