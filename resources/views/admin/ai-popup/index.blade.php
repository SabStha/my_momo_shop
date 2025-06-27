@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">🤖 AI Popup Management</h1>
            <p class="text-gray-600 mt-2">Intelligent popup system that decides when and what offers to show</p>
        </div>
        <div class="flex gap-3">
            <button onclick="testAIPopup()" 
                    class="bg-gradient-to-r from-green-600 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-green-700 hover:to-blue-700 transition-all duration-300 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Test AI Popup
            </button>
            <a href="{{ route('ai-popup.analytics') }}" 
               class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition-colors">
                Analytics
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Popups Shown</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-popups">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Click Rate</p>
                    <p class="text-2xl font-bold text-gray-900" id="click-rate">0%</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Conversion Rate</p>
                    <p class="text-2xl font-bold text-gray-900" id="conversion-rate">0%</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Revenue Generated</p>
                    <p class="text-2xl font-bold text-gray-900" id="revenue-generated">Rs 0</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Popup Settings -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">AI Popup Settings</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Popup Timing</label>
                    <select id="popup-timing" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="immediate">Immediate (2 seconds)</option>
                        <option value="delayed">Delayed (5 seconds)</option>
                        <option value="exit_intent">Exit Intent</option>
                        <option value="scroll">On Scroll (50%)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                    <select id="target-audience" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="all">All Users</option>
                        <option value="new">New Customers</option>
                        <option value="returning">Returning Customers</option>
                        <option value="vip">VIP Customers</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Discount</label>
                    <input type="number" id="min-discount" min="5" max="50" value="10" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Discount</label>
                    <input type="number" id="max-discount" min="10" max="100" value="50" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
            </div>
            <div class="mt-6">
                <button onclick="saveAIPopupSettings()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Save Settings
                </button>
            </div>
        </div>
    </div>

    <!-- Recent AI Popup Activity -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent AI Popup Activity</h3>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Offer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Context</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="activity-table">
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No recent activity</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Test AI Popup Modal -->
<div id="testModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Test AI Popup</h3>
            </div>
            <div class="px-6 py-4">
                <p class="text-gray-600 mb-4">Test the AI popup system with different scenarios.</p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Test Context</label>
                    <select id="testContext" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="homepage">Homepage</option>
                        <option value="menu">Menu Page</option>
                        <option value="cart">Cart Page</option>
                        <option value="checkout">Checkout Page</option>
                        <option value="exit_intent">Exit Intent</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
                    <select id="testUserType" class="w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="new">New Customer</option>
                        <option value="returning">Returning Customer</option>
                        <option value="vip">VIP Customer</option>
                        <option value="anonymous">Anonymous User</option>
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <button onclick="closeTestModal()" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    Cancel
                </button>
                <button onclick="runAIPopupTest()" 
                        class="px-4 py-2 bg-gradient-to-r from-green-600 to-blue-600 text-white rounded-md hover:from-green-700 hover:to-blue-700 transition-all duration-300">
                    Run Test
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Load analytics data
document.addEventListener('DOMContentLoaded', function() {
    loadAIPopupAnalytics();
    loadRecentActivity();
});

function loadAIPopupAnalytics() {
    fetch('{{ route("ai-popup.analytics") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-popups').textContent = data.analytics.total_popups_shown || 0;
                document.getElementById('click-rate').textContent = (data.analytics.click_through_rate || 0) + '%';
                document.getElementById('conversion-rate').textContent = (data.analytics.conversion_rate || 0) + '%';
                document.getElementById('revenue-generated').textContent = 'Rs ' + (data.analytics.revenue_generated || 0);
            }
        })
        .catch(error => {
            console.log('Failed to load analytics:', error);
        });
}

function loadRecentActivity() {
    // This would load recent popup activity
    // For now, show placeholder
    const table = document.getElementById('activity-table');
    table.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No recent activity</td></tr>';
}

function testAIPopup() {
    document.getElementById('testModal').classList.remove('hidden');
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
}

function runAIPopupTest() {
    const context = document.getElementById('testContext').value;
    const userType = document.getElementById('testUserType').value;
    
    // Simulate AI popup test
    fetch('{{ route("ai-popup.decision") }}?context=' + context + '&test_user_type=' + userType)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.show_popup) {
                showNotification('AI popup would be shown with ' + data.offer.discount + '% discount', 'success');
            } else {
                showNotification('AI decided not to show popup', 'info');
            }
        })
        .catch(error => {
            showNotification('Test failed: ' + error.message, 'error');
        })
        .finally(() => {
            closeTestModal();
        });
}

function saveAIPopupSettings() {
    const settings = {
        timing: document.getElementById('popup-timing').value,
        target_audience: document.getElementById('target-audience').value,
        min_discount: document.getElementById('min-discount').value,
        max_discount: document.getElementById('max-discount').value,
    };
    
    // Save settings (would implement API endpoint)
    showNotification('Settings saved successfully!', 'success');
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.add('translate-x-0');
        notification.classList.remove('translate-x-full');
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        notification.classList.remove('translate-x-0');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>
@endsection 