@extends('layouts.admin')

@section('title', 'Add New Supplier')

@section('content')
<div class="max-w-5xl mx-auto py-10 px-4">
    @if(isset($branch))
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $branch->name }}</h2>
                <p class="text-sm text-gray-600">Branch Code: {{ $branch->code }}</p>
            </div>
            <a href="{{ route('admin.suppliers.index', ['branch' => $branch->id]) }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Suppliers
            </a>
        </div>
    </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="bg-gray-100 px-6 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-plus text-blue-500 mr-2"></i> Add New Supplier
            </h2>
        </div>

        <div class="px-6 py-6">
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.suppliers.store') }}" method="POST">
                @csrf
                @if(isset($branch))
                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-base font-semibold text-gray-800 mb-2">Supplier Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('name') border-red-500 bg-red-50 @enderror" required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="contact" class="block text-base font-semibold text-gray-800 mb-2">Contact Person</label>
                        <input type="text" id="contact" name="contact" value="{{ old('contact') }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3">
                    </div>

                    <div>
                        <label for="email" class="block text-base font-semibold text-gray-800 mb-2">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3">
                    </div>

                    <div>
                        <label for="phone" class="block text-base font-semibold text-gray-800 mb-2">Phone</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3">
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="block text-base font-semibold text-gray-800 mb-2">Address</label>
                        <textarea id="address" name="address" rows="3"
                                  class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3">{{ old('address') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_shared" value="1" {{ old('is_shared') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-gray-700">Share this supplier with all branches</span>
                        </label>
                    </div>
                </div>

                <div class="mt-8 flex justify-start gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Add Supplier
                    </button>
                    <a href="{{ route('admin.suppliers.index', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                       class="bg-gray-500 text-white px-6 py-3 rounded hover:bg-gray-600">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection