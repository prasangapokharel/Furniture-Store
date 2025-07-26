<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel - Pink Home'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'pink-primary': '#ec4899',
                        'pink-secondary': '#f472b6',
                        'pink-light': '#fce7f3'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-pink-primary">🏠 Pink Home</h1>
                <p class="text-sm text-gray-600">Admin Panel</p>
            </div>
            
            <nav class="mt-6">
                <a href="index.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-pink-light hover:text-pink-primary transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-pink-light text-pink-primary' : ''; ?>">
                    <span class="mr-3">📊</span>
                    Dashboard
                </a>
                <a href="products.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-pink-light hover:text-pink-primary transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'bg-pink-light text-pink-primary' : ''; ?>">
                    <span class="mr-3">📦</span>
                    Products
                </a>
                <a href="orders.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-pink-light hover:text-pink-primary transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'bg-pink-light text-pink-primary' : ''; ?>">
                    <span class="mr-3">🛒</span>
                    Orders
                </a>
                <a href="users.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-pink-light hover:text-pink-primary transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'bg-pink-light text-pink-primary' : ''; ?>">
                    <span class="mr-3">👥</span>
                    Users
                </a>
                <a href="categories.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-pink-light hover:text-pink-primary transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'bg-pink-light text-pink-primary' : ''; ?>">
                    <span class="mr-3">📂</span>
                    Categories
                </a>
                
                <div class="border-t border-gray-200 mt-6 pt-6">
                    <a href="../index.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-pink-light hover:text-pink-primary transition-colors">
                        <span class="mr-3">🏠</span>
                        View Website
                    </a>
                    <a href="../logout.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-red-100 hover:text-red-600 transition-colors">
                        <span class="mr-3">🚪</span>
                        Logout
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex justify-between items-center px-8 py-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900"><?php echo $pageTitle ?? 'Admin Panel'; ?></h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <div class="w-8 h-8 bg-pink-primary rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-semibold"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto"></main>