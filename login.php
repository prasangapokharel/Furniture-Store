<?php
$pageTitle = 'Login - Pink Home';
require_once __DIR__ . '/include/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Handle login BEFORE including header
if ($_POST['action'] ?? '' === 'login') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            loginUser($user);
            
            // Redirect to intended page or dashboard
            $redirect = $_GET['redirect'] ?? 'index.php';
            header("Location: $redirect");
            exit();
        } else {
            $error_message = "Invalid email or password.";
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
                <h2 class="text-2xl font-bold text-gray-900">Sign in to your account</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Or 
                    <a href="register.php" class="font-medium text-pink-primary hover:text-pink-600">
                        create a new account
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
                <input type="hidden" name="action" value="login">
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <input id="email" name="email" type="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pink-primary focus:border-pink-primary">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-pink-primary focus:border-pink-primary">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" 
                               class="h-4 w-4 text-pink-primary focus:ring-pink-primary border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-medium text-pink-primary hover:text-pink-600">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-pink-primary hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-primary transition-colors">
                        Sign in
                    </button>
                </div>
            </form>

            <!-- Demo Accounts -->
            <div class="mt-6 border-t border-gray-200 pt-6">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Demo Accounts:</h3>
                <div class="text-xs text-gray-600 space-y-1">
                    <p><strong>Admin:</strong> admin@pinkhome.com / password</p>
                    <p><strong>Customer:</strong> Create new account below</p>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <div class="text-2xl mb-2">🚚</div>
                <p class="text-xs text-gray-600">Free Delivery</p>
            </div>
            <div>
                <div class="text-2xl mb-2">🔧</div>
                <p class="text-xs text-gray-600">Assembly Service</p>
            </div>
            <div>
                <div class="text-2xl mb-2">↩️</div>
                <p class="text-xs text-gray-600">30-Day Returns</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/include/footer.php'; ?>
