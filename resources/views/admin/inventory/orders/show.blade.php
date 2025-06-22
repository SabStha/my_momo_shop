@extends('layouts.admin')

@section('title', "Order #{$order->order_number}")

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    <!-- Header Section -->
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-indigo-100 p-3 rounded-full">
                    <i class="fas fa-shopping-cart text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Order #{{ $order->order_number }}</h2>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Created: {{ $order->created_at->format('M d, Y H:i') }}</span>
                        @php
                            $isBranchOrder = $order->requesting_branch_id && $order->requesting_branch_id != $order->branch_id;
                            $statusColor = $order->status === 'received' ? 'bg-green-100 text-green-800' :
                                          ($order->status === 'processed' ? 'bg-blue-100 text-blue-800' :
                                          ($order->status === 'sent' ? 'bg-blue-100 text-blue-800' :
                                          ($order->status === 'pending' ? ($isBranchOrder ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') : 'bg-red-100 text-red-800')));
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                            @if($order->status === 'pending' && $isBranchOrder)
                                Auto-Sent
                            @elseif($order->status === 'processed')
                                Processed
                            @else
                                {{ ucfirst($order->status) }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.inventory.orders.index', ['branch' => $order->branch_id]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Orders
                </a>
                @if($order->status === 'pending' && !$isBranchView)
                    <a href="{{ route('admin.inventory.orders.edit', $order) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <i class="fas fa-edit mr-2"></i>Edit Order
                    </a>
                    <form action="{{ route('admin.inventory.orders.destroy', $order) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                onclick="return confirm('Are you sure you want to delete this order?')">
                            <i class="fas fa-trash mr-2"></i>Delete Order
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column: Order Details -->
        <div class="space-y-6">
            <!-- Supply Chain Status Timeline -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Supply Chain Status</h3>
                @php
                    $isBranchOrder = $order->requesting_branch_id && $order->requesting_branch_id != $order->branch_id;
                    $isSent = in_array($order->status, ['sent', 'supplier_confirmed', 'received', 'processed']);
                    $isProcessed = in_array($order->status, ['processed', 'received']);
                    $stepColor = $isSent ? ($isBranchOrder ? 'bg-green-500' : 'bg-blue-500') : 'bg-gray-300';
                    $connectorColor = $isSent ? ($isBranchOrder ? 'bg-green-500' : 'bg-blue-500') : 'bg-gray-300';
                @endphp
                <div class="relative">
                    @if($isBranchOrder)
                        <!-- Branch Order Timeline -->
                        <div class="flex items-center justify-between">
                            <!-- Step 1: Order Created -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-semibold">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="mt-2 text-center">
                                    <p class="text-sm font-medium text-gray-900">Order Created</p>
                                    <p class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>

                            <!-- Connector 1 -->
                            <div class="flex-1 h-0.5 bg-green-500 mx-4"></div>

                            <!-- Step 2: Sent to Main Branch -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-semibold">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="mt-2 text-center">
                                    <p class="text-sm font-medium text-gray-900">Sent to Main Branch</p>
                                    <p class="text-xs text-gray-500">
                                        <span class="text-green-600 font-medium">Automatically Sent</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Connector 2 -->
                            <div class="flex-1 h-0.5 {{ $isProcessed ? 'bg-blue-500' : 'bg-gray-300' }} mx-4"></div>

                            <!-- Step 3: Processed by Main Branch -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full {{ $isProcessed ? 'bg-blue-500' : 'bg-gray-300' }} flex items-center justify-center text-white font-semibold">
                                    @if($isProcessed)
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="fas fa-cogs"></i>
                                    @endif
                                </div>
                                <div class="mt-2 text-center">
                                    <p class="text-sm font-medium text-gray-900">Processed by Main Branch</p>
                                    <p class="text-xs text-gray-500">
                                        @if($order->status === 'processed')
                                            <span class="text-blue-600 font-medium">Ready for Pickup</span>
                                        @else
                                            Pending
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Connector 3 -->
                            <div class="flex-1 h-0.5 {{ $order->status === 'received' ? 'bg-green-500' : 'bg-gray-300' }} mx-4"></div>

                            <!-- Step 4: Received by Branch -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full {{ $order->status === 'received' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white font-semibold">
                                    @if($order->status === 'received')
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="fas fa-box"></i>
                                    @endif
                                </div>
                                <div class="mt-2 text-center">
                                    <p class="text-sm font-medium text-gray-900">Received by Branch</p>
                                    <p class="text-xs text-gray-500">
                                        @if($order->received_at)
                                            {{ $order->received_at->format('M d, Y H:i') }}
                                        @else
                                            Pending
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Supplier Order Timeline -->
                        <div class="flex items-center justify-between">
                            <!-- Step 1: Order Created -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-semibold">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="mt-2 text-center">
                                    <p class="text-sm font-medium text-gray-900">Order Created</p>
                                    <p class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>

                            <!-- Connector 1 -->
                            <div class="flex-1 h-0.5 {{ $connectorColor }} mx-4"></div>

                            <!-- Step 2: Sent to Supplier -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full {{ $stepColor }} flex items-center justify-center text-white font-semibold">
                                    @if($isSent)
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="fas fa-paper-plane"></i>
                                    @endif
                                </div>
                                <div class="mt-2 text-center">
                                    <p class="text-sm font-medium text-gray-900">Sent to Supplier</p>
                                    <p class="text-xs text-gray-500">
                                        @if($order->sent_at)
                                            {{ $order->sent_at->format('M d, Y H:i') }}
                                        @else
                                            Pending
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Connector 2 -->
                            <div class="flex-1 h-0.5 {{ $order->status === 'received' ? 'bg-blue-500' : 'bg-gray-300' }} mx-4"></div>

                            <!-- Step 3: Supplier Confirmed -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full {{ $order->status === 'supplier_confirmed' || $order->status === 'received' ? 'bg-blue-500' : 'bg-gray-300' }} flex items-center justify-center text-white font-semibold">
                                    @if($order->status === 'supplier_confirmed' || $order->status === 'received')
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="fas fa-clock"></i>
                                    @endif
                                </div>
                                <div class="mt-2 text-center">
                                    <p class="text-sm font-medium text-gray-900">Supplier Confirmed</p>
                                    <p class="text-xs text-gray-500">
                                        @if($order->supplier_confirmed_at)
                                            {{ $order->supplier_confirmed_at->format('M d, Y H:i') }}
                                        @else
                                            Awaiting
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Connector 3 -->
                            <div class="flex-1 h-0.5 {{ $order->status === 'received' ? 'bg-blue-500' : 'bg-gray-300' }} mx-4"></div>

                            <!-- Step 4: Admin Confirmed Receipt -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full {{ $order->status === 'received' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white font-semibold">
                                    @if($order->status === 'received')
                                        <i class="fas fa-check"></i>
                                    @else
                                        <i class="fas fa-box"></i>
                                    @endif
                                </div>
                                <div class="mt-2 text-center">
                                    <p class="text-sm font-medium text-gray-900">Admin Confirmed Receipt</p>
                                    <p class="text-xs text-gray-500">
                                        @if($order->received_at)
                                            {{ $order->received_at->format('M d, Y H:i') }}
                                        @else
                                            Pending
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons for Main Branch -->
            @if(auth()->user()->hasRole('admin') && $order->branch->is_main && !$isBranchView)
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Order Actions</h3>
                    <div class="flex flex-wrap gap-4">
                        @if($order->status === 'pending')
                            @if($order->requesting_branch_id && $order->requesting_branch_id != $order->branch_id)
                                <!-- Branch order - main branch can process it -->
                                <div class="flex flex-wrap gap-4">
                                    <div class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md">
                                        <i class="fas fa-check-circle mr-2"></i>Automatically Sent to Main Branch
                                    </div>
                                    <button onclick="processBranchOrder({{ $order->id }})" 
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        <i class="fas fa-cogs mr-2"></i>Process & Fulfill Order
                                    </button>
                                </div>
                            @else
                                <!-- Main branch order to external supplier -->
                                <button onclick="sendToSupplier({{ $order->id }})" 
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <i class="fas fa-paper-plane mr-2"></i>Send to Supplier
                                </button>
                            @endif
                        @endif

                        @if($order->status === 'sent')
                            <button onclick="confirmSupplierDelivery({{ $order->id }})" 
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                <i class="fas fa-check-circle mr-2"></i>Confirm Supplier Delivery
                            </button>
                        @endif

                        @if($order->status === 'processed')
                            @if($isBranchOrder)
                                <!-- Branch order that has been processed - branch can confirm receipt -->
                                <button onclick="confirmBranchReceipt({{ $order->id }})" 
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    <i class="fas fa-check-circle mr-2"></i>Confirm Receipt
                                </button>
                            @else
                                <!-- Processed supplier order - admin can confirm receipt -->
                                <button onclick="confirmReceipt({{ $order->id }})" 
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    <i class="fas fa-check-circle mr-2"></i>Confirm Receipt
                                </button>
                            @endif
                        @endif

                        @if($order->status === 'supplier_confirmed')
                            <button onclick="confirmReceipt({{ $order->id }})" 
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                <i class="fas fa-check-circle mr-2"></i>Confirm Receipt
                            </button>
                        @endif

                        @if($order->status === 'received')
                            <button onclick="distributeToBranches({{ $order->id }})" 
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                <i class="fas fa-truck mr-2"></i>Distribute to Branches
                            </button>
                        @endif

                        @if($order->status !== 'received')
                            <button onclick="cancelOrder({{ $order->id }})" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <i class="fas fa-times mr-2"></i>Cancel Order
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Order Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Details</h3>
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Supplier</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $order->supplier->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Expected Delivery Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $order->expected_delivery_date->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                        <dd class="mt-1 text-sm text-gray-900">Rs. {{ number_format($order->total_amount, 2) }}</dd>
                    </div>
                    @if($order->notes)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Notes</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $order->notes }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->items as $item)
                            @php
                                $supplierQty = $item->quantity;
                                $originalQty = $item->original_quantity ?? $item->quantity;
                                $isEdited = $supplierQty != $originalQty;
                            @endphp
                            <tr @if($isEdited) style="background-color: #fef3c7;" @endif>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->item->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $item->item->sku }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $originalQty }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Supplier Overview -->
        @if(!$isBranchView)
        <div class="space-y-6">
            <!-- Supplier Overview -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Supplier Overview</h3>
                <div class="space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700">{{ $order->supplier->name }}</h4>
                        <div class="mt-2 space-y-1 text-sm text-gray-600">
                            <div><i class="fas fa-phone mr-2"></i>{{ $order->supplier->phone }}</div>
                            <div><i class="fas fa-envelope mr-2"></i>{{ $order->supplier->email }}</div>
                            <div><i class="fas fa-map-marker-alt mr-2"></i>{{ $order->supplier->address }}</div>
                        </div>
                    </div>
                    
                    <!-- Supplier Statistics -->
                    <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $order->supplier->orders->where('status', 'pending')->count() }}</div>
                            <div class="text-xs text-gray-500">Pending</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600">{{ $order->supplier->orders->where('status', 'sent')->count() }}</div>
                            <div class="text-xs text-gray-500">Sent</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $order->supplier->orders->where('status', 'received')->count() }}</div>
                            <div class="text-xs text-gray-500">Received</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders from This Supplier -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Orders from {{ $order->supplier->name }}</h3>
                <div class="space-y-3">
                    @foreach($order->supplier->orders()->where('id', '!=', $order->id)->latest()->take(5)->get() as $recentOrder)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-900">#{{ $recentOrder->order_number }}</span>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $recentOrder->status === 'received' ? 'bg-green-100 text-green-800' :
                                       ($recentOrder->status === 'sent' ? 'bg-blue-100 text-blue-800' :
                                       ($recentOrder->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                    {{ ucfirst($recentOrder->status) }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $recentOrder->created_at->format('M d, Y') }} • Rs. {{ number_format($recentOrder->total_amount, 2) }}
                            </div>
                        </div>
                        <a href="{{ route('admin.inventory.orders.show', $recentOrder) }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View
                        </a>
                    </div>
                    @endforeach
                    
                    @if($order->supplier->orders()->where('id', '!=', $order->id)->count() === 0)
                    <div class="text-center py-4 text-gray-500 text-sm">
                        No other orders from this supplier
                    </div>
                    @endif
                </div>
                
                @if($order->supplier->orders()->where('id', '!=', $order->id)->count() > 5)
                <div class="mt-4 text-center">
                    <a href="{{ route('admin.inventory.orders.supplier-view') }}" 
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View All Orders from {{ $order->supplier->name }}
                    </a>
                </div>
                @endif
            </div>
        @endif

        <!-- Activity Timeline -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Order Activity</h3>
            <div class="space-y-4">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-plus text-green-600 text-sm"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">Order created</p>
                        <p class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                
                @if($order->sent_at)
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-paper-plane text-blue-600 text-sm"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">
                            @if($isBranchOrder)
                                Order sent to main branch
                            @else
                                Order sent to supplier
                            @endif
                        </p>
                        <p class="text-xs text-gray-500">{{ $order->sent_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                @endif
                
                @if($order->status === 'processed' && $isBranchOrder)
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-cogs text-blue-600 text-sm"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">Order processed by main branch</p>
                        <p class="text-xs text-gray-500">Ready for branch pickup</p>
                    </div>
                </div>
                @endif
                
                @if($order->supplier_confirmed_at && !$isBranchView)
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-check text-blue-600 text-sm"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">Supplier confirmed delivery</p>
                        <p class="text-xs text-gray-500">{{ $order->supplier_confirmed_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                @endif
                
                @if($order->received_at)
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-check text-green-600 text-sm"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">
                            @if($isBranchOrder)
                                Branch confirmed receipt
                            @else
                                Admin confirmed receipt
                            @endif
                        </p>
                        <p class="text-xs text-gray-500">{{ $order->received_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Action Modals -->
<div id="actionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">Confirm Action</h3>
            <p class="text-sm text-gray-600 mb-6" id="modalMessage">Are you sure you want to proceed with this action?</p>
            <div class="flex space-x-3">
                <button onclick="closeActionModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                    Cancel
                </button>
                <button id="confirmActionBtn" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

@push('modals')
<!-- Confirm Receipt Modal -->
<div id="confirmReceiptModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Receipt - Detailed</h3>
            <form id="confirmReceiptForm">
                <table class="min-w-full mb-4">
                    <thead>
                        <tr>
                            <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase">Ordered Qty</th>
                            <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase">Supplier Confirmed Qty</th>
                            <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase">Received Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        @php
                            $supplierQty = $item->quantity;
                            $originalQty = $item->original_quantity ?? $item->quantity;
                            $isEdited = $supplierQty != $originalQty;
                        @endphp
                        <tr @if($isEdited) style="background-color: #fef3c7;" @endif>
                            <td class="px-2 py-1 text-sm text-gray-900">{{ $item->item->name }}</td>
                            <td class="px-2 py-1 text-sm text-gray-900">{{ $originalQty }}</td>
                            <td class="px-2 py-1 text-sm text-gray-900">{{ $supplierQty }}</td>
                            <td class="px-2 py-1">
                                <input type="number" name="received_quantities[{{ $item->id }}]" min="0" max="{{ $supplierQty }}" value="{{ $supplierQty }}" class="border rounded px-2 py-1 w-24" required>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mb-4">
                    <label for="receiptNotes" class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                    <textarea id="receiptNotes" name="receipt_notes" rows="2" class="w-full border rounded px-2 py-1"></textarea>
                </div>
                <div class="mb-4">
                    <label for="receiptDate" class="block text-sm font-medium text-gray-700 mb-1">Receipt Date</label>
                    <input type="date" id="receiptDate" name="receipt_date" class="border rounded px-2 py-1 w-48" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeConfirmReceiptModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700">Save & Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
function processBranchOrder(orderId) {
    showActionModal(
        'Process Branch Order',
        'This will process the branch order and mark it as received. The inventory will be updated and the requesting branch will be notified. Continue?',
        () => processBranchOrderAction(orderId)
    );
}

function processBranchOrderAction(orderId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmActionBtn');
    const originalText = confirmBtn.textContent;
    confirmBtn.textContent = 'Processing...';
    confirmBtn.disabled = true;
    
    fetch(`/admin/inventory/orders/${orderId}/process-branch-order`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeActionModal();
            showSuccessMessage(data.message || 'Branch order processed successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alert('Error processing branch order: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error. Please try again.');
    })
    .finally(() => {
        // Reset button state
        confirmBtn.textContent = originalText;
        confirmBtn.disabled = false;
    });
}

function sendToSupplier(orderId) {
    showActionModal(
        'Send to Supplier',
        'Are you sure you want to send this order to the supplier? This action cannot be undone.',
        () => updateOrderStatus(orderId, 'sent')
    );
}

function confirmSupplierDelivery(orderId) {
    showActionModal(
        'Confirm Supplier Delivery',
        'Has the supplier confirmed delivery of all items? This will mark the order as supplier confirmed.',
        () => updateOrderStatus(orderId, 'supplier_confirmed')
    );
}

function confirmReceipt(orderId) {
    document.getElementById('confirmReceiptModal').classList.remove('hidden');
}

function confirmBranchReceipt(orderId) {
    showActionModal(
        'Confirm Branch Receipt',
        'Has the branch received and confirmed this order? This will mark the order as received and update the branch inventory.',
        () => updateOrderStatus(orderId, 'received')
    );
}

function closeConfirmReceiptModal() {
    document.getElementById('confirmReceiptModal').classList.add('hidden');
}

function distributeToBranches(orderId) {
    showActionModal(
        'Distribute to Branches',
        'Are you ready to distribute the received items to the requesting branches?',
        () => distributeItems(orderId)
    );
}

function cancelOrder(orderId) {
    showActionModal(
        'Cancel Order',
        'Are you sure you want to cancel this order? This action cannot be undone.',
        () => updateOrderStatus(orderId, 'cancelled')
    );
}

function showActionModal(title, message, onConfirm) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;
    document.getElementById('confirmActionBtn').onclick = onConfirm;
    document.getElementById('actionModal').classList.remove('hidden');
}

function closeActionModal() {
    document.getElementById('actionModal').classList.add('hidden');
}

function updateOrderStatus(orderId, status) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmActionBtn');
    const originalText = confirmBtn.textContent;
    confirmBtn.textContent = 'Updating...';
    confirmBtn.disabled = true;
    
    fetch(`/admin/inventory/orders/${orderId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeActionModal();
            // Show success message
            showSuccessMessage(data.message || 'Order status updated successfully!');
            // Reload the page to show updated status
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alert('Error updating order status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error. Please try again.');
    })
    .finally(() => {
        // Reset button state
        confirmBtn.textContent = originalText;
        confirmBtn.disabled = false;
    });
}

function showSuccessMessage(message) {
    // Create success message element
    const successDiv = document.createElement('div');
    successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    successDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(successDiv);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (successDiv.parentNode) {
            successDiv.parentNode.removeChild(successDiv);
        }
    }, 3000);
}

function distributeItems(orderId) {
    window.location.href = `/admin/inventory/orders/${orderId}#distribute`;
}

// Close modal when clicking outside
document.getElementById('actionModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeActionModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeActionModal();
    }
});

const confirmReceiptForm = document.getElementById('confirmReceiptForm');
if (confirmReceiptForm) {
    console.log('Form found, adding event listener'); // Debug log
    confirmReceiptForm.addEventListener('submit', function(e) {
        console.log('Form submitted!'); // Debug log
        e.preventDefault();
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Processing...';
        submitBtn.disabled = true;
        
        const form = e.target;
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            if (key.startsWith('received_quantities')) {
                if (!data['received_quantities']) data['received_quantities'] = {};
                const id = key.match(/\[(\d+)\]/)[1];
                data['received_quantities'][id] = parseInt(value);
            } else {
                data[key] = value;
            }
        });
        
        console.log('Submitting data:', data); // Debug log
        console.log('Order ID: {{ $order->id }}'); // Debug log
        console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').getAttribute('content')); // Debug log
        
        const url = '/admin/inventory/orders/{{ $order->id }}/detailed-confirm';
        console.log('Submitting to URL:', url); // Debug log
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log('Response status:', response.status); // Debug log
            console.log('Response headers:', response.headers); // Debug log
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data); // Debug log
            if (data.success) {
                closeConfirmReceiptModal();
                showSuccessMessage(data.message || 'Order received and inventory updated!');
                setTimeout(() => { window.location.reload(); }, 1500);
            } else {
                showErrorMessage(data.message || 'Unknown error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error); // Debug log
            showErrorMessage('Network error: ' + error.message);
        })
        .finally(() => {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
} else {
    console.log('Form not found!'); // Debug log
}

function showErrorMessage(message) {
    // Create error message element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    errorDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(errorDiv);
    
    // Remove after 5 seconds
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.parentNode.removeChild(errorDiv);
        }
    }, 5000);
}
</script>
@endpush

@endsection 