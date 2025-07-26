<?php
$pageTitle = 'Shopping Cart - Pink Home';
require_once 'include/session.php';
require_once 'include/functions.php';
requireLogin();
require_once 'include/header.php';

$user_id = getCurrentUserId();
$conn = getConnection();

// Handle cart actions
if ($_POST['action'] ?? '' === 'update_cart') {
    $cart_id = $_POST['cart_id'];
    $quantity = max(1, intval($_POST['quantity']));
    
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$quantity, $cart_id, $user_id]);
    
    $success_message = "Cart updated successfully!";
}

if ($_POST['action'] ?? '' === 'remove_item') {
    $cart_id = $_POST['cart_id'];
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    
    $success_message = "Item removed from cart!";
}

// Get cart items with complete product data for image function
$stmt = $conn->prepare("
    SELECT c.*, p.id, p.name, p.price, p.image_url, p.stock_quantity, p.dimensions, p.material 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ? 
    ORDER BY c.added_at DESC
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$delivery_fee = calculateDeliveryFee($subtotal);
$total = $subtotal + $delivery_fee;
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

.modal-icon.danger {
    background-color: #fee2e2;
    color: #dc2626;
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

.modal-btn-danger {
    background-color: #dc2626;
    color: white;
}

.modal-btn-danger:hover {
    background-color: #b91c1c;
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

@media (max-width: 768px) {
    .confirmation-modal {
        left: 1rem;
        right: 1rem;
    }
    
    .modal-actions {
        flex-direction: column;
    }
}
</style>

<div class="theme-container" style="max-width: 1200px; margin: 0 auto; padding: 32px 20px;">
    <h1 class="theme-heading" style="font-size: 2rem; margin-bottom: 32px;">Shopping Cart</h1>

    <?php if (isset($success_message)): ?>
        <div style="background-color: #dcfce7; border: 1px solid #16a34a; color: #15803d; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div style="text-align: center; padding: 48px 0;">
            <div style="font-size: 4rem; margin-bottom: 16px;">🛒</div>
            <h2 class="theme-heading" style="font-size: 1.5rem; margin-bottom: 16px;">Your cart is empty</h2>
            <p class="theme-text" style="margin-bottom: 32px;">Looks like you haven't added any furniture to your cart yet.</p>
            <a href="products.php" class="theme-button" style="padding: 12px 32px; text-decoration: none; border-radius: 8px;">
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
            <!-- Cart Items -->
            <div>
                <div class="theme-card">
                    <div style="padding: 16px; border-bottom: 2px solid var(--light-green);">
                        <h2 class="theme-subheading" style="font-size: 1.125rem;">Cart Items (<?php echo count($cart_items); ?>)</h2>
                    </div>
                    
                    <?php foreach ($cart_items as $item): ?>
                        <div style="padding: 24px; border-bottom: 1px solid var(--light-green);">
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <img src="<?php echo getProductImage($item); ?>"
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;"
                                     onerror="this.src='images/placeholder.jpg'">
                                
                                <div style="flex: 1;">
                                    <h3 class="theme-subheading" style="font-size: 1.125rem;">
                                        <a href="product-detail.php?id=<?php echo $item['id']; ?>" class="theme-link">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                    </h3>
                                    <p class="theme-text" style="font-size: 0.875rem; margin-top: 4px;">
                                        📏 <?php echo htmlspecialchars($item['dimensions']); ?> | 
                                        🏗️ <?php echo htmlspecialchars($item['material']); ?>
                                    </p>
                                    <p class="theme-heading" style="font-size: 1.125rem; margin-top: 8px; color: var(--medium-green);">
                                        <?php echo formatPrice($item['price']); ?>
                                    </p>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <!-- Quantity Update -->
                                    <form method="POST" style="display: flex; align-items: center; gap: 8px;">
                                        <input type="hidden" name="action" value="update_cart">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <label class="theme-text" style="font-size: 0.875rem;">Qty:</label>
                                        <select name="quantity" onchange="this.form.submit()"
                                                style="padding: 4px 8px; border: 2px solid var(--light-green); border-radius: 4px; outline: none;">
                                            <?php for ($i = 1; $i <= min(10, $item['stock_quantity']); $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $i == $item['quantity'] ? 'selected' : ''; ?>>
                                                    <?php echo $i; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </form>
                                    
                                    <!-- Remove Item -->
                                    <button type="button" onclick="showRemoveModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')" 
                                            style="color: #ef4444; background: none; border: none; cursor: pointer; font-size: 0.875rem; padding: 4px 8px; border-radius: 4px; transition: background-color 0.2s;"
                                            onmouseover="this.style.backgroundColor='#fef2f2'"
                                            onmouseout="this.style.backgroundColor='transparent'">
                                        🗑️ Remove
                                    </button>
                                </div>
                            </div>
                            
                            <div style="margin-top: 16px; text-align: right;">
                                <span class="theme-heading" style="font-size: 1.125rem;">
                                    Subtotal: <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Order Summary -->
            <div>
                <div class="theme-card" style="position: sticky; top: 96px;">
                    <h2 class="theme-subheading" style="font-size: 1.125rem; margin-bottom: 16px;">Order Summary</h2>
                    
                    <div style="margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span class="theme-text">Subtotal</span>
                            <span class="theme-text" style="font-weight: 600;"><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span class="theme-text">Delivery Fee</span>
                            <span class="theme-text" style="font-weight: 600;">
                                <?php echo $delivery_fee > 0 ? formatPrice($delivery_fee) : 'FREE'; ?>
                            </span>
                        </div>
                        <?php if ($delivery_fee > 0 && $subtotal < 50000): ?>
                            <p class="theme-text" style="font-size: 0.875rem; color: var(--medium-green); margin-top: 8px;">
                                Add <?php echo formatPrice(50000 - $subtotal); ?> more for free delivery!
                            </p>
                        <?php endif; ?>
                        <hr style="margin: 16px 0; border-color: var(--light-green);">
                        <div style="display: flex; justify-content: space-between; font-size: 1.125rem;">
                            <span class="theme-heading">Total</span>
                            <span class="theme-heading" style="color: var(--medium-green);"><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>
                    
                    <a href="checkout.php" class="theme-button" style="width: 100%; padding: 12px 24px; text-decoration: none; text-align: center; display: block; margin-bottom: 16px;">
                        Proceed to Checkout
                    </a>
                    
                    <a href="products.php" class="theme-link" style="width: 100%; padding: 12px 24px; border: 2px solid var(--medium-green); text-decoration: none; text-align: center; display: block; border-radius: 5px;">
                        Continue Shopping
                    </a>
                    
                    <!-- Delivery Information -->
                    <div style="margin-top: 24px; background-color: var(--light-green); padding: 16px; border-radius: 8px;">
                        <h3 class="theme-subheading" style="margin-bottom: 8px;">🚚 Delivery Info</h3>
                        <ul class="theme-text" style="font-size: 0.875rem; line-height: 1.6; list-style: none; padding: 0;">
                            <li style="margin-bottom: 4px;">• Free delivery over Rs 50,000</li>
                            <li style="margin-bottom: 4px;">• 5-7 business days</li>
                            <li>• Assembly service available</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Overlay -->
<div id="modalOverlay" class="modal-overlay"></div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="confirmation-modal">
    <div class="modal-handle"></div>
    
    <div class="modal-header">
        <div id="modalIcon" class="modal-icon danger">
            <svg id="modalIconSvg" class="icon-large" viewBox="0 0 24 24">
                <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14zM10 11v6M14 11v6"/>
            </svg>
        </div>
        <h3 id="modalTitle" class="modal-title">Remove Item</h3>
        <p id="modalMessage" class="modal-message">Are you sure you want to remove this item from your cart?</p>
    </div>
    
    <div class="modal-actions">
        <button id="cancelBtn" class="modal-btn modal-btn-secondary">Cancel</button>
        <button id="confirmBtn" class="modal-btn modal-btn-danger">Remove</button>
    </div>
</div>

<!-- Hidden form for removal -->
<form id="removeForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="remove_item">
    <input type="hidden" name="cart_id" id="removeCartId">
</form>

<script>
const modalOverlay = document.getElementById('modalOverlay');
const confirmationModal = document.getElementById('confirmationModal');
const cancelBtn = document.getElementById('cancelBtn');
const confirmBtn = document.getElementById('confirmBtn');
const removeForm = document.getElementById('removeForm');
const removeCartId = document.getElementById('removeCartId');
const modalMessage = document.getElementById('modalMessage');

let currentCartId = null;

function showRemoveModal(cartId, itemName) {
    currentCartId = cartId;
    modalMessage.textContent = `Are you sure you want to remove "${itemName}" from your cart?`;
    
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
        currentCartId = null;
    }, 300);
}

// Event listeners
cancelBtn.addEventListener('click', hideModal);
modalOverlay.addEventListener('click', hideModal);

confirmBtn.addEventListener('click', () => {
    if (currentCartId) {
        removeCartId.value = currentCartId;
        removeForm.submit();
    }
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

// Add loading state to quantity selects
document.querySelectorAll('select[name="quantity"]').forEach(select => {
    select.addEventListener('change', function() {
        this.style.opacity = '0.6';
        this.style.pointerEvents = 'none';
        
        // Add loading text
        const loadingOption = document.createElement('option');
        loadingOption.textContent = 'Updating...';
        loadingOption.selected = true;
        this.appendChild(loadingOption);
    });
});
</script>

<?php require_once 'include/footer.php'; ?>