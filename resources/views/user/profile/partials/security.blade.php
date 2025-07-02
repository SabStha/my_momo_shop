<!-- Security Settings -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Security Settings</h2>
        <p class="text-sm text-gray-600 mt-1">Manage your password and account security</p>
    </div>
    <div class="p-6">
        <!-- Change Password Form -->
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')
            
            <div>
                <h3 class="text-md font-medium text-gray-900 mb-4">Change Password</h3>
                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                        <input type="password" 
                               name="current_password" 
                               id="current_password" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('current_password') border-red-500 @enderror"
                               required>
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                               required>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input type="password" 
                               name="password_confirmation" 
                               id="password_confirmation" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Password
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Security Information -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-md font-medium text-gray-900 mb-4">Security Information</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Last Password Change</span>
                    <span class="text-sm text-gray-900">{{ $user->password_changed_at ? $user->password_changed_at->format('M d, Y') : 'Never' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Account Created</span>
                    <span class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Last Login</span>
                    <span class="text-sm text-gray-900">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y g:i A') : 'Unknown' }}</span>
                </div>
            </div>
        </div>
        
        <!-- Security Tips -->
        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <h4 class="text-sm font-medium text-blue-900 mb-2">Security Tips</h4>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• Use a strong password with at least 8 characters</li>
                <li>• Include a mix of letters, numbers, and symbols</li>
                <li>• Never share your password with anyone</li>
                <li>• Log out when using shared devices</li>
            </ul>
        </div>
    </div>
</div> 