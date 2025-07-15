@extends('layouts.payment')

@section('content')
    <div id="paymentApp" data-branch-id="{{ $branch->id ?? 1 }}">
        @include('admin.payments.partials.header')
        @include('admin.payments.partials.status-bar')
        <div id="mainPanels" class="relative">
                <div class="flex h-full relative">
                    <!-- Orders Grid - 30% width -->
                    <div class="w-1/3 flex flex-col overflow-hidden border-r border-gray-200">
                    @include('admin.payments.partials.orders.dinein')
                    @include('admin.payments.partials.orders.takeaway')
                    @include('admin.payments.partials.orders.online')
                                </div>
                <!-- Payment Panel - 70% width, increased height -->
                <div class="w-2/3 bg-white shadow-lg border-l border-gray-200 flex flex-col h-[90vh]">
                    @include('admin.payments.partials.payment-panel')
                                                    </div>
                                                    </div>
                                                </div>
        @include('partials.payment-modals')
        @include('admin.payments.partials.modals.cash-drawer')
        @include('admin.payments.partials.modals.settlement')
        @include('admin.payments.partials.modals.alert-settings')
        @include('admin.payments.partials.modals.cash-adjustment')
        @include('admin.payments.partials.modals.physical-drawer-denominations')
        <div id="toastContainer" class="fixed top-6 right-6 z-50 space-y-2"></div>
        <!-- Cash Drawer Actions Dropdown (fixed bottom left) -->
        <div class="fixed bottom-6 left-6 z-50">
            <div class="relative group">
                <button class="px-4 py-2 bg-yellow-500 text-white rounded-md shadow-lg hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 flex items-center">
                    <i class="fas fa-cash-register mr-2"></i> Cash Drawer <i class="fas fa-chevron-up ml-2"></i>
                                                    </button>
                <div class="absolute left-0 bottom-full mb-2 w-56 bg-white rounded-md shadow-lg border border-gray-200 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none group-hover:pointer-events-auto group-focus-within:pointer-events-auto transition-opacity duration-200">
                    <button onclick="openPhysicalCashDrawer()" class="w-full text-left px-4 py-2 hover:bg-yellow-100 text-gray-800 flex items-center">
                        <i class="fas fa-door-open mr-2"></i> Open Physical Cash Drawer
                                                </button>
                    <!-- Add more actions here if needed -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Order section toggle functions
        function toggleDineInSection() {
            const content = document.getElementById('dineInSectionContent');
            const icon = document.getElementById('dineInSectionIcon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        function toggleTakeawaySection() {
            const content = document.getElementById('takeawaySectionContent');
            const icon = document.getElementById('takeawaySectionIcon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        function toggleOnlineSection() {
            const content = document.getElementById('onlineSectionContent');
            const icon = document.getElementById('onlineSectionIcon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        // Sound Management System
        class SoundManager {
            constructor() {
                this.audioContext = null;
                this.isMuted = false;
                this.volume = 0.7;
                this.initializeAudioContext();
                this.loadUserPreferences();
            }

            initializeAudioContext() {
                try {
                    // Initialize Web Audio API
                    this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
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
                if (this.isMuted || !this.audioContext) {
                    return;
                }

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

        // Initialize sound manager
        let soundManager;

        // Initialize sound manager when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            soundManager = new SoundManager();
            soundManager.updateMuteButton();
            
            // Add click handler to initialize audio context on first user interaction
            document.addEventListener('click', function initAudio() {
                if (soundManager && soundManager.audioContext && soundManager.audioContext.state === 'suspended') {
                    soundManager.audioContext.resume();
                }
                document.removeEventListener('click', initAudio);
            }, { once: true });
        });

        // Sound control functions
        function playPaymentSuccess() {
            if (soundManager) {
                // Ensure audio context is resumed if suspended
                if (soundManager.audioContext && soundManager.audioContext.state === 'suspended') {
                    soundManager.audioContext.resume();
                }
                soundManager.playSound('paymentSuccess');
            }
        }

        function playPaymentFailed() {
            if (soundManager) {
                // Ensure audio context is resumed if suspended
                if (soundManager.audioContext && soundManager.audioContext.state === 'suspended') {
                    soundManager.audioContext.resume();
                }
                soundManager.playSound('paymentFailed');
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

        // Modal close functions
        function closeErrorModal() {
            const modal = document.getElementById('errorModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Wire up modal close buttons when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            const errorModalClose = document.getElementById('errorModalClose');
            if (errorModalClose) {
                errorModalClose.addEventListener('click', closeErrorModal);
            }
            
            const successModalClose = document.getElementById('successModalClose');
            if (successModalClose) {
                successModalClose.addEventListener('click', closeSuccessModal);
            }

            // Close error modal when clicking outside
            const errorModal = document.getElementById('errorModal');
            if (errorModal) {
                errorModal.addEventListener('click', function(e) {
                    if (e.target === errorModal) {
                        closeErrorModal();
                    }
                });
            }

            // Close modals with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeErrorModal();
                    closeSuccessModal();
                }
            });
        });
    </script>
@endpush 