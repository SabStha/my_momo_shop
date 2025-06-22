@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6 bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Inventory Orders</h2>
                    <p class="text-sm text-gray-600">Manage and track inventory orders</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.inventory.orders.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-plus mr-2"></i> New Order
                </a>
                <a href="{{ route('admin.inventory.orders.export') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <i class="fas fa-file-export mr-2"></i> Export
                </a>
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.inventory.orders.show', 14) }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <i class="fas fa-truck-loading mr-2"></i> Supplier Order Details
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Debug Info -->
    @if(auth()->user()->hasRole('admin'))
        <div class="mb-6 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium"><strong>Debug Info:</strong></p>
                    <p class="text-sm">User Role: {{ auth()->user()->roles->pluck('name')->first() }}</p>
                    @php
                        $debugBranch = \App\Models\Branch::find(session('selected_branch_id'));
                    @endphp
                    <p class="text-sm">Selected Branch: {{ $debugBranch ? $debugBranch->name : 'None' }}</p>
                    <p class="text-sm">Is Main Branch: {{ $debugBranch && $debugBranch->is_main ? 'Yes' : 'No' }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Branch Orders Section -->
    @if($branchOrders && $branchOrders->count() > 0)
        <div class="mb-6 bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="bg-green-100 p-2 rounded-full">
                        <i class="fas fa-building text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">
                            @if($branch && $branch->is_main)
                                Branch Orders to Process
                            @else
                                My Branch Orders
                            @endif
                        </h3>
                        <p class="text-sm text-gray-600">
                            @if($branch && $branch->is_main)
                                Orders requested by other branches that need processing
                            @else
                                Orders you've requested from the main branch
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($branchOrders as $requestingBranchId => $orders)
                    @php
                        $requestingBranch = $orders->first()->requestingBranch;
                        $latestOrder = $orders->first();
                        $pendingCount = $orders->where('status', 'pending')->count();
                        $totalCount = $orders->count();
                    @endphp
                    
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:border-green-300 transition-colors">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 rounded-full {{ $pendingCount > 0 ? 'bg-yellow-400' : 'bg-green-400' }}"></div>
                                <h4 class="font-semibold text-gray-800">{{ $requestingBranch->name }}</h4>
                            </div>
                            <span class="text-xs text-gray-500">{{ $totalCount }} order(s)</span>
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <div class="text-sm text-gray-600">
                                <span class="font-medium">Latest Order:</span> #{{ $latestOrder->order_number }}
                            </div>
                            <div class="text-sm text-gray-600">
                                <span class="font-medium">Status:</span> 
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $latestOrder->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                       ($latestOrder->status === 'received' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ ucfirst($latestOrder->status) }}
                                </span>
                            </div>
                            @if($pendingCount > 0)
                                <div class="text-sm text-yellow-600 font-medium">
                                    ⚠️ {{ $pendingCount }} pending order(s) need attention
                                </div>
                            @endif
                        </div>

                        <div class="flex space-x-2">
                            <a href="{{ route('admin.inventory.orders.show', ['order' => $latestOrder->id, 'branch' => $branch ? $branch->id : null]) }}" 
                               class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <i class="fas fa-eye mr-1"></i>
                                View Latest
                            </a>
                            @if($orders->count() > 1)
                                <button onclick="showAllBranchOrders({{ $requestingBranchId }})" 
                                        class="inline-flex items-center justify-center px-3 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    <i class="fas fa-list mr-1"></i>
                                    All ({{ $orders->count() }})
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Orders Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Delivery</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $order->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->supplier->name ?? 'No Supplier' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->supplier->contact ?? 'No Contact' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->status === 'pending')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @elseif($order->status === 'sent')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Sent</span>
                                @elseif($order->status === 'received')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Received</span>
                                @elseif($order->status === 'cancelled')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->expected_delivery ? $order->expected_delivery->format('Y-m-d') : 'Not set' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex justify-center space-x-3">
                                    <a href="{{ route('admin.inventory.orders.show', ['order' => $order->id, 'branch' => $branch ? $branch->id : null]) }}" 
                                       class="text-blue-600 hover:text-blue-900" 
                                       title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($order->status === 'pending')
                                        <button class="text-green-600 hover:text-green-900 confirm-order" 
                                                data-id="{{ $order->id }}"
                                                title="Confirm">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-900 cancel-order" 
                                                data-id="{{ $order->id }}"
                                                title="Cancel">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No orders found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Branch Orders Modal -->
<div id="branchOrdersModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900" id="branchOrdersModalTitle">Branch Orders</h3>
                <button onclick="closeBranchOrdersModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="branchOrdersModalContent">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Branch orders data from PHP
const branchOrdersData = @json($branchOrders);

document.addEventListener('DOMContentLoaded', function() {
    // Confirm order
    document.querySelectorAll('.confirm-order').forEach(function(button) {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to confirm this order?')) {
                fetch(`{{ route('admin.inventory.orders.update', '') }}/${orderId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: 'confirmed'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error confirming order: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error confirming order: ' + error.message);
                });
            }
        });
    });

    // Cancel order
    document.querySelectorAll('.cancel-order').forEach(function(button) {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to cancel this order?')) {
                fetch(`{{ route('admin.inventory.orders.update', '') }}/${orderId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: 'cancelled'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error cancelling order: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error cancelling order: ' + error.message);
                });
            }
        });
    });
});

// Show all branch orders for a specific branch
function showAllBranchOrders(branchId) {
    const orders = branchOrdersData[branchId];
    if (!orders || orders.length === 0) {
        alert('No orders found for this branch');
        return;
    }

    const branchName = orders[0].requesting_branch?.name || 'Unknown Branch';
    const modalTitle = document.getElementById('branchOrdersModalTitle');
    const modalContent = document.getElementById('branchOrdersModalContent');

    modalTitle.textContent = `${branchName} - All Orders (${orders.length})`;

    let content = `
        <div class="space-y-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-semibold text-blue-800 mb-2">Branch Information</h4>
                <p class="text-sm text-blue-700">Branch: ${branchName}</p>
                <p class="text-sm text-blue-700">Total Orders: ${orders.length}</p>
                <p class="text-sm text-blue-700">Pending Orders: ${orders.filter(o => o.status === 'pending').length}</p>
            </div>
            
            <div class="space-y-3">
                <h4 class="font-semibold text-gray-800">Order List</h4>
    `;

    orders.forEach(order => {
        const statusColor = order.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                           (order.status === 'received' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800');
        
        content += `
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <span class="font-semibold text-gray-800">#${order.order_number}</span>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusColor}">
                            ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                        </span>
                    </div>
                    <span class="text-sm text-gray-500">${new Date(order.created_at).toLocaleDateString()}</span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 mb-3">
                    <div>
                        <span class="font-medium">Total Amount:</span> Rs. ${parseFloat(order.total_amount).toFixed(2)}
                    </div>
                    <div>
                        <span class="font-medium">Expected Delivery:</span> ${order.expected_delivery ? new Date(order.expected_delivery).toLocaleDateString() : 'Not set'}
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.inventory.orders.show', '') }}/${order.id}?branch={{ $branch ? $branch->id : '' }}" 
                       class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-eye mr-1"></i>
                        View Details
                    </a>
                </div>
            </div>
        `;
    });

    content += `
            </div>
        </div>
    `;

    modalContent.innerHTML = content;
    document.getElementById('branchOrdersModal').classList.remove('hidden');
}

// Close branch orders modal
function closeBranchOrdersModal() {
    document.getElementById('branchOrdersModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('branchOrdersModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeBranchOrdersModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeBranchOrdersModal();
    }
});
</script>
@endpush
@endsection 
