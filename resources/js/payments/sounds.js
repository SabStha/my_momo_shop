// Sound Management System
class SoundManager {
    constructor() {
        this.audioContext = null;
        this.isMuted = false;
        this.volume = 0.7;
        this.audioContextInitialized = false;
        this.loadUserPreferences();
        this.setupUserInteractionHandler();
    }

    setupUserInteractionHandler() {
        // Add event listeners for user interactions to initialize AudioContext
        const initAudio = () => {
            if (!this.audioContextInitialized) {
                this.initializeAudioContext();
                this.audioContextInitialized = true;
            }
            // Remove listeners after first interaction
            document.removeEventListener('click', initAudio);
            document.removeEventListener('keydown', initAudio);
            document.removeEventListener('touchstart', initAudio);
        };

        document.addEventListener('click', initAudio, { once: true });
        document.addEventListener('keydown', initAudio, { once: true });
        document.addEventListener('touchstart', initAudio, { once: true });
    }

    initializeAudioContext() {
        try {
            // Initialize Web Audio API only after user interaction
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            console.log('AudioContext initialized successfully');
        } catch (error) {
            console.log('Web Audio API not supported:', error);
        }
    }

    loadUserPreferences() {
        // Load user preferences from localStorage
        const savedVolume = localStorage.getItem('paymentManagerVolume');
        const savedMuted = localStorage.getItem('paymentManagerMuted');

        if (savedVolume !== null) {
            this.volume = parseFloat(savedVolume);
        }

        if (savedMuted !== null) {
            this.isMuted = JSON.parse(savedMuted);
        }
    }

    saveUserPreferences() {
        localStorage.setItem('paymentManagerVolume', this.volume.toString());
        localStorage.setItem('paymentManagerMuted', this.isMuted.toString());
    }

    playTone(frequency, duration, type = 'sine') {
        if (this.isMuted) {
            return;
        }

        // Initialize AudioContext if not already done
        if (!this.audioContextInitialized) {
            this.initializeAudioContext();
            this.audioContextInitialized = true;
        }

        if (!this.audioContext) {
            console.log('AudioContext not available');
            return;
        }

        try {
            // Resume AudioContext if suspended (required by some browsers)
            if (this.audioContext.state === 'suspended') {
                this.audioContext.resume().then(() => {
                    this.playToneInternal(frequency, duration, type);
                }).catch(error => {
                    console.log('Failed to resume AudioContext:', error);
                });
            } else {
                this.playToneInternal(frequency, duration, type);
            }
        } catch (error) {
            console.log('Tone generation failed:', error);
        }
    }

    playToneInternal(frequency, duration, type = 'sine') {
        try {
            // Create oscillator
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            // Connect nodes
            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            // Set oscillator properties
            oscillator.frequency.setValueAtTime(frequency, this.audioContext.currentTime);
            oscillator.type = type;

            // Set gain (volume)
            gainNode.gain.setValueAtTime(0, this.audioContext.currentTime);
            gainNode.gain.linearRampToValueAtTime(this.volume * 0.3, this.audioContext.currentTime + 0.01);
            gainNode.gain.exponentialRampToValueAtTime(0.001, this.audioContext.currentTime + duration);

            // Start and stop oscillator
            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + duration);

        } catch (error) {
            console.log('Tone generation failed:', error);
        }
    }

    playSound(soundName) {
        if (this.isMuted) {
            return;
        }

        switch (soundName) {
            case 'paymentSuccess':
                // Play a pleasant success sound (ascending notes)
                this.playTone(523.25, 0.2, 'sine'); // C5
                setTimeout(() => this.playTone(659.25, 0.2, 'sine'), 100); // E5
                setTimeout(() => this.playTone(783.99, 0.3, 'sine'), 200); // G5
                break;

            case 'paymentFailed':
                // Play a warning sound (descending notes)
                this.playTone(783.99, 0.2, 'sine'); // G5
                setTimeout(() => this.playTone(659.25, 0.2, 'sine'), 100); // E5
                setTimeout(() => this.playTone(523.25, 0.3, 'sine'), 200); // C5
                break;

            case 'orderReceived':
                // Play a notification sound for new orders
                this.playTone(880, 0.15, 'sine'); // A5
                setTimeout(() => this.playTone(1047, 0.15, 'sine'), 150); // C6
                setTimeout(() => this.playTone(1319, 0.2, 'sine'), 300); // E6
                break;

            case 'paymentProcessing':
                // Play a processing sound
                this.playTone(440, 0.1, 'square'); // A4
                setTimeout(() => this.playTone(554, 0.1, 'square'), 100); // C#5
                setTimeout(() => this.playTone(659, 0.1, 'square'), 200); // E5
                break;

            case 'buttonClick':
                // Play a simple button click sound
                this.playTone(800, 0.08, 'square');
                break;

            case 'cashRegister':
                // Play a cash register sound
                this.playTone(523, 0.1, 'square'); // C5
                setTimeout(() => this.playTone(659, 0.1, 'square'), 50); // E5
                setTimeout(() => this.playTone(784, 0.1, 'square'), 100); // G5
                setTimeout(() => this.playTone(1047, 0.2, 'square'), 150); // C6
                break;

            case 'cardPayment':
                // Play a card payment sound
                this.playTone(659, 0.15, 'sine'); // E5
                setTimeout(() => this.playTone(784, 0.15, 'sine'), 100); // G5
                setTimeout(() => this.playTone(880, 0.2, 'sine'), 200); // A5
                break;

            case 'walletPayment':
                // Play a wallet payment sound
                this.playTone(784, 0.15, 'sine'); // G5
                setTimeout(() => this.playTone(880, 0.15, 'sine'), 100); // A5
                setTimeout(() => this.playTone(1047, 0.2, 'sine'), 200); // C6
                break;

            case 'khaltiPayment':
                // Play a Khalti payment sound
                this.playTone(698, 0.15, 'sine'); // F5
                setTimeout(() => this.playTone(784, 0.15, 'sine'), 100); // G5
                setTimeout(() => this.playTone(932, 0.2, 'sine'), 200); // A#5
                break;

            case 'mobilePayment':
                // Play a mobile payment sound
                this.playTone(622, 0.15, 'sine'); // D#5
                setTimeout(() => this.playTone(740, 0.15, 'sine'), 100); // F#5
                setTimeout(() => this.playTone(880, 0.2, 'sine'), 200); // A5
                break;

            case 'notification':
                // Play a general notification sound
                this.playTone(1000, 0.2, 'sine');
                break;

            case 'warning':
                // Play a warning sound
                this.playTone(440, 0.2, 'sawtooth');
                setTimeout(() => this.playTone(440, 0.2, 'sawtooth'), 300);
                break;

            default:
                console.log('Unknown sound:', soundName);
        }
    }

    setVolume(volume) {
        this.volume = Math.max(0, Math.min(1, volume));
        this.saveUserPreferences();
    }

    toggleMute() {
        this.isMuted = !this.isMuted;
        this.saveUserPreferences();
        this.updateMuteButton();
    }

    updateMuteButton() {
        const muteBtn = document.getElementById('soundMuteBtn');
        if (muteBtn) {
            const icon = muteBtn.querySelector('i');
            if (this.isMuted) {
                icon.className = 'fas fa-volume-mute';
                muteBtn.title = 'Unmute sounds';
            } else {
                icon.className = 'fas fa-volume-up';
                muteBtn.title = 'Mute sounds';
            }
        }
    }
}

// Initialize sound manager variable (will be set in main DOMContentLoaded)
let soundManager;

// Sound control functions
function playPaymentSuccess() {
    if (soundManager) {
        soundManager.playSound('paymentSuccess');
    }
}

function playPaymentSuccessWithMethod(method) {
    if (soundManager) {
        switch (method) {
            case 'cash':
                soundManager.playSound('cashRegister');
                break;
            case 'card':
                soundManager.playSound('cardPayment');
                break;
            case 'wallet':
                soundManager.playSound('walletPayment');
                break;
            case 'khalti':
                soundManager.playSound('khaltiPayment');
                break;
            case 'mobile':
                soundManager.playSound('mobilePayment');
                break;
            default:
                soundManager.playSound('paymentSuccess');
        }
    }
}

function playPaymentFailed() {
    if (soundManager) {
        soundManager.playSound('paymentFailed');
    }
}

function playOrderReceived() {
    if (soundManager) {
        soundManager.playSound('orderReceived');
    }
}

function playPaymentProcessing() {
    if (soundManager) {
        soundManager.playSound('paymentProcessing');
    }
}

function playButtonClick() {
    if (soundManager) {
        soundManager.playSound('buttonClick');
    }
}

function playNotification() {
    if (soundManager) {
        soundManager.playSound('notification');
    }
}

function playWarning() {
    if (soundManager) {
        soundManager.playSound('warning');
    }
}

function toggleSoundMute() {
    if (soundManager) {
        soundManager.toggleMute();
    }
}

function setSoundVolume(volume) {
    if (soundManager) {
        soundManager.setVolume(volume);
    }
}

// Export sound functions to global scope
window.playPaymentSuccess = playPaymentSuccess;
window.playPaymentSuccessWithMethod = playPaymentSuccessWithMethod;
window.playPaymentFailed = playPaymentFailed;
window.playOrderReceived = playOrderReceived;
window.playPaymentProcessing = playPaymentProcessing;
window.playButtonClick = playButtonClick;
window.playNotification = playNotification;
window.playWarning = playWarning;
window.toggleSoundMute = toggleSoundMute;
window.setSoundVolume = setSoundVolume;
