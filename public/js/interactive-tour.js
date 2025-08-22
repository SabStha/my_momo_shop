// Interactive Tour System for AmaKo
class AmaKoTour {
    constructor() {
        this.tourSteps = [
            {
                id: 'welcome',
                target: null,
                message: "Hi! I'm AmaKo, your friendly guide! 🥟 Let's explore how to order delicious momos together!",
                position: 'center',
                page: 'all'
            },
            {
                id: 'menu-button',
                target: 'a[href*="menu"]',
                message: "First, let's look at the menu! Click the 'Menu' button to see all our delicious momos and more!",
                position: 'bottom-right',
                page: 'all'
            },
            {
                id: 'home-button',
                target: 'a[href*="home"]',
                message: "This is your home page - where you'll find featured items and special offers!",
                position: 'bottom-right',
                page: 'all'
            },
            {
                id: 'help-button',
                target: 'a[href*="help"]',
                message: "Need help? Click here anytime for support and guides!",
                position: 'top-right',
                page: 'all'
            },
            {
                id: 'profile-button',
                target: 'a[href*="profile"]',
                message: "Create an account to track orders, earn rewards, and save your favorite items!",
                position: 'top-right',
                page: 'all'
            },
            {
                id: 'finds-button',
                target: 'a[href*="finds"]',
                message: "Discover Ama's special finds and recommendations!",
                position: 'top-right',
                page: 'all'
            },
            {
                id: 'bulk-button',
                target: 'a[href*="bulk"]',
                message: "Ordering for a group? Check out our bulk ordering options!",
                position: 'top-right',
                page: 'all'
            },
            {
                id: 'tour-button',
                target: 'button[onclick*="startAmaKoTour"]',
                message: "Want to take this tour again? Click this button anytime!",
                position: 'top-left',
                page: 'all'
            },
            {
                id: 'welcome-banner',
                target: '.bg-gradient-to-r.from-blue-500.to-purple-600',
                message: "New users get special welcome offers and guides!",
                position: 'bottom-right',
                page: 'all'
            },
            {
                id: 'completion',
                target: null,
                message: "You're all set! 🎉 You now know how to use AmaKo. Start ordering and enjoy delicious momos!",
                position: 'center',
                page: 'all'
            }
        ];
        
        this.currentStep = 0;
        this.tourActive = false;
        this.currentPage = this.getCurrentPage();
        
        this.init();
    }
    
    init() {
        this.createTourElements();
        this.bindEvents();
        
        // Check if tour should start automatically
        const urlParams = new URLSearchParams(window.location.search);
        const showTour = urlParams.get('tour') === 'true';
        const tourCompleted = localStorage.getItem('amako-tour-completed');
        
        if (showTour || (!tourCompleted && this.shouldShowTour())) {
            setTimeout(() => {
                this.startTour();
            }, 1000);
        }
    }
    
    getCurrentPage() {
        const path = window.location.pathname;
        if (path.includes('/menu')) return 'menu';
        if (path.includes('/cart')) return 'cart';
        if (path.includes('/checkout')) return 'checkout';
        if (path.includes('/orders')) return 'orders';
        if (path.includes('/profile')) return 'profile';
        return 'all';
    }
    
    shouldShowTour() {
        // Only show tour on main pages
        const mainPages = ['/', '/menu', '/home'];
        return mainPages.includes(window.location.pathname);
    }
    
    createTourElements() {
        // Create tour overlay
        const overlay = document.createElement('div');
        overlay.id = 'tour-overlay';
        overlay.className = 'fixed inset-0 z-50 bg-black bg-opacity-50 hidden';
        overlay.innerHTML = `
            <div class="absolute inset-0 backdrop-blur-sm"></div>
            <div class="relative h-full flex items-center justify-center">
                <div id="tour-mascot" class="absolute bottom-8 right-8 z-10">
                    <div class="bg-white rounded-full p-4 shadow-lg border-4 border-[#6E0D25] animate-bounce">
                        <div class="text-4xl">🥟</div>
                    </div>
                    <div id="tour-speech" class="absolute bottom-full right-0 mb-2 bg-white rounded-lg p-4 shadow-lg max-w-xs border-2 border-[#6E0D25]">
                        <div class="text-sm font-semibold text-[#6E0D25] mb-1">AmaKo Guide</div>
                        <div id="tour-message" class="text-gray-700 text-sm">Welcome! Let's explore AmaKo together!</div>
                        <div class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-white"></div>
                    </div>
                </div>
                
                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
                    <button id="tour-prev" class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#8B0D2F] transition-colors">
                        ← Previous
                    </button>
                    <button id="tour-next" class="bg-[#6E0D25] text-white px-4 py-2 rounded-lg hover:bg-[#8B0D2F] transition-colors">
                        Next →
                    </button>
                    <button id="tour-skip" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        Skip Tour
                    </button>
                </div>
                
                <div class="absolute top-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
                    <div id="tour-progress" class="flex space-x-1"></div>
                </div>
            </div>
        `;
        
        // Create highlight element
        const highlight = document.createElement('div');
        highlight.id = 'tour-highlight';
        highlight.className = 'absolute z-45 pointer-events-none hidden';
        highlight.innerHTML = '<div class="absolute inset-0 bg-white bg-opacity-30 rounded-lg border-4 border-[#6E0D25] shadow-2xl animate-pulse" style="box-shadow: 0 0 30px rgba(110, 13, 37, 0.6);"></div>';
        
        document.body.appendChild(overlay);
        document.body.appendChild(highlight);
    }
    
    bindEvents() {
        document.getElementById('tour-prev').addEventListener('click', () => this.previousStep());
        document.getElementById('tour-next').addEventListener('click', () => this.nextStep());
        document.getElementById('tour-skip').addEventListener('click', () => this.skipTour());
        
        // Handle window resize
        window.addEventListener('resize', () => {
            if (this.tourActive) {
                this.showCurrentStep();
            }
        });
    }
    
    startTour() {
        this.tourActive = true;
        this.currentStep = 0;
        this.showCurrentStep();
        document.getElementById('tour-overlay').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    showCurrentStep() {
        const step = this.getCurrentStep();
        if (!step) return;
        
        const message = document.getElementById('tour-message');
        const highlight = document.getElementById('tour-highlight');
        
        // Clean up previous highlighting
        this.cleanupHighlightedElements();
        
        // Update message
        message.textContent = step.message;
        
        // Update progress
        this.updateProgress();
        
        // Handle target element
        if (step.target) {
            const targetElement = document.querySelector(step.target);
            console.log('Tour step:', step.id, 'Target selector:', step.target, 'Element found:', !!targetElement);
            
            if (targetElement) {
                console.log('Highlighting element:', targetElement);
                this.highlightElement(targetElement);
                
                // Position mascot after a short delay to allow highlighting to complete
                setTimeout(() => {
                    this.positionMascot(targetElement, step.position);
                }, 150);
            } else {
                console.log('Target element not found, positioning mascot in center');
                // If target not found, position mascot in center
                this.positionMascot(null, 'center');
                highlight.style.display = 'none';
            }
        } else {
            // No target (welcome/completion steps)
            this.positionMascot(null, 'center');
            highlight.style.display = 'none';
        }
        
        // Update navigation buttons
        this.updateNavigationButtons();
    }
    
    getCurrentStep() {
        const step = this.tourSteps[this.currentStep];
        if (!step) return null;
        
        // Filter steps based on current page
        if (step.page !== 'all' && step.page !== this.currentPage) {
            // Skip to next relevant step
            this.currentStep++;
            return this.getCurrentStep();
        }
        
        return step;
    }
    
    highlightElement(element) {
        const highlight = document.getElementById('tour-highlight');
        
        // First, ensure the element is visible in the viewport
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'center'
        });
        
        // Wait a moment for scroll to complete, then get the rect
        setTimeout(() => {
            const rect = element.getBoundingClientRect();
            
            console.log('Highlighting element with rect:', rect);
            
            // Calculate position with proper bounds checking
            const scrollX = window.pageXOffset || document.documentElement.scrollLeft;
            const scrollY = window.pageYOffset || document.documentElement.scrollTop;
            
            // Ensure coordinates are never negative
            const left = Math.max(0, rect.left + scrollX - 8);
            const top = Math.max(0, rect.top + scrollY - 8);
            const width = rect.width + 16;
            const height = rect.height + 16;
            
            highlight.style.display = 'block';
            highlight.style.position = 'absolute';
            highlight.style.left = left + 'px';
            highlight.style.top = top + 'px';
            highlight.style.width = width + 'px';
            highlight.style.height = height + 'px';
            highlight.style.zIndex = '45';
            
            // Make element clickable and ensure it's visible
            element.style.zIndex = '60';
            element.style.position = 'relative';
            
            // Add a subtle glow effect to the highlighted element
            element.style.boxShadow = '0 0 20px rgba(110, 13, 37, 0.8)';
            element.style.transition = 'box-shadow 0.3s ease';
            
            console.log('Highlight element styles:', {
                display: highlight.style.display,
                left: highlight.style.left,
                top: highlight.style.top,
                width: highlight.style.width,
                height: highlight.style.height,
                zIndex: highlight.style.zIndex,
                scrollX,
                scrollY,
                elementRect: rect
            });
        }, 100);
    }
    

    
    positionMascot(element, position) {
        const mascot = document.getElementById('tour-mascot');
        
        if (element && position !== 'center') {
            const rect = element.getBoundingClientRect();
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;
            const mascotWidth = 200; // Approximate mascot width
            const mascotHeight = 150; // Approximate mascot height
            
            // Position mascot relative to the highlighted element
            let left, top;
            
            // Calculate center position relative to the element
            const elementCenterX = rect.left + rect.width / 2;
            const elementCenterY = rect.top + rect.height / 2;
            
            switch (position) {
                case 'bottom-right':
                    // Position to the right and below the element
                    left = Math.min(rect.right + 20, windowWidth - mascotWidth - 20);
                    top = Math.min(rect.bottom + 20, windowHeight - mascotHeight - 20);
                    break;
                case 'bottom-left':
                    // Position to the left and below the element
                    left = Math.max(rect.left - mascotWidth - 20, 20);
                    top = Math.min(rect.bottom + 20, windowHeight - mascotHeight - 20);
                    break;
                case 'top-right':
                    // Position to the right and above the element
                    left = Math.min(rect.right + 20, windowWidth - mascotWidth - 20);
                    top = Math.max(rect.top - mascotHeight - 20, 20);
                    break;
                case 'top-left':
                    // Position to the left and above the element
                    left = Math.max(rect.left - mascotWidth - 20, 20);
                    top = Math.max(rect.top - mascotHeight - 20, 20);
                    break;
                default:
                    // Default: position below and centered relative to the element
                    left = Math.max(20, Math.min(elementCenterX - mascotWidth / 2, windowWidth - mascotWidth - 20));
                    top = Math.min(rect.bottom + 20, windowHeight - mascotHeight - 20);
            }
            
            // Ensure mascot stays within viewport bounds
            left = Math.max(20, Math.min(left, windowWidth - mascotWidth - 20));
            top = Math.max(20, Math.min(top, windowHeight - mascotHeight - 20));
            
            mascot.style.left = left + 'px';
            mascot.style.top = top + 'px';
            mascot.style.right = 'auto';
            mascot.style.bottom = 'auto';
            mascot.style.transform = 'none';
            
            console.log('Positioning mascot at:', { 
                left, 
                top, 
                elementRect: rect, 
                elementCenter: { x: elementCenterX, y: elementCenterY },
                windowSize: { width: windowWidth, height: windowHeight },
                position: position
            });
        } else {
            // Center position for welcome/completion steps
            mascot.style.left = '50%';
            mascot.style.top = '50%';
            mascot.style.transform = 'translate(-50%, -50%)';
            mascot.style.right = 'auto';
            mascot.style.bottom = 'auto';
        }
    }
    
    updateProgress() {
        const progress = document.getElementById('tour-progress');
        progress.innerHTML = '';
        
        this.tourSteps.forEach((step, index) => {
            const dot = document.createElement('div');
            dot.className = `w-3 h-3 rounded-full ${
                index === this.currentStep ? 'bg-[#6E0D25]' : 'bg-gray-300'
            }`;
            progress.appendChild(dot);
        });
    }
    
    updateNavigationButtons() {
        const prevBtn = document.getElementById('tour-prev');
        const nextBtn = document.getElementById('tour-next');
        
        prevBtn.disabled = this.currentStep === 0;
        nextBtn.textContent = this.currentStep === this.tourSteps.length - 1 ? 'Finish' : 'Next →';
        
        prevBtn.style.opacity = this.currentStep === 0 ? '0.5' : '1';
    }
    
    nextStep() {
        if (this.currentStep < this.tourSteps.length - 1) {
            this.currentStep++;
            this.showCurrentStep();
        } else {
            this.completeTour();
        }
    }
    
    previousStep() {
        if (this.currentStep > 0) {
            this.currentStep--;
            this.showCurrentStep();
        }
    }
    
    skipTour() {
        this.completeTour();
    }
    
    completeTour() {
        this.tourActive = false;
        document.getElementById('tour-overlay').classList.add('hidden');
        document.getElementById('tour-highlight').style.display = 'none';
        document.body.style.overflow = '';
        
        // Clean up any highlighted elements
        this.cleanupHighlightedElements();
        
        // Show completion message
        this.showToast('Tour completed! You\'re ready to start ordering! 🎉', 'success');
        
        // Save tour completion to localStorage
        localStorage.setItem('amako-tour-completed', 'true');
    }
    
    cleanupHighlightedElements() {
        // Remove any styling we added to elements during the tour
        const allElements = document.querySelectorAll('*');
        allElements.forEach(element => {
            if (element.style.zIndex === '60' || element.style.boxShadow.includes('rgba(110, 13, 37, 0.8)')) {
                element.style.zIndex = '';
                element.style.boxShadow = '';
                element.style.transition = '';
            }
        });
    }
    
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white ${
            type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'
        } shadow-lg z-50 transform transition-all duration-300 ease-in-out`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
}

// Initialize tour when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.amakoTour = new AmaKoTour();
});

// Global function to start tour from anywhere
function startAmaKoTour() {
    if (window.amakoTour) {
        window.amakoTour.startTour();
    }
} 