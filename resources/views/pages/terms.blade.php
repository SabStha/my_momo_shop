@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Terms and Conditions</h1>
            
            <div class="prose prose-lg max-w-none">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">1. Acceptance of Terms</h2>
                <p class="text-gray-600 mb-6">
                    By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">2. Use License</h2>
                <p class="text-gray-600 mb-6">
                    Permission is granted to temporarily download one copy of the materials (information or software) on Ama Ko Shop's website for personal, non-commercial transitory viewing only.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">3. Disclaimer</h2>
                <p class="text-gray-600 mb-6">
                    The materials on Ama Ko Shop's website are provided on an 'as is' basis. Ama Ko Shop makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">4. Limitations</h2>
                <p class="text-gray-600 mb-6">
                    In no event shall Ama Ko Shop or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on Ama Ko Shop's website.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">5. Revisions and Errata</h2>
                <p class="text-gray-600 mb-6">
                    The materials appearing on Ama Ko Shop's website could include technical, typographical, or photographic errors. Ama Ko Shop does not warrant that any of the materials on its website are accurate, complete or current.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">6. Links</h2>
                <p class="text-gray-600 mb-6">
                    Ama Ko Shop has not reviewed all of the sites linked to its website and is not responsible for the contents of any such linked site. The inclusion of any link does not imply endorsement by Ama Ko Shop of the site.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">7. Modifications</h2>
                <p class="text-gray-600 mb-6">
                    Ama Ko Shop may revise these terms of service for its website at any time without notice. By using this website you are agreeing to be bound by the then current version of these Terms and Conditions of Use.
                </p>

                <h2 class="text-xl font-semibold text-gray-800 mb-4">8. Governing Law</h2>
                <p class="text-gray-600 mb-6">
                    These terms and conditions are governed by and construed in accordance with the laws and you irrevocably submit to the exclusive jurisdiction of the courts in that location.
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