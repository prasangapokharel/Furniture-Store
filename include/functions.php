<?php



// Get product image URL - FIXED VERSION
function getProductImage($product) {
    if (!$product || !isset($product['image_url'])) {
        return 'images/placeholder.jpg';
    }
    
    $image_url = $product['image_url'];
    
    // If it's already a full URL (starts with http), return as is
    if (strpos($image_url, 'http') === 0) {
        return $image_url;
    }
    
    // If it's a local file, check if it exists
    $local_path = 'images/' . $image_url;
    if (file_exists($local_path)) {
        return $local_path;
    }
    
    // If the product has additional images, try to get the first one
    if (isset($product['id'])) {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = ? LIMIT 1");
        $stmt->execute([$product['id']]);
        $additional_image = $stmt->fetchColumn();
        
        if ($additional_image) {
            // If it's a full URL, return as is
            if (strpos($additional_image, 'http') === 0) {
                return $additional_image;
            }
            // If it's a local file, add images/ prefix
            return 'images/' . $additional_image;
        }
    }
    
    // Fallback to placeholder
    return 'images/placeholder.jpg';
}

// Session management
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}




function logout() {
    startSession();
    session_destroy();
    header('Location: login.php');
    exit();
}

// Cart functions
function getCartItemCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([getCurrentUserId()]);
    return $stmt->fetchColumn() ?: 0;
}

function addToCart($product_id, $quantity = 1) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $conn = getConnection();
    
    // Check if item already exists in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([getCurrentUserId(), $product_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
        return $stmt->execute([$quantity, $existing['id']]);
    } else {
        // Add new item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        return $stmt->execute([getCurrentUserId(), $product_id, $quantity]);
    }
}

// Email validation
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Password validation
function isValidPassword($password) {
    return strlen($password) >= 6;
}

// Generate random string
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

// Date formatting
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M j, Y \a\t g:i A', strtotime($datetime));
}

// Truncate text
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Get category image - NEW FUNCTION
function getCategoryImage($category) {
    if (!$category || !isset($category['image_url']) || empty($category['image_url'])) {
        return 'images/placeholder.jpg';
    }
    
    $image_url = $category['image_url'];
    
    // If it's already a full URL (starts with http), return as is
    if (strpos($image_url, 'http') === 0) {
        return $image_url;
    }
    
    // If it's a local file, add images/ prefix if not already there
    if (strpos($image_url, 'images/') !== 0) {
        return 'images/' . $image_url;
    }
    
    return $image_url;
}
?>