<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Account Settings</h2>
    </div>
    <div class="p-6 space-y-8">
        
        <!-- Account Recovery -->
        <div class="border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Account Recovery</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Backup Email</p>
                        <p class="text-sm text-gray-500">Add a backup email for account recovery</p>
                    </div>
                    <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                        Add Backup Email
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Backup Phone</p>
                        <p class="text-sm text-gray-500">Add a backup phone number for account recovery</p>
                    </div>
                    <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                        Add Backup Phone
                    </button>
                </div>
            </div>
        </div>

        <!-- Data & Privacy -->
        <div class="border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Data & Privacy</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Download My Data</p>
                        <p class="text-sm text-gray-500">Get a copy of all your data</p>
                    </div>
                    <button class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700">
                        Download Data
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Privacy Settings</p>
                        <p class="text-sm text-gray-500">Manage your privacy preferences</p>
                    </div>
                    <button class="px-4 py-2 text-sm bg-purple-600 text-white rounded hover:bg-purple-700">
                        Manage Privacy
                    </button>
                </div>
            </div>
        </div>

        <!-- Notification Preferences -->
        <div class="border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Preferences</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Email Notifications</p>
                        <p class="text-sm text-gray-500">Order updates, promotions, and newsletters</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">SMS Notifications</p>
                        <p class="text-sm text-gray-500">Order status updates and delivery alerts</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Push Notifications</p>
                        <p class="text-sm text-gray-500">App notifications and real-time updates</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Account Actions -->
        <div class="border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Account Actions</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Deactivate Account</p>
                        <p class="text-sm text-gray-500">Temporarily disable your account</p>
                    </div>
                    <button class="px-4 py-2 text-sm bg-yellow-600 text-white rounded hover:bg-yellow-700">
                        Deactivate
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Delete Account</p>
                        <p class="text-sm text-gray-500">Permanently delete your account and all data</p>
                    </div>
                    <button onclick="showDeleteConfirmation()" class="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Account Modal -->
        <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Delete Account</h3>
                    <p class="text-sm text-gray-600 mb-6">This action cannot be undone. All your data will be permanently deleted.</p>
                    
                    <form action="{{ route('profile.destroy') }}" method="POST" id="deleteForm">
                        @csrf
                        @method('DELETE')
                        <div class="space-y-4">
                            <div>
                                <label for="confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Type <span class="font-bold text-red-600">DELETE</span> to confirm:
                                </label>
                                <input type="text" name="confirmation" id="confirmation" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" 
                                       required>
                            </div>
                            <div>
                                <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                <input type="password" name="password" id="delete_password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="flex space-x-3 mt-6">
                            <button type="button" onclick="hideDeleteConfirmation()" 
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                Delete Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showDeleteConfirmation() {
    document.getElementById('deleteModal').classList.remove('hidden');
}

function hideDeleteConfirmation() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeleteConfirmation();
    }
});
</script>