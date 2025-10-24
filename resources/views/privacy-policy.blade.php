@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-8">
                <h1 class="text-4xl font-bold text-white">Privacy Policy</h1>
                <p class="text-orange-100 mt-2">Last Updated: October 16, 2025</p>
            </div>
            
            <div class="px-6 py-8 prose prose-lg max-w-none">
                <!-- Introduction -->
                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">Introduction</h2>
                <p class="text-gray-700 mb-4">
                    Welcome to AmaKo Momo Shop ("we", "our", or "us"). We are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our mobile application and website.
                </p>
                <p class="text-gray-700 mb-6">
                    By using AmaKo Momo Shop, you agree to the collection and use of information in accordance with this policy.
                </p>

                <!-- Information We Collect -->
                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4 border-b-2 border-orange-500 pb-2">Information We Collect</h2>
                
                <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">1. Personal Information You Provide</h3>
                <p class="text-gray-700 mb-2">When you create an account and use our services, we collect:</p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-4">
                    <li><strong>Account Information:</strong> Full name, email address, phone number, password (encrypted)</li>
                    <li><strong>Delivery Information:</strong> Delivery address, GPS coordinates (with your permission)</li>
                    <li><strong>Payment Information:</strong> Payment method preferences, transaction history, Amako Credits balance</li>
                    <li><strong>Profile Information:</strong> Profile picture, date of birth, dietary preferences (all optional)</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">2. Information Automatically Collected</h3>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6">
                    <li><strong>Device Information:</strong> Device type, OS version, unique identifiers</li>
                    <li><strong>Usage Information:</strong> App features used, pages viewed, time spent</li>
                    <li><strong>Location Information:</strong> GPS location (only with your permission)</li>
                    <li><strong>Order Information:</strong> Order history, items purchased, preferences</li>
                </ul>

                <!-- How We Use Your Information -->
                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4 border-b-2 border-orange-500 pb-2">How We Use Your Information</h2>
                <p class="text-gray-700 mb-2">We use the collected information for:</p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6">
                    <li><strong>Service Delivery:</strong> Process orders, arrange delivery, send updates</li>
                    <li><strong>Payment Processing:</strong> Process payments, manage wallet, prevent fraud</li>
                    <li><strong>Communication:</strong> Order notifications, promotions, support</li>
                    <li><strong>Personalization:</strong> Recommend items, show relevant offers</li>
                    <li><strong>Analytics:</strong> Improve app, fix bugs, develop features</li>
                    <li><strong>Loyalty Program:</strong> Track points, award badges, send rewards</li>
                </ul>

                <!-- How We Share -->
                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4 border-b-2 border-orange-500 pb-2">How We Share Your Information</h2>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-4">
                    <li><strong>Delivery Partners:</strong> Name, phone, address to complete deliveries</li>
                    <li><strong>Payment Processors:</strong> Secure payment processing (eSewa, Khalti, FonePay)</li>
                    <li><strong>Service Providers:</strong> Cloud hosting, analytics, customer support</li>
                    <li><strong>Legal Requirements:</strong> When required by law or to prevent fraud</li>
                </ul>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <p class="font-semibold text-yellow-800 mb-2">We DO NOT:</p>
                    <ul class="list-disc list-inside space-y-1 text-yellow-700">
                        <li>Sell your personal information to third parties</li>
                        <li>Share your data for third-party marketing</li>
                        <li>Use your location when app is closed (except during active delivery)</li>
                        <li>Access your contacts, photos, or other apps without permission</li>
                    </ul>
                </div>

                <!-- Data Security -->
                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4 border-b-2 border-orange-500 pb-2">Data Security</h2>
                <p class="text-gray-700 mb-2">We protect your information using:</p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6">
                    <li><strong>Encryption:</strong> SSL/TLS encryption for all data transmission</li>
                    <li><strong>Secure Storage:</strong> Industry-standard password hashing</li>
                    <li><strong>Access Controls:</strong> Limited employee access</li>
                    <li><strong>Payment Security:</strong> PCI-DSS compliant payment processing</li>
                </ul>

                <!-- Your Rights -->
                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4 border-b-2 border-orange-500 pb-2">Your Privacy Rights</h2>
                <p class="text-gray-700 mb-2">You have the right to:</p>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6">
                    <li><strong>Access Your Data:</strong> Request a copy of your information</li>
                    <li><strong>Correct Your Data:</strong> Update profile and delivery information</li>
                    <li><strong>Delete Your Data:</strong> Request account deletion (30 days)</li>
                    <li><strong>Opt-Out:</strong> Unsubscribe from emails, disable notifications</li>
                    <li><strong>Data Portability:</strong> Export your order history</li>
                </ul>

                <!-- Contact Box -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 mb-8">
                    <h3 class="text-xl font-semibold text-blue-900 mb-3">How to Exercise Your Rights</h3>
                    <p class="text-blue-800 mb-3">To exercise any of these rights:</p>
                    <ul class="space-y-2 text-blue-900">
                        <li>📧 <strong>Email:</strong> <a href="mailto:privacy@amakoshop.com" class="text-blue-600 hover:underline">privacy@amakoshop.com</a></li>
                        <li>📱 <strong>Phone:</strong> +977-1-XXXXXXX</li>
                        <li>⚙️ <strong>In-App:</strong> Profile > Settings > Privacy Settings</li>
                    </ul>
                    <p class="text-blue-700 mt-3">We will respond to your request within 30 days.</p>
                </div>

                <!-- Additional Sections -->
                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4 border-b-2 border-orange-500 pb-2">Children's Privacy</h2>
                <p class="text-gray-700 mb-6">
                    AmaKo Momo Shop is not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4 border-b-2 border-orange-500 pb-2">Data Retention</h2>
                <ul class="list-disc list-inside space-y-2 text-gray-700 mb-6">
                    <li><strong>Account Data:</strong> As long as account is active</li>
                    <li><strong>Order History:</strong> 7 years (legal requirement)</li>
                    <li><strong>Deleted Accounts:</strong> Data deleted within 30 days</li>
                </ul>

                <!-- Summary Box -->
                <div class="bg-green-50 border-l-4 border-green-500 p-6 mb-8">
                    <h2 class="text-2xl font-bold text-green-900 mb-4">Summary (TL;DR)</h2>
                    <ul class="space-y-2 text-green-800">
                        <li>✅ <strong>We collect:</strong> Name, email, phone, address, location, orders, device info</li>
                        <li>✅ <strong>We use it for:</strong> Processing orders, delivery, payments, improving service</li>
                        <li>✅ <strong>We protect it with:</strong> Encryption, secure servers, limited access</li>
                        <li>✅ <strong>We share with:</strong> Delivery partners, payment processors (never sold!)</li>
                        <li>✅ <strong>You control:</strong> Access, update, delete your data anytime</li>
                        <li>✅ <strong>Contact us:</strong> privacy@amakoshop.com for any privacy concerns</li>
                    </ul>
                </div>

                <!-- Footer -->
                <div class="text-center mt-12 pt-8 border-t border-gray-200">
                    <p class="text-gray-600 font-semibold mb-2">Thank you for trusting AmaKo Momo Shop with your information!</p>
                    <p class="text-gray-500 text-sm">This privacy policy is effective as of October 16, 2025</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




