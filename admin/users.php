<?php
$pageTitle = 'Manage Users - Admin Panel';
require_once '../include/session.php';
requireAdmin();

$conn = getConnection();

// Handle user actions
if ($_POST['action'] ?? '' === 'toggle_admin') {
    $user_id = intval($_POST['user_id']);
    $is_admin = intval($_POST['is_admin']);
    
    try {
        $stmt = $conn->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
        $stmt->execute([$is_admin, $user_id]);
        $success_message = "User permissions updated successfully!";
    } catch (Exception $e) {
        $error_message = "Error updating user permissions.";
    }
}

if ($_POST['action'] ?? '' === 'delete_user') {
    $user_id = intval($_POST['user_id']);
    
    // Don't allow deleting current admin
    if ($user_id == getCurrentUserId()) {
        $error_message = "You cannot delete your own account.";
    } else {
        try {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $success_message = "User deleted successfully!";
        } catch (Exception $e) {
            $error_message = "Error deleting user. User may have existing orders.";
        }
    }
}

// Get users with search
$search = $_GET['search'] ?? '';
$user_type = $_GET['user_type'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(full_name LIKE ? OR email LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($user_type === 'admin') {
    $where_conditions[] = "is_admin = 1";
} elseif ($user_type === 'customer') {
    $where_conditions[] = "is_admin = 0";
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

$stmt = $conn->prepare("SELECT * FROM users $where_clause ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once 'header.php';
?>

<div class="flex-1 p-8">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Manage Users</h1>
                <p class="text-gray-600">View and manage user accounts</p>
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
                       placeholder="Search by name or email..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-primary">
            </div>
            <div>
                <select name="user_type" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-primary">
                    <option value="">All Users</option>
                    <option value="admin" <?php echo $user_type == 'admin' ? 'selected' : ''; ?>>Admins</option>
                    <option value="customer" <?php echo $user_type == 'customer' ? 'selected' : ''; ?>>Customers</option>
                </select>
            </div>
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                Filter
            </button>
            <a href="users.php" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                Clear
            </a>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-pink-primary rounded-full flex items-center justify-center">
                                        <span class="text-white font-semibold"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                        <div class="text-sm text-gray-500">ID: <?php echo $user['id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                <?php if ($user['phone']): ?>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['phone']); ?></div>
                                <?php endif; ?>
                                <?php if ($user['city']): ?>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['city']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($user['is_admin']): ?>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                        Admin
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Customer
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <?php if ($user['id'] != getCurrentUserId()): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="toggle_admin">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="is_admin" value="<?php echo $user['is_admin'] ? 0 : 1; ?>">
                                        <button type="submit" class="text-indigo-600 hover:text-indigo-900">
                                            <?php echo $user['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" class="inline" onsubmit="return confirmDelete('Are you sure you want to delete this user?')">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-gray-400">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
