// Main JavaScript file for Online Book Store

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileMenuClose = document.querySelector('.mobile-menu-close');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', function() {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (mobileMenu && mobileMenu.classList.contains('active') && 
            !mobileMenu.contains(e.target) && 
            !mobileMenuBtn.contains(e.target)) {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // Dropdown functionality
    document.querySelectorAll('.dropdown').forEach(function(dropdown) {
        const trigger = dropdown.querySelector('a, button');
        
        if (trigger) {
            trigger.addEventListener('click', function(e) {
                if (window.innerWidth < 992) {
                    e.preventDefault();
                    dropdown.classList.toggle('active');
                }
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    });
    
    // Back to top button
    const backToTopBtn = document.querySelector('.back-to-top');
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        });
        
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Quantity selectors
    document.querySelectorAll('.quantity-selector').forEach(function(selector) {
        const minusBtn = selector.querySelector('.qty-btn.minus');
        const plusBtn = selector.querySelector('.qty-btn.plus');
        const input = selector.querySelector('input[type="number"]');
        
        if (minusBtn && plusBtn && input) {
            minusBtn.addEventListener('click', function() {
                let value = parseInt(input.value) || 1;
                const min = parseInt(input.min) || 1;
                if (value > min) {
                    input.value = value - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
            
            plusBtn.addEventListener('click', function() {
                let value = parseInt(input.value) || 1;
                const max = parseInt(input.max) || 999;
                if (value < max) {
                    input.value = value + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
    });
    
    // Form validation
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Add error message
                    if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-message')) {
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.textContent = 'This field is required';
                        field.parentNode.insertBefore(errorMsg, field.nextSibling);
                    }
                } else {
                    field.classList.remove('error');
                    const errorMsg = field.nextElementSibling;
                    if (errorMsg && errorMsg.classList.contains('error-message')) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
            }
        });
    });
    
    // Image preview for file inputs
    document.querySelectorAll('input[type="file"]').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewId = e.target.dataset.preview;
            
            if (file && previewId) {
                const preview = document.getElementById(previewId);
                if (preview) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Global notification function
function showNotification(message, type = 'success') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-icon">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        </div>
        <div class="notification-content">${message}</div>
        <button class="notification-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Close button
    notification.querySelector('.notification-close').addEventListener('click', function() {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    });
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Add to cart function
function addToCart(bookId, quantity = 1) {
    fetch(`/api/cart.php?action=add&book_id=${bookId}&quantity=${quantity}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Book added to cart successfully!', 'success');
                updateCartCount(data.cartCount);
            } else {
                if (data.redirect) {
                    window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                } else {
                    showNotification(data.message, 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to add item to cart', 'error');
        });
}

// Update cart count in UI
function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count, .cart-badge');
    cartCountElements.forEach(element => {
        if (count > 0) {
            element.textContent = count;
            element.style.display = 'flex';
        } else {
            element.style.display = 'none';
        }
    });
}

// Format price
function formatPrice(price) {
    return '$' + parseFloat(price).toFixed(2);
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add CSS for notifications
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        padding: var(--space-md) var(--space-lg);
        display: flex;
        align-items: center;
        gap: var(--space-md);
        transform: translateX(120%);
        transition: transform 0.3s ease;
        z-index: 9999;
        max-width: 400px;
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification.success {
        border-left: 4px solid var(--success);
    }
    
    .notification.error {
        border-left: 4px solid var(--danger);
    }
    
    .notification.warning {
        border-left: 4px solid var(--warning);
    }
    
    .notification-icon {
        font-size: 1.5rem;
    }
    
    .notification.success .notification-icon {
        color: var(--success);
    }
    
    .notification.error .notification-icon {
        color: var(--danger);
    }
    
    .notification.warning .notification-icon {
        color: var(--warning);
    }
    
    .notification-content {
        flex: 1;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: var(--gray);
        cursor: pointer;
        padding: 0;
    }
    
    .error-message {
        color: var(--danger);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    input.error {
        border-color: var(--danger) !important;
    }
`;
document.head.appendChild(notificationStyles);

// Export global functions
window.addToCart = addToCart;
window.showNotification = showNotification;
window.formatPrice = formatPrice;