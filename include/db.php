<?php
// Prevent multiple inclusions
if (defined('DB_INCLUDED')) {
    return;
}
define('DB_INCLUDED', true);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'coushin_cloud');

// Create connection
function getConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Helper functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatPrice($price) {
    return 'Rs' . number_format($price, 2);
}

function calculateDeliveryFee($total) {
    if ($total >= 500) {
        return 0; // Free delivery for orders over $500
    }
    return 50; // Standard delivery fee
}
?>
