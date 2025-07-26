<?php
$pageTitle = 'Manage Products - Admin Panel';
require_once '../include/session.php';
require_once '../include/functions.php';
requireAdmin();

$conn = getConnection();

// Handle product actions
if (isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $dimensions = sanitizeInput($_POST['dimensions'] ?? '');
    $material = sanitizeInput($_POST['material'] ?? '');
    $color = sanitizeInput($_POST['color'] ?? '');
    $weight = floatval($_POST['weight'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $image_url = sanitizeInput($_POST['image_url'] ?? '');
    $assembly_required = isset($_POST['assembly_required']) ? 1 : 0;
    $care_instructions = sanitizeInput($_POST['care_instructions'] ?? '');
    
    if (empty($name) || empty($price) || empty($category_id)) {
        $error_message = "Please fill in all required fields.";
    } else {
        try {
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, dimensions, material, color, weight, category_id, stock_quantity, image_url, assembly_required, care_instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $dimensions, $material, $color, $weight, $category_id, $stock_quantity, $image_url, $assembly_required, $care_instructions]);
            
            $product_id = $conn->lastInsertId();
            
            // Handle additional images
            if (!empty($_POST['additional_images'])) {
                $additional_images = array_filter(array_map('trim', explode("\n", $_POST['additional_images'])));
                foreach ($additional_images as $img_url) {
                    if (!empty($img_url)) {
                        $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, 0)");
                        $stmt->execute([$product_id, sanitizeInput($img_url)]);
                    }
                }
            }
            
            $conn->commit();
            $success_message = "Product added successfully!";
            
        } catch (Exception $e) {
            $conn->rollBack();
            $error_message = "Error adding product: " . $e->getMessage();
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'update_product') {
    $id = intval($_POST['id'] ?? 0);
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $dimensions = sanitizeInput($_POST['dimensions'] ?? '');
    $material = sanitizeInput($_POST['material'] ?? '');
    $color = sanitizeInput($_POST['color'] ?? '');
    $weight = floatval($_POST['weight'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $image_url = sanitizeInput($_POST['image_url'] ?? '');
    $assembly_required = isset($_POST['assembly_required']) ? 1 : 0;
    $care_instructions = sanitizeInput($_POST['care_instructions'] ?? '');
    
    if (empty($name) || empty($price) || empty($category_id) || empty($id)) {
        $error_message = "Please fill in all required fields.";
    } else {
        try {
            $conn->beginTransaction();
            
            // Update main product
            $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, dimensions = ?, material = ?, color = ?, weight = ?, category_id = ?, stock_quantity = ?, image_url = ?, assembly_required = ?, care_instructions = ? WHERE id = ?");
            $stmt->execute([$name, $description, $price, $dimensions, $material, $color, $weight, $category_id, $stock_quantity, $image_url, $assembly_required, $care_instructions, $id]);
            
            // Handle additional images - only if provided
            if (isset($_POST['additional_images'])) {
                // First, delete existing additional images
                $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ? AND is_primary = 0");
                $stmt->execute([$id]);
                
                // Add new additional images
                if (!empty($_POST['additional_images'])) {
                    $additional_images = array_filter(array_map('trim', explode("\n", $_POST['additional_images'])));
                    foreach ($additional_images as $img_url) {
                        if (!empty($img_url)) {
                            $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, 0)");
                            $stmt->execute([$id, sanitizeInput($img_url)]);
                        }
                    }
                }
            }
            
            $conn->commit();
            $success_message = "Product updated successfully!";
            
        } catch (Exception $e) {
            $conn->rollBack();
            $error_message = "Error updating product: " . $e->getMessage();
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $id = intval($_POST['id'] ?? 0);
    
    if (empty($id)) {
        $error_message = "Invalid product ID.";
    } else {
        try {
            $conn->beginTransaction();
            
            // Get product name for confirmation message
            $stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product_name = $stmt->fetchColumn();
            
            if (!$product_name) {
                $error_message = "Product not found.";
            } else {
                // Check if product has been ordered
                $stmt = $conn->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
                $stmt->execute([$id]);
                $order_count = $stmt->fetchColumn();
                
                if ($order_count > 0) {
                    // Don't delete, just mark as discontinued or hide
                    $stmt = $conn->prepare("UPDATE products SET stock_quantity = 0, name = CONCAT('[DISCONTINUED] ', name) WHERE id = ? AND name NOT LIKE '[DISCONTINUED]%'");
                    $stmt->execute([$id]);
                    $success_message = "Product '" . htmlspecialchars($product_name) . "' has been marked as discontinued (it has existing orders).";
                } else {
                    // Safe to delete - no orders exist
                    
                    // Delete product images first
                    $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
                    $stmt->execute([$id]);
                    
                    // Delete from cart items
                    $stmt = $conn->prepare("DELETE FROM cart WHERE product_id = ?");
                    $stmt->execute([$id]);
                    
                    // Delete product
                    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    $success_message = "Product '" . htmlspecialchars($product_name) . "' deleted successfully!";
                }
            }
            
            $conn->commit();
            
        } catch (Exception $e) {
            $conn->rollBack();
            $error_message = "Error deleting product: " . $e->getMessage();
        }
    }
}

// Get products with proper image data
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$stock_filter = $_GET['stock'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if ($stock_filter === 'low') {
    $where_conditions[] = "p.stock_quantity <= 5 AND p.stock_quantity > 0";
} elseif ($stock_filter === 'out') {
    $where_conditions[] = "p.stock_quantity = 0";
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $where_clause 
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for dropdown
$stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

// Get product for editing
$edit_product = null;
$additional_images = [];
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_product = $stmt->fetch();
    
    if ($edit_product) {
        // Get additional images
        $stmt = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_primary = 0 ORDER BY id");
        $stmt->execute([$edit_id]);
        $additional_images = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

require_once 'header.php';
?>

<link rel="stylesheet" href="../css/admin.css">

<style>
/* Fix scrolling and layout issues */
.admin-content {
    flex: 1;
    padding: 2rem;
    background-color: #f9fafb;
    min-height: calc(100vh - 80px);
    overflow-y: auto;
    max-height: calc(100vh - 80px);
}

.admin-grid {
    display: grid;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.admin-products-grid {
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
}

.admin-product-card {
    display: flex;
    flex-direction: column;
    height: auto;
    min-height: 400px;
}

.admin-product-image {
    position: relative;
    height: 200px;
    overflow: hidden;
    flex-shrink: 0;
}

.admin-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.admin-product-content {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.admin-product-actions {
    margin-top: auto;
    padding-top: 1rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Modal fixes */
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
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    margin: auto;
}

.admin-modal-large {
    max-width: 800px;
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

/* Responsive fixes */
@media (max-width: 768px) {
    .admin-content {
        padding: 1rem;
        min-height: calc(100vh - 60px);
        max-height: calc(100vh - 60px);
    }
    
    .admin-products-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-modal {
        padding: 0.5rem;
    }
    
    .admin-modal-show {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    
    .admin-product-actions {
        flex-direction: column;
    }
}
</style>

<div class="admin-content">
    <div class="admin-header">
        <div class="admin-header-content">
            <div>
                <h1 class="admin-title">Manage Products</h1>
                <p class="admin-subtitle">Add, edit, and manage your furniture inventory</p>
            </div>
            <button onclick="showModal('add-product-modal')" class="admin-btn admin-btn-primary">
                Add New Product
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

    <!-- Filters -->
    <div class="admin-card admin-filters">
        <form method="GET" class="admin-filter-form">
            <div class="admin-filter-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Search products..." class="admin-input admin-search-input">
                
                <select name="category" class="admin-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="stock" class="admin-select">
                    <option value="">All Stock Levels</option>
                    <option value="low" <?php echo $stock_filter == 'low' ? 'selected' : ''; ?>>Low Stock (≤5)</option>
                    <option value="out" <?php echo $stock_filter == 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
                
                <button type="submit" class="admin-btn admin-btn-secondary">Filter</button>
                <a href="products.php" class="admin-btn admin-btn-outline">Clear</a>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="admin-grid admin-products-grid">
        <?php foreach ($products as $product): ?>
            <div class="admin-card admin-product-card">
                <div class="admin-product-image">
                    <img src="<?php echo getProductImage($product); ?>"
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         onerror="this.src='../images/placeholder.jpg'">
                    
                    <?php if ($product['stock_quantity'] <= 5): ?>
                        <span class="admin-stock-badge admin-stock-<?php echo $product['stock_quantity'] == 0 ? 'out' : 'low'; ?>">
                            <?php echo $product['stock_quantity'] == 0 ? 'Out of Stock' : 'Low Stock'; ?>
                        </span>
                    <?php endif; ?>
                    
                    <span class="admin-category-badge">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </span>
                </div>
                
                <div class="admin-product-content">
                    <h3 class="admin-product-name">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h3>
                    
                    <p class="admin-product-description">
                        <?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 80)) . '...'; ?>
                    </p>
                    
                    <div class="admin-product-price-stock">
                        <span class="admin-product-price">
                            <?php echo formatPrice($product['price']); ?>
                        </span>
                        <span class="admin-product-stock <?php echo $product['stock_quantity'] <= 5 ? 'admin-stock-warning' : ''; ?>">
                            Stock: <?php echo $product['stock_quantity']; ?>
                        </span>
                    </div>
                    
                    <div class="admin-product-meta">
                        <span class="admin-meta-item">📏 <?php echo htmlspecialchars($product['dimensions'] ?? 'N/A'); ?></span>
                        <span class="admin-meta-item">🏗️ <?php echo htmlspecialchars($product['material'] ?? 'N/A'); ?></span>
                    </div>
                    
                    <div class="admin-product-actions">
                        <a href="?edit=<?php echo $product['id']; ?>" class="admin-btn admin-btn-sm admin-btn-secondary">
                            Edit
                        </a>
                        <a href="../product-detail.php?id=<?php echo $product['id']; ?>" 
                           class="admin-btn admin-btn-sm admin-btn-outline" target="_blank">
                            View
                        </a>
                        <form method="POST" class="admin-inline-form" 
                              onsubmit="return confirm('Are you sure you want to delete this product? Products with existing orders will be marked as discontinued.')"
                              style="display: inline;">
                            <input type="hidden" name="action" value="delete_product">
                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($products)): ?>
        <div class="admin-empty-state">
            <div class="admin-empty-icon">📦</div>
            <h2 class="admin-empty-title">No products found</h2>
            <p class="admin-empty-text">
                <?php if ($search || $category_filter || $stock_filter): ?>
                    Try adjusting your filters to see more products.
                <?php else: ?>
                    Add your first product to get started.
                <?php endif; ?>
            </p>
            <?php if (!$search && !$category_filter && !$stock_filter): ?>
                <button onclick="showModal('add-product-modal')" class="admin-btn admin-btn-primary">
                    Add First Product
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Product Modal -->
<div id="add-product-modal" class="admin-modal <?php echo $edit_product ? 'admin-modal-show' : ''; ?>">
    <div class="admin-modal-content admin-modal-large">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">
                <?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?>
            </h3>
            <button onclick="hideModal('add-product-modal')" class="admin-modal-close">
                &times;
            </button>
        </div>
        
        <form method="POST" class="admin-modal-form">
            <input type="hidden" name="action" value="<?php echo $edit_product ? 'update_product' : 'add_product'; ?>">
            <?php if ($edit_product): ?>
                <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
            <?php endif; ?>
            
            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label class="admin-label">Product Name *</label>
                    <input type="text" name="name" required
                           value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>"
                           class="admin-input">
                </div>
                <div class="admin-form-group">
                    <label class="admin-label">Category *</label>
                    <select name="category_id" required class="admin-select">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"
                                    <?php echo ($edit_product['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-label">Description</label>
                <textarea name="description" rows="3" class="admin-textarea"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="admin-form-row admin-form-row-3">
                <div class="admin-form-group">
                    <label class="admin-label">Price * (Rs)</label>
                    <input type="number" name="price" step="0.01" required
                           value="<?php echo $edit_product['price'] ?? ''; ?>"
                           class="admin-input">
                </div>
                <div class="admin-form-group">
                    <label class="admin-label">Stock Quantity</label>
                    <input type="number" name="stock_quantity"
                           value="<?php echo $edit_product['stock_quantity'] ?? '0'; ?>"
                           class="admin-input">
                </div>
                <div class="admin-form-group">
                    <label class="admin-label">Weight (kg)</label>
                    <input type="number" name="weight" step="0.1"
                           value="<?php echo $edit_product['weight'] ?? ''; ?>"
                           class="admin-input">
                </div>
            </div>
            
            <div class="admin-form-row admin-form-row-3">
                <div class="admin-form-group">
                    <label class="admin-label">Dimensions</label>
                    <input type="text" name="dimensions" placeholder="L x W x H cm"
                           value="<?php echo htmlspecialchars($edit_product['dimensions'] ?? ''); ?>"
                           class="admin-input">
                </div>
                <div class="admin-form-group">
                    <label class="admin-label">Material</label>
                    <input type="text" name="material"
                           value="<?php echo htmlspecialchars($edit_product['material'] ?? ''); ?>"
                           class="admin-input">
                </div>
                <div class="admin-form-group">
                    <label class="admin-label">Color</label>
                    <input type="text" name="color"
                           value="<?php echo htmlspecialchars($edit_product['color'] ?? ''); ?>"
                           class="admin-input">
                </div>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-label">Primary Image URL *</label>
                <input type="text" name="image_url" required
                       value="<?php echo htmlspecialchars($edit_product['image_url'] ?? ''); ?>"
                       placeholder="e.g., pink-sofa.jpg OR https://example.com/image.jpg"
                       class="admin-input">
                <p class="admin-help-text">Enter filename for local images (place in /images/ folder) OR full URL for external images</p>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-label">Additional Images (Optional)</label>
                <textarea name="additional_images" rows="3" class="admin-textarea"
                          placeholder="Enter image URLs or filenames, one per line&#10;e.g.:&#10;pink-sofa-2.jpg&#10;https://example.com/image.jpg"><?php echo implode("\n", $additional_images); ?></textarea>
                <p class="admin-help-text">One image per line - can be local filenames or full URLs</p>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-label">Care Instructions</label>
                <textarea name="care_instructions" rows="2" class="admin-textarea"><?php echo htmlspecialchars($edit_product['care_instructions'] ?? ''); ?></textarea>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-checkbox">
                    <input type="checkbox" name="assembly_required"
                           <?php echo ($edit_product['assembly_required'] ?? 1) ? 'checked' : ''; ?>>
                    <span class="admin-checkbox-text">Assembly Required</span>
                </label>
            </div>
            
            <div class="admin-modal-actions">
                <button type="button" onclick="hideModal('add-product-modal')" class="admin-btn admin-btn-outline">
                    Cancel
                </button>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../js/admin.js"></script>

<?php if ($edit_product): ?>
<script>
    // Show modal if editing
    showModal('add-product-modal');
</script>
<?php endif; ?>

<script>
// Clear URL parameters after form submission to prevent resubmission
if (window.location.search.includes('edit=')) {
    // Only clear if not showing edit modal
    <?php if (!$edit_product): ?>
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
</script>

<?php require_once 'footer.php'; ?>
