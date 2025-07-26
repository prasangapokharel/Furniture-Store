<?php
$pageTitle = 'Checkout - Pink Home';
require_once __DIR__ . '/include/session.php';
require_once __DIR__ . '/include/functions.php';
requireLogin();

$user_id = getCurrentUserId();
$conn = getConnection();

// Get cart items with complete product data for image function
$stmt = $conn->prepare("
    SELECT c.*, p.id, p.name, p.price, p.image_url 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$delivery_fee = calculateDeliveryFee($subtotal);
$total = $subtotal + $delivery_fee;

// Get user info
$user = getCurrentUser();

// Handle order submission BEFORE including header
if ($_POST['action'] ?? '' === 'place_order') {
    $delivery_address = sanitizeInput($_POST['delivery_address']);
    $delivery_date = $_POST['delivery_date'];
    $payment_method = $_POST['payment_method'];
    
    if (empty($delivery_address) || empty($delivery_date) || empty($payment_method)) {
        $error_message = "Please fill in all required fields.";
    } else {
        try {
            $conn->beginTransaction();
            
            // Create order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, delivery_fee, delivery_address, delivery_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $total, $delivery_fee, $delivery_address, $delivery_date]);
            $order_id = $conn->lastInsertId();
            
            // Add order items
            foreach ($cart_items as $item) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
                
                // Update stock
                $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['id']]);
            }
            
            // Clear cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $conn->commit();
            
            // Redirect to success page BEFORE any output
            header("Location: orders.php?success=1&order_id=$order_id");
            exit();
            
        } catch (Exception $e) {
            $conn->rollBack();
            $error_message = "Error processing order. Please try again.";
        }
    }
}

// Include header AFTER processing form
require_once __DIR__ . '/include/header.php';
?>

<link rel="stylesheet" href="css/styles.css">

<style>
/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 40;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-overlay.show {
    opacity: 1;
}

.confirmation-modal {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: white;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
    box-shadow: 0 -10px 25px -3px rgba(0, 0, 0, 0.1);
    transform: translateY(100%);
    transition: transform 0.3s ease-in-out;
    z-index: 50;
    max-width: 500px;
    margin: 0 auto;
}

.confirmation-modal.show {
    transform: translateY(0);
}

.modal-handle {
    width: 40px;
    height: 4px;
    background-color: #d1d5db;
    border-radius: 2px;
    margin: 0.75rem auto 0;
}

.modal-header {
    padding: 1.5rem 1.5rem 1rem;
    text-align: center;
}

.modal-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.modal-icon.info {
    background-color: #dbeafe;
    color: #2563eb;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.modal-message {
    color: #6b7280;
    line-height: 1.5;
    font-size: 0.875rem;
}

.modal-actions {
    padding: 1rem 1.5rem 1.5rem;
    display: flex;
    gap: 0.75rem;
}

.modal-btn {
    flex: 1;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.modal-btn-primary {
    background-color: #000;
    color: white;
}

.modal-btn-primary:hover {
    background-color: #374151;
}

.modal-btn-secondary {
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.modal-btn-secondary:hover {
    background-color: #e5e7eb;
}

.icon-large {
    width: 1.5rem;
    height: 1.5rem;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

body.no-scroll {
    overflow: hidden;
}
</style>

<div class="theme-container" style="max-width: 800px; margin: 0 auto; padding: 32px 20px;">
    <h1 class="theme-heading" style="font-size: 2rem; margin-bottom: 32px;">Checkout</h1>

    <?php if (isset($error_message)): ?>
        <div style="background-color: #fef2f2; border: 1px solid #ef4444; color: #dc2626; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
        <!-- Checkout Form -->
        <div>
            <form id="checkoutForm" method="POST" style="display: grid; gap: 24px;">
                <input type="hidden" name="action" value="place_order">
                
                <!-- Customer Information -->
                <div class="theme-card">
                    <h2 class="theme-subheading" style="font-size: 1.125rem; margin-bottom: 16px;">Customer Information</h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label class="theme-text" style="display: block; font-weight: 500; margin-bottom: 8px;">Full Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                   style="width: 100%; padding: 8px 12px; border: 2px solid var(--light-green); border-radius: 6px; background-color: #f9fafb;" readonly>
                        </div>
                        <div>
                            <label class="theme-text" style="display: block; font-weight: 500; margin-bottom: 8px;">Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                   style="width: 100%; padding: 8px 12px; border: 2px solid var(--light-green); border-radius: 6px; background-color: #f9fafb;" readonly>
                        </div>
                        <div style="grid-column: span 2;">
                            <label class="theme-text" style="display: block; font-weight: 500; margin-bottom: 8px;">Phone</label>
                            <input type="tel" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                   style="width: 100%; padding: 8px 12px; border: 2px solid var(--light-green); border-radius: 6px; background-color: #f9fafb;" readonly>
                        </div>
                    </div>
                </div>

                <!-- Delivery Information -->
                <div class="theme-card">
                    <h2 class="theme-subheading" style="font-size: 1.125rem; margin-bottom: 16px;">Delivery Information</h2>
                    <div style="display: grid; gap: 16px;">
                        <div>
                            <label class="theme-text" style="display: block; font-weight: 500; margin-bottom: 8px;">Delivery Address *</label>
                            <textarea name="delivery_address" rows="3" required
                                      placeholder="Enter your complete delivery address"
                                      style="width: 100%; padding: 8px 12px; border: 2px solid var(--light-green); border-radius: 6px; outline: none; resize: vertical;"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        <div>
                            <label class="theme-text" style="display: block; font-weight: 500; margin-bottom: 8px;">Preferred Delivery Date *</label>
                            <input type="date" name="delivery_date" required
                                   min="<?php echo date('Y-m-d', strtotime('+3 days')); ?>"
                                   style="width: 100%; padding: 8px 12px; border: 2px solid var(--light-green); border-radius: 6px; outline: none;">
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="theme-card">
                    <h2 class="theme-subheading" style="font-size: 1.125rem; margin-bottom: 16px;">Payment Method</h2>
                    <div style="display: grid; gap: 12px;">
                        <label style="display: flex; align-items: center; padding: 12px; border: 2px solid var(--light-green); border-radius: 6px; cursor: pointer;">
                            <input type="radio" name="payment_method" value="credit_card" required style="margin-right: 12px;">
                            <span class="theme-text">💳 Credit/Debit Card</span>
                        </label>
                        <label style="display: flex; align-items: center; padding: 12px; border: 2px solid var(--light-green); border-radius: 6px; cursor: pointer;">
                            <input type="radio" name="payment_method" value="paypal" required style="margin-right: 12px;">
                            <span class="theme-text">🅿️ PayPal</span>
                        </label>
                        <label style="display: flex; align-items: center; padding: 12px; border: 2px solid var(--light-green); border-radius: 6px; cursor: pointer;">
                            <input type="radio" name="payment_method" value="bank_transfer" required style="margin-right: 12px;">
                            <span class="theme-text">🏦 Bank Transfer</span>
                        </label>
                        <label style="display: flex; align-items: center; padding: 12px; border: 2px solid var(--light-green); border-radius: 6px; cursor: pointer;">
                            <input type="radio" name="payment_method" value="cash_on_delivery" required style="margin-right: 12px;">
                            <span class="theme-text">💵 Cash on Delivery</span>
                        </label>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="theme-card">
                    <label style="display: flex; align-items: flex-start;">
                        <input type="checkbox" required style="margin-top: 4px; margin-right: 12px;">
                        <span class="theme-text" style="font-size: 0.875rem;">
                            I agree to the <a href="#" class="theme-link">Terms and Conditions</a>
                            and <a href="#" class="theme-link">Privacy Policy</a>
                        </span>
                    </label>
                </div>

                <button type="button" onclick="showOrderModal()" class="theme-button" style="width: 100%; padding: 16px 24px; font-size: 1.125rem;">
                    Place Order - <?php echo formatPrice($total); ?>
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div>
            <div class="theme-card" style="position: sticky; top: 96px;">
                <h2 class="theme-subheading" style="font-size: 1.125rem; margin-bottom: 16px;">Order Summary</h2>
                
                <!-- Order Items -->
                <div style="margin-bottom: 24px;">
                    <?php foreach ($cart_items as $item): ?>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <img src="<?php echo getProductImage($item); ?>"
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;"
                                 onerror="this.src='images/placeholder.jpg'">
                            <div style="flex: 1;">
                                <h4 class="theme-text" style="font-size: 0.875rem; font-weight: 500;"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p class="theme-text" style="font-size: 0.75rem;">Qty: <?php echo $item['quantity']; ?></p>
                            </div>
                            <span class="theme-text" style="font-size: 0.875rem; font-weight: 500;"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Totals -->
                <div style="border-top: 2px solid var(--light-green); padding-top: 16px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span class="theme-text">Subtotal</span>
                        <span class="theme-text"><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span class="theme-text">Delivery Fee</span>
                        <span class="theme-text"><?php echo $delivery_fee > 0 ? formatPrice($delivery_fee) : 'FREE'; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.125rem; border-top: 1px solid var(--light-green); padding-top: 8px;">
                        <span class="theme-heading">Total</span>
                        <span class="theme-heading" style="color: var(--medium-green);"><?php echo formatPrice($total); ?></span>
                    </div>
                </div>
                
                <!-- Security Notice -->
                <div style="margin-top: 24px; background-color: #dcfce7; padding: 12px; border-radius: 6px;">
                    <div style="display: flex; align-items: center;">
                        <span style="color: #16a34a; margin-right: 8px;">🔒</span>
                        <span class="theme-text" style="font-size: 0.875rem; color: #15803d;">Your payment information is secure and encrypted</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Overlay -->
<div id="modalOverlay" class="modal-overlay"></div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="confirmation-modal">
    <div class="modal-handle"></div>
    
    <div class="modal-header">
        <div id="modalIcon" class="modal-icon info">
            <svg id="modalIconSvg" class="icon-large" viewBox="0 0 24 24">
                <path d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13v-1a3 3 0 116 0v1m-6 0h6"/>
            </svg>
        </div>
        <h3 id="modalTitle" class="modal-title">Confirm Order</h3>
        <p id="modalMessage" class="modal-message">You are about to place an order for <?php echo formatPrice($total); ?>. Do you want to proceed with the purchase?</p>
    </div>
    
    <div class="modal-actions">
        <button id="cancelBtn" class="modal-btn modal-btn-secondary">Cancel</button>
        <button id="confirmBtn" class="modal-btn modal-btn-primary">Place Order</button>
    </div>
</div>

<script>
const modalOverlay = document.getElementById('modalOverlay');
const confirmationModal = document.getElementById('confirmationModal');
const cancelBtn = document.getElementById('cancelBtn');
const confirmBtn = document.getElementById('confirmBtn');
const checkoutForm = document.getElementById('checkoutForm');

function showOrderModal() {
    // Validate form first
    if (!checkoutForm.checkValidity()) {
        checkoutForm.reportValidity();
        return;
    }
    
    // Show modal
    modalOverlay.style.display = 'block';
    document.body.classList.add('no-scroll');
    
    // Trigger animations
    setTimeout(() => {
        modalOverlay.classList.add('show');
        confirmationModal.classList.add('show');
    }, 10);
}

function hideModal() {
    modalOverlay.classList.remove('show');
    confirmationModal.classList.remove('show');
    document.body.classList.remove('no-scroll');
    
    setTimeout(() => {
        modalOverlay.style.display = 'none';
    }, 300);
}

// Event listeners
cancelBtn.addEventListener('click', hideModal);
modalOverlay.addEventListener('click', hideModal);

confirmBtn.addEventListener('click', () => {
    checkoutForm.submit();
    hideModal();
});

// Prevent modal from closing when clicking inside it
confirmationModal.addEventListener('click', (e) => {
    e.stopPropagation();
});

// Handle escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && confirmationModal.classList.contains('show')) {
        hideModal();
    }
});
</script>

<?php require_once __DIR__ . '/include/footer.php'; ?>