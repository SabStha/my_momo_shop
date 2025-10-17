@extends('layouts.app')

@section('title', 'Delivery Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">🚗 Delivery Dashboard</h1>
        <p class="text-gray-600 mt-2">Manage your delivery orders</p>
    </div>

    <!-- Available Orders Section -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">📦 Available for Delivery</h2>
        
        @if($availableOrders->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($availableOrders as $order)
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $order->order_number }}</h3>
                                <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($order->created_at)->diffForHumans() }}</p>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                Ready
                            </span>
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="font-semibold text-gray-700 mb-2">📍 Delivery Address:</h4>
                            @php
                                $address = is_string($order->delivery_address) 
                                    ? json_decode($order->delivery_address, true) 
                                    : $order->delivery_address;
                            @endphp
                            @if($address)
                                <div class="bg-blue-50 border border-blue-200 rounded p-3">
                                    @php
                                        $addressParts = [];
                                        if (!empty($address['building_name'])) {
                                            $addressParts[] = '<strong>' . e($address['building_name']) . '</strong>';
                                        }
                                        if (!empty($address['area_locality'])) {
                                            $addressParts[] = e($address['area_locality']);
                                        }
                                        if (!empty($address['ward_number']) && !empty($address['city'])) {
                                            $addressParts[] = 'Ward ' . e($address['ward_number']) . ', ' . e($address['city']);
                                        } elseif (!empty($address['city'])) {
                                            $addressParts[] = e($address['city']);
                                        }
                                    @endphp
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        {!! implode('<br>', $addressParts) !!}
                                    </p>
                                    @if(!empty($address['detailed_directions']))
                                        <div class="mt-2 pt-2 border-t border-blue-200">
                                            <p class="text-xs font-medium text-blue-800">🧭 Directions:</p>
                                            <p class="text-sm text-gray-600 italic mt-1">{{ $address['detailed_directions'] }}</p>
                                        </div>
                                    @endif
                                    @if(!empty($address['city']) || !empty($address['area_locality']))
                                        @php
                                            $mapQuery = urlencode(
                                                ($address['building_name'] ?? '') . ' ' .
                                                ($address['area_locality'] ?? '') . ' ' .
                                                ($address['city'] ?? '')
                                            );
                                        @endphp
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" 
                                           target="_blank" 
                                           class="inline-block mt-2 text-xs font-medium text-blue-600 hover:text-blue-800">
                                            🗺️ Open in Maps
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <div class="mb-4">
                            <h4 class="font-semibold text-gray-700 mb-2">🍽️ Items:</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                @foreach($order->items as $item)
                                    <li>{{ $item->quantity }}x {{ $item->item_name }}</li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-xl font-bold text-gray-900">Total: Rs. {{ number_format($order->total_amount, 2) }}</p>
                        </div>
                        
                        <button 
                            onclick="acceptOrder({{ $order->id }})"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200">
                            ✅ Accept Delivery
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-100 rounded-lg p-8 text-center">
                <p class="text-gray-600">No orders available for delivery at the moment</p>
            </div>
        @endif
    </div>

    <!-- My Deliveries Section -->
    <div>
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">🚗 My Active Deliveries</h2>
        
        @if($assignedOrders->count() > 0)
            <div class="space-y-4">
                @foreach($assignedOrders as $order)
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $order->order_number }}</h3>
                                <p class="text-sm text-gray-600">
                                    Assigned {{ $order->out_for_delivery_at ? \Carbon\Carbon::parse($order->out_for_delivery_at)->diffForHumans() : 'just now' }}
                                </p>
                            </div>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                Out for Delivery
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div>
                                <div class="mb-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">📍 Delivery Address:</h4>
                                    @php
                                        $address = is_string($order->delivery_address) 
                                            ? json_decode($order->delivery_address, true) 
                                            : $order->delivery_address;
                                    @endphp
                                    @if($address)
                                        <div class="bg-blue-50 border border-blue-200 rounded p-3">
                                            @php
                                                $addressParts = [];
                                                if (!empty($address['building_name'])) {
                                                    $addressParts[] = '<strong>' . e($address['building_name']) . '</strong>';
                                                }
                                                if (!empty($address['area_locality'])) {
                                                    $addressParts[] = e($address['area_locality']);
                                                }
                                                if (!empty($address['ward_number']) && !empty($address['city'])) {
                                                    $addressParts[] = 'Ward ' . e($address['ward_number']) . ', ' . e($address['city']);
                                                } elseif (!empty($address['city'])) {
                                                    $addressParts[] = e($address['city']);
                                                }
                                            @endphp
                                            <p class="text-sm text-gray-700 leading-relaxed">
                                                {!! implode('<br>', $addressParts) !!}
                                            </p>
                                            @if(!empty($address['detailed_directions']))
                                                <div class="mt-2 pt-2 border-t border-blue-200">
                                                    <p class="text-xs font-medium text-blue-800">🧭 Directions:</p>
                                                    <p class="text-sm text-gray-600 italic mt-1">{{ $address['detailed_directions'] }}</p>
                                                </div>
                                            @endif
                                            @if(!empty($address['city']) || !empty($address['area_locality']))
                                                @php
                                                    $mapQuery = urlencode(
                                                        ($address['building_name'] ?? '') . ' ' .
                                                        ($address['area_locality'] ?? '') . ' ' .
                                                        ($address['city'] ?? '')
                                                    );
                                                @endphp
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" 
                                                   target="_blank" 
                                                   class="inline-block mt-2 text-xs font-medium text-blue-600 hover:text-blue-800">
                                                    🗺️ Open in Maps
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="mb-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">👤 Customer:</h4>
                                    <p class="text-sm text-gray-600">
                                        {{ $order->customer_name }}<br>
                                        📞 {{ $order->customer_phone }}<br>
                                        ✉️ {{ $order->customer_email }}
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Right Column -->
                            <div>
                                <div class="mb-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">🍽️ Items:</h4>
                                    <ul class="text-sm text-gray-600 space-y-1 bg-gray-50 p-3 rounded">
                                        @foreach($order->items as $item)
                                            <li class="flex justify-between">
                                                <span>{{ $item->quantity }}x {{ $item->item_name }}</span>
                                                <span class="font-medium">Rs. {{ number_format($item->subtotal, 2) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                
                                <div class="mb-4">
                                    <p class="text-xl font-bold text-gray-900">Total: Rs. {{ number_format($order->total_amount, 2) }}</p>
                                    <p class="text-sm text-gray-600">Payment: {{ ucfirst($order->payment_method) }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Delivery Confirmation Form -->
                        <form id="delivery-form-{{ $order->id }}" onsubmit="markAsDelivered(event, {{ $order->id }})" class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-700 mb-3">📸 Mark as Delivered</h4>
                            
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Photo (Required)</label>
                                <input type="file" 
                                       name="delivery_photo" 
                                       accept="image/*"
                                       capture="environment"
                                       required
                                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none">
                                <p class="text-xs text-gray-500 mt-1">Take a photo of the delivered order</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Notes (Optional)</label>
                                <textarea 
                                    name="notes"
                                    rows="2"
                                    placeholder="Any notes about the delivery..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            </div>
                            
                            <button 
                                type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200">
                                ✅ Confirm Delivery
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-100 rounded-lg p-8 text-center">
                <p class="text-gray-600">You have no active deliveries</p>
            </div>
        @endif
    </div>
</div>

<script>
// Accept order for delivery
async function acceptOrder(orderId) {
    const confirmed = await showConfirmationModal(
        'Accept Delivery Order?',
        'You will be assigned this order and customers will see you\'re on the way.',
        'Accept Order',
        'bg-green-600 hover:bg-green-700'
    );
    
    if (!confirmed) return;
    
    // Get current location if available
    navigator.geolocation.getCurrentPosition(
        (position) => {
            sendAcceptRequest(orderId, position.coords.latitude, position.coords.longitude);
        },
        (error) => {
            console.warn('Location not available:', error);
            sendAcceptRequest(orderId, null, null);
        }
    );
}

// Beautiful confirmation modal
function showConfirmationModal(title, message, confirmText = 'Confirm', confirmButtonClass = 'bg-blue-600 hover:bg-blue-700') {
    return new Promise((resolve) => {
        // Create backdrop
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 overflow-y-auto';
        modal.setAttribute('aria-labelledby', 'modal-title');
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        
        modal.innerHTML = `
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" style="opacity: 0; transition: opacity 300ms ease-out;" id="modal-backdrop"></div>
                
                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" 
                     style="opacity: 0; transform: scale(0.95) translateY(20px); transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);"
                     id="modal-panel">
                    
                    <!-- Icon container with gradient background -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 px-6 pt-8 pb-6">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-white shadow-lg">
                            <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="bg-white px-6 pb-6">
                        <div class="text-center">
                            <h3 class="text-2xl font-bold text-gray-900 mb-3" id="modal-title">
                                ${title}
                            </h3>
                            <div class="mt-2">
                                <p class="text-base text-gray-600 leading-relaxed">
                                    ${message}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action buttons -->
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button" 
                                id="modal-confirm-btn"
                                class="${confirmButtonClass} inline-flex justify-center items-center w-full sm:w-auto px-6 py-3 text-base font-semibold text-white shadow-lg rounded-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            ${confirmText}
                        </button>
                        <button type="button" 
                                id="modal-cancel-btn"
                                class="bg-white inline-flex justify-center items-center w-full sm:w-auto px-6 py-3 mt-3 sm:mt-0 text-base font-semibold text-gray-700 border-2 border-gray-300 rounded-xl hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
        
        // Trigger animations
        requestAnimationFrame(() => {
            const backdrop = document.getElementById('modal-backdrop');
            const panel = document.getElementById('modal-panel');
            backdrop.style.opacity = '1';
            panel.style.opacity = '1';
            panel.style.transform = 'scale(1) translateY(0)';
        });
        
        const closeModal = (result) => {
            const backdrop = document.getElementById('modal-backdrop');
            const panel = document.getElementById('modal-panel');
            
            backdrop.style.opacity = '0';
            panel.style.opacity = '0';
            panel.style.transform = 'scale(0.95) translateY(20px)';
            
            setTimeout(() => {
                document.body.removeChild(modal);
                document.body.style.overflow = '';
                resolve(result);
            }, 300);
        };
        
        // Event listeners
        document.getElementById('modal-confirm-btn').onclick = () => closeModal(true);
        document.getElementById('modal-cancel-btn').onclick = () => closeModal(false);
        document.getElementById('modal-backdrop').onclick = () => closeModal(false);
        
        // ESC key handler
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                closeModal(false);
                document.removeEventListener('keydown', handleEscape);
            }
        };
        document.addEventListener('keydown', handleEscape);
    });
}

function sendAcceptRequest(orderId, latitude, longitude) {
    // Show loading notification
    const loadingNotif = showLoadingNotification('Accepting order...');
    
    fetch(`/delivery/orders/${orderId}/accept`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ latitude, longitude })
    })
    .then(response => response.json())
    .then(data => {
        loadingNotif.remove();
        if (data.success) {
            showNotification('Order accepted for delivery!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        loadingNotif.remove();
        console.error('Error:', error);
        showNotification('Failed to accept order', 'error');
    });
}

// Mark as delivered
async function markAsDelivered(event, orderId) {
    event.preventDefault();
    
    const form = event.target;
    const photoInput = form.querySelector('input[name="delivery_photo"]');
    
    // Validate photo is selected
    if (!photoInput.files || !photoInput.files[0]) {
        showNotification('Please select a delivery photo', 'error');
        return;
    }
    
    const confirmed = await showConfirmationModal(
        'Confirm Delivery?',
        'This will mark the order as delivered and notify the customer. Make sure you have handed over the order.',
        'Mark as Delivered',
        'bg-blue-600 hover:bg-blue-700'
    );
    
    if (!confirmed) return;
    
    const formData = new FormData(form);
    
    // Get current location
    navigator.geolocation.getCurrentPosition(
        (position) => {
            formData.append('latitude', position.coords.latitude);
            formData.append('longitude', position.coords.longitude);
            sendDeliveryConfirmation(orderId, formData);
        },
        (error) => {
            console.warn('Location not available:', error);
            sendDeliveryConfirmation(orderId, formData);
        }
    );
}

function sendDeliveryConfirmation(orderId, formData) {
    // Show loading notification
    const loadingNotif = showLoadingNotification('Marking as delivered...');
    
    fetch(`/delivery/orders/${orderId}/delivered`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        loadingNotif.remove();
        if (data.success) {
            showNotification('Order marked as delivered!', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        loadingNotif.remove();
        console.error('Error:', error);
        showNotification('Failed to mark as delivered', 'error');
    });
}

function showNotification(message, type) {
    const isSuccess = type === 'success';
    const bgColor = isSuccess ? 'bg-green-500' : 'bg-red-500';
    const icon = isSuccess 
        ? '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
        : '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
    
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 z-50 transform transition-all duration-300 ease-out';
    notification.style.opacity = '0';
    notification.style.transform = 'translateY(-20px)';
    
    notification.innerHTML = `
        <div class="${bgColor} text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 max-w-md backdrop-blur-sm">
            <div class="flex-shrink-0">
                ${icon}
            </div>
            <p class="font-semibold">${message}</p>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 flex-shrink-0 hover:bg-white hover:bg-opacity-20 rounded-lg p-1 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Trigger animation
    requestAnimationFrame(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    });
    
    // Auto-remove after 4 seconds with fade out animation
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

function showLoadingNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 z-50 transform transition-all duration-300 ease-out';
    notification.style.opacity = '0';
    notification.style.transform = 'translateY(-20px)';
    
    notification.innerHTML = `
        <div class="bg-blue-500 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 max-w-md backdrop-blur-sm">
            <div class="flex-shrink-0">
                <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <p class="font-semibold">${message}</p>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Trigger animation
    requestAnimationFrame(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    });
    
    return notification;
}

// Update location every 10 seconds for active deliveries
@if($assignedOrders->count() > 0)
    setInterval(() => {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                @foreach($assignedOrders as $order)
                    updateOrderLocation({{ $order->id }}, position.coords.latitude, position.coords.longitude);
                @endforeach
            },
            (error) => {
                console.warn('Location update failed:', error);
            }
        );
    }, 10000); // Update every 10 seconds

    function updateOrderLocation(orderId, latitude, longitude) {
        fetch(`/delivery/orders/${orderId}/location`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ latitude, longitude })
        })
        .then(response => response.json())
        .then(data => {
            // Location updated successfully (silent in production)
        })
        .catch(error => {
            console.error('Location update error:', error);
        });
    }
@endif
</script>

<style>
.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
</style>
@endsection

