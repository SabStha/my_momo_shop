// Special Offers JavaScript Functions

// Global state for offers
let offersState = {
    claimedOffers: new Set(),
    newsletterSubscribed: false,
    flashSaleTimer: null
};

// Enhanced copy to clipboard functionality
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Code copied to clipboard!', 'success');
        }).catch(() => {
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showToast('Code copied to clipboard!', 'success');
    } catch (err) {
        showToast('Failed to copy code', 'error');
    }
    
    document.body.removeChild(textArea);
}

// Enhanced offer claiming with better UX
function claimOffer(code, button) {
    if (offersState.claimedOffers.has(code)) {
        showToast('Offer already claimed!', 'info');
        return;
    }

    // Add loading state
    const originalText = button.innerHTML;
    button.innerHTML = `
        <div class="flex items-center justify-center gap-2">
            <div class="w-3 h-3 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
            <span class="text-xs">Claiming...</span>
        </div>
    `;
    button.disabled = true;

    // Simulate API call with realistic delay
    setTimeout(() => {
        offersState.claimedOffers.add(code);
        
        // Success animation
        button.innerHTML = `
            <div class="flex items-center justify-center gap-2">
                <span>✅</span>
                <span class="text-xs">Claimed!</span>
            </div>
        `;
        button.classList.add('bg-green-500', 'text-white');
        button.classList.remove('bg-[#6E0D25]', 'text-white');
        
        // Show success message
        showToast(`Offer "${code}" claimed successfully!`, 'success');
        
        // Add confetti effect
        createConfetti(button);
        
        // Reset button after delay
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('bg-green-500', 'text-white');
            button.classList.add('bg-[#6E0D25]', 'text-white');
            button.disabled = false;
        }, 3000);
        
    }, 1500);
}

// Enhanced combo deal functionality
function addComboToCart(type, button) {
    const originalText = button.innerHTML;
    
    // Add loading state
    button.innerHTML = `
        <div class="flex items-center justify-center gap-2">
            <div class="w-3 h-3 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
            <span class="text-xs">Adding...</span>
        </div>
    `;
    button.disabled = true;

    setTimeout(() => {
        // Success state
        button.innerHTML = `
            <div class="flex items-center justify-center gap-2">
                <span>✅</span>
                <span class="text-xs">Added!</span>
            </div>
        `;
        
        showToast(`${type === 'bogo' ? 'Buy 2 Get 1 Free' : 'Weekend Special'} added to cart!`, 'success');
        createConfetti(button);
        
        // Reset after delay
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
    }, 1000);
}

// Enhanced loyalty program signup
function joinLoyalty(button) {
    const originalText = button.innerHTML;
    
    button.innerHTML = `
        <div class="flex items-center justify-center gap-2">
            <div class="w-3 h-3 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
            <span class="text-xs">Joining...</span>
        </div>
    `;
    button.disabled = true;

    setTimeout(() => {
        button.innerHTML = `
            <div class="flex items-center justify-center gap-2">
                <span>👑</span>
                <span class="text-xs">Welcome!</span>
            </div>
        `;
        
        showToast('Welcome to our loyalty program! You\'ll earn points on every order.', 'success');
        createConfetti(button);
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 3000);
    }, 1500);
}

// Enhanced flash sale functionality
function addFlashSale(button) {
    const originalText = button.innerHTML;
    
    button.innerHTML = `
        <div class="flex items-center justify-center gap-2">
            <div class="w-3 h-3 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
            <span class="text-xs">Adding...</span>
        </div>
    `;
    button.disabled = true;

    setTimeout(() => {
        button.innerHTML = `
            <div class="flex items-center justify-center gap-2">
                <span>⚡</span>
                <span class="text-xs">Active!</span>
            </div>
        `;
        
        showToast('Flash sale items added to cart! 30% off applied.', 'success');
        createConfetti(button);
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 3000);
    }, 1200);
}

// Enhanced toast notifications
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast-notification fixed top-4 right-4 z-50 p-3 rounded-lg shadow-lg transform transition-all duration-500 translate-x-full`;
    
    const colors = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white',
        warning: 'bg-yellow-500 text-black'
    };
    
    const icons = {
        success: '✅',
        error: '❌',
        info: 'ℹ️',
        warning: '⚠️'
    };
    
    toast.className += ` ${colors[type]}`;
    
    toast.innerHTML = `
        <div class="flex items-center gap-2">
            <span class="text-sm">${icons[type]}</span>
            <span class="text-sm font-medium">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-sm hover:opacity-70 transition-opacity">×</button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}

// Enhanced confetti effect
function createConfetti(element) {
    const rect = element.getBoundingClientRect();
    const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#feca57', '#ff9ff3'];
    
    for (let i = 0; i < 10; i++) {
        const confetti = document.createElement('div');
        confetti.style.position = 'fixed';
        confetti.style.left = rect.left + rect.width / 2 + 'px';
        confetti.style.top = rect.top + rect.height / 2 + 'px';
        confetti.style.width = '6px';
        confetti.style.height = '6px';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.borderRadius = '50%';
        confetti.style.pointerEvents = 'none';
        confetti.style.zIndex = '9999';
        confetti.style.transform = 'translate(-50%, -50%)';
        
        document.body.appendChild(confetti);
        
        const angle = Math.random() * Math.PI * 2;
        const velocity = 2 + Math.random() * 2;
        const vx = Math.cos(angle) * velocity;
        const vy = Math.sin(angle) * velocity - 1;
        
        let x = 0;
        let y = 0;
        let opacity = 1;
        
        const animate = () => {
            x += vx;
            y += vy;
            vy += 0.1; // gravity
            opacity -= 0.02;
            
            confetti.style.transform = `translate(calc(-50% + ${x}px), calc(-50% + ${y}px))`;
            confetti.style.opacity = opacity;
            
            if (opacity > 0) {
                requestAnimationFrame(animate);
            } else {
                confetti.remove();
            }
        };
        
        requestAnimationFrame(animate);
    }
}

// Enhanced email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Enhanced newsletter subscription
function subscribeNewsletter() {
    const emailInput = document.getElementById('newsletter-email');
    if (!emailInput) return;
    
    const email = emailInput.value.trim();
    
    if (!email) {
        showToast('Please enter your email address', 'error');
        emailInput.focus();
        return;
    }
    
    if (!isValidEmail(email)) {
        showToast('Please enter a valid email address', 'error');
        emailInput.focus();
        return;
    }
    
    if (offersState.newsletterSubscribed) {
        showToast('You\'re already subscribed!', 'info');
        return;
    }
    
    const subscribeBtn = emailInput.nextElementSibling;
    if (!subscribeBtn) return;
    
    const originalText = subscribeBtn.innerHTML;
    
    subscribeBtn.innerHTML = `
        <div class="flex items-center justify-center gap-2">
            <div class="w-3 h-3 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
            <span class="text-xs">Subscribing...</span>
        </div>
    `;
    subscribeBtn.disabled = true;
    
    setTimeout(() => {
        offersState.newsletterSubscribed = true;
        subscribeBtn.innerHTML = `
            <div class="flex items-center justify-center gap-2">
                <span>✅</span>
                <span class="text-xs">Subscribed!</span>
            </div>
        `;
        
        showToast('Successfully subscribed to our newsletter!', 'success');
        emailInput.value = '';
        createConfetti(subscribeBtn);
        
        setTimeout(() => {
            subscribeBtn.innerHTML = originalText;
            subscribeBtn.disabled = false;
        }, 3000);
    }, 1500);
}

// Enhanced flash sale countdown timer
function startFlashSaleTimer() {
    const timerElement = document.getElementById('flash-sale-timer');
    if (!timerElement) return;
    
    let hours = 2;
    let minutes = 0;
    let seconds = 0;
    
    offersState.flashSaleTimer = setInterval(() => {
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
            clearInterval(offersState.flashSaleTimer);
            timerElement.innerHTML = '<div class="text-red-400 font-bold text-xs">EXPIRED</div>';
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

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎁 Special Offers System Loaded!');
    
    // Start flash sale timer
    startFlashSaleTimer();
    
    // Show welcome message after a delay
    setTimeout(() => {
        showToast('Special offers available! 🎁', 'info');
    }, 2000);
}); 