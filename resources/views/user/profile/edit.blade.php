@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        @include('user.profile.partials.breadcrumb')

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edit Profile</h1>
            <p class="text-gray-600 mt-2">Update your personal information</p>
        </div>

        <!-- Tabbed Interface -->
        @include('user.profile.partials.tabs')

        <!-- Profile Info Tab Content -->
        <div id="profile-info">
            @include('user.profile.partials.profile-info')
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
    console.log('Profile page loaded, initializing tabs...');
    
    const tabs = document.querySelectorAll('[href^="#"]');
    const tabContents = document.querySelectorAll('[id="profile-info"], [id="order-history"], [id="address-book"], [id="security"], [id="referrals"], [id="account"]');
    
    console.log('Found tabs:', tabs.length);
    console.log('Found tab contents:', tabContents.length);
    
    // Show only the first tab (profile-info) by default
    tabContents.forEach(content => {
        if (content.id !== 'profile-info') {
            content.classList.add('hidden');
        } else {
            content.classList.remove('hidden');
        }
    });
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Tab clicked:', this.getAttribute('href'));
            
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
                console.log('Showing tab content:', targetId);
            } else {
                console.error('Target content not found:', targetId);
            }
        });
    });
    
    // Set initial active tab
    const firstTab = document.querySelector('[href="#profile-info"]');
    if (firstTab) {
        firstTab.classList.remove('text-gray-500');
        firstTab.classList.add('text-blue-700', 'border-blue-600');
    }
});

// Profile completion percentage animation
function animateProgressBar() {
    const progressBar = document.querySelector('.bg-green-500');
    const percentage = {{ $completionPercentage }};
    
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
        // Make tabs scrollable on mobile
        const tabContainer = document.querySelector('nav');
        if (tabContainer) {
            tabContainer.classList.add('overflow-x-auto', 'scrollbar-hide');
        }
        
        // Adjust spacing for mobile
        document.querySelectorAll('.space-y-8').forEach(el => {
            el.classList.remove('space-y-8');
            el.classList.add('space-y-6');
        });
    }
}

// Handle mobile layout on load and resize
document.addEventListener('DOMContentLoaded', handleMobileLayout);
window.addEventListener('resize', handleMobileLayout);

// Add touch support for mobile
document.addEventListener('DOMContentLoaded', function() {
    if ('ontouchstart' in window) {
        // Add touch-friendly classes
        document.querySelectorAll('button, input, select, textarea').forEach(el => {
            el.classList.add('touch-manipulation');
        });
    }
});
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