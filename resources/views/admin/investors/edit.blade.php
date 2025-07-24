@extends('layouts.admin')

@section('title', 'Edit Investor')

@section('content')
<div class="px-4 py-6 mx-auto max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Investor</h1>
            <p class="text-gray-600 mt-1">Update investor information and investment details</p>
        </div>
        <a href="{{ route('admin.investors.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 transition duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Investors
        </a>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Investor Information</h2>
        </div>
        
        <form action="{{ route('admin.investors.update', $investor->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <!-- Personal Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $investor->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                           placeholder="Enter full name">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $investor->email) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                           placeholder="Enter email address">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Login Credentials (Optional for editing) -->
            <div class="border-t border-gray-200 pt-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Login Credentials</h3>
                <p class="text-sm text-gray-600 mb-4">Leave password fields empty to keep current password</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="password" id="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                               placeholder="Enter new password (optional)">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('password_confirmation') border-red-500 @enderror"
                               placeholder="Confirm new password">
                        @error('password_confirmation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $investor->phone) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror"
                           placeholder="Enter phone number">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="investment_type" class="block text-sm font-medium text-gray-700 mb-2">Investment Type *</label>
                    <select name="investment_type" id="investment_type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('investment_type') border-red-500 @enderror">
                        <option value="">Select investment type</option>
                        <option value="individual" {{ old('investment_type', $investor->investment_type) == 'individual' ? 'selected' : '' }}>Individual</option>
                        <option value="corporate" {{ old('investment_type', $investor->investment_type) == 'corporate' ? 'selected' : '' }}>Corporate</option>
                        <option value="partnership" {{ old('investment_type', $investor->investment_type) == 'partnership' ? 'selected' : '' }}>Partnership</option>
                    </select>
                    @error('investment_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Company Information (shown/hidden based on investment type) -->
            <div id="companyFields" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" style="display: none;">
                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">Company Name</label>
                    <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $investor->company_name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('company_name') border-red-500 @enderror"
                           placeholder="Enter company name">
                    @error('company_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="company_registration" class="block text-sm font-medium text-gray-700 mb-2">Company Registration Number</label>
                    <input type="text" name="company_registration" id="company_registration" value="{{ old('company_registration', $investor->company_registration) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('company_registration') border-red-500 @enderror"
                           placeholder="Enter registration number">
                    @error('company_registration')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Address Information -->
            <div class="mb-6">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea name="address" id="address" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror"
                          placeholder="Enter full address">{{ old('address', $investor->address) }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Investment Details -->
            <div class="border-t border-gray-200 pt-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Investment Details</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="investment_amount" class="block text-sm font-medium text-gray-700 mb-2">Investment Amount (Rs) *</label>
                        <input type="number" name="investment_amount" id="investment_amount" value="{{ old('investment_amount', $investor->investment_amount) }}" step="0.01" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('investment_amount') border-red-500 @enderror"
                               placeholder="0.00">
                        @error('investment_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="ownership_percentage" class="block text-sm font-medium text-gray-700 mb-2">Ownership Percentage (%) *</label>
                        <input type="number" name="ownership_percentage" id="ownership_percentage" value="{{ old('ownership_percentage', $investor->ownership_percentage) }}" step="0.01" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('ownership_percentage') border-red-500 @enderror"
                               placeholder="0.00">
                        @error('ownership_percentage')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="investment_date" class="block text-sm font-medium text-gray-700 mb-2">Investment Date *</label>
                        <input type="date" name="investment_date" id="investment_date" value="{{ old('investment_date', $investor->investment_date) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('investment_date') border-red-500 @enderror">
                        @error('investment_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select name="status" id="status" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                            <option value="">Select status</option>
                            <option value="active" {{ old('status', $investor->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ old('status', $investor->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="inactive" {{ old('status', $investor->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Branch Selection -->
            <div class="border-t border-gray-200 pt-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Branch Investment</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Select Branch *</label>
                        <select name="branch_id" id="branch_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('branch_id') border-red-500 @enderror">
                            <option value="">Select a branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $investor->branch_id) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }} - {{ $branch->location }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="branch_ownership_percentage" class="block text-sm font-medium text-gray-700 mb-2">Branch Ownership % *</label>
                        <input type="number" name="branch_ownership_percentage" id="branch_ownership_percentage" value="{{ old('branch_ownership_percentage', $investor->branch_ownership_percentage) }}" step="0.01" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('branch_ownership_percentage') border-red-500 @enderror"
                               placeholder="0.00">
                        @error('branch_ownership_percentage')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="border-t border-gray-200 pt-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                              placeholder="Any additional notes about this investor">{{ old('notes', $investor->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.investors.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Update Investor
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const investmentTypeSelect = document.getElementById('investment_type');
    const companyFields = document.getElementById('companyFields');
    
    function toggleCompanyFields() {
        const selectedValue = investmentTypeSelect.value;
        if (selectedValue === 'corporate' || selectedValue === 'partnership') {
            companyFields.style.display = 'block';
        } else {
            companyFields.style.display = 'none';
        }
    }
    
    investmentTypeSelect.addEventListener('change', toggleCompanyFields);
    
    // Initialize on page load
    toggleCompanyFields();
});
</script> 