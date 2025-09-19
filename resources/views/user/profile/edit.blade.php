@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
@php
    $user = Auth::user();
    $user->syncThemesWithBadges(); // Ensure themes are up to date
    $activeTheme = $user->activeTheme;
@endphp

<div class="min-h-screen py-8" style="{{ $activeTheme ? $activeTheme->theme_styles['background'] : 'background-color: #f3f4f6;' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        @include('user.profile.partials.breadcrumb')

        <!-- Main Layout -->
        <div class="flex flex-col lg:flex-row gap-4 lg:gap-8">
            <!-- Sidebar Navigation -->
            <div class="lg:w-1/4">
                @include('user.profile.partials.tabs')
            </div>

            <!-- Main Content Area -->
            <div class="lg:w-3/4">
                <!-- Credits Tab Content -->
                <div id="credits" class="hidden">
                    @include('user.profile.partials.credits')
                </div>

                <!-- Badges Tab Content -->
                <div id="badges" class="hidden">
                    @include('user.profile.badges', ['badges' => $user->userBadges()->with(['badgeClass', 'badgeRank', 'badgeTier'])->active()->get()])
                </div>

                <!-- Order History Tab Content -->
                <div id="order-history" class="hidden">
                    @include('user.profile.partials.order-history')
                </div>

                <!-- Address Book Tab Content -->
                <div id="address-book" class="hidden">
                    @include('user.profile.partials.address-book')
                </div>

                <!-- Security Tab Content -->
                <div id="security" class="hidden">
                    @include('user.profile.partials.security')
                </div>

                <!-- Referrals Tab Content -->
                <div id="referrals" class="hidden">
                    @include('user.profile.partials.referrals')
                </div>

                <!-- Account Tab Content -->
                <div id="account" class="hidden">
                    @include('user.profile.partials.account')
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top-up QR Code Modal -->
<div id="topUpModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900">Credits Top-up QR Code</h3>
                <button type="button" onclick="closeTopUpModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div class="text-center mb-6">
                <div class="mb-6">
                    <div id="qrCodeContainer" class="flex justify-center">
                        <!-- QR Code will be loaded here -->
                    </div>
                </div>
                
                <div class="mb-6">
                    <p class="text-sm text-gray-600 mb-3 font-medium">Show this QR code to an employee</p>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <p class="text-sm text-gray-700"><span class="font-medium">Account:</span> <span id="qrAccountNumber" class="font-mono">N/A</span></p>
                        <p class="text-sm text-gray-700"><span class="font-medium">Barcode:</span> <span id="qrBarcode" class="font-mono">N/A</span></p>
                    </div>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-800 font-medium mb-3">Important Notes:</p>
                    <ul class="text-sm text-blue-700 space-y-2 text-left">
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-0.5">•</span>
                            <span>Credits are non-refundable and cannot be transferred</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-0.5">•</span>
                            <span>Credits can only be used within AmaKo stores</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2 mt-0.5">•</span>
                            <span>1 Credit = 1 point (not a currency)</span>
                        </li>
                    </ul>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="downloadQRCode()" 
                            class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        Download QR
                    </button>
                    <button type="button" onclick="closeTopUpModal()" 
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Processing...</span>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div id="successToast" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ease-in-out translate-y-0 opacity-100 hidden">
    <div class="flex items-center space-x-2">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 001.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        <span id="successMessage"></span>
    </div>
</div>

<script>
// Immediately hide all tabs to prevent flash of content
(function() {
    console.log('Immediate tab hiding script running...');
    const tabContents = document.querySelectorAll('[id="credits"], [id="badges"], [id="order-history"], [id="address-book"], [id="security"], [id="referrals"], [id="account"]');
    console.log('Found tab contents:', tabContents.length);
    
    tabContents.forEach(content => {
        content.classList.add('hidden');
        console.log('Hidden tab:', content.id);
    });
    
    // Force show only credits initially
    const creditsTab = document.getElementById('credits');
    if (creditsTab) {
        creditsTab.classList.remove('hidden');
        console.log('Showing credits tab');
    }
})();

// Mobile menu toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const profileSidebar = document.getElementById('profileSidebar');
    const mobileMenuIcon = document.getElementById('mobileMenuIcon');
    
    if (mobileMenuToggle && profileSidebar && mobileMenuIcon) {
        mobileMenuToggle.addEventListener('click', function() {
            const isHidden = profileSidebar.classList.contains('hidden');
            
            if (isHidden) {
                profileSidebar.classList.remove('hidden');
                mobileMenuIcon.style.transform = 'rotate(180deg)';
            } else {
                profileSidebar.classList.add('hidden');
                mobileMenuIcon.style.transform = 'rotate(0deg)';
            }
        });
    }
});

// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Tab switching logic starting...');
    
    const tabs = document.querySelectorAll('[href^="#"]');
    const tabContents = document.querySelectorAll('[id="credits"], [id="badges"], [id="order-history"], [id="address-book"], [id="security"], [id="referrals"], [id="account"]');
    
    // Force hide ALL tabs first
    tabContents.forEach(content => {
        content.classList.add('hidden');
        console.log('Force hidden tab:', content.id);
    });
    
    // Check if there's a hash in the URL to show specific tab
    const hash = window.location.hash.substring(1);
    let defaultTab = 'credits';
    
    if (hash && Array.from(tabContents).some(content => content.id === hash)) {
        defaultTab = hash;
    }
    
    console.log('Default tab will be:', defaultTab);
    
    // Show only the default tab
    const defaultTabElement = document.getElementById(defaultTab);
    if (defaultTabElement) {
        defaultTabElement.classList.remove('hidden');
        console.log('Showing tab:', defaultTab);
    }
    
    // Update active tab styling and current page indicator
    updateTabStyling(defaultTab);
    updateCurrentPageIndicator(defaultTab);
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show the target tab content
            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.classList.remove('hidden');
            }
            
            // Update tab styling and current page indicator
            updateTabStyling(targetId);
            updateCurrentPageIndicator(targetId);
            
            // Close mobile menu after selection
            const profileSidebar = document.getElementById('profileSidebar');
            const mobileMenuIcon = document.getElementById('mobileMenuIcon');
            if (profileSidebar && mobileMenuIcon && window.innerWidth < 1024) {
                profileSidebar.classList.add('hidden');
                mobileMenuIcon.style.transform = 'rotate(0deg)';
            }
        });
    });
});

// Profile completion percentage animation
function animateProgressBar() {
    const progressBar = document.querySelector('.bg-green-500');
    const percentage = {{ $completionPercentage ?? 0 }};
    
    if (progressBar) {
        progressBar.style.width = '0%';
        setTimeout(() => {
            progressBar.style.transition = 'width 1s ease-in-out';
            progressBar.style.width = percentage + '%';
        }, 100);
    }
}

// Animate progress bar on page load
document.addEventListener('DOMContentLoaded', animateProgressBar);

// Loading state management
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}

// Success toast management
function showSuccessToast(message) {
    const toast = document.getElementById('successToast');
    const messageElement = document.getElementById('successMessage');
    messageElement.textContent = message;
    
    toast.classList.remove('hidden');
    toast.classList.add('translate-y-0', 'opacity-100');
    
    setTimeout(() => {
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 300);
    }, 3000);
}

// Form submission with loading states
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    // QR Code Modal Functions
    window.showTopUpQR = function() {
        console.log('showTopUpQR function called');
        
        const modal = document.getElementById('topUpModal');
        const qrContainer = document.getElementById('qrCodeContainer');
        
        if (!modal || !qrContainer) {
            console.error('Modal or QR container not found');
            return;
        }
        
        console.log('Showing modal and loading QR code');
        
        // Show loading
        qrContainer.innerHTML = '<div class="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>';
        modal.classList.remove('hidden');
        
        // Generate QR code
        const url = '{{ route("user.credits.generate-qr") }}';
        const token = '{{ csrf_token() }}';
        
        console.log('Making request to:', url);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                qrContainer.innerHTML = `<img src="${data.qr_code}" alt="Credits QR Code" class="w-32 h-32">`;
                document.getElementById('qrAccountNumber').textContent = data.account_number || 'N/A';
                document.getElementById('qrBarcode').textContent = data.credits_barcode || 'N/A';
            } else {
                qrContainer.innerHTML = '<p class="text-red-500">Error generating QR code</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            qrContainer.innerHTML = '<p class="text-red-500">Error generating QR code</p>';
        });
    };
    
    window.closeTopUpModal = function() {
        document.getElementById('topUpModal').classList.add('hidden');
    };
    
    window.downloadQRCode = function() {
        const qrImage = document.querySelector('#qrCodeContainer img');
        if (qrImage) {
            const link = document.createElement('a');
            link.download = 'credits-qr-code.png';
            link.href = qrImage.src;
            link.click();
        }
    };
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Don't show loading for delete account form (it has its own confirmation)
            if (form.action.includes('delete-account')) {
                return;
            }
            
            showLoading();
            
            // Hide loading after a minimum time to prevent flickering
            setTimeout(() => {
                hideLoading();
            }, 500);
        });
    });
});

// Show success message if present
@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        showSuccessToast('{{ session('success') }}');
    });
@endif

// Mobile optimization
function handleMobileLayout() {
    const isMobile = window.innerWidth < 768;
    const tabs = document.querySelectorAll('[href^="#"]');
    
    if (isMobile) {
        tabs.forEach(tab => {
            tab.classList.add('text-sm', 'px-2', 'py-1');
        });
    } else {
        tabs.forEach(tab => {
            tab.classList.remove('text-sm', 'px-2', 'py-1');
        });
    }
}

// Tab switching functionality
function switchTab(tabId) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('[id$="-history"], [id="credits"], [id="badges"], [id="address-book"], [id="security"], [id="referrals"], [id="account"]');
    tabContents.forEach(content => {
        content.classList.add('hidden');
    });
    
    // Show the selected tab content
    const selectedContent = document.getElementById(tabId);
    if (selectedContent) {
        selectedContent.classList.remove('hidden');
    }
    
    // Update tab navigation styles
    const tabs = document.querySelectorAll('[href^="#"]');
    tabs.forEach(tab => {
        tab.classList.remove('text-blue-700', 'border-blue-600');
        tab.classList.add('text-gray-500', 'hover:text-blue-700');
    });
    
    // Highlight the active tab
    const activeTab = document.querySelector(`[href="#${tabId}"]`);
    if (activeTab) {
        activeTab.classList.remove('text-gray-500', 'hover:text-blue-700');
        activeTab.classList.add('text-blue-700', 'border-blue-600');
    }
}

// Handle tab navigation
document.addEventListener('DOMContentLoaded', function() {
    // Add click event listeners to all tab links
    const tabLinks = document.querySelectorAll('[href^="#"]');
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tabId = this.getAttribute('href').substring(1);
            switchTab(tabId);
            
            // Update URL hash without scrolling
            history.pushState(null, null, `#${tabId}`);
        });
    });
    
    // Handle initial load with hash in URL
    if (window.location.hash) {
        const tabId = window.location.hash.substring(1);
        switchTab(tabId);
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        if (window.location.hash) {
            const tabId = window.location.hash.substring(1);
            switchTab(tabId);
        } else {
            // Default to profile-info if no hash
            switchTab('profile-info');
        }
    });
});

// Handle mobile layout on load and resize
document.addEventListener('DOMContentLoaded', handleMobileLayout);
window.addEventListener('resize', handleMobileLayout);
</script>

<style>
/* Ensure hidden class works properly */
.hidden {
    display: none !important;
}

/* Force hide all tab contents except the active one */
#credits.hidden,
#badges.hidden,
#order-history.hidden,
#address-book.hidden,
#security.hidden,
#referrals.hidden,
#account.hidden {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    height: 0 !important;
    overflow: hidden !important;
}

/* Ensure credits content is completely contained */
#credits.hidden * {
    display: none !important;
}

/* Touch-friendly interactions */
.touch-manipulation {
    touch-action: manipulation;
}

/* Fix notification badge visibility on profile page */
.notification-badge {
    z-index: 9999 !important;
    position: absolute !important;
    overflow: visible !important;
}

.notification-container {
    overflow: visible !important;
    position: relative !important;
}

/* Ensure top navigation is above all profile page elements */
nav.fixed {
    z-index: 9999 !important;
}

/* Mobile optimizations */
@media (max-width: 1024px) {
    .max-w-7xl {
        max-width: 100%;
    }
    
    .px-4 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .space-y-6 > * + * {
        margin-top: 1.5rem;
    }
    
    /* Stack grid items on mobile */
    .grid-cols-2 {
        grid-template-columns: 1fr;
    }
    
    /* Adjust button sizes for mobile */
    button {
        min-height: 44px;
    }
    
    /* Improve form field spacing */
    input, select, textarea {
        font-size: 16px; /* Prevents zoom on iOS */
    }
    
    /* Mobile sidebar improvements */
    #profileSidebar {
        position: relative;
        z-index: 10;
    }
    
    /* Smooth transitions for mobile menu */
    #mobileMenuIcon {
        transition: transform 0.3s ease-in-out;
    }
}

/* Desktop sidebar styling */
@media (min-width: 1024px) {
    #profileSidebar {
        position: sticky;
        top: 2rem;
        height: fit-content;
    }
}

/* Enhanced hover effects for sidebar */
@media (hover: hover) {
    #profileSidebar a:hover {
        transform: translateX(2px);
        transition: all 0.2s ease-in-out;
    }
}

/* Focus states for accessibility */
#profileSidebar a:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Mobile menu button styling */
#mobileMenuToggle {
    transition: all 0.2s ease-in-out;
}

#mobileMenuToggle:hover {
    background-color: #1d4ed8;
}

#mobileMenuToggle:active {
    transform: scale(0.98);
}

/* Enhanced tab styling */
.nav-tab.active {
    background: linear-gradient(to right, #dbeafe, #e0e7ff) !important;
    color: #1d4ed8 !important;
    border-left: 4px solid #3b82f6 !important;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
}

.nav-tab.active .nav-icon {
    background: linear-gradient(to bottom right, #3b82f6, #1d4ed8) !important;
}

.nav-tab.active .nav-dot {
    background-color: #3b82f6 !important;
}
</style>

<script>
// Helper function to update tab styling
function updateTabStyling(activeTabId) {
    const tabs = document.querySelectorAll('.nav-tab');
    
    tabs.forEach(tab => {
        const tabId = tab.getAttribute('data-tab');
        
        if (tabId === activeTabId) {
            // Add active state
            tab.classList.add('active');
            tab.classList.remove('text-gray-600');
            tab.classList.add('text-blue-700', 'bg-gradient-to-r', 'from-blue-50', 'to-indigo-50', 'border-l-4', 'border-blue-600', 'shadow-sm');
            
            // Update icon
            const icon = tab.querySelector('div');
            if (icon) {
                icon.classList.remove('from-gray-400', 'to-gray-500');
                icon.classList.add('from-blue-500', 'to-indigo-600');
            }
            
            // Update text weight
            const text = tab.querySelector('span');
            if (text) {
                text.classList.add('font-semibold');
            }
            
            // Add active dot
            if (!tab.querySelector('.nav-dot')) {
                const dot = document.createElement('div');
                dot.className = 'nav-dot ml-auto w-2 h-2 bg-blue-500 rounded-full';
                tab.appendChild(dot);
            }
        } else {
            // Remove active state
            tab.classList.remove('active', 'text-blue-700', 'bg-gradient-to-r', 'from-blue-50', 'to-indigo-50', 'border-l-4', 'border-blue-600', 'shadow-sm');
            tab.classList.add('text-gray-600');
            
            // Update icon
            const icon = tab.querySelector('div');
            if (icon) {
                icon.classList.remove('from-blue-500', 'to-indigo-600');
                icon.classList.add('from-gray-400', 'to-gray-500');
            }
            
            // Update text weight
            const text = tab.querySelector('span');
            if (text) {
                text.classList.remove('font-semibold');
            }
            
            // Remove active dot
            const dot = tab.querySelector('.nav-dot');
            if (dot) {
                dot.remove();
            }
        }
    });
}

// Helper function to update current page indicator
function updateCurrentPageIndicator(activeTabId) {
    const indicator = document.getElementById('currentPageIndicator');
    const tabs = document.querySelectorAll('.nav-tab');
    
    let pageLabel = 'Profile Info'; // Default
    
    tabs.forEach(tab => {
        const tabId = tab.getAttribute('data-tab');
        if (tabId === activeTabId) {
            pageLabel = tab.getAttribute('data-label');
        }
    });
    
    if (indicator) {
        indicator.textContent = pageLabel;
    }
}
</script>

@endsection