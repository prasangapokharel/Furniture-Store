<?php
require_once 'include/session.php';
require_once 'include/functions.php';

$product_id = $_GET['id'] ?? 0;
if (!$product_id) {
    header('Location: products.php');
    exit();
}

// Get product details
$conn = getConnection();
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit();
}

$pageTitle = htmlspecialchars($product['name']) . ' - Pink Home';
require_once 'include/header.php';

// Get product images
$stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
$stmt->execute([$product_id]);
$product_images = $stmt->fetchAll();

// Get related products
$stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();

// Handle add to cart
if ($_POST['action'] ?? '' === 'add_to_cart') {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    $user_id = getCurrentUserId();
    
    // Check if item already in cart
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing_item = $stmt->fetch();
    
    if ($existing_item) {
        // Update quantity
        $new_quantity = $existing_item['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$new_quantity, $user_id, $product_id]);
    } else {
        // Add new item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    
    $success_message = "Product added to cart successfully!";
}
?>

<link rel="stylesheet" href="css/styles.css">

<div class="theme-container" style="max-width: 1200px; margin: 0 auto; padding: 32px 20px;">
    <!-- Breadcrumb -->
    <nav style="margin-bottom: 32px;">
        <ol style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem;" class="theme-text">
            <li><a href="index.php" class="theme-link">Home</a></li>
            <li>/</li>
            <li><a href="products.php" class="theme-link">Products</a></li>
            <li>/</li>
            <li><a href="products.php?category=<?php echo $product['category_id']; ?>" class="theme-link"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
            <li>/</li>
            <li class="theme-text"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>

    <?php if (isset($success_message)): ?>
        <div style="background-color: #dcfce7; border: 1px solid #16a34a; color: #15803d; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px;">
        <!-- Product Images -->
        <div>
            <div style="margin-bottom: 16px;">
                <img id="main-image" src="<?php echo getProductImage($product); ?>"
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     style="width: 100%; height: 400px; object-fit: cover; border-radius: 8px; cursor: zoom-in; transition: transform 0.3s ease;"
                     onerror="this.src='images/placeholder.jpg'">
            </div>
            
            <?php if (!empty($product_images) || $product['image_url']): ?>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                    <img src="<?php echo getProductImage($product); ?>"
                         alt="Main image"
                         class="thumbnail"
                         style="width: 100%; height: 80px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 2px solid var(--medium-green);"
                         onclick="changeMainImage(this.src)">
                    <?php foreach ($product_images as $image): ?>
                        <?php if (filter_var($image['image_url'], FILTER_VALIDATE_URL)): ?>
                            <img src="<?php echo $image['image_url']; ?>"
                                 alt="Product image"
                                 class="thumbnail"
                                 style="width: 100%; height: 80px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 2px solid transparent; transition: border-color 0.3s ease;"
                                 onclick="changeMainImage(this.src)"
                                 onmouseover="this.style.borderColor='var(--medium-green)'"
                                 onmouseout="this.style.borderColor='transparent'">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Details -->
        <div>
            <div style="margin-bottom: 16px;">
                <span class="theme-text" style="font-size: 0.875rem; color: var(--medium-green); font-weight: 500;"><?php echo htmlspecialchars($product['category_name']); ?></span>
                <h1 class="theme-heading" style="font-size: 2rem; margin-top: 8px;"><?php echo htmlspecialchars($product['name']); ?></h1>
            </div>

            <div style="margin-bottom: 24px;">
                <span class="theme-heading" style="font-size: 2.5rem; color: var(--medium-green);"><?php echo formatPrice($product['price']); ?></span>
                <?php if ($product['stock_quantity'] <= 5): ?>
                    <p style="color: #ef4444; font-size: 0.875rem; margin-top: 4px;">Only <?php echo $product['stock_quantity']; ?> left in stock!</p>
                <?php else: ?>
                    <p style="color: var(--medium-green); font-size: 0.875rem; margin-top: 4px;">In Stock</p>
                <?php endif; ?>
            </div>

            <div style="margin-bottom: 24px;">
                <p class="theme-text" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <!-- Product Specifications -->
            <div class="theme-card" style="margin-bottom: 24px;">
                <h3 class="theme-subheading" style="margin-bottom: 12px;">Specifications</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 0.875rem;">
                    <div>
                        <span class="theme-text" style="color: var(--dark-green);">Dimensions:</span>
                        <span class="theme-text" style="font-weight: 500;"><?php echo htmlspecialchars($product['dimensions']); ?></span>
                    </div>
                    <div>
                        <span class="theme-text" style="color: var(--dark-green);">Material:</span>
                        <span class="theme-text" style="font-weight: 500;"><?php echo htmlspecialchars($product['material']); ?></span>
                    </div>
                    <div>
                        <span class="theme-text" style="color: var(--dark-green);">Color:</span>
                        <span class="theme-text" style="font-weight: 500;"><?php echo htmlspecialchars($product['color']); ?></span>
                    </div>
                    <?php if ($product['weight']): ?>
                    <div>
                        <span class="theme-text" style="color: var(--dark-green);">Weight:</span>
                        <span class="theme-text" style="font-weight: 500;"><?php echo $product['weight']; ?> kg</span>
                    </div>
                    <?php endif; ?>
                    <div>
                        <span class="theme-text" style="color: var(--dark-green);">Assembly:</span>
                        <span class="theme-text" style="font-weight: 500;"><?php echo $product['assembly_required'] ? 'Required' : 'Pre-assembled'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Add to Cart Form -->
            <?php if ($product['stock_quantity'] > 0): ?>
                <form method="POST" style="margin-bottom: 24px;">
                    <input type="hidden" name="action" value="add_to_cart">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                        <label class="theme-text" style="font-weight: 500;">Quantity:</label>
                        <select name="quantity" style="padding: 8px 12px; border: 2px solid var(--light-green); border-radius: 6px; outline: none;">
                            <?php for ($i = 1; $i <= min(10, $product['stock_quantity']); $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                        <button type="submit" class="theme-button" style="width: 100%; padding: 12px 24px; font-size: 1.125rem; margin-bottom: 16px;">
                            Add to Cart
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="theme-button" style="display: block; width: 100%; padding: 12px 24px; font-size: 1.125rem; text-align: center; text-decoration: none; margin-bottom: 16px;">
                            Login to Add to Cart
                        </a>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <div style="background-color: #f3f4f6; color: #6b7280; padding: 12px 24px; border-radius: 8px; text-align: center; margin-bottom: 24px;">
                    Out of Stock
                </div>
            <?php endif; ?>

            <!-- Care Instructions -->
            <?php if ($product['care_instructions']): ?>
                <div style="background-color: var(--pale-green); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                    <h3 class="theme-subheading" style="margin-bottom: 8px;">Care Instructions</h3>
                    <p class="theme-text" style="font-size: 0.875rem;"><?php echo nl2br(htmlspecialchars($product['care_instructions'])); ?></p>
                </div>
            <?php endif; ?>

            <!-- Delivery Information -->
            <div style="background-color: var(--light-green); padding: 16px; border-radius: 8px;">
                <h3 class="theme-subheading" style="margin-bottom: 8px;">🚚 Delivery Information</h3>
                <ul class="theme-text" style="font-size: 0.875rem; line-height: 1.6;">
                    <li>• Free delivery on orders over Rs 50,000</li>
                    <li>• Standard delivery: 5-7 business days</li>
                    <li>• Assembly service available for additional fee</li>
                    <li>• White glove delivery available</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div style="margin-top: 64px;">
            <h2 class="theme-heading" style="font-size: 1.5rem; margin-bottom: 32px;">Related Products</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px;">
                <?php foreach ($related_products as $related): ?>
                    <div class="theme-card" style="overflow: hidden; transition: all 0.3s ease;">
                        <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="theme-link" style="text-decoration: none;">
                            <img src="<?php echo getProductImage($related); ?>"
                                 alt="<?php echo htmlspecialchars($related['name']); ?>"
                                 style="width: 100%; height: 200px; object-fit: cover; margin-bottom: 16px; border-radius: 6px;"
                                 onerror="this.src='images/placeholder.jpg'">
                            <div>
                                <h3 class="theme-subheading" style="margin-bottom: 8px;"><?php echo htmlspecialchars($related['name']); ?></h3>
                                <p class="theme-heading" style="color: var(--medium-green);"><?php echo formatPrice($related['price']); ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function changeMainImage(src) {
    document.getElementById('main-image').src = src;
    
    // Update thumbnail borders
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.style.borderColor = 'transparent';
    });
    
    // Add border to clicked thumbnail
    event.target.style.borderColor = 'var(--medium-green)';
}

// Image zoom functionality
document.getElementById('main-image').addEventListener('click', function() {
    if (this.style.transform === 'scale(1.5)') {
        this.style.transform = 'scale(1)';
        this.style.cursor = 'zoom-in';
    } else {
        this.style.transform = 'scale(1.5)';
        this.style.cursor = 'zoom-out';
    }
});
</script>

<?php require_once 'include/footer.php'; ?>