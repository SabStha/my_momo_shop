<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Security Settings</h2>
    </div>
    <div class="p-6">
        <!-- Password Change Form -->
        <form action="{{ route('profile.password') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password <span class="text-red-500">*</span></label>
                    <input type="password" name="current_password" id="current_password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('current_password') border-red-500 @enderror"
                           placeholder="Enter your current password">
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">New Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" id="new_password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                           placeholder="Enter your new password">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Password must be at least 8 characters long</p>
                </div>
                
                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" id="new_password_confirmation" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Confirm your new password">
                </div>
                
                <div class="flex items-center justify-between">
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Password
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Security Tips -->
        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
            <h3 class="text-sm font-semibold text-blue-900 mb-2">Security Tips</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• Use a strong password with at least 8 characters</li>
                <li>• Include a mix of letters, numbers, and symbols</li>
                <li>• Don't reuse passwords from other accounts</li>
                <li>• Consider using a password manager</li>
            </ul>
        </div>
    </div>
</div> 