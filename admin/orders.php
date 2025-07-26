<?php
$pageTitle = 'Manage Orders - Admin Panel';
require_once '../include/session.php';
requireAdmin();

$conn = getConnection();

// Handle order status update
if ($_POST['action'] ?? '' === 'update_status') {
    $order_id = intval($_POST['order_id']);
    $status = sanitizeInput($_POST['status']);
    
    try {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        $success_message = "Order status updated successfully!";
    } catch (Exception $e) {
        $error_message = "Error updating order status.";
    }
}

// Get orders with filters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(u.full_name LIKE ? OR u.email LIKE ? OR o.id = ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

$stmt = $conn->prepare("
    SELECT o.*, u.full_name, u.email, u.phone 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    $where_clause 
    ORDER BY o.created_at DESC
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

require_once 'header.php';
?>

<div class="flex-1 p-8">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Manage Orders</h1>
                <p class="text-gray-600">View and manage customer orders</p>
            </div>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 alert-auto-hide">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 alert-auto-hide">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search by customer name, email, or order ID..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-primary">
            </div>
            <div>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-primary">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                Filter
            </button>
            <a href="orders.php" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                Clear
            </a>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">#<?php echo $order['id']; ?></div>
                                <?php if ($order['delivery_date']): ?>
                                    <div class="text-sm text-gray-500">Delivery: <?php echo date('M j, Y', strtotime($order['delivery_date'])); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($order['full_name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($order['email']); ?></div>
                                <?php if ($order['phone']): ?>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($order['phone']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-pink-primary">
                                <?php echo formatPrice($order['total_amount']); ?>
                                <?php if ($order['delivery_fee'] > 0): ?>
                                    <div class="text-xs text-gray-500">+<?php echo formatPrice($order['delivery_fee']); ?> delivery</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
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
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                <div class="text-xs text-gray-500"><?php echo date('g:i A', strtotime($order['created_at'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" onchange="this.form.submit()" class="text-xs px-2 py-1 border border-gray-300 rounded">
                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
