<script>
let currentModalItem = null;

function showProductDetails(title, description, price) {
    currentModalItem = { title, description, price };
    const modal = document.getElementById('productModal');
    const modalContent = document.getElementById('modalContent');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    const modalPrice = document.getElementById('modalPrice');
    
    modalTitle.textContent = title;
    modalDescription.textContent = description;
    modalPrice.textContent = `Rs. ${price}`;
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeProductModal() {
    const modal = document.getElementById('productModal');
    const modalContent = document.getElementById('modalContent');
    
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function addToCart(productName, price, imageUrl) {
    // Add to cart logic here
    console.log('Adding to cart:', productName, price);
    
    // Show success animation
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '✅ Added!';
    button.classList.add('bg-green-600');
    
    // Add to cart functionality
    if (typeof window.cartManager !== 'undefined' && window.cartManager.addItem) {
        window.cartManager.addItem({
            name: productName,
            price: price,
            image: imageUrl,
            quantity: 1
        });
    }
    
    // Show success notification
    if (typeof showSuccessNotification === 'function') {
        showSuccessNotification(`${productName} added to cart!`, 'View Cart', '/cart');
    }
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('bg-green-600');
    }, 2000);
}

function addToCartFromModal() {
    if (currentModalItem) {
        addToCart(currentModalItem.title, currentModalItem.price, '');
        closeProductModal();
    }
}

// Close modal on backdrop click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('productModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeProductModal();
            }
        });
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeProductModal();
    }
});

// Enhanced hover effects for product cards
document.addEventListener('DOMContentLoaded', function() {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        // Desktop hover effects
        card.addEventListener('mouseenter', function() {
            if (window.innerWidth > 768) {
                this.style.transform = 'translateY(-8px) scale(1.02)';
                this.style.boxShadow = '0 20px 40px rgba(0,0,0,0.15)';
                
                // Show badges on hover
                const badge = this.querySelector('.mobile-badge');
                if (badge) {
                    badge.style.opacity = '1';
                    badge.style.transform = 'translateY(0)';
                }
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (window.innerWidth > 768) {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
                
                // Hide badges on leave
                const badge = this.querySelector('.mobile-badge');
                if (badge) {
                    badge.style.opacity = '0';
                    badge.style.transform = 'translateY(4px)';
                }
            }
        });
        
        // Mobile touch effects
        card.addEventListener('touchstart', function() {
            if (window.innerWidth <= 768) {
                this.style.transform = 'translateY(-4px) scale(1.01)';
                this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.15)';
                
                // Show badges on touch
                const badge = this.querySelector('.mobile-badge');
                if (badge) {
                    badge.style.opacity = '1';
                    badge.style.transform = 'translateY(0)';
                }
            }
        });
        
        card.addEventListener('touchend', function() {
            if (window.innerWidth <= 768) {
                setTimeout(() => {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
                    
                    // Hide badges after touch
                    const badge = this.querySelector('.mobile-badge');
                    if (badge) {
                        badge.style.opacity = '0';
                        badge.style.transform = 'translateY(4px)';
                    }
                }, 300);
            }
        });
    });
    
    // Mobile-specific enhancements
    if (window.innerWidth <= 768) {
        // Add touch feedback to all interactive elements
        const interactiveElements = document.querySelectorAll('.animated-text, .slide-content, .glow-on-hover');
        interactiveElements.forEach(element => {
            element.addEventListener('touchstart', function() {
                this.style.transform = this.style.transform + ' scale(0.98)';
            });
            
            element.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.style.transform = this.style.transform.replace(' scale(0.98)', '');
                }, 150);
            });
        });
    }
});
</script>

<style>
/* Enhanced image hover effects */
.product-card img {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.product-card:hover img {
    transform: scale(1.1);
    filter: brightness(1.1);
}

/* Mobile responsive */
@media (max-width: 1024px) {
    .product-card {
        margin-bottom: 2rem;
    }
    
    .product-card img {
        height: 200px;
    }
}

@media (max-width: 768px) {
    .product-card img {
        height: 180px;
    }
    
    .product-card h2 {
        font-size: 1.5rem;
    }
}

/* Mobile-specific badge animations */
@media (max-width: 768px) {
    .mobile-badge {
        opacity: 0;
        transform: translateY(4px);
        transition: all 0.3s ease;
    }
    
    .mobile-badge.show {
        opacity: 1;
        transform: translateY(0);
    }
    
    .product-card:active .mobile-badge {
        opacity: 1;
        transform: translateY(0);
    }
}
</style> 