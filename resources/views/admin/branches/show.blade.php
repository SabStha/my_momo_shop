@extends('layouts.admin')

@section('title', $branch->name)

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    <!-- Branch Header -->
    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-store text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">{{ $branch->name }}</h2>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="text-sm text-gray-600">Code: {{ $branch->code }}</span>
                            @if($branch->is_main)
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Main Branch</span>
                            @endif
                            <span class="px-2 py-1 text-xs rounded-full {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.branches.index') }}" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Branches
                    </a>
                    <button onclick="showEditModal()" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                        <i class="fas fa-edit mr-2"></i>Edit Branch
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Contact Information -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Contact Information</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Address</p>
                    <p class="text-gray-800">{{ $branch->address ?? 'Not specified' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Contact Person</p>
                    <p class="text-gray-800">{{ $branch->contact ?? 'Not specified' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="text-gray-800">{{ $branch->email ?? 'Not specified' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Phone</p>
                    <p class="text-gray-800">{{ $branch->phone ?? 'Not specified' }}</p>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistics</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded">
                    <p class="text-sm text-gray-600">Total Products</p>
                    <p class="text-2xl font-semibold">{{ $branch->products_count }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded">
                    <p class="text-sm text-gray-600">Total Orders</p>
                    <p class="text-2xl font-semibold">{{ $branch->orders_count }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded">
                    <p class="text-sm text-gray-600">Total Employees</p>
                    <p class="text-2xl font-semibold">{{ $branch->employees_count }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded">
                    <p class="text-sm text-gray-600">Total Tables</p>
                    <p class="text-2xl font-semibold">{{ $branch->tables_count }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded">
                    <p class="text-sm text-gray-600">Total Wallets</p>
                    <p class="text-2xl font-semibold">{{ $branch->wallets_count }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded">
                    <p class="text-sm text-gray-600">Total Sales</p>
                    <p class="text-2xl font-semibold">${{ number_format($branch->total_sales, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('admin.products.index', ['branch' => $branch->id]) }}" 
                   class="block w-full text-left px-4 py-2 bg-blue-50 text-blue-700 rounded hover:bg-blue-100">
                    <i class="fas fa-box mr-2"></i>Manage Products
                </a>
                <a href="{{ route('admin.orders.index', ['branch' => $branch->id]) }}" 
                   class="block w-full text-left px-4 py-2 bg-green-50 text-green-700 rounded hover:bg-green-100">
                    <i class="fas fa-shopping-cart mr-2"></i>View Orders
                </a>
                <a href="{{ route('admin.employees.index', ['branch' => $branch->id]) }}" 
                   class="block w-full text-left px-4 py-2 bg-purple-50 text-purple-700 rounded hover:bg-purple-100">
                    <i class="fas fa-users mr-2"></i>Manage Employees
                </a>
                <a href="{{ route('admin.tables.index', ['branch' => $branch->id]) }}" 
                   class="block w-full text-left px-4 py-2 bg-yellow-50 text-yellow-700 rounded hover:bg-yellow-100">
                    <i class="fas fa-chair mr-2"></i>Manage Tables
                </a>
                <a href="{{ route('admin.wallets.index', ['branch' => $branch->id]) }}" 
                   class="block w-full text-left px-4 py-2 bg-indigo-50 text-indigo-700 rounded hover:bg-indigo-100">
                    <i class="fas fa-wallet mr-2"></i>Manage Wallets
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Recent Activity</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <!-- Recent Orders -->
                <div>
                    <h4 class="text-md font-semibold text-gray-700 mb-2">Recent Orders</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($branch->orders()->latest()->take(5)->get() as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $order->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($order->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Employees -->
                <div>
                    <h4 class="text-md font-semibold text-gray-700 mb-2">Recent Employees</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hire Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($branch->employees()->latest()->take(5)->get() as $employee)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $employee->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $employee->position }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $employee->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($employee->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $employee->hire_date->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showEditModal() {
        // Implement edit modal functionality
    }
</script>
@endpush 