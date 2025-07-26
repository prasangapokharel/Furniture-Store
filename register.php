<?php
$pageTitle = 'Register - Pink Home';
require_once __DIR__ . '/include/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Handle registration BEFORE including header
if ($_POST['action'] ?? '' === 'register') {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $postal_code = sanitizeInput($_POST['postal_code']);
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        $conn = getConnection();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error_message = "An account with this email already exists.";
        } else {
            // Create account
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, address, city, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$full_name, $email, $hashed_password, $phone, $address, $city, $postal_code]);
                
                // Auto login
                $user_id = $conn->lastInsertId();
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
                loginUser($user);
                
                // Redirect BEFORE any output
                header('Location: index.php?welcome=1');
                exit();
                
            } catch (Exception $e) {
                $error_message = "Error creating account. Please try again.";
            }
        }
    }
}

// Include header AFTER processing form
require_once __DIR__ . '/include/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="text-center">
                <h1 class="text-4xl font-bold text-pink-primary mb-2">🏠 Pink Home</h1>
                <h2 class="text-2xl font-bold text-gray-900">Create your account</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Already have an account? 
                    <a href="login.php" class="font-medium text-pink-primary hover:text-pink-600">
                        Sign in here
                    </a>
                </p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-8">
            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="register">
                
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                    <input id="full_name" name="full_name" type="text" required 
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pink-primary focus:border-pink-primary">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                    <input id="email" name="email" type="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pink-primary focus:border-pink-primary">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                        <input id="password" name="password" type="password" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pink-primary focus:border-pink-primary">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                        <input id="confirm_password" name="confirm_password" type="password" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pink-primary focus:border-pink-primary">
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input id="phone" name="phone" type="tel" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pink-primary focus:border-pink-primary">
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea id="address" name="address" rows="2" 
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pink-primary focus:border-pink-primary"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                        <input id="city" name="city" type="text" 
                               value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pink-primary focus:border-pink-primary">
                    </div>
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                        <input id="postal_code" name="postal_code" type="text" 
                               value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pink-primary focus:border-pink-primary">
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="terms" name="terms" type="checkbox" required 
                           class="h-4 w-4 text-pink-primary focus:ring-pink-primary border-gray-300 rounded">
                    <label for="terms" class="ml-2 block text-sm text-gray-900">
                        I agree to the <a href="#" class="text-pink-primary hover:text-pink-600">Terms and Conditions</a> 
                        and <a href="#" class="text-pink-primary hover:text-pink-600">Privacy Policy</a>
                    </label>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-pink-primary hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-primary transition-colors">
                        Create Account
                    </button>
                </div>
            </form>
        </div>

        <!-- Benefits -->
        <div class="bg-pink-light rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Join Pink Home and enjoy:</h3>
            <ul class="text-xs text-gray-700 space-y-1">
                <li>✓ Exclusive member discounts</li>
                <li>✓ Early access to new collections</li>
                <li>✓ Free delivery on orders over $500</li>
                <li>✓ Priority customer support</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/include/footer.php'; ?>
