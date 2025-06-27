@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
            <p class="mt-2 text-gray-600">Manage your account information and preferences</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were some errors with your submission:</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Picture Section -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Picture</h3>
                    
                    <div class="flex flex-col items-center">
                        <div class="relative">
                            <img class="h-24 w-24 rounded-full object-cover border-4 border-gray-200" 
                                 src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('images/default-avatar.png') }}" 
                                 alt="{{ $user->name }}'s profile picture">
                            
                            <button onclick="document.getElementById('profile-picture-input').click()" 
                                    class="absolute bottom-0 right-0 bg-[#6E0D25] text-white p-2 rounded-full shadow-lg hover:bg-[#8B0D2F] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                        
                        <form id="profile-picture-form" action="{{ route('profile.picture') }}" method="POST" enctype="multipart/form-data" class="hidden">
                            @csrf
                            <input type="file" id="profile-picture-input" name="profile_picture" accept="image/*" onchange="this.form.submit()">
                        </form>
                        
                        <p class="mt-2 text-sm text-gray-500 text-center">Click the camera icon to update your profile picture</p>
                    </div>
                </div>
            </div>

            <!-- Profile Information Section -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#6E0D25] focus:border-[#6E0D25] sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#6E0D25] focus:border-[#6E0D25] sm:text-sm">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" 
                                    class="w-full sm:w-auto bg-[#6E0D25] text-white px-6 py-2 rounded-md hover:bg-[#8B0D2F] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6E0D25]">
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                    
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="space-y-4">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                <input type="password" name="current_password" id="current_password" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#6E0D25] focus:border-[#6E0D25] sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                <input type="password" name="password" id="password" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#6E0D25] focus:border-[#6E0D25] sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">Password must be at least 8 characters.</p>
                            </div>
                            
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-[#6E0D25] focus:border-[#6E0D25] sm:text-sm">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" 
                                    class="w-full sm:w-auto bg-[#6E0D25] text-white px-6 py-2 rounded-md hover:bg-[#8B0D2F] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6E0D25]">
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Account Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Member Since</span>
                            <span class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-700">User ID</span>
                            <span class="text-sm text-gray-900">#{{ $user->id }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Phone Number</span>
                            <span class="text-sm text-gray-900">{{ $user->phone ?: 'Not provided' }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-3">
                            <span class="text-sm font-medium text-gray-700">Account Status</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit profile picture form when file is selected
    document.getElementById('profile-picture-input').addEventListener('change', function() {
        if (this.files.length > 0) {
            this.form.submit();
        }
    });
</script>
@endpush 