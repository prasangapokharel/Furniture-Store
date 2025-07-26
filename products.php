<?php
$pageTitle = 'All Products - Pink Home';
require_once __DIR__ . '/include/functions.php';
require_once __DIR__ . '/include/session.php';
require_once __DIR__ . '/include/header.php';

$conn = getConnection();

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Build query conditions
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($category) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category;
}

if ($min_price) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $min_price;
}

if ($max_price) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $max_price;
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Sort options
$order_clause = "ORDER BY ";
switch ($sort) {
    case 'price_low':
        $order_clause .= "p.price ASC";
        break;
    case 'price_high':
        $order_clause .= "p.price DESC";
        break;
    case 'name':
        $order_clause .= "p.name ASC";
        break;
    case 'newest':
    default:
        $order_clause .= "p.created_at DESC";
        break;
}

// Get products
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $where_clause 
    $order_clause
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<link rel="stylesheet" href="css/styles.css">

<div class="theme-container" style="max-width: 1200px; margin: 0 auto; padding: 32px 20px;">
    <div style="margin-bottom: 32px;">
        <h1 class="theme-heading" style="font-size: 2rem;">All Furniture</h1>
        <p class="theme-text">Discover our complete collection of modern furniture</p>
    </div>

    <!-- Filters -->
    <div class="theme-card" style="margin-bottom: 32px;">
        <form method="GET" style="display: grid; gap: 16px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <!-- Search -->
                <div>
                    <label class="theme-text" style="display: block; font-weight: 500; margin-bottom: 4px;">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search products..."
                           style="width: 100%; padding: 8px 12px; border: 2px solid var(--light-green); border-radius: 6px; outline: none;">
                </div>
                
                <!-- Category -->
                <div>
                    <label class="theme-text" style="display: block; font-weight: 500; margin-bottom: 4px;">Category</label>
                    <select name="category" style="width: 100%; padding: 8px 12px; border: 2px solid var(--light-green); border-radius: 6px; outline: none;">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Price Range -->
                <div>
                    <label class="theme-text" style="display: block; font-weight: 500; margin-bottom: 4px;">Min Price (Rs)</label>
                    <input type="number" name="min_price" value="<?php echo htmlspecialchars($min_price); ?>"
                           placeholder="0"
                           style="width: 100%; padding: 8px 12px; border: 2px solid var(--light-green); border-radius: 6px; outline: none;">
                </div>
                
                <div>
                    <label class="theme-text" style="display: block; font-weight: 500; margin-bottom: 4px;">Max Price (Rs)</label>
                    <input type="number" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>"
                           placeholder="999999"
                           style="width: 100%; padding: 8px 12px; border: 2px solid var(--light-green); border-radius: 6px; outline: none;">
                </div>
                
                <!-- Sort -->
                <div>
                    <label class="theme-text" style="display: block; font-weight: 500; margin-bottom: 4px;">Sort By</label>
                    <select name="sort" style="width: 100%; padding: 8px 12px; border: 2px solid var(--light-green); border-radius: 6px; outline: none;">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; gap: 16px;">
                <button type="submit" class="theme-button" style="padding: 8px 24px; border: none; border-radius: 6px; cursor: pointer;">
                    Apply Filters
                </button>
                <a href="products.php" class="theme-link" style="padding: 8px 24px; border: 2px solid var(--medium-green); border-radius: 6px; text-decoration: none; display: inline-block;">
                    Clear All
                </a>
            </div>
        </form>
    </div>

    <!-- Results -->
    <div style="margin-bottom: 24px;">
        <p class="theme-text">
            Showing <?php echo count($products); ?> product<?php echo count($products) != 1 ? 's' : ''; ?>
            <?php if ($search): ?>
                for "<?php echo htmlspecialchars($search); ?>"
            <?php endif; ?>
        </p>
    </div>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 48px 0;">
            <div style="font-size: 4rem; margin-bottom: 16px;">🔍</div>
            <h2 class="theme-heading" style="font-size: 1.5rem; margin-bottom: 16px;">No products found</h2>
            <p class="theme-text" style="margin-bottom: 32px;">Try adjusting your search criteria or browse all categories.</p>
            <a href="products.php" class="theme-button" style="padding: 12px 32px; text-decoration: none; border-radius: 8px;">
                View All Products
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px;">
            <?php foreach ($products as $product): ?>
                <div class="theme-card" style="overflow: hidden; transition: all 0.3s ease;">
                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="theme-link" style="text-decoration: none;">
                        <div style="position: relative; overflow: hidden; margin-bottom: 16px;">
                            <img src="<?php echo getProductImage($product); ?>"
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 style="width: 100%; height: 200px; object-fit: cover; transition: transform 0.3s ease;"
                                 onmouseover="this.style.transform='scale(1.05)'"
                                 onmouseout="this.style.transform='scale(1)'"
                                 onerror="this.src='images/placeholder.jpg'">
                            <?php if ($product['stock_quantity'] <= 2 && $product['stock_quantity'] > 0): ?>
                                <span style="position: absolute; top: 8px; left: 8px; background-color: #ef4444; color: white; padding: 4px 8px; font-size: 0.75rem; border-radius: 4px;">Low Stock</span>
                            <?php endif; ?>
                            <?php if ($product['stock_quantity'] == 0): ?>
                                <div style="position: absolute; inset: 0; background-color: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center;">
                                    <span style="color: white; font-weight: 600;">Out of Stock</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                <h3 class="theme-subheading" style="font-size: 1.125rem; flex: 1;">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h3>
                                <span style="font-size: 0.75rem; color: var(--medium-green); background-color: var(--pale-green); padding: 4px 8px; border-radius: 4px; margin-left: 8px;">
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </span>
                            </div>
                            <p class="theme-text" style="font-size: 0.875rem; margin-bottom: 12px; line-height: 1.4;">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span class="theme-heading" style="font-size: 1.5rem; color: var(--medium-green);">
                                    <?php echo formatPrice($product['price']); ?>
                                </span>
                                <span class="theme-text" style="font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($product['material']); ?>
                                </span>
                            </div>
                            <div class="theme-text" style="font-size: 0.75rem; margin-bottom: 12px;">
                                📏 <?php echo htmlspecialchars($product['dimensions']); ?>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.875rem; color: <?php echo $product['stock_quantity'] > 0 ? 'var(--medium-green)' : '#ef4444'; ?>;">
                                    <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                </span>
                                <span class="theme-link" style="font-size: 0.875rem; font-weight: 500;">
                                    View Details →
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/include/footer.php'; ?>