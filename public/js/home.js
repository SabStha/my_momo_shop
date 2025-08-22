// Home Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize home page functionality
    initializeHomePage();
    
    // Start flash sale timer
    startFlashSaleTimer();
    
    // Initialize counter animations
    initializeCounters();
    
    // Initialize mobile-specific features
    initializeMobileFeatures();
    
    // Initialize hero carousel
    initializeHeroCarousel();
});

// Global variables
let quickOrderItems = [];
let flashSaleEndTime = new Date().getTime() + (2 * 60 * 60 * 1000); // 2 hours from now
let isMobile = window.innerWidth < 768;

// Carousel variables
let currentSlide = 0;
let totalSlides = 0;
let carouselInterval = null;
let isCarouselPaused = false;
let carouselAutoPlay = true;

// Initialize hero carousel
function initializeHeroCarousel() {
    const carousel = document.getElementById('hero-carousel');
    if (!carousel) return;
    
    const slides = carousel.querySelectorAll('.carousel-slide');
    totalSlides = slides.length;
    
    if (totalSlides <= 1) return;
    
    // Start auto-rotation
    startCarouselAutoPlay();
    
    // Add touch/swipe support
    addCarouselTouchSupport();
    
    // Add keyboard navigation
    addCarouselKeyboardSupport();
    
    // Add visibility change handling
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            pauseCarousel();
        } else {
            resumeCarousel();
        }
    });
}

// Start carousel auto-play
function startCarouselAutoPlay() {
    if (carouselInterval) clearInterval(carouselInterval);
    
    carouselInterval = setInterval(() => {
        if (!isCarouselPaused && carouselAutoPlay) {
            nextSlide();
        }
    }, 5000); // 5 seconds
}

// Pause carousel
function pauseCarousel() {
    isCarouselPaused = true;
    updatePauseButton();
}

// Resume carousel
function resumeCarousel() {
    isCarouselPaused = false;
    updatePauseButton();
}

// Toggle carousel play/pause
function toggleCarousel() {
    if (isCarouselPaused) {
        resumeCarousel();
    } else {
        pauseCarousel();
    }
}

// Update pause button appearance
function updatePauseButton() {
    const pauseIcon = document.getElementById('pause-icon');
    const playIcon = document.getElementById('play-icon');
    
    if (!pauseIcon || !playIcon) return;
    
    if (isCarouselPaused) {
        pauseIcon.classList.add('hidden');
        playIcon.classList.remove('hidden');
    } else {
        pauseIcon.classList.remove('hidden');
        playIcon.classList.add('hidden');
    }
}

// Go to specific slide
function goToSlide(slideIndex) {
    if (slideIndex < 0 || slideIndex >= totalSlides) return;
    
    currentSlide = slideIndex;
    updateCarousel();
    updateCarouselDots();
    
    // Reset auto-play timer
    if (carouselAutoPlay) {
        startCarouselAutoPlay();
    }
}

// Go to next slide
function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    updateCarousel();
    updateCarouselDots();
}

// Go to previous slide
function previousSlide() {
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    updateCarousel();
    updateCarouselDots();
}

// Update carousel position
function updateCarousel() {
    const carousel = document.getElementById('hero-carousel');
    if (!carousel) return;
    
    const translateX = -currentSlide * 100;
    carousel.style.transform = `translateX(${translateX}%)`;
}

// Update carousel dots
function updateCarouselDots() {
    const dots = document.querySelectorAll('.carousel-dot');
    dots.forEach((dot, index) => {
        if (index === currentSlide) {
            dot.classList.add('bg-white');
            dot.classList.remove('bg-white/50');
        } else {
            dot.classList.remove('bg-white');
            dot.classList.add('bg-white/50');
        }
    });
}

// Add touch/swipe and mouse drag support for carousel
function addCarouselTouchSupport() {
    const carousel = document.getElementById('hero-carousel');
    if (!carousel) return;
    
    // Touch support (mobile)
    let startX = 0;
    let startY = 0;
    let isDragging = false;
    
    carousel.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        isDragging = false;
        pauseCarousel();
    });
    
    carousel.addEventListener('touchmove', function(e) {
        if (!startX || !startY) return;
        const diffX = startX - e.touches[0].clientX;
        const diffY = startY - e.touches[0].clientY;
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 10) {
            isDragging = true;
            e.preventDefault();
        }
    });
    
    carousel.addEventListener('touchend', function(e) {
        if (!isDragging) {
            resumeCarousel();
            return;
        }
        const diffX = startX - e.changedTouches[0].clientX;
        if (Math.abs(diffX) > 50) {
            if (diffX > 0) {
                nextSlide();
            } else {
                previousSlide();
            }
        }
        resumeCarousel();
        startX = 0;
        startY = 0;
        isDragging = false;
    });

    // Mouse drag support (desktop)
    let mouseDown = false;
    let mouseStartX = 0;
    let mouseDragging = false;

    carousel.addEventListener('mousedown', function(e) {
        mouseDown = true;
        mouseStartX = e.clientX;
        mouseDragging = false;
        pauseCarousel();
    });

    carousel.addEventListener('mousemove', function(e) {
        if (!mouseDown) return;
        const diffX = mouseStartX - e.clientX;
        if (Math.abs(diffX) > 10) {
            mouseDragging = true;
        }
    });

    carousel.addEventListener('mouseup', function(e) {
        if (!mouseDown) return;
        const diffX = mouseStartX - e.clientX;
        if (mouseDragging && Math.abs(diffX) > 50) {
            if (diffX > 0) {
                nextSlide();
            } else {
                previousSlide();
            }
        }
        mouseDown = false;
        mouseDragging = false;
        resumeCarousel();
    });

    // If mouse leaves the carousel while dragging, reset
    carousel.addEventListener('mouseleave', function(e) {
        mouseDown = false;
        mouseDragging = false;
        resumeCarousel();
    });
}

// Add keyboard navigation for carousel
function addCarouselKeyboardSupport() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            previousSlide();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
        }
    });
}

// Initialize home page
function initializeHomePage() {
    // Initialize mobile features
    initializeMobileFeatures();
    
    // Initialize counter animations
    initializeCounters();
    
    // Add touch feedback
    addTouchFeedback();
    
    // Handle mobile scroll
    handleMobileScroll();
    
    // Optimize images for mobile
    optimizeImagesForMobile();
    
    // Add mobile gestures
    addMobileGestures();
    
    // Update mobile layout
    updateMobileLayout();
}

// Initialize mobile features
function initializeMobileFeatures() {
    // Check if device is mobile
    isMobile = window.innerWidth < 768;
    
    // Add mobile-specific classes
    if (isMobile) {
        document.body.classList.add('mobile-device');
    }
    
    // Add touch feedback
    addTouchFeedback();
    
    // Handle mobile scroll
    handleMobileScroll();
    
    // Optimize images for mobile
    optimizeImagesForMobile();
    
    // Add mobile gestures
    addMobileGestures();
    
    // Update mobile layout
    updateMobileLayout();
}

// Add touch feedback for mobile
function addTouchFeedback() {
    if (!isMobile) return;
    
    document.querySelectorAll('button, .clickable').forEach(element => {
        element.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
            this.style.transition = 'transform 0.1s ease';
        });
        
        element.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
}

// Handle mobile scroll
function handleMobileScroll() {
    if (!isMobile) return;
    
    let lastScrollTop = 0;
    const navbar = document.querySelector('nav');
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scrolling down
            navbar.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up
            navbar.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    });
}

// Optimize images for mobile
function optimizeImagesForMobile() {
    if (!isMobile) return;
    
    document.querySelectorAll('img').forEach(img => {
        if (img.dataset.mobileSrc) {
            img.src = img.dataset.mobileSrc;
        }
    });
}

// Add mobile gestures
function addMobileGestures() {
    if (!isMobile) return;
    
    // Add swipe gestures for navigation
    let startX = 0;
    let startY = 0;
    
    document.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    });
    
    document.addEventListener('touchend', function(e) {
        if (!startX || !startY) return;
        
        const endX = e.changedTouches[0].clientX;
        const endY = e.changedTouches[0].clientY;
        
        const diffX = startX - endX;
        const diffY = startY - endY;
        
        // Horizontal swipe - removed console logs
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
            if (diffX > 0) {
                // Swipe left - could be used for next action
                // console.log('Swipe left detected');
            } else {
                // Swipe right - could be used for previous action
                // console.log('Swipe right detected');
            }
        }
        
        startX = 0;
        startY = 0;
    });
}

// Update mobile layout
function updateMobileLayout() {
    if (!isMobile) return;
    
    // Adjust font sizes for mobile
    document.querySelectorAll('h1').forEach(h1 => {
        h1.style.fontSize = '1.5rem';
    });
    
    document.querySelectorAll('h2').forEach(h2 => {
        h2.style.fontSize = '1.25rem';
    });
}

// Quick order functionality
function openQuickOrder() {
    const modal = document.getElementById('quick-order-modal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeQuickOrder() {
    const modal = document.getElementById('quick-order-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

function addToQuickOrder(itemName, price) {
    const existingItem = quickOrderItems.find(item => item.name === itemName);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        quickOrderItems.push({
            name: itemName,
            price: price,
            quantity: 1
        });
    }
    
    updateQuickOrderDisplay();
    
    // Show success message
    showCartToast(`${itemName} added to quick order!`);
}

function updateQuickOrderDisplay() {
    const itemsContainer = document.getElementById('quick-order-items');
    const totalElement = document.getElementById('quick-order-total');
    
    if (!itemsContainer || !totalElement) return;
    
    itemsContainer.innerHTML = '';
    let total = 0;
    
    quickOrderItems.forEach(item => {
        const itemElement = document.createElement('div');
        itemElement.className = 'flex justify-between items-center p-2 border-b border-gray-200';
        itemElement.innerHTML = `
            <div>
                <span class="font-semibold">${item.name}</span>
                <span class="text-gray-500 text-sm">x${item.quantity}</span>
            </div>
            <div class="flex items-center gap-2">
                                        <span class="font-semibold">${window.currencySymbol}${(item.price * item.quantity).toFixed(2)}</span>
                <button onclick="removeFromQuickOrder('${item.name}')" class="text-red-500 hover:text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        `;
        itemsContainer.appendChild(itemElement);
        
        total += item.price * item.quantity;
    });
    
                totalElement.textContent = `${window.currencySymbol}${total.toFixed(2)}`;
}

function removeFromQuickOrder(itemName) {
    quickOrderItems = quickOrderItems.filter(item => item.name !== itemName);
    updateQuickOrderDisplay();
}

function viewFullMenu() {
    window.location.href = '/menu';
}

function proceedToCheckout() {
    if (quickOrderItems.length === 0) {
        showErrorToast('Please add items to your order first');
        return;
    }
    
    // Here you would typically redirect to checkout or cart page
    console.log('Proceeding to checkout with items:', quickOrderItems);
    showCartToast('Redirecting to checkout...');
}

// Cart functionality
function homeAddToCart(productId) {
    // Simulate adding to cart
    console.log('Adding product to cart:', productId);
    
    // Show success message
    showCartToast('Item added to cart!');
    
    // Update cart count
    updateCartCount();
}

// Counter animations
function initializeCounters() {
    const counters = document.querySelectorAll('[data-counter]');
    
    counters.forEach(counter => {
        const target = parseInt(counter.dataset.counter);
        const duration = 2000; // 2 seconds
        const step = target / (duration / 16); // 60fps
        let current = 0;
        
        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        // Start animation when element is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(counter);
    });
}

// Update statistics in real-time
function updateStatistics() {
    fetch('/statistics')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Update statistics on the page
            document.querySelectorAll('[data-stat]').forEach(element => {
                const statType = element.dataset.stat;
                if (data[statType]) {
                    // Animate the number change
                    animateNumberChange(element, data[statType]);
                }
            });
        })
        .catch(error => {
            // Silently handle statistics errors - they're not critical for functionality
            console.debug('Statistics update failed (non-critical):', error.message);
        });
}

// Animate number changes
function animateNumberChange(element, newValue) {
    const currentText = element.textContent;
    const currentNumber = parseFloat(currentText.replace(/[^\d.]/g, ''));
    const newNumber = parseFloat(newValue.replace(/[^\d.]/g, ''));
    
    if (isNaN(currentNumber) || isNaN(newNumber) || currentNumber === newNumber) {
        return;
    }
    
    const duration = 1000; // 1 second
    const startTime = performance.now();
    const startNumber = currentNumber;
    const endNumber = newNumber;
    
    function animate(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function for smooth animation
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const currentNumber = startNumber + (endNumber - startNumber) * easeOutQuart;
        
        // Format the number based on the original format
        if (newValue.includes('+')) {
            element.textContent = Math.round(currentNumber) + '+';
        } else if (newValue.includes('⭐')) {
            element.textContent = currentNumber.toFixed(1) + '⭐';
        } else {
            element.textContent = currentNumber.toFixed(1);
        }
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    }
    
    requestAnimationFrame(animate);
}

// Update statistics every 5 minutes
setInterval(updateStatistics, 300000); // 5 minutes

// Update statistics on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initial statistics update after 2 seconds
    setTimeout(updateStatistics, 2000);
});

// Flash sale timer
function startFlashSaleTimer() {
    const timerElement = document.getElementById('flash-sale-timer');
    if (!timerElement) return;
    
    let hours = 2;
    let minutes = 0;
    let seconds = 0;
    
    const timer = setInterval(() => {
        if (seconds > 0) {
            seconds--;
        } else if (minutes > 0) {
            minutes--;
            seconds = 59;
        } else if (hours > 0) {
            hours--;
            minutes = 59;
            seconds = 59;
        } else {
            clearInterval(timer);
            timerElement.innerHTML = '<div class="text-red-400 font-bold">EXPIRED</div>';
            return;
        }
        
        const timeElements = timerElement.children;
        if (timeElements.length >= 3) {
            timeElements[0].textContent = hours.toString().padStart(2, '0');
            timeElements[1].textContent = minutes.toString().padStart(2, '0');
            timeElements[2].textContent = seconds.toString().padStart(2, '0');
        }
    }, 1000);
}

// Utility functions
function updateCartCount() {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        const currentCount = parseInt(cartCount.textContent) || 0;
        cartCount.textContent = currentCount + 1;
    }
}

function searchProducts(query) {
    console.log('Searching for:', query);
    // Implement search functionality
}

function showCartToast(message) {
    const toast = document.getElementById('cart-toast');
    if (toast) {
        toast.textContent = message;
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }
}

function hideCartToast() {
    const toast = document.getElementById('cart-toast');
    if (toast) {
        toast.classList.add('hidden');
    }
}

function showErrorToast(message) {
    const toast = document.getElementById('error-toast');
    if (toast) {
        toast.textContent = message;
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }
}

function hideErrorToast() {
    const toast = document.getElementById('error-toast');
    if (toast) {
        toast.classList.add('hidden');
    }
}

function showLoadingSpinner() {
    const spinner = document.getElementById('loading-spinner');
    if (spinner) {
        spinner.classList.remove('hidden');
    }
}

function hideLoadingSpinner() {
    const spinner = document.getElementById('loading-spinner');
    if (spinner) {
        spinner.classList.add('hidden');
    }
}

// Contact functions
function contactUs() {
    window.location.href = '/contact';
}

function callUs() {
    window.location.href = 'tel:+1234567890';
}

function emailUs() {
    window.location.href = 'mailto:info@amakomomo.com';
}

function openMap() {
    // Get address from the page (from the map overlay)
    const addressElement = document.querySelector('.absolute.bottom-0 .text-white .font-bold');
    const address = addressElement ? addressElement.textContent.replace('📍 ', '') : 'Amako Momo Restaurant, Thamel, Kathmandu, Nepal';
    
    const encodedAddress = encodeURIComponent(address);
    const mapUrl = `https://www.google.com/maps/search/?api=1&query=${encodedAddress}`;
    window.open(mapUrl, '_blank');
}

function copyAddress() {
    // Get address from the page (from the map overlay)
    const addressElement = document.querySelector('.absolute.bottom-0 .text-white .font-bold');
    const addressLine2 = document.querySelector('.absolute.bottom-0 .text-white .opacity-95');
    
    let address = 'Amako Momo Restaurant, Thamel, Kathmandu, Nepal'; // fallback
    
    if (addressElement && addressLine2) {
        const line1 = addressElement.textContent.replace('📍 ', '');
        const line2 = addressLine2.textContent;
        address = `${line1}\n${line2}`;
    }
    
    // Try to use the modern clipboard API
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(address).then(() => {
            showSuccessToast('Address copied to clipboard! 📋');
        }).catch(err => {
            console.error('Failed to copy address:', err);
            fallbackCopyAddress(address);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyAddress(address);
    }
}

function fallbackCopyAddress(address) {
    // Create a temporary textarea element
    const textArea = document.createElement('textarea');
    textArea.value = address;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showSuccessToast('Address copied to clipboard! 📋');
    } catch (err) {
        console.error('Fallback copy failed:', err);
        showErrorToast('Failed to copy address. Please copy manually.');
    }
    
    document.body.removeChild(textArea);
}

function showSuccessToast(message) {
    // Create a success toast notification
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
    toast.innerHTML = `
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

function writeReview() {
    window.open('https://google.com/search?q=amakomomo+reviews', '_blank');
}

window.openMap = openMap;
window.copyAddress = copyAddress;
window.handleSocialMediaClick = handleSocialMediaClick;

// Social media link handler
function handleSocialMediaClick(event, platform) {
    const url = event.target.closest('a').href;
    if (url === '#' || url.includes('#')) {
        event.preventDefault();
        showSuccessToast(`${platform} link not set yet. Please update in admin settings.`);
    }
} 