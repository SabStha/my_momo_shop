@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 to-orange-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-block mb-6">
                <img src="{{ asset('storage/logo/momokologo.png') }}" alt="AmaKo Mascot" class="w-32 h-32 mx-auto mb-4">
            </div>
            <h1 class="text-5xl font-bold text-gray-900 mb-4">
                Join the AmaKo Beta! 🚀
            </h1>
            <p class="text-xl text-gray-700">
                Be among the first to experience our new mobile app
            </p>
        </div>

        <!-- Beta Access Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden mb-8">
            <!-- Status Banner -->
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-white font-semibold">Beta Testing Active</span>
                    </div>
                    <span class="bg-white bg-opacity-20 text-white px-3 py-1 rounded-full text-sm font-medium">
                        Version 1.0.0-beta
                    </span>
                </div>
            </div>

            <!-- Main Content -->
            <div class="p-8">
                <!-- What's New -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="text-3xl">✨</span>
                        What's in This Beta?
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3 p-4 bg-orange-50 rounded-lg">
                            <span class="text-2xl">🥟</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">Full Menu Browsing</h3>
                                <p class="text-sm text-gray-600">Browse our complete momo menu with beautiful images</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 bg-orange-50 rounded-lg">
                            <span class="text-2xl">📍</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">Real-Time Tracking</h3>
                                <p class="text-sm text-gray-600">Track your order live with GPS location</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 bg-orange-50 rounded-lg">
                            <span class="text-2xl">🎁</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">Loyalty Rewards</h3>
                                <p class="text-sm text-gray-600">Earn points and badges with every order</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 bg-orange-50 rounded-lg">
                            <span class="text-2xl">💳</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">Multiple Payments</h3>
                                <p class="text-sm text-gray-600">Cash, eSewa, Khalti, Amako Credits</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Access Code Section -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="text-2xl">🔐</span>
                        Beta Access Code
                    </h2>
                    <p class="text-gray-700 mb-4">
                        Enter the access code to download the beta app:
                    </p>
                    
                    <form id="accessForm" class="space-y-4">
                        <div>
                            <input 
                                type="text" 
                                id="accessCode" 
                                placeholder="Enter access code"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-orange-500 focus:outline-none text-center text-lg font-mono tracking-wider uppercase"
                                maxlength="20"
                            >
                        </div>
                        <button 
                            type="submit"
                            class="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white py-4 rounded-lg font-bold text-lg hover:from-orange-600 hover:to-orange-700 transition-all transform hover:scale-105 shadow-lg"
                        >
                            🔓 Unlock Download
                        </button>
                    </form>
                    
                    <div id="downloadSection" class="hidden mt-6">
                        <div class="bg-green-50 border-2 border-green-200 rounded-lg p-6 mb-4">
                            <h3 class="text-lg font-bold text-green-900 mb-2">✅ Access Granted!</h3>
                            <p class="text-green-800 mb-4">Your beta access has been verified. Download the app below:</p>
                            
                            <a 
                                href="{{ asset('downloads/amako-shop-beta.apk') }}" 
                                download
                                class="inline-flex items-center gap-2 bg-green-600 text-white px-6 py-4 rounded-lg font-bold text-lg hover:bg-green-700 transition-all shadow-lg"
                            >
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/>
                                </svg>
                                Download AmaKo Shop Beta (APK)
                            </a>
                            
                            <p class="text-sm text-gray-600 mt-3">
                                📱 File size: ~50MB | Version: 1.0.0-beta | Build: {{ date('Y-m-d') }}
                            </p>
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-500 mt-4 text-center">
                        Don't have an access code? Contact us: <a href="mailto:beta@amakoshop.com" class="text-orange-600 hover:underline">beta@amakoshop.com</a>
                    </p>
                </div>

                <!-- Installation Instructions -->
                <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="text-2xl">📱</span>
                        Installation Instructions
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center font-bold">
                                1
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">Download the APK file</h3>
                                <p class="text-sm text-gray-600">Use the download button above after entering access code</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center font-bold">
                                2
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">Enable "Unknown Sources"</h3>
                                <div class="bg-white rounded-lg p-3 mt-2 border border-yellow-300">
                                    <p class="text-sm text-gray-700 font-medium mb-2">📲 For Android 8.0+:</p>
                                    <ul class="text-sm text-gray-600 space-y-1 ml-4">
                                        <li>• Go to <strong>Settings</strong></li>
                                        <li>• Tap <strong>Apps & notifications</strong></li>
                                        <li>• Tap <strong>Advanced</strong> > <strong>Special app access</strong></li>
                                        <li>• Tap <strong>Install unknown apps</strong></li>
                                        <li>• Select your browser (e.g., Chrome)</li>
                                        <li>• Enable <strong>"Allow from this source"</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center font-bold">
                                3
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">Install the app</h3>
                                <p class="text-sm text-gray-600">Open the downloaded APK file and tap "Install"</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center font-bold">
                                4
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">Launch and test!</h3>
                                <p class="text-sm text-gray-600">Open the app and start testing. Report any bugs to us!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feedback Section -->
                <div class="bg-purple-50 border-2 border-purple-200 rounded-xl p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="text-2xl">💬</span>
                        We Need Your Feedback!
                    </h2>
                    <p class="text-gray-700 mb-4">
                        As a beta tester, your feedback is invaluable. Please report:
                    </p>
                    <ul class="space-y-2 text-gray-700 mb-4">
                        <li class="flex items-start gap-2">
                            <span class="text-red-500">🐛</span>
                            <span><strong>Bugs:</strong> Any crashes, errors, or unexpected behavior</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500">💡</span>
                            <span><strong>Suggestions:</strong> Features you'd like to see</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-green-500">✨</span>
                            <span><strong>UI/UX:</strong> Design improvements or confusing flows</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-yellow-500">⚡</span>
                            <span><strong>Performance:</strong> Slow loading or laggy screens</span>
                        </li>
                    </ul>
                    
                    <div class="flex gap-3">
                        <a 
                            href="mailto:beta@amakoshop.com?subject=Beta Feedback - AmaKo Shop"
                            class="flex-1 bg-purple-600 text-white px-4 py-3 rounded-lg font-semibold text-center hover:bg-purple-700 transition-all"
                        >
                            📧 Email Feedback
                        </a>
                        <a 
                            href="https://forms.google.com/your-form-id"
                            target="_blank"
                            class="flex-1 bg-purple-600 text-white px-4 py-3 rounded-lg font-semibold text-center hover:bg-purple-700 transition-all"
                        >
                            📝 Feedback Form
                        </a>
                    </div>
                </div>

                <!-- Important Notes -->
                <div class="bg-red-50 border-2 border-red-200 rounded-xl p-6 mb-8">
                    <h2 class="text-xl font-bold text-red-900 mb-4 flex items-center gap-2">
                        <span class="text-2xl">⚠️</span>
                        Important Notes
                    </h2>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex items-start gap-2">
                            <span class="text-red-500 font-bold">•</span>
                            <span><strong>Beta Software:</strong> This app is in testing. Expect bugs and issues.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-red-500 font-bold">•</span>
                            <span><strong>Real Orders:</strong> You can place real orders, but test carefully!</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-red-500 font-bold">•</span>
                            <span><strong>Privacy:</strong> Your data is protected. See our <a href="{{ route('privacy-policy') }}" class="text-orange-600 hover:underline font-semibold">Privacy Policy</a>.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-red-500 font-bold">•</span>
                            <span><strong>Updates:</strong> We'll send updates as we fix bugs and add features.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-red-500 font-bold">•</span>
                            <span><strong>Security Warning:</strong> Only download from this official page.</span>
                        </li>
                    </ul>
                </div>

                <!-- System Requirements -->
                <div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">📋 System Requirements</h2>
                    <ul class="space-y-2 text-gray-700">
                        <li>• <strong>Android Version:</strong> 6.0 (Marshmallow) or higher</li>
                        <li>• <strong>RAM:</strong> 2GB minimum, 4GB recommended</li>
                        <li>• <strong>Storage:</strong> 100MB free space</li>
                        <li>• <strong>Internet:</strong> Required for ordering and tracking</li>
                        <li>• <strong>GPS:</strong> Optional but recommended for delivery</li>
                    </ul>
                </div>

                <!-- FAQ -->
                <div class="space-y-4">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">❓ Frequently Asked Questions</h2>
                    
                    <details class="bg-white border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 transition-colors">
                        <summary class="font-semibold text-gray-900 cursor-pointer">
                            Why do I need to enable "Unknown Sources"?
                        </summary>
                        <p class="text-gray-600 mt-3 text-sm">
                            This beta app is not yet on Google Play Store. Android requires this permission to install apps from sources other than the Play Store. This is safe as long as you download only from this official page.
                        </p>
                    </details>
                    
                    <details class="bg-white border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 transition-colors">
                        <summary class="font-semibold text-gray-900 cursor-pointer">
                            Is my data safe in the beta?
                        </summary>
                        <p class="text-gray-600 mt-3 text-sm">
                            Yes! We use the same security measures as our live website. All data is encrypted and stored securely. Read our <a href="{{ route('privacy-policy') }}" class="text-orange-600 hover:underline">Privacy Policy</a>.
                        </p>
                    </details>
                    
                    <details class="bg-white border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 transition-colors">
                        <summary class="font-semibold text-gray-900 cursor-pointer">
                            Can I place real orders?
                        </summary>
                        <p class="text-gray-600 mt-3 text-sm">
                            Yes! The beta app connects to our live system. Your orders are real and will be delivered. However, please test carefully and report any payment or order issues immediately.
                        </p>
                    </details>
                    
                    <details class="bg-white border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 transition-colors">
                        <summary class="font-semibold text-gray-900 cursor-pointer">
                            How do I get an access code?
                        </summary>
                        <p class="text-gray-600 mt-3 text-sm">
                            Beta access codes are distributed to our loyal customers and testers. Email us at <a href="mailto:beta@amakoshop.com" class="text-orange-600 hover:underline">beta@amakoshop.com</a> to request access.
                        </p>
                    </details>
                    
                    <details class="bg-white border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 transition-colors">
                        <summary class="font-semibold text-gray-900 cursor-pointer">
                            When will the app be on Google Play Store?
                        </summary>
                        <p class="text-gray-600 mt-3 text-sm">
                            We're planning to launch on Google Play Store within the next few weeks after beta testing. You'll be notified when it's available!
                        </p>
                    </details>
                </div>

                <!-- Contact -->
                <div class="mt-8 p-6 bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl text-white text-center">
                    <h3 class="text-xl font-bold mb-2">Need Help? 💬</h3>
                    <p class="mb-4">Our support team is here for you!</p>
                    <div class="flex justify-center gap-4">
                        <a href="mailto:beta@amakoshop.com" class="bg-white text-orange-600 px-6 py-2 rounded-lg font-semibold hover:bg-orange-50 transition-all">
                            📧 Email Support
                        </a>
                        <a href="tel:+977-1-XXXXXXX" class="bg-white text-orange-600 px-6 py-2 rounded-lg font-semibold hover:bg-orange-50 transition-all">
                            📞 Call Us
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Privacy Policy Link -->
        <div class="text-center text-gray-600">
            <p class="mb-2">
                By downloading and using this app, you agree to our 
                <a href="{{ route('privacy-policy') }}" class="text-orange-600 hover:underline font-semibold">Privacy Policy</a> 
                and Terms of Service.
            </p>
            <p class="text-sm">
                © {{ date('Y') }} AmaKo Momo Shop. All rights reserved.
            </p>
        </div>
    </div>
</div>

<script>
// Access code validation
document.getElementById('accessForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const accessCode = document.getElementById('accessCode').value.trim().toUpperCase();
    
    // Valid access codes (you can change these)
    const validCodes = [
        'AMAKO2025',
        'BETA2025',
        'MOMOTEST',
        'TESTAMAKO',
        'BETAUSER'
    ];
    
    if (validCodes.includes(accessCode)) {
        // Show download section
        document.getElementById('downloadSection').classList.remove('hidden');
        
        // Scroll to download section
        document.getElementById('downloadSection').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'nearest' 
        });
        
        // Show success message
        const input = document.getElementById('accessCode');
        input.classList.add('border-green-500', 'bg-green-50');
        input.disabled = true;
        
        // Log access
        console.log('✅ Beta access granted with code:', accessCode);
        
        // Optional: Send analytics or log to server
        fetch('/api/beta-access-log', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ access_code: accessCode })
        }).catch(err => console.log('Analytics error:', err));
        
    } else {
        // Show error
        const input = document.getElementById('accessCode');
        input.classList.add('border-red-500', 'bg-red-50');
        
        alert('❌ Invalid access code. Please check your code and try again.\n\nDon\'t have a code? Email us at beta@amakoshop.com');
        
        setTimeout(() => {
            input.classList.remove('border-red-500', 'bg-red-50');
        }, 2000);
    }
});

// Auto-capitalize access code input
document.getElementById('accessCode').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});
</script>

<style>
/* Smooth animations */
details {
    transition: all 0.3s ease;
}

details[open] {
    background-color: #FFF7ED;
}

/* Download button animation */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
@endsection


