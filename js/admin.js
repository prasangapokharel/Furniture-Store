// Admin Panel JavaScript

// Modal functions
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Trigger animation
        setTimeout(() => {
            modal.classList.add('admin-modal-show');
        }, 10);
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('admin-modal-show');
        document.body.style.overflow = '';
        
        // Hide after animation
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('admin-modal')) {
        const modalId = e.target.id;
        hideModal(modalId);
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.admin-modal.admin-modal-show');
        if (openModal) {
            hideModal(openModal.id);
        }
    }
});

// Confirmation dialogs
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-auto-hide');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 3500);
    });
});

// Form validation helpers
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = 'var(--admin-danger)';
            isValid = false;
        } else {
            field.style.borderColor = 'var(--admin-border)';
        }
    });
    
    return isValid;
}

// Image preview functionality
function previewImage(input, previewId) {
    const file = input.files[0];
    const preview = document.getElementById(previewId);
    
    if (file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

// Search functionality
function initializeSearch() {
    const searchInputs = document.querySelectorAll('input[name="search"]');
    
    searchInputs.forEach(input => {
        let timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                // Auto-submit search after 500ms of no typing
                if (this.value.length >= 3 || this.value.length === 0) {
                    this.form.submit();
                }
            }, 500);
        });
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    
    // Add loading states to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
            }
        });
    });
    
    // Add hover effects to cards
    const cards = document.querySelectorAll('.admin-product-card, .admin-category-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = 'var(--admin-shadow-lg)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--admin-shadow)';
        });
    });
});

// Utility functions
function formatPrice(price) {
    return new Intl.NumberFormat('en-NP', {
        style: 'currency',
        currency: 'NPR'
    }).format(price);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `admin-alert admin-alert-${type} alert-auto-hide`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '1000';
    notification.style.minWidth = '300px';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3500);
}

// Export functions for global use
window.showModal = showModal;
window.hideModal = hideModal;
window.confirmDelete = confirmDelete;
window.validateForm = validateForm;
window.previewImage = previewImage;
window.showNotification = showNotification;