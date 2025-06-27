@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Privacy Policy</h1>
            
            <div class="prose prose-lg max-w-none">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">1. Information We Collect</h2>
                <p class="text-gray-600 mb-6">
                    We collect information you provide directly to us, such as when you create an account, place an order, or contact us for support.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">2. How We Use Your Information</h2>
                <p class="text-gray-600 mb-6">
                    We use the information we collect to provide, maintain, and improve our services, process transactions, and communicate with you.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">3. Information Sharing</h2>
                <p class="text-gray-600 mb-6">
                    We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">4. Data Security</h2>
                <p class="text-gray-600 mb-6">
                    We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">5. Cookies and Tracking</h2>
                <p class="text-gray-600 mb-6">
                    We use cookies and similar tracking technologies to enhance your experience on our website and analyze usage patterns.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">6. Your Rights</h2>
                <p class="text-gray-600 mb-6">
                    You have the right to access, update, or delete your personal information. You may also opt out of certain communications from us.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">7. Changes to This Policy</h2>
                <p class="text-gray-600 mb-6">
                    We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">8. Contact Us</h2>
                <p class="text-gray-600 mb-6">
                    If you have any questions about this privacy policy, please contact us through our website or at the contact information provided.
                </p>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    Back to Registration
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 