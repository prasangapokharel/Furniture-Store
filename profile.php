<?php
$pageTitle = 'My Profile - Pink Home';
require_once 'include/session.php';
requireLogin();
require_once 'include/header.php';

$user = getCurrentUser();
$conn = getConnection();

// Handle profile update
if ($_POST['action'] ?? '' === 'update_profile') {
    $full_name = sanitizeInput($_POST['full_name']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $postal_code = sanitizeInput($_POST['postal_code']);
    
    if (empty($full_name)) {
        $error_message = "Full name is required.";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, city = ?, postal_code = ? WHERE id = ?");
            $stmt->execute([$full_name, $phone, $address, $city, $postal_code, getCurrentUserId()]);
            
            $success_message = "Profile updated successfully!";
            $user = getCurrentUser(); // Refresh user data
        } catch (Exception $e) {
            $error_message = "Error updating profile. Please try again.";
        }
    }
}

// Handle password change
if ($_POST['action'] ?? '' === 'change_password') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $password_error = "Please fill in all password fields.";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $password_error = "New password must be at least 6 characters long.";
    } elseif (!password_verify($current_password, $user['password'])) {
        $password_error = "Current password is incorrect.";
    } else {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, getCurrentUserId()]);
            
            $password_success = "Password changed successfully!";
        } catch (Exception $e) {
            $password_error = "Error changing password. Please try again.";
        }
    }
}

// Get recent orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([getCurrentUserId()]);
$recent_orders = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
        <p class="text-gray-600">Manage your account settings and view your order history</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Navigation -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-pink-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl text-white"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></span>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p class="text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                
                <nav class="space-y-2">
                    <a href="#profile-info" class="block px-4 py-2 text-gray-700 hover:bg-pink-light rounded-md transition-colors">
                        👤 Profile Information
                    </a>
                    <a href="#change-password" class="block px-4 py-2 text-gray-700 hover:bg-pink-light rounded-md transition-colors">
                        🔒 Change Password
                    </a>
                    <a href="orders.php" class="block px-4 py-2 text-gray-700 hover:bg-pink-light rounded-md transition-colors">
                        📦 Order History
                    </a>
                    <a href="cart.php" class="block px-4 py-2 text-gray-700 hover:bg-pink-light rounded-md transition-colors">
                        🛒 Shopping Cart
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Profile Information -->
            <div id="profile-info" class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Profile Information</h2>
                
                <?php if (isset($success_message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="full_name" required
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                            <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="address" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-primary"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input type="text" name="city" 
                                   value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                            <input type="text" name="postal_code" 
                                   value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-primary">
                        </div>
                    </div>

                    <button type="submit" class="bg-pink-primary text-white px-6 py-2 rounded-md hover:bg-pink-600 transition-colors">
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div id="change-password" class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Change Password</h2>
                
                <?php if (isset($password_success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        <?php echo $password_success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($password_error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <?php echo $password_error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <input type="password" name="current_password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-primary">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                            <input type="password" name="new_password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                            <input type="password" name="confirm_password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-primary">
                        </div>
                    </div>

                    <button type="submit" class="bg-pink-primary text-white px-6 py-2 rounded-md hover:bg-pink-600 transition-colors">
                        Change Password
                    </button>
                </form>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Recent Orders</h2>
                    <a href="orders.php" class="text-pink-primary hover:text-pink-600 text-sm font-medium">View All</a>
                </div>
                
                <?php if (empty($recent_orders)): ?>
                    <div class="text-center py-8">
                        <div class="text-4xl mb-4">📦</div>
                        <p class="text-gray-600">No orders yet</p>
                        <a href="products.php" class="text-pink-primary hover:text-pink-600 text-sm font-medium">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recent_orders as $order): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-900">Order #<?php echo $order['id']; ?></p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-pink-primary"><?php echo formatPrice($order['total_amount']); ?></p>
                                        <span class="inline-block px-2 py-1 text-xs rounded-full 
                                            <?php 
                                            switch($order['status']) {
                                                case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                case 'confirmed': echo 'bg-blue-100 text-blue-800'; break;
                                                case 'shipped': echo 'bg-purple-100 text-purple-800'; break;
                                                case 'delivered': echo 'bg-green-100 text-green-800'; break;
                                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                            }
                                            ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'include/footer.php'; ?>