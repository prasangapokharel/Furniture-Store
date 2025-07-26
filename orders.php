<?php
$pageTitle = 'Order History - Pink Home';
require_once 'include/session.php';
require_once 'include/functions.php';
requireLogin();
require_once 'include/header.php';

$user_id = getCurrentUserId();
$conn = getConnection();

// Get all orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Get order details if requested
$order_details = null;
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    
    // Get order
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order_details = $stmt->fetch();
    
    if ($order_details) {
        // Get order items with complete product data for image function
        $stmt = $conn->prepare("
            SELECT oi.*, 
                   p.id, p.name, p.image_url, p.dimensions, p.material 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll();
    }
}
?>

<link rel="stylesheet" href="css/styles.css">

<div class="theme-container" style="max-width: 1200px; margin: 0 auto; padding: 32px 20px;">
    <div style="margin-bottom: 32px;">
        <h1 class="theme-heading" style="font-size: 2rem;">Order History</h1>
        <p class="theme-text">Track your orders and view purchase history</p>
    </div>

    <?php if (isset($_GET['success']) && isset($_GET['order_id'])): ?>
        <div style="background-color: #dcfce7; border: 1px solid #16a34a; color: #15803d; padding: 24px; border-radius: 8px; margin-bottom: 32px;">
            <div style="display: flex; align-items: center;">
                <span style="font-size: 2rem; margin-right: 12px;">✅</span>
                <div>
                    <h3 class="theme-subheading">Order Placed Successfully!</h3>
                    <p class="theme-text">Your order #<?php echo htmlspecialchars($_GET['order_id']); ?> has been received and is being processed.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 48px 0;">
            <div style="font-size: 4rem; margin-bottom: 16px;">📦</div>
            <h2 class="theme-heading" style="font-size: 1.5rem; margin-bottom: 16px;">No orders yet</h2>
            <p class="theme-text" style="margin-bottom: 32px;">You haven't placed any orders yet. Start shopping to see your orders here.</p>
            <a href="products.php" class="theme-button" style="padding: 12px 32px; text-decoration: none; border-radius: 8px;">
                Start Shopping
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
            <!-- Orders List -->
            <div>
                <div style="display: grid; gap: 24px;">
                    <?php foreach ($orders as $order): ?>
                        <div class="theme-card">
                            <div style="padding: 16px; border-bottom: 2px solid var(--light-green);">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div>
                                        <h3 class="theme-subheading" style="font-size: 1.125rem;">Order #<?php echo $order['id']; ?></h3>
                                        <p class="theme-text" style="font-size: 0.875rem;">
                                            Placed on <?php echo date('M j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                                        </p>
                                        <?php if ($order['delivery_date']): ?>
                                            <p class="theme-text" style="font-size: 0.875rem;">
                                                Expected delivery: <?php echo date('M j, Y', strtotime($order['delivery_date'])); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div style="text-align: right;">
                                        <p class="theme-heading" style="font-size: 1.25rem; color: var(--medium-green);"><?php echo formatPrice($order['total_amount']); ?></p>
                                        <span style="display: inline-block; padding: 4px 12px; font-size: 0.75rem; border-radius: 20px; font-weight: 500;
                                            <?php
                                            switch($order['status']) {
                                                case 'pending': echo 'background-color: #fef3c7; color: #92400e;'; break;
                                                case 'confirmed': echo 'background-color: #dbeafe; color: #1e40af;'; break;
                                                case 'shipped': echo 'background-color: #e9d5ff; color: #7c3aed;'; break;
                                                case 'delivered': echo 'background-color: #dcfce7; color: #16a34a;'; break;
                                                case 'cancelled': echo 'background-color: #fecaca; color: #dc2626;'; break;
                                            }
                                            ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="padding: 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div class="theme-text" style="font-size: 0.875rem;">
                                        <?php if ($order['delivery_address']): ?>
                                            <p><strong>Delivery to:</strong> <?php echo htmlspecialchars(substr($order['delivery_address'], 0, 50)) . '...'; ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <a href="?order_id=<?php echo $order['id']; ?>" class="theme-link" style="font-weight: 500; font-size: 0.875rem;">
                                        View Details →
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Order Details Sidebar -->
            <div>
                <?php if ($order_details): ?>
                    <div class="theme-card" style="position: sticky; top: 96px;">
                        <h2 class="theme-subheading" style="font-size: 1.125rem; margin-bottom: 16px;">Order #<?php echo $order_details['id']; ?></h2>
                        
                        <!-- Order Status -->
                        <div style="margin-bottom: 24px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <span class="theme-text" style="font-weight: 500;">Status</span>
                                <span style="padding: 4px 12px; font-size: 0.75rem; border-radius: 20px; font-weight: 500;
                                    <?php
                                    switch($order_details['status']) {
                                        case 'pending': echo 'background-color: #fef3c7; color: #92400e;'; break;
                                        case 'confirmed': echo 'background-color: #dbeafe; color: #1e40af;'; break;
                                        case 'shipped': echo 'background-color: #e9d5ff; color: #7c3aed;'; break;
                                        case 'delivered': echo 'background-color: #dcfce7; color: #16a34a;'; break;
                                        case 'cancelled': echo 'background-color: #fecaca; color: #dc2626;'; break;
                                    }
                                    ?>">
                                    <?php echo ucfirst($order_details['status']); ?>
                                </span>
                            </div>
                            
                            <!-- Order Progress -->
                            <div style="margin-top: 16px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; color: var(--dark-green); margin-bottom: 8px;">
                                    <span>Order Placed</span>
                                    <span>Processing</span>
                                    <span>Shipped</span>
                                    <span>Delivered</span>
                                </div>
                                <div style="width: 100%; background-color: #e5e7eb; border-radius: 4px; height: 8px;">
                                    <div style="background-color: var(--medium-green); height: 8px; border-radius: 4px; width:
                                        <?php
                                        switch($order_details['status']) {
                                            case 'pending': echo '25%'; break;
                                            case 'confirmed': echo '50%'; break;
                                            case 'shipped': echo '75%'; break;
                                            case 'delivered': echo '100%'; break;
                                            default: echo '25%';
                                        }
                                        ?>;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div style="margin-bottom: 24px;">
                            <h3 class="theme-subheading" style="margin-bottom: 12px;">Items Ordered</h3>
                            <div style="display: grid; gap: 12px;">
                                <?php if (isset($order_items)): ?>
                                    <?php foreach ($order_items as $item): ?>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <img src="<?php echo getProductImage($item); ?>"
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                 style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;"
                                                 onerror="this.src='images/placeholder.jpg'">
                                            <div style="flex: 1;">
                                                <h4 class="theme-text" style="font-size: 0.875rem; font-weight: 500;"><?php echo htmlspecialchars($item['name']); ?></h4>
                                                <p class="theme-text" style="font-size: 0.75rem;">Qty: <?php echo $item['quantity']; ?></p>
                                            </div>
                                            <span class="theme-text" style="font-size: 0.875rem; font-weight: 500;"><?php echo formatPrice($item['unit_price'] * $item['quantity']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div style="border-top: 2px solid var(--light-green); padding-top: 16px;">
                            <div style="display: grid; gap: 8px; font-size: 0.875rem;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span class="theme-text">Subtotal</span>
                                    <span class="theme-text"><?php echo formatPrice($order_details['total_amount'] - $order_details['delivery_fee']); ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span class="theme-text">Delivery Fee</span>
                                    <span class="theme-text"><?php echo $order_details['delivery_fee'] > 0 ? formatPrice($order_details['delivery_fee']) : 'FREE'; ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 1.125rem; border-top: 1px solid var(--light-green); padding-top: 8px;">
                                    <span class="theme-heading">Total</span>
                                    <span class="theme-heading" style="color: var(--medium-green);"><?php echo formatPrice($order_details['total_amount']); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Information -->
                        <?php if ($order_details['delivery_address']): ?>
                            <div style="margin-top: 24px; background-color: var(--pale-green); padding: 16px; border-radius: 8px;">
                                <h4 class="theme-subheading" style="margin-bottom: 8px;">Delivery Address</h4>
                                <p class="theme-text" style="font-size: 0.875rem;"><?php echo nl2br(htmlspecialchars($order_details['delivery_address'])); ?></p>
                                <?php if ($order_details['delivery_date']): ?>
                                    <p class="theme-text" style="font-size: 0.875rem; margin-top: 8px;">
                                        <strong>Expected:</strong> <?php echo date('M j, Y', strtotime($order_details['delivery_date'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="theme-card">
                        <div style="text-align: center;">
                            <div style="font-size: 3rem; margin-bottom: 16px;">📋</div>
                            <h3 class="theme-subheading" style="font-size: 1.125rem; margin-bottom: 8px;">Order Details</h3>
                            <p class="theme-text" style="font-size: 0.875rem;">Click on any order to view detailed information</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'include/footer.php'; ?>