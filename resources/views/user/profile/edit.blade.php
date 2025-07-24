@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
@php
    $user = Auth::user();
    $user->syncThemesWithBadges(); // Ensure themes are up to date
    $activeTheme = $user->activeTheme;
@endphp

<div class="min-h-screen py-8" style="{{ $activeTheme ? $activeTheme->theme_styles['background'] : 'background-color: #f3f4f6;' }}">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        @include('user.profile.partials.breadcrumb')

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold" style="{{ $activeTheme ? $activeTheme->theme_styles['text'] : 'color: #111827;' }}">Edit Profile</h1>
            <p class="mt-2" style="{{ $activeTheme ? 'color: ' . $activeTheme->theme_colors['text'] . '80;' : 'color: #6b7280;' }}">Update your personal information</p>
        </div>

        <!-- Tabbed Interface -->
        @include('user.profile.partials.tabs')

        <!-- Profile Info Tab Content -->
        <div id="profile-info">
            @include('user.profile.partials.profile-info')
        </div>

        <!-- Badges Tab Content -->
        <div id="badges" class="hidden">
            @include('user.profile.badges', ['badges' => $user->userBadges()->with(['badgeClass', 'badgeRank', 'badgeTier'])->active()->get()])
        </div>

        <!-- Themes Tab Content -->
        <div id="themes" class="hidden">
            @include('user.profile.partials.themes', ['user' => $user])
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

<!-- Top-up QR Code Modal -->
<div id="topUpModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Credits Top-up QR Code</h3>
                <button type="button" onclick="closeTopUpModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="text-center">
                <div class="mb-4">
                    <div id="qrCodeContainer" class="flex justify-center">
                        <!-- QR Code will be loaded here -->
                    </div>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Show this QR code to an employee</p>
                    <p class="text-xs text-gray-500">Account: <span id="qrAccountNumber">N/A</span></p>
                    <p class="text-xs text-gray-500">Barcode: <span id="qrBarcode">N/A</span></p>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <p class="text-xs text-blue-800 font-medium mb-1">Important Notes:</p>
                    <ul class="text-xs text-blue-700 space-y-1">
                        <li>• Credits are non-refundable and cannot be transferred</li>
                        <li>• Credits can only be used within AmaKo stores</li>
                        <li>• 1 Credit = 1 point (not a currency)</li>
                    </ul>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="downloadQRCode()" 
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                        Download QR
                    </button>
                    <button type="button" onclick="closeTopUpModal()" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
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
// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('[href^="#"]');
    const tabContents = document.querySelectorAll('[id="profile-info"], [id="badges"], [id="themes"], [id="order-history"], [id="address-book"], [id="security"], [id="referrals"], [id="account"]');
    
    // Check if there's a hash in the URL to show specific tab
    const hash = window.location.hash.substring(1);
    let defaultTab = 'profile-info';
    
    if (hash && Array.from(tabContents).some(content => content.id === hash)) {
        defaultTab = hash;
    }
    
    // Show only the default tab
    tabContents.forEach(content => {
        if (content.id !== defaultTab) {
            content.classList.add('hidden');
        }
    });
    
    // Update active tab styling
    tabs.forEach(tab => {
        const tabId = tab.getAttribute('href').substring(1);
        if (tabId === defaultTab) {
            tab.classList.remove('text-gray-500');
            tab.classList.add('text-blue-700', 'border-blue-600');
        } else {
            tab.classList.remove('text-blue-700', 'border-blue-600');
            tab.classList.add('text-gray-500');
        }
    });
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active state from all tabs
            tabs.forEach(t => {
                t.classList.remove('text-blue-700', 'border-blue-600');
                t.classList.add('text-gray-500');
            });
            
            // Add active state to clicked tab
            this.classList.remove('text-gray-500');
            this.classList.add('text-blue-700', 'border-blue-600');
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show the target tab content
            const targetId = this.getAttribute('href').substring(1);
            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.classList.remove('hidden');
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
    const tabContents = document.querySelectorAll('[id$="-history"], [id="profile-info"], [id="badges"], [id="themes"], [id="address-book"], [id="security"], [id="referrals"], [id="account"]');
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
/* Hide scrollbar for webkit browsers */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Touch-friendly interactions */
.touch-manipulation {
    touch-action: manipulation;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .max-w-2xl {
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
}
</style>

@endsection