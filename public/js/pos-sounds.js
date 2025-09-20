/**
 * POS Sound System
 * Provides audio feedback for all interactive elements
 */

class POSSoundSystem {
    constructor() {
        this.enabled = true;
        this.volume = 0.7;
        this.sounds = {};
        this.hapticEnabled = true;
        this.init();
    }

    init() {
        // Create audio context for better sound management
        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) {
            console.warn('AudioContext not supported, using HTML5 audio fallback');
            this.audioContext = null;
        }

        // Load sound settings from localStorage
        this.loadSettings();
        
        // Create sound elements
        this.createSoundElements();
        
        // Add sound toggle functionality
        this.addSoundToggle();
    }

    loadSettings() {
        const savedSettings = localStorage.getItem('pos_sound_settings');
        if (savedSettings) {
            const settings = JSON.parse(savedSettings);
            this.enabled = settings.enabled !== false;
            this.volume = settings.volume || 0.7;
            this.hapticEnabled = settings.hapticEnabled !== false;
        }
    }

    saveSettings() {
        localStorage.setItem('pos_sound_settings', JSON.stringify({
            enabled: this.enabled,
            volume: this.volume,
            hapticEnabled: this.hapticEnabled
        }));
    }

    createSoundElements() {
        // Create different types of sounds using Web Audio API or fallback to HTML5 audio
        
        // Button click sound - short beep
        this.sounds.button = this.createTone(800, 0.08, 'square');
        
        // Success sound - pleasant chime
        this.sounds.success = this.createMelody([1047, 1319, 1568], 0.12); // C6, E6, G6
        
        // Error sound - descending tone
        this.sounds.error = this.createMelody([800, 600, 400], 0.2);
        
        // Add to cart sound - ascending tone
        this.sounds.addToCart = this.createMelody([800, 1000], 0.1);
        
        // Remove from cart sound - descending tone
        this.sounds.removeFromCart = this.createMelody([600, 400], 0.1);
        
        // Quantity change sound - short click
        this.sounds.quantityChange = this.createTone(700, 0.06, 'square');
        
        // Search sound - soft beep
        this.sounds.search = this.createTone(900, 0.05, 'sine');
        
        // Order creation sound - celebratory melody
        this.sounds.orderCreated = this.createMelody([523, 659, 784, 1047], 0.15); // C5, E5, G5, C6
        
        // Category selection sound - medium beep
        this.sounds.categorySelect = this.createTone(850, 0.1, 'square');
        
        // Modal open sound - soft ascending
        this.sounds.modalOpen = this.createMelody([600, 800], 0.15);
        
        // Modal close sound - soft descending
        this.sounds.modalClose = this.createMelody([800, 600], 0.15);
        
        // Table selection sound - distinctive beep
        this.sounds.tableSelect = this.createTone(950, 0.12, 'triangle');
        
        // Notification sound
        this.sounds.notification = this.createTone(1000, 0.2, 'sine');
        
        // Warning sound
        this.sounds.warning = this.createMelody([440, 440, 440], 0.1);
    }

    createTone(frequency, duration, waveType = 'sine') {
        if (!this.audioContext) {
            return this.createHTML5Audio(frequency, duration);
        }

        return () => {
            if (!this.enabled) return;
            
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);
            
            oscillator.frequency.setValueAtTime(frequency, this.audioContext.currentTime);
            oscillator.type = waveType;
            
            gainNode.gain.setValueAtTime(0, this.audioContext.currentTime);
            gainNode.gain.linearRampToValueAtTime(this.volume * 0.3, this.audioContext.currentTime + 0.01);
            gainNode.gain.exponentialRampToValueAtTime(0.001, this.audioContext.currentTime + duration);
            
            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + duration);
        };
    }

    createMelody(frequencies, noteDuration) {
        return () => {
            if (!this.enabled) return;
            
            frequencies.forEach((freq, index) => {
                setTimeout(() => {
                    this.createTone(freq, noteDuration, 'sine')();
                }, index * noteDuration * 1000);
            });
        };
    }

    createHTML5Audio(frequency, duration) {
        // Fallback for browsers without Web Audio API support
        return () => {
            if (!this.enabled) return;
            
            try {
                const audio = new Audio();
                const dataUrl = this.generateToneDataUrl(frequency, duration);
                audio.src = dataUrl;
                audio.volume = this.volume * 0.3;
                audio.play().catch(e => console.warn('Audio play failed:', e));
            } catch (e) {
                console.warn('HTML5 audio fallback failed:', e);
            }
        };
    }

    generateToneDataUrl(frequency, duration) {
        // Generate a simple tone using Web Audio API and create data URL
        if (!this.audioContext) return '';
        
        const sampleRate = this.audioContext.sampleRate;
        const length = sampleRate * duration;
        const buffer = this.audioContext.createBuffer(1, length, sampleRate);
        const data = buffer.getChannelData(0);
        
        for (let i = 0; i < length; i++) {
            data[i] = Math.sin(2 * Math.PI * frequency * i / sampleRate) * this.volume * 0.3;
        }
        
        // Convert buffer to WAV and return data URL
        return this.bufferToWav(buffer);
    }

    bufferToWav(buffer) {
        const length = buffer.length;
        const arrayBuffer = new ArrayBuffer(44 + length * 2);
        const view = new DataView(arrayBuffer);
        
        // WAV header
        const writeString = (offset, string) => {
            for (let i = 0; i < string.length; i++) {
                view.setUint8(offset + i, string.charCodeAt(i));
            }
        };
        
        writeString(0, 'RIFF');
        view.setUint32(4, 36 + length * 2, true);
        writeString(8, 'WAVE');
        writeString(12, 'fmt ');
        view.setUint32(16, 16, true);
        view.setUint16(20, 1, true);
        view.setUint16(22, 1, true);
        view.setUint32(24, buffer.sampleRate, true);
        view.setUint32(28, buffer.sampleRate * 2, true);
        view.setUint16(32, 2, true);
        view.setUint16(34, 16, true);
        writeString(36, 'data');
        view.setUint32(40, length * 2, true);
        
        // Convert float samples to 16-bit PCM
        const channelData = buffer.getChannelData(0);
        let offset = 44;
        for (let i = 0; i < length; i++) {
            const sample = Math.max(-1, Math.min(1, channelData[i]));
            view.setInt16(offset, sample * 0x7FFF, true);
            offset += 2;
        }
        
        const blob = new Blob([arrayBuffer], { type: 'audio/wav' });
        return URL.createObjectURL(blob);
    }

    addSoundToggle() {
        // Add sound toggle button to the POS interface
        const soundToggle = document.createElement('button');
        soundToggle.innerHTML = `
            <i class="fas fa-volume-${this.enabled ? 'up' : 'mute'} text-white"></i>
        `;
        soundToggle.className = 'text-xs text-white hover:text-gray-900 px-2 py-1';
        soundToggle.title = this.enabled ? 'Disable sounds' : 'Enable sounds';
        
        soundToggle.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleSound();
            soundToggle.innerHTML = `
                <i class="fas fa-volume-${this.enabled ? 'up' : 'mute'} text-white"></i>
            `;
            soundToggle.title = this.enabled ? 'Disable sounds' : 'Enable sounds';
            
            // Play feedback sound and haptic
            if (this.enabled) {
                this.safePlay(() => this.sounds.success(), 'success');
            } else {
                this.triggerHaptic('light');
            }
        });
        
        // Add haptic toggle button
        const hapticToggle = document.createElement('button');
        hapticToggle.innerHTML = `
            <i class="fas fa-hand-paper text-white"></i>
        `;
        hapticToggle.className = 'text-xs text-white hover:text-gray-900 px-2 py-1';
        hapticToggle.title = this.hapticEnabled ? 'Disable haptic feedback' : 'Enable haptic feedback';
        
        hapticToggle.addEventListener('click', (e) => {
            e.preventDefault();
            this.hapticEnabled = !this.hapticEnabled;
            this.saveSettings();
            hapticToggle.title = this.hapticEnabled ? 'Disable haptic feedback' : 'Enable haptic feedback';
            this.triggerHaptic(this.hapticEnabled ? 'success' : 'light');
        });
        
        // Add to navigation
        const nav = document.querySelector('.navbar .flex.items-center.space-x-3');
        if (nav) {
            nav.insertBefore(hapticToggle, nav.lastElementChild);
            nav.insertBefore(soundToggle, nav.lastElementChild);
        }
    }

    toggleSound() {
        this.enabled = !this.enabled;
        this.saveSettings();
    }

    // Public methods for playing specific sounds
    playButton() {
        this.sounds.button();
    }

    playSuccess() {
        this.sounds.success();
    }

    playError() {
        this.sounds.error();
    }

    playAddToCart() {
        this.sounds.addToCart();
    }

    playRemoveFromCart() {
        this.sounds.removeFromCart();
    }

    playQuantityChange() {
        this.sounds.quantityChange();
    }

    playSearch() {
        this.sounds.search();
    }

    playOrderCreated() {
        this.sounds.orderCreated();
    }

    playCategorySelect() {
        this.sounds.categorySelect();
    }

    playModalOpen() {
        this.sounds.modalOpen();
    }

    playModalClose() {
        this.sounds.modalClose();
    }

    playTableSelect() {
        this.sounds.tableSelect();
    }

    playNotification() {
        this.sounds.notification();
    }

    playWarning() {
        this.sounds.warning();
    }

    // Haptic feedback for mobile devices
    triggerHaptic(type = 'light') {
        if (!this.hapticEnabled) return;
        
        try {
            if ('vibrate' in navigator) {
                switch (type) {
                    case 'light':
                        navigator.vibrate(10);
                        break;
                    case 'medium':
                        navigator.vibrate(25);
                        break;
                    case 'heavy':
                        navigator.vibrate([50, 10, 50]);
                        break;
                    case 'success':
                        navigator.vibrate([25, 10, 25, 10, 25]);
                        break;
                    case 'error':
                        navigator.vibrate([100, 50, 100]);
                        break;
                    default:
                        navigator.vibrate(10);
                }
            }
        } catch (error) {
            console.warn('Haptic feedback failed:', error);
        }
    }

    // Utility method to play sound with error handling
    safePlay(soundFunction, hapticType = null) {
        try {
            soundFunction();
            if (hapticType) {
                this.triggerHaptic(hapticType);
            }
        } catch (error) {
            console.warn('Sound playback failed:', error);
        }
    }
}

// Initialize the sound system
let posSoundSystem;

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sound system
    posSoundSystem = new POSSoundSystem();
    
    // Add sound effects to all interactive elements
    addSoundEffectsToPOS();
});

// Function to add sound effects to all POS elements
function addSoundEffectsToPOS() {
    if (!posSoundSystem) return;

    // Use event delegation for dynamic content
    document.addEventListener('click', (e) => {
        if (!posSoundSystem.enabled) return;

        const target = e.target.closest('button, .product-card, .order-method-btn, .category-btn');
        if (!target) return;

        // Order method buttons
        if (target.classList.contains('order-method-btn')) {
            posSoundSystem.safePlay(() => posSoundSystem.playButton(), 'light');
        }
        // Category filter buttons
        else if (target.classList.contains('category-btn')) {
            posSoundSystem.safePlay(() => posSoundSystem.playCategorySelect(), 'light');
        }
        // Product cards
        else if (target.classList.contains('product-card')) {
            posSoundSystem.safePlay(() => posSoundSystem.playAddToCart(), 'medium');
        }
        // Cart quantity buttons
        else if (target.onclick && target.onclick.toString().includes('updateCartItemQuantity')) {
            posSoundSystem.safePlay(() => posSoundSystem.playQuantityChange(), 'light');
        }
        // Clear cart button
        else if (target.id === 'clearCartBtn') {
            posSoundSystem.safePlay(() => posSoundSystem.playButton(), 'heavy');
        }
        // Create order button
        else if (target.id === 'createOrderBtn') {
            posSoundSystem.safePlay(() => posSoundSystem.playOrderCreated(), 'success');
        }
        // Edit order buttons
        else if (target.onclick && target.onclick.toString().includes('editOrder')) {
            posSoundSystem.safePlay(() => posSoundSystem.playModalOpen(), 'medium');
        }
        // Remove from cart buttons
        else if (target.onclick && target.onclick.toString().includes('removeFromCart')) {
            posSoundSystem.safePlay(() => posSoundSystem.playRemoveFromCart(), 'medium');
        }
        // Update quantity buttons in edit modal
        else if (target.onclick && target.onclick.toString().includes('updateQuantity')) {
            posSoundSystem.safePlay(() => posSoundSystem.playQuantityChange(), 'light');
        }
        // Active orders toggle
        else if (target.onclick && target.onclick.toString().includes('toggleActiveOrders')) {
            posSoundSystem.safePlay(() => posSoundSystem.playButton(), 'light');
        }
        // Modal close buttons
        else if (target.onclick && target.onclick.toString().includes('closeEditModal')) {
            posSoundSystem.safePlay(() => posSoundSystem.playModalClose(), 'light');
        }
        // Generic button clicks
        else if (target.tagName === 'BUTTON') {
            posSoundSystem.safePlay(() => posSoundSystem.playButton(), 'light');
        }
    });

    // Search input
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                posSoundSystem.safePlay(() => posSoundSystem.playSearch(), 'light');
            }, 300); // Debounce search sound
        });
    }

    // Table selection
    const tableSelect = document.getElementById('tableSelect');
    if (tableSelect) {
        tableSelect.addEventListener('change', () => {
            posSoundSystem.safePlay(() => posSoundSystem.playTableSelect(), 'medium');
        });
    }

    // Quantity input changes (for edit modal)
    document.addEventListener('change', (e) => {
        if (e.target.type === 'number' && posSoundSystem.enabled) {
            posSoundSystem.safePlay(() => posSoundSystem.playQuantityChange(), 'light');
        }
    });
}

// Override existing functions to add sound effects
const originalAddToCart = window.addToCart;
window.addToCart = function(productId) {
    if (posSoundSystem) {
        posSoundSystem.safePlay(() => posSoundSystem.playAddToCart(), 'medium');
    }
    return originalAddToCart(productId);
};

// Override showToast function to add sound feedback
const originalShowToast = window.showToast;
window.showToast = function(message, type = 'success') {
    if (posSoundSystem) {
        if (type === 'success') {
            posSoundSystem.safePlay(() => posSoundSystem.playSuccess(), 'success');
        } else if (type === 'error') {
            posSoundSystem.safePlay(() => posSoundSystem.playError(), 'error');
        } else {
            posSoundSystem.safePlay(() => posSoundSystem.playNotification(), 'light');
        }
    }
    return originalShowToast(message, type);
};

// Override showConfirmationModal to add sound feedback
const originalShowConfirmationModal = window.showConfirmationModal;
window.showConfirmationModal = function(message, onConfirm) {
    if (posSoundSystem) {
        posSoundSystem.safePlay(() => posSoundSystem.playWarning(), 'heavy');
    }
    return originalShowConfirmationModal(message, onConfirm);
};

// Override showSuccessModal to add sound feedback
const originalShowSuccessModal = window.showSuccessModal;
window.showSuccessModal = function(message, details = '') {
    if (posSoundSystem) {
        posSoundSystem.safePlay(() => posSoundSystem.playSuccess(), 'success');
    }
    return originalShowSuccessModal(message, details);
};

const originalRemoveFromCart = window.removeFromCart;
window.removeFromCart = function(index) {
    if (posSoundSystem) {
        posSoundSystem.safePlay(() => posSoundSystem.playRemoveFromCart(), 'medium');
    }
    return originalRemoveFromCart(index);
};

const originalUpdateCartItemQuantity = window.updateCartItemQuantity;
window.updateCartItemQuantity = function(index, change) {
    if (posSoundSystem) {
        posSoundSystem.safePlay(() => posSoundSystem.playQuantityChange(), 'light');
    }
    return originalUpdateCartItemQuantity(index, change);
};

const originalSetOrderMethod = window.setOrderMethod;
window.setOrderMethod = function(method) {
    if (posSoundSystem) {
        posSoundSystem.safePlay(() => posSoundSystem.playButton(), 'light');
    }
    return originalSetOrderMethod(method);
};

const originalFilterProducts = window.filterProducts;
window.filterProducts = function(filter) {
    if (posSoundSystem) {
        posSoundSystem.safePlay(() => posSoundSystem.playCategorySelect(), 'light');
    }
    return originalFilterProducts(filter);
};

const originalCreateOrder = window.createOrder;
window.createOrder = function() {
    if (posSoundSystem) {
        posSoundSystem.safePlay(() => posSoundSystem.playOrderCreated(), 'success');
    }
    return originalCreateOrder();
};

const originalClearCart = window.clearCart;
window.clearCart = function() {
    if (posSoundSystem) {
        posSoundSystem.safePlay(() => posSoundSystem.playButton(), 'heavy');
    }
    return originalClearCart();
};

const originalEditOrder = window.editOrder;
window.editOrder = function(orderId) {
    if (posSoundSystem) {
        posSoundSystem.safePlay(() => posSoundSystem.playModalOpen(), 'medium');
    }
    return originalEditOrder(orderId);
};

const originalCloseEditModal = window.closeEditModal;
window.closeEditModal = function() {
    if (posSoundSystem) {
        posSoundSystem.safePlay(() => posSoundSystem.playModalClose(), 'light');
    }
    return originalCloseEditModal();
};

const originalUpdateQuantity = window.updateQuantity;
window.updateQuantity = function(itemId, change) {
    if (posSoundSystem) {
        posSoundSystem.safePlay(() => posSoundSystem.playQuantityChange(), 'light');
    }
    return originalUpdateQuantity(itemId, change);
};

const originalToggleActiveOrders = window.toggleActiveOrders;
window.toggleActiveOrders = function() {
    if (posSoundSystem) {
        posSoundSystem.safePlay(() => posSoundSystem.playButton(), 'light');
    }
    return originalToggleActiveOrders();
};

// Override SweetAlert2 for sound feedback
const originalSwalFire = window.Swal?.fire;
if (window.Swal && originalSwalFire) {
    window.Swal.fire = function(options) {
        if (posSoundSystem) {
            if (options.icon === 'success') {
                posSoundSystem.safePlay(() => posSoundSystem.playSuccess(), 'success');
            } else if (options.icon === 'error') {
                posSoundSystem.safePlay(() => posSoundSystem.playError(), 'error');
            } else if (options.icon === 'warning') {
                posSoundSystem.safePlay(() => posSoundSystem.playWarning(), 'heavy');
            } else if (options.icon === 'question') {
                posSoundSystem.safePlay(() => posSoundSystem.playWarning(), 'heavy');
            } else {
                posSoundSystem.safePlay(() => posSoundSystem.playNotification(), 'light');
            }
        }
        return originalSwalFire.call(this, options);
    };
}

// Add sound feedback for keyboard interactions
document.addEventListener('keydown', (e) => {
    if (!posSoundSystem || !posSoundSystem.enabled) return;
    
    // Play sound for Enter key on buttons
    if (e.key === 'Enter' && e.target.tagName === 'BUTTON') {
        posSoundSystem.safePlay(() => posSoundSystem.playButton(), 'light');
    }
    // Play sound for Escape key
    else if (e.key === 'Escape') {
        posSoundSystem.safePlay(() => posSoundSystem.playModalClose(), 'light');
    }
});

// Add sound feedback for focus events on important inputs
document.addEventListener('focus', (e) => {
    if (!posSoundSystem || !posSoundSystem.enabled) return;
    
    if (e.target.id === 'productSearch' || e.target.id === 'tableSelect') {
        posSoundSystem.safePlay(() => posSoundSystem.playSearch(), 'light');
    }
}, true);

// Export for global access
window.posSoundSystem = posSoundSystem;
