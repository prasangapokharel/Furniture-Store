<?php
$pageTitle = 'Admin Dashboard - Pink Home';
require_once '../include/session.php';
requireAdmin();

$conn = getConnection();

// Get dashboard statistics
$stats = [];

// Total products
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM products");
$stmt->execute();
$stats['products'] = $stmt->fetchColumn();

// Total orders
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders");
$stmt->execute();
$stats['orders'] = $stmt->fetchColumn();

// Total users
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_admin = 0");
$stmt->execute();
$stats['users'] = $stmt->fetchColumn();

// Total revenue
$stmt = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
$stmt->execute();
$stats['revenue'] = $stmt->fetchColumn() ?? 0;

// Recent orders
$stmt = $conn->prepare("
    SELECT o.*, u.full_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_orders = $stmt->fetchAll();

// Low stock products
$stmt = $conn->prepare("SELECT * FROM products WHERE stock_quantity <= 5 ORDER BY stock_quantity ASC");
$stmt->execute();
$low_stock = $stmt->fetchAll();

require_once 'header.php';
?>

<div class="flex-1 p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-gray-600">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <span class="text-2xl">📦</span>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Products</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['products']); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <span class="text-2xl">🛒</span>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['orders']); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <span class="text-2xl">👥</span>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['users']); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-pink-100 text-pink-600">
                    <span class="text-2xl">💰</span>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo formatPrice($stats['revenue']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Orders</h2>
                    <a href="orders.php" class="text-pink-primary hover:text-pink-600 text-sm font-medium">View All</a>
                </div>
            </div>
            <div class="p-6">
                <?php if (empty($recent_orders)): ?>
                    <p class="text-gray-600 text-center py-4">No orders yet</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recent_orders as $order): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-semibold text-gray-900">Order #<?php echo $order['id']; ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['full_name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
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
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">Low Stock Alert</h2>
                    <a href="products.php" class="text-pink-primary hover:text-pink-600 text-sm font-medium">Manage Products</a>
                </div>
            </div>
            <div class="p-6">
                <?php if (empty($low_stock)): ?>
                    <p class="text-gray-600 text-center py-4">All products are well stocked</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($low_stock as $product): ?>
                            <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg border border-red-200">
                                <div>
                                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></p>
                                    <p class="text-sm text-gray-600">ID: <?php echo $product['id']; ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-red-600"><?php echo $product['stock_quantity']; ?> left</p>
                                    <a href="products.php?edit=<?php echo $product['id']; ?>" class="text-xs text-pink-primary hover:text-pink-600">Update Stock</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
