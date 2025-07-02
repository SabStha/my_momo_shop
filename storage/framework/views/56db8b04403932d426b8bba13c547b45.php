<?php $__env->startSection('title', 'Edit Profile'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $user = Auth::user();
    $user->syncThemesWithBadges(); // Ensure themes are up to date
    $activeTheme = $user->activeTheme;
?>

<div class="min-h-screen py-8" style="<?php echo e($activeTheme ? $activeTheme->theme_styles['background'] : 'background-color: #f3f4f6;'); ?>">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <?php echo $__env->make('user.profile.partials.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold" style="<?php echo e($activeTheme ? $activeTheme->theme_styles['text'] : 'color: #111827;'); ?>">Edit Profile</h1>
            <p class="mt-2" style="<?php echo e($activeTheme ? 'color: ' . $activeTheme->theme_colors['text'] . '80;' : 'color: #6b7280;'); ?>">Update your personal information</p>
        </div>

        <!-- Tabbed Interface -->
        <?php echo $__env->make('user.profile.partials.tabs', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <!-- Profile Info Tab Content -->
        <div id="profile-info">
            <?php echo $__env->make('user.profile.partials.profile-info', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!-- Badges Tab Content -->
        <div id="badges" class="hidden">
            <?php echo $__env->make('user.profile.badges', ['badges' => $user->userBadges()->with(['badgeClass', 'badgeRank', 'badgeTier'])->active()->get()], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!-- Themes Tab Content -->
        <div id="themes" class="hidden">
            <?php echo $__env->make('user.profile.partials.themes', ['user' => $user], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!-- Order History Tab Content -->
        <div id="order-history" class="hidden">
            <?php echo $__env->make('user.profile.partials.order-history', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!-- Credits Tab Content -->
        <div id="credits" class="hidden">
            <?php echo $__env->make('user.profile.partials.credits', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!-- Address Book Tab Content -->
        <div id="address-book" class="hidden">
            <?php echo $__env->make('user.profile.partials.address-book', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!-- Security Tab Content -->
        <div id="security" class="hidden">
            <?php echo $__env->make('user.profile.partials.security', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!-- Referrals Tab Content -->
        <div id="referrals" class="hidden">
            <?php echo $__env->make('user.profile.partials.referrals', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!-- Account Tab Content -->
        <div id="account" class="hidden">
            <?php echo $__env->make('user.profile.partials.account', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
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
    const tabContents = document.querySelectorAll('[id="profile-info"], [id="badges"], [id="themes"], [id="order-history"], [id="credits"], [id="address-book"], [id="security"], [id="referrals"], [id="account"]');
    
    // Show only the first tab (profile-info) by default
    tabContents.forEach(content => {
        if (content.id !== 'profile-info') {
            content.classList.add('hidden');
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
    const percentage = <?php echo e($completionPercentage ?? 0); ?>;
    
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
<?php if(session('success')): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showSuccessToast('<?php echo e(session('success')); ?>');
    });
<?php endif; ?>

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

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/user/profile/edit.blade.php ENDPATH**/ ?>