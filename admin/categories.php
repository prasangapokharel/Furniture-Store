<?php
$pageTitle = 'Manage Categories - Admin Panel';
require_once '../include/session.php';
require_once '../include/functions.php';
requireAdmin();

$conn = getConnection();

// Handle category actions
if (isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $image_url = sanitizeInput($_POST['image_url'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name)) {
        $error_message = "Category name is required.";
    } else {
        try {
            // Check if category name already exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
            $stmt->execute([$name]);
            $exists = $stmt->fetchColumn();
            
            if ($exists > 0) {
                $error_message = "A category with this name already exists.";
            } else {
                $stmt = $conn->prepare("INSERT INTO categories (name, description, image_url, is_active) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $description, $image_url, $is_active]);
                $success_message = "Category added successfully!";
            }
        } catch (Exception $e) {
            $error_message = "Error adding category: " . $e->getMessage();
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'update_category') {
    $id = intval($_POST['id'] ?? 0);
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $image_url = sanitizeInput($_POST['image_url'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name) || empty($id)) {
        $error_message = "Category name and ID are required.";
    } else {
        try {
            // Check if category name already exists (excluding current category)
            $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?");
            $stmt->execute([$name, $id]);
            $exists = $stmt->fetchColumn();
            
            if ($exists > 0) {
                $error_message = "A category with this name already exists.";
            } else {
                $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, image_url = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $description, $image_url, $is_active, $id]);
                $success_message = "Category updated successfully!";
            }
        } catch (Exception $e) {
            $error_message = "Error updating category: " . $e->getMessage();
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    $id = intval($_POST['id'] ?? 0);
    
    if (empty($id)) {
        $error_message = "Invalid category ID.";
    } else {
        try {
            $conn->beginTransaction();
            
            // Get category name for confirmation message
            $stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $category_name = $stmt->fetchColumn();
            
            if (!$category_name) {
                $error_message = "Category not found.";
            } else {
                // Check if category has products
                $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                $stmt->execute([$id]);
                $product_count = $stmt->fetchColumn();
                
                if ($product_count > 0) {
                    // Don't delete, just deactivate
                    $stmt = $conn->prepare("UPDATE categories SET is_active = 0, name = CONCAT('[INACTIVE] ', name) WHERE id = ? AND name NOT LIKE '[INACTIVE]%'");
                    $stmt->execute([$id]);
                    $success_message = "Category '" . htmlspecialchars($category_name) . "' has been deactivated (it has $product_count products).";
                } else {
                    // Safe to delete - no products exist
                    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->execute([$id]);
                    $success_message = "Category '" . htmlspecialchars($category_name) . "' deleted successfully!";
                }
            }
            
            $conn->commit();
            
        } catch (Exception $e) {
            $conn->rollBack();
            $error_message = "Error deleting category: " . $e->getMessage();
        }
    }
}

// Get categories with product count
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(c.name LIKE ? OR c.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($status_filter === 'active') {
    $where_conditions[] = "c.is_active = 1";
} elseif ($status_filter === 'inactive') {
    $where_conditions[] = "c.is_active = 0";
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

$stmt = $conn->prepare("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    $where_clause 
    GROUP BY c.id 
    ORDER BY c.created_at DESC
");
$stmt->execute($params);
$categories = $stmt->fetchAll();

// Get category for editing
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_category = $stmt->fetch();
}

require_once 'header.php';
?>

<link rel="stylesheet" href="../css/admin.css">

<style>
/* Categories specific styles */
.admin-content {
    flex: 1;
    padding: 2rem;
    background-color: #f9fafb;
    min-height: calc(100vh - 80px);
    overflow-y: auto;
    max-height: calc(100vh - 80px);
}

.admin-categories-grid {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}

.admin-category-card {
    display: flex;
    flex-direction: column;
    height: auto;
    min-height: 350px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.admin-category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.admin-category-image {
    position: relative;
    height: 180px;
    overflow: hidden;
    flex-shrink: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.admin-category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.admin-category-placeholder {
    font-size: 3rem;
    color: white;
    opacity: 0.8;
}

.admin-category-content {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.admin-category-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.admin-category-description {
    color: #6b7280;
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 1rem;
    flex: 1;
}

.admin-category-stats {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background-color: #f9fafb;
    border-radius: 0.5rem;
}

.admin-category-product-count {
    font-size: 0.875rem;
    color: #374151;
    font-weight: 500;
}

.admin-category-status {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.admin-status-active {
    background-color: #d1fae5;
    color: #065f46;
}

.admin-status-inactive {
    background-color: #fee2e2;
    color: #991b1b;
}

.admin-category-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: auto;
}

/* Modal and form styles */
.admin-modal {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease;
    overflow-y: auto;
    padding: 1rem;
}

.admin-modal-show {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    opacity: 1;
    padding-top: 2rem;
    padding-bottom: 2rem;
}

.admin-modal-content {
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    margin: auto;
}

/* Alert styles */
.admin-alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.admin-alert-success {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.admin-alert-error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

/* Stats cards */
.admin-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.admin-stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.admin-stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
}

.admin-stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-content {
        padding: 1rem;
    }
    
    .admin-categories-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-category-actions {
        flex-direction: column;
    }
    
    .admin-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="admin-content">
    <div class="admin-header">
        <div class="admin-header-content">
            <div>
                <h1 class="admin-title">Manage Categories</h1>
                <p class="admin-subtitle">Organize your furniture products into categories</p>
            </div>
            <button onclick="showModal('add-category-modal')" class="admin-btn admin-btn-primary">
                Add New Category
            </button>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="admin-alert admin-alert-success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="admin-alert admin-alert-error">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="admin-stats-grid">
        <?php
        $total_categories = count($categories);
        $active_categories = count(array_filter($categories, function($cat) { return $cat['is_active']; }));
        $total_products = array_sum(array_column($categories, 'product_count'));
        ?>
        <div class="admin-stat-card">
            <div class="admin-stat-number"><?php echo $total_categories; ?></div>
            <div class="admin-stat-label">Total Categories</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-number"><?php echo $active_categories; ?></div>
            <div class="admin-stat-label">Active Categories</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-number"><?php echo $total_products; ?></div>
            <div class="admin-stat-label">Total Products</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-number"><?php echo $total_categories - $active_categories; ?></div>
            <div class="admin-stat-label">Inactive Categories</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="admin-card admin-filters">
        <form method="GET" class="admin-filter-form">
            <div class="admin-filter-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Search categories..." class="admin-input admin-search-input">
                
                <select name="status" class="admin-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active Only</option>
                    <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive Only</option>
                </select>
                
                <button type="submit" class="admin-btn admin-btn-secondary">Filter</button>
                <a href="categories.php" class="admin-btn admin-btn-outline">Clear</a>
            </div>
        </form>
    </div>

    <!-- Categories Grid -->
    <div class="admin-grid admin-categories-grid">
        <?php foreach ($categories as $category): ?>
            <div class="admin-card admin-category-card">
                <div class="admin-category-image">
                    <?php if (!empty($category['image_url'])): ?>
                        <img src="<?php echo getCategoryImage($category); ?>"
                             alt="<?php echo htmlspecialchars($category['name']); ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="admin-category-placeholder" style="display: none;">📂</div>
                    <?php else: ?>
                        <div class="admin-category-placeholder">📂</div>
                    <?php endif; ?>
                </div>
                
                <div class="admin-category-content">
                    <h3 class="admin-category-name">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </h3>
                    
                    <p class="admin-category-description">
                        <?php echo !empty($category['description']) 
                            ? htmlspecialchars(substr($category['description'], 0, 100)) . (strlen($category['description']) > 100 ? '...' : '')
                            : 'No description provided.'; ?>
                    </p>
                    
                    <div class="admin-category-stats">
                        <span class="admin-category-product-count">
                            <?php echo $category['product_count']; ?> Products
                        </span>
                        <span class="admin-category-status admin-status-<?php echo $category['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    
                    <div class="admin-category-actions">
                        <a href="?edit=<?php echo $category['id']; ?>" class="admin-btn admin-btn-sm admin-btn-secondary">
                            Edit
                        </a>
                        <a href="products.php?category=<?php echo $category['id']; ?>" 
                           class="admin-btn admin-btn-sm admin-btn-outline">
                            View Products
                        </a>
                        <form method="POST" class="admin-inline-form" 
                              onsubmit="return confirm('Are you sure? Categories with products will be deactivated instead of deleted.')"
                              style="display: inline;">
                            <input type="hidden" name="action" value="delete_category">
                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($categories)): ?>
        <div class="admin-empty-state">
            <div class="admin-empty-icon">📂</div>
            <h2 class="admin-empty-title">No categories found</h2>
            <p class="admin-empty-text">
                <?php if ($search || $status_filter): ?>
                    Try adjusting your filters to see more categories.
                <?php else: ?>
                    Create your first category to organize your products.
                <?php endif; ?>
            </p>
            <?php if (!$search && !$status_filter): ?>
                <button onclick="showModal('add-category-modal')" class="admin-btn admin-btn-primary">
                    Add First Category
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Category Modal -->
<div id="add-category-modal" class="admin-modal <?php echo $edit_category ? 'admin-modal-show' : ''; ?>">
    <div class="admin-modal-content">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">
                <?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?>
            </h3>
            <button onclick="hideModal('add-category-modal')" class="admin-modal-close">
                &times;
            </button>
        </div>
        
        <form method="POST" class="admin-modal-form">
            <input type="hidden" name="action" value="<?php echo $edit_category ? 'update_category' : 'add_category'; ?>">
            <?php if ($edit_category): ?>
                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
            <?php endif; ?>
            
            <div class="admin-form-group">
                <label class="admin-label">Category Name *</label>
                <input type="text" name="name" required
                       value="<?php echo htmlspecialchars($edit_category['name'] ?? ''); ?>"
                       placeholder="e.g., Sofas, Chairs, Tables"
                       class="admin-input">
            </div>
            
            <div class="admin-form-group">
                <label class="admin-label">Description</label>
                <textarea name="description" rows="4" class="admin-textarea"
                          placeholder="Describe what products belong in this category..."><?php echo htmlspecialchars($edit_category['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-label">Category Image URL</label>
                <input type="text" name="image_url"
                       value="<?php echo htmlspecialchars($edit_category['image_url'] ?? ''); ?>"
                       placeholder="e.g., category-sofas.jpg OR https://example.com/image.jpg"
                       class="admin-input">
                <p class="admin-help-text">Enter filename for local images (place in /images/ folder) OR full URL for external images</p>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-checkbox">
                    <input type="checkbox" name="is_active"
                           <?php echo ($edit_category['is_active'] ?? 1) ? 'checked' : ''; ?>>
                    <span class="admin-checkbox-text">Active (visible to customers)</span>
                </label>
            </div>
            
            <div class="admin-modal-actions">
                <button type="button" onclick="hideModal('add-category-modal')" class="admin-btn admin-btn-outline">
                    Cancel
                </button>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <?php echo $edit_category ? 'Update Category' : 'Add Category'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../js/admin.js"></script>

<?php if ($edit_category): ?>
<script>
    // Show modal if editing
    showModal('add-category-modal');
</script>
<?php endif; ?>

<script>
// Clear URL parameters after form submission
if (window.location.search.includes('edit=')) {
    <?php if (!$edit_category): ?>
    window.history.replaceState({}, document.title, window.location.pathname);
    <?php endif; ?>
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.admin-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// Image preview functionality
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.querySelector('input[name="image_url"]');
    if (imageInput) {
        imageInput.addEventListener('blur', function() {
            // You could add image preview functionality here
        });
    }
});
</script>

<?php require_once 'footer.php'; ?>
