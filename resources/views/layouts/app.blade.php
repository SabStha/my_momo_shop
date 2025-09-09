<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AmaKo Momo — Premium Nepali Momo, Fast Ordering, Rewards</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="AmaKo Momo — premium Nepali momo, fast ordering, rewards." />
    <meta name="theme-color" content="#111111">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.webmanifest">
    
    <!-- Google Fonts with preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) { 
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js')); 
        }
    </script>
    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="min-h-screen bg-[#F4E9E1] text-gray-800">

    {{-- TOP NAVBAR --}}
    @include('partials.topnav')

    {{-- MAIN PAGE CONTENT --}}
    <main class="pt-8 pb-1">
        @yield('content')
    </main>

    {{-- BOTTOM NAVBAR --}}
    @include('partials.bottomnav')

    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="{{ asset('js/cart.js') }}" defer></script>
    <script src="{{ asset('js/home.js') }}" defer></script>
    <script src="{{ asset('js/interactive-tour.js') }}" defer></script>

    <!-- Global JavaScript Functions -->
    <script>
        // Global settings
        window.currencySymbol = '{{ getCurrencySymbol() }}';
        window.currencyCode = '{{ getCurrencyCode() }}';
        window.taxRate = {{ getTaxRate() }};
        window.deliveryFee = {{ getDeliveryFee() }};
        
        // Copy to clipboard function
        function copyToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    console.log('Text copied to clipboard successfully');
                    if (typeof showToast === 'function') {
                        showToast('Copied to clipboard!', 'success');
                    } else if (typeof showSuccessNotification === 'function') {
                        showSuccessNotification('Copied to clipboard!');
                    } else {
                        alert('Copied to clipboard!');
                    }
                }).catch(function(err) {
                    console.error('Failed to copy to clipboard:', err);
                    fallbackCopy(text);
                });
            } else {
                fallbackCopy(text);
            }
        }

        // Fallback copy method
        function fallbackCopy(text) {
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
                if (typeof showToast === 'function') {
                    showToast('Copied to clipboard!', 'success');
                } else if (typeof showSuccessNotification === 'function') {
                    showSuccessNotification('Copied to clipboard!');
                } else {
                    alert('Copied to clipboard!');
                }
            } catch (err) {
                console.error('Fallback copy failed:', err);
                if (typeof showToast === 'function') {
                    showToast('Failed to copy to clipboard', 'error');
                } else {
                    alert('Failed to copy to clipboard');
                }
            }
            
            document.body.removeChild(textArea);
        }

        // Global toast function if not already defined
        if (typeof showToast === 'undefined') {
            window.showToast = function(message, type = 'success') {
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white ${
                    type === 'success' ? 'bg-green-500' : 'bg-red-500'
                } shadow-lg z-50 transform transition-all duration-300 ease-in-out translate-y-0 opacity-100`;
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="${type === 'success' ? 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' : 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z'}" clip-rule="evenodd"/>
                        </svg>
                        <span>${message}</span>
                    </div>
                `;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.classList.add('translate-y-2', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 2700);
            };
        }

        // Global success notification function if not already defined
        if (typeof showSuccessNotification === 'undefined') {
            window.showSuccessNotification = function(message, actionText = null, actionUrl = null) {
                const notification = document.createElement('div');
                notification.className = 'success-notification fixed top-4 right-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl shadow-2xl z-50 transform transition-all duration-500 translate-x-full max-w-sm';
                
                let notificationContent = `
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium">${message}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-white hover:text-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                `;
                
                if (actionText && actionUrl) {
                    notificationContent += `
                        <div class="mt-3 pt-3 border-t border-white border-opacity-20">
                            <a href="${actionUrl}" class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 text-white text-sm font-semibold rounded-lg hover:bg-opacity-30 transition-all duration-200 transform hover:scale-105">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"/>
                                </svg>
                                ${actionText}
                            </a>
                        </div>
                    `;
                }
                
                notification.innerHTML = notificationContent;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.remove('translate-x-full');
                    notification.classList.add('translate-x-0');
                }, 10);
                
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.classList.add('translate-x-full');
                        notification.classList.remove('translate-x-0');
                        setTimeout(() => {
                            if (notification.parentElement) {
                                notification.remove();
                            }
                        }, 500);
                    }
                }, 5000);
            };
        }

        // Expose copyToClipboard globally
        window.copyToClipboard = copyToClipboard;
    </script>

    <!-- AI Popup System -->
    <div id="ai-popup-overlay" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="ai-popup-content">
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-6 text-white">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold">🤖 AI Special Offer</h3>
                    <button onclick="closeAIPopup()" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    <h4 id="ai-popup-title" class="text-lg font-semibold text-gray-800 mb-2">AI-Generated Special Offer</h4>
                    <p id="ai-popup-description" class="text-gray-600 text-sm">Personalized offer just for you!</p>
                </div>
                
                <!-- Offer Details -->
                <div class="text-center mb-6">
                    <div class="flex items-center justify-center gap-2 mb-4">
                        <span class="text-3xl">🎁</span>
                        <span class="text-lg text-gray-600">OFF</span>
                    </div>
                    <div id="ai-popup-code" class="font-mono text-lg font-bold text-gray-800 bg-white px-4 py-2 rounded-lg border-2 border-purple-300 shadow-sm">
                        AIOFFER123
                    </div>
                    <div class="text-xs text-gray-500 mt-2">Valid until <span id="ai-popup-valid-until">3 days</span></div>
                </div>
                
                <!-- Buttons -->
                <div class="space-y-3">
                    <button onclick="claimAIOffer()" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3 px-6 rounded-xl font-semibold text-lg hover:from-purple-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                        🎯 Claim This Offer
                    </button>
                    <button onclick="useAIOffer()" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-3 px-6 rounded-xl font-semibold text-lg hover:from-green-700 hover:to-emerald-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                        🎁 Use This Offer
                    </button>
                    <button onclick="closeAIPopup()" class="w-full bg-gray-100 text-gray-700 py-3 px-6 rounded-xl font-semibold text-lg hover:bg-gray-200 transition-all duration-200">
                        Maybe Later
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // AI Popup System
        let currentAIOffer = null;
        let popupShown = false;

        // Initialize AI popup system
        document.addEventListener('DOMContentLoaded', function() {
            // Set session start time for analytics
            if (!sessionStorage.getItem('session_start')) {
                sessionStorage.setItem('session_start', Date.now());
            }
            
            // Check if popup was already shown in this session
            if (sessionStorage.getItem('ai_popup_shown')) {
                console.log('AI popup already shown in this session');
                return;
            }
            
            // Check for AI popup after page load (increased delay)
            setTimeout(checkAIPopup, 5000); // Increased from 2 seconds to 5 seconds
            
            // Check for exit intent (only if popup hasn't been shown)
            document.addEventListener('mouseleave', function(e) {
                if (e.clientY <= 0 && !popupShown && !sessionStorage.getItem('ai_popup_shown')) {
                    checkAIPopup('exit_intent');
                }
            });
        });

        // Check if AI popup should be shown
        function checkAIPopup(context = 'homepage') {
            if (popupShown) {
                console.log('AI popup already shown, skipping check');
                return;
            }
            
            console.log('Checking AI popup for context:', context);
            
            fetch('{{ route("ai-popup.decision") }}?context=' + context, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('AI popup decision:', data);
                
                if (data.success && data.show_popup) {
                    console.log('Showing AI popup with reasoning:', data.reasoning);
                    showAIPopup(data);
                } else {
                    console.log('AI popup not shown. Reason:', data.reason);
                    if (data.debug_info) {
                        console.log('Debug info:', data.debug_info);
                    }
                }
            })
            .catch(error => {
                console.log('AI popup check failed:', error);
            });
        }

        // Show AI popup
        function showAIPopup(data) {
            currentAIOffer = data.offer;
            popupShown = true;
            
            // Mark popup as shown in session storage
            sessionStorage.setItem('ai_popup_shown', 'true');
            
            // Update popup content
            document.getElementById('ai-popup-title').textContent = data.offer.title;
            document.getElementById('ai-popup-description').textContent = data.offer.description;
            document.getElementById('ai-popup-discount').textContent = data.offer.discount + '%';
            document.getElementById('ai-popup-code').textContent = data.offer.code;
            document.getElementById('ai-popup-reasoning').textContent = data.reasoning;
            
            // Format valid until
            const validUntil = new Date(data.offer.valid_until);
            const daysUntil = Math.ceil((validUntil - new Date()) / (1000 * 60 * 60 * 24));
            document.getElementById('ai-popup-valid-until').textContent = daysUntil + ' days';
            
            // Show popup with animation
            const overlay = document.getElementById('ai-popup-overlay');
            const content = document.getElementById('ai-popup-content');
            
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
            
            // Track popup shown
            trackPopupInteraction('shown');
        }

        // Close AI popup
        function closeAIPopup() {
            const overlay = document.getElementById('ai-popup-overlay');
            const content = document.getElementById('ai-popup-content');
            
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }, 300);
            
            // Track popup dismissed
            trackPopupInteraction('dismissed');
        }

        // Use AI offer
        function useAIOffer() {
            if (!currentAIOffer) return;
            
            // Apply the offer directly to cart
            const offerData = {
                code: currentAIOffer.code,
                discount: currentAIOffer.discount,
                title: currentAIOffer.title,
                applied_at: new Date().toISOString()
            };
            
            // Store in localStorage
            localStorage.setItem('applied_offer', JSON.stringify(offerData));
            
            // Also try to use cartManager if available
            if (window.cartManager && typeof window.cartManager.applyOffer === 'function') {
                window.cartManager.applyOffer(offerData);
            }
            
            // Show success notification with "Go to Cart" action
            if (typeof showSuccessNotification === 'function') {
                showSuccessNotification(
                    `🎉 AI Offer "${currentAIOffer.title}" applied to your cart! You'll save ${currentAIOffer.discount}% on your order.`,
                    'Go to Cart',
                    '/cart'
                );
            } else {
                // Fallback: copy offer code to clipboard
                const offerCode = currentAIOffer.code;
                
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(offerCode).then(function() {
                        showOfferCopiedMessage();
                    }).catch(function(err) {
                        fallbackCopyOffer(offerCode);
                    });
                } else {
                    fallbackCopyOffer(offerCode);
                }
            }
            
            // Track offer used
            trackPopupInteraction('converted');
            
            // Close popup
            closeAIPopup();
        }

        // Claim AI offer
        function claimAIOffer() {
            if (!currentAIOffer) return;
            
            // Show loading state
            const claimButton = event.target;
            const originalText = claimButton.innerHTML;
            claimButton.innerHTML = `
                <div class="flex items-center justify-center gap-2">
                    <div class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
                    <span>Claiming...</span>
                </div>
            `;
            claimButton.disabled = true;
            
            // Make API call to claim the offer
            fetch('{{ route("offers.claim") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    code: currentAIOffer.code
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showClaimSuccessMessage();
                    
                    // Track offer claimed
                    trackPopupInteraction('claimed');
                    
                    // Close popup
                    closeAIPopup();
                } else {
                    // Show error message
                    showErrorMessage(data.message || 'Failed to claim offer');
                    
                    // Reset button
                    claimButton.innerHTML = originalText;
                    claimButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error claiming offer:', error);
                showErrorMessage('Failed to claim offer. Please try again.');
                
                // Reset button
                claimButton.innerHTML = originalText;
                claimButton.disabled = false;
            });
        }

        // Show claim success message
        function showClaimSuccessMessage() {
            if (typeof showSuccessNotification === 'function') {
                showSuccessNotification(
                    `🎯 AI Offer "${currentAIOffer.title}" claimed successfully!`,
                    'View My Offers',
                    '/cart'
                );
            } else {
                // Fallback to old method
                const message = document.createElement('div');
                message.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
                message.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>🎯 AI Offer claimed successfully!</span>
                    </div>
                `;
                
                document.body.appendChild(message);
                
                // Animate in
                setTimeout(() => {
                    message.classList.remove('translate-x-full');
                    message.classList.add('translate-x-0');
                }, 10);
                
                // Remove after 3 seconds
                setTimeout(() => {
                    message.classList.add('translate-x-full');
                    message.classList.remove('translate-x-0');
                    setTimeout(() => {
                        document.body.removeChild(message);
                    }, 300);
                }, 3000);
            }
        }

        // Show error message
        function showErrorMessage(errorText) {
            const message = document.createElement('div');
            message.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
            message.innerHTML = `
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>${errorText}</span>
                </div>
            `;
            
            document.body.appendChild(message);
            
            // Animate in
            setTimeout(() => {
                message.classList.remove('translate-x-full');
                message.classList.add('translate-x-0');
            }, 10);
            
            // Remove after 3 seconds
            setTimeout(() => {
                message.classList.add('translate-x-full');
                message.classList.remove('translate-x-0');
                setTimeout(() => {
                    document.body.removeChild(message);
                }, 300);
            }, 3000);
        }

        // Show offer copied message
        function showOfferCopiedMessage() {
            if (typeof showSuccessNotification === 'function') {
                showSuccessNotification(
                    `🤖 AI Offer code "${currentAIOffer.code}" copied to clipboard!`,
                    'Go to Cart',
                    '/cart'
                );
            } else {
                // Fallback to old method
                const message = document.createElement('div');
                message.className = 'fixed top-4 right-4 bg-purple-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300';
                message.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>🤖 AI Offer code copied to clipboard!</span>
                    </div>
                `;
                
                document.body.appendChild(message);
                
                // Animate in
                setTimeout(() => {
                    message.classList.add('translate-x-0');
                    message.classList.remove('translate-x-full');
                }, 10);
                
                // Remove after 3 seconds
                setTimeout(() => {
                    message.classList.add('translate-x-full');
                    message.classList.remove('translate-x-0');
                    setTimeout(() => {
                        document.body.removeChild(message);
                    }, 300);
                }, 3000);
            }
        }

        // Fallback copy method
        function fallbackCopyOffer(offerCode) {
            const textarea = document.createElement('textarea');
            textarea.value = offerCode;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            
            textarea.select();
            try {
                document.execCommand('copy');
                showOfferCopiedMessage();
            } catch (err) {
                alert(`🤖 AI Offer code: ${offerCode}\n\nCopy this code and use it during checkout!`);
            }
            
            document.body.removeChild(textarea);
        }

        // Track popup interaction
        function trackPopupInteraction(action) {
            if (!currentAIOffer) return;
            
            fetch('{{ route("ai-popup.track") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    offer_id: currentAIOffer.id,
                    action: action
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Popup interaction tracked:', action);
            })
            .catch(error => {
                console.log('Failed to track popup interaction:', error);
            });
        }

        // Close popup when clicking outside
        document.addEventListener('click', function(e) {
            const overlay = document.getElementById('ai-popup-overlay');
            const content = document.getElementById('ai-popup-content');
            
            if (overlay && content && e.target === overlay) {
                closeAIPopup();
            }
        });

        // Temporary debug function - remove in production
        window.resetAIPopup = function() {
            sessionStorage.removeItem('ai_popup_shown');
            popupShown = false;
            console.log('AI popup state reset for testing');
            alert('AI popup state reset. Reload page to test again.');
        };
        
        // Log popup state on page load
        console.log('AI popup state on load:', {
            popupShown: popupShown,
            sessionStorage: sessionStorage.getItem('ai_popup_shown'),
            resetFunction: 'Use window.resetAIPopup() to reset for testing'
        });
    </script>

    @stack('scripts')

</body>
</html>
