@extends('layouts.app')

@section('title', 'Help & User Guide')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">🦸‍♂️ Help & User Guide</h1>
            <p class="text-lg text-gray-600">Everything you need to know about AmaKo Momo</p>
        </div>

        <!-- Quick Navigation -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">📋 Quick Navigation</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('new-user-guide') }}" class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg hover:from-blue-100 hover:to-purple-100 transition-colors">
                    <span class="text-2xl mr-3">🥟</span>
                    <div>
                        <h3 class="font-semibold text-blue-900">New User Guide</h3>
                        <p class="text-sm text-blue-700">First time here? Start with this!</p>
                    </div>
                </a>
                <a href="#getting-started" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                    <span class="text-2xl mr-3">🚀</span>
                    <div>
                        <h3 class="font-semibold text-blue-900">Getting Started</h3>
                        <p class="text-sm text-blue-700">First time here? Start here!</p>
                    </div>
                </a>
                <a href="#ordering-guide" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                    <span class="text-2xl mr-3">🛒</span>
                    <div>
                        <h3 class="font-semibold text-green-900">How to Order</h3>
                        <p class="text-sm text-green-700">Step-by-step ordering guide</p>
                    </div>
                </a>
                <a href="#gps-location" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                    <span class="text-2xl mr-3">📍</span>
                    <div>
                        <h3 class="font-semibold text-purple-900">GPS Location</h3>
                        <p class="text-sm text-purple-700">Location & delivery setup</p>
                    </div>
                </a>
                <a href="#badge-system" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                    <span class="text-2xl mr-3">🏆</span>
                    <div>
                        <h3 class="font-semibold text-yellow-900">Badge System</h3>
                        <p class="text-sm text-yellow-700">Earn rewards & points</p>
                    </div>
                </a>
                <a href="#payment-guide" class="flex items-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                    <span class="text-2xl mr-3">💳</span>
                    <div>
                        <h3 class="font-semibold text-red-900">Payment Guide</h3>
                        <p class="text-sm text-red-700">Payment methods & security</p>
                    </div>
                </a>
                <a href="#troubleshooting" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <span class="text-2xl mr-3">🔧</span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Troubleshooting</h3>
                        <p class="text-sm text-gray-700">Common issues & solutions</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Getting Started Section -->
        <div id="getting-started" class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">🚀 Getting Started</h2>
            
            <!-- New User Guide Promo -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="text-3xl">🥟</div>
                        <div>
                            <h3 class="font-bold text-blue-900 text-lg">New to AmaKo?</h3>
                            <p class="text-blue-700">Start with our comprehensive new user guide for the best experience!</p>
                        </div>
                    </div>
                    <a href="{{ route('new-user-guide') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all duration-200">
                        View New User Guide
                    </a>
                </div>
            </div>
            
            <div class="space-y-6">
                <div class="border-l-4 border-blue-500 pl-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">1. Welcome to AmaKo!</h3>
                    <p class="text-gray-600 mb-3">AmaKo is your go-to place for delicious momos and more. We're here to make ordering easy and fun!</p>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-blue-800"><strong>💡 Tip:</strong> Try our interactive onboarding tour! Look for the "🦸‍♂️ Start Onboarding Tour" button on the home page.</p>
                    </div>
                </div>

                <div class="border-l-4 border-green-500 pl-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">2. Create Your Account</h3>
                    <p class="text-gray-600 mb-3">Sign up to unlock exclusive features, track your orders, and earn rewards.</p>
                    <a href="{{ route('register') }}" class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">Sign Up Now</a>
                </div>

                <div class="border-l-4 border-purple-500 pl-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">3. Set Your Location</h3>
                    <p class="text-gray-600 mb-3">Enable GPS or enter your address to get accurate delivery times and branch selection.</p>
                    <a href="#gps-location" class="inline-block bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">Learn About GPS</a>
                </div>
            </div>
        </div>

        <!-- Ordering Guide Section -->
        <div id="ordering-guide" class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">🛒 How to Order</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">1</div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Browse Menu</h3>
                            <p class="text-gray-600 text-sm">Explore our delicious menu with momos, combos, drinks, and more.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">2</div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Add to Cart</h3>
                            <p class="text-gray-600 text-sm">Click "Add to Cart" on your favorite items. Customize quantities as needed.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">3</div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Review Cart</h3>
                            <p class="text-gray-600 text-sm">Check your order, apply any available discounts or promo codes.</p>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">4</div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Choose Branch</h3>
                            <p class="text-gray-600 text-sm">Select the nearest branch for pickup or delivery to your location.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">5</div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Payment</h3>
                            <p class="text-gray-600 text-sm">Pay securely with cash, card, or digital wallet options.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">6</div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Track Order</h3>
                            <p class="text-gray-600 text-sm">Get real-time updates on your order status and delivery time.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 bg-yellow-50 p-4 rounded-lg">
                <h3 class="font-semibold text-yellow-900 mb-2">🎯 Pro Tips</h3>
                <ul class="text-sm text-yellow-800 space-y-1">
                    <li>• Use our GPS feature for accurate delivery times</li>
                    <li>• Check for daily offers and discounts</li>
                    <li>• Earn badges and rewards with every order</li>
                    <li>• Save your favorite orders for quick reordering</li>
                </ul>
            </div>
        </div>

        <!-- GPS Location Section -->
        <div id="gps-location" class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">📍 GPS Location Guide</h2>
            
            <div class="space-y-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-900 mb-2">Why Use GPS?</h3>
                    <ul class="text-blue-800 text-sm space-y-1">
                        <li>• Get accurate delivery times</li>
                        <li>• Find the nearest branch automatically</li>
                        <li>• Ensure delivery to the correct address</li>
                        <li>• Save time with automatic location detection</li>
                    </ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">📱 Mobile Devices</h3>
                        <ol class="text-sm text-gray-600 space-y-2">
                            <li>1. Go to your device <strong>Settings</strong></li>
                            <li>2. Navigate to <strong>Privacy</strong> or <strong>Privacy & Security</strong></li>
                            <li>3. Find <strong>Location Services</strong></li>
                            <li>4. Enable location access for your browser</li>
                            <li>5. Return to AmaKo and try GPS again</li>
                        </ol>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">💻 Desktop Browsers</h3>
                        <ol class="text-sm text-gray-600 space-y-2">
                            <li>1. Click the lock/info icon in the address bar</li>
                            <li>2. Look for <strong>Location</strong> or <strong>Site settings</strong></li>
                            <li>3. Change from <strong>Block</strong> to <strong>Allow</strong></li>
                            <li>4. Refresh the page and try GPS again</li>
                        </ol>
                    </div>
                </div>

                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-green-900 mb-2">✅ Alternative Options</h3>
                    <p class="text-green-800 text-sm">If GPS doesn't work, you can always:</p>
                    <ul class="text-green-800 text-sm mt-2 space-y-1">
                        <li>• Enter your address manually</li>
                        <li>• Use the demo location for testing</li>
                        <li>• Contact support for assistance</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Badge System Section -->
        <div id="badge-system" class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">🏆 Badge System Guide</h2>
            
            <div class="space-y-6">
                <div class="bg-gradient-to-r from-yellow-100 to-orange-100 p-4 rounded-lg">
                    <h3 class="font-semibold text-orange-900 mb-2">🎯 How It Works</h3>
                    <p class="text-orange-800 text-sm">Earn points and badges by ordering regularly, trying new items, and engaging with our community. Unlock exclusive rewards and privileges!</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">🥟 Loyalty Badges</h3>
                        <ul class="text-sm text-gray-600 space-y-2">
                            <li>• <strong>Bronze:</strong> 100-500 points</li>
                            <li>• <strong>Silver:</strong> 300-1500 points</li>
                            <li>• <strong>Gold:</strong> 600-3000 points</li>
                            <li>• <strong>Prestige:</strong> 1200+ points</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">🎯 Engagement Badges</h3>
                        <ul class="text-sm text-gray-600 space-y-2">
                            <li>• Try new menu items</li>
                            <li>• Refer friends and family</li>
                            <li>• Share on social media</li>
                            <li>• Participate in community events</li>
                        </ul>
                    </div>
                </div>

                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-purple-900 mb-2">🎁 Available Rewards</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <h4 class="font-semibold text-purple-800 mb-1">Free Items</h4>
                            <ul class="text-purple-700 space-y-1">
                                <li>• Free Momo (any variety)</li>
                                <li>• Free Drink</li>
                                <li>• Tasting Kit</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold text-purple-800 mb-1">Privileges</h4>
                            <ul class="text-purple-700 space-y-1">
                                <li>• Skip the Line</li>
                                <li>• Loyalty Discounts</li>
                                <li>• Event Passes</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Guide Section -->
        <div id="payment-guide" class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">💳 Payment Guide</h2>
            
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-green-900 mb-2">💵 Cash Payment</h3>
                        <p class="text-green-800 text-sm">Pay with cash upon delivery or pickup. Exact change preferred.</p>
                    </div>
                    
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-blue-900 mb-2">💳 Card Payment</h3>
                        <p class="text-blue-800 text-sm">Secure card payments accepted at all branches.</p>
                    </div>
                    
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-purple-900 mb-2">📱 Digital Wallet</h3>
                        <p class="text-purple-800 text-sm">Use your AmaKo wallet for quick and easy payments.</p>
                    </div>
                </div>

                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-yellow-900 mb-2">🔒 Security</h3>
                    <ul class="text-yellow-800 text-sm space-y-1">
                        <li>• All payments are processed securely</li>
                        <li>• Your payment information is encrypted</li>
                        <li>• We never store your card details</li>
                        <li>• Multiple payment options for your convenience</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Troubleshooting Section -->
        <div id="troubleshooting" class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">🔧 Troubleshooting</h2>
            
            <div class="space-y-6">
                <div class="border-l-4 border-red-500 pl-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Can't access GPS location?</h3>
                    <p class="text-gray-600 mb-2">This is usually a browser permission issue.</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Check browser location permissions</li>
                        <li>• Try refreshing the page</li>
                        <li>• Use manual address entry as alternative</li>
                    </ul>
                </div>

                <div class="border-l-4 border-orange-500 pl-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Order not showing up?</h3>
                    <p class="text-gray-600 mb-2">Check your order status and contact support if needed.</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Check your order confirmation email</li>
                        <li>• Look in your account order history</li>
                        <li>• Contact our support team</li>
                    </ul>
                </div>

                <div class="border-l-4 border-blue-500 pl-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Payment issues?</h3>
                    <p class="text-gray-600 mb-2">We're here to help with any payment problems.</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Try a different payment method</li>
                        <li>• Check your card details</li>
                        <li>• Contact your bank if needed</li>
                    </ul>
                </div>

                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-900 mb-2">📞 Need More Help?</h3>
                    <p class="text-blue-800 text-sm mb-3">Our support team is here to help you with any issues.</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('contact') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">Contact Support</a>
                        <a href="{{ route('about') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm">About Us</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Start Onboarding Tour Button -->
        <div class="text-center bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-8 text-white">
            <h2 class="text-2xl font-bold mb-4">🎉 Ready to Get Started?</h2>
            <p class="text-lg mb-6">Take our interactive tour to learn all the features!</p>
            <button onclick="startAmaKoTour()" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                🦸‍♂️ Start Interactive Tour
            </button>
        </div>
    </div>
</div>


@endsection 