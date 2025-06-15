@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Notifications Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Recent Notifications</h2>
            <button onclick="loadNotifications()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        <div id="dashboardNotifications" class="space-y-3">
            <!-- Notifications will be loaded here -->
            <div class="text-center text-gray-500 py-4">
                Loading notifications...
            </div>
        </div>
    </div>

    <!-- Branch Indicator -->
    @if($branch)
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $branch->name }}</h2>
                <p class="text-sm text-gray-500">{{ $branch->address }}</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500">Last Updated: {{ now()->format('M d, Y H:i') }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Orders</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalOrders }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-shopping-cart text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Products</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalProducts }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-box text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending Orders</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $pendingOrders }}</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Sales -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Sales</p>
                    <p class="text-2xl font-semibold text-gray-900">${{ number_format($totalSales, 2) }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-dollar-sign text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales Trend</h3>
            <canvas id="salesChart" height="300"></canvas>
        </div>

        <!-- Orders Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Orders Trend</h3>
            <canvas id="ordersChart" height="300"></canvas>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Orders</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentOrders as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $order->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->customer->name ?? 'Guest' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($order->total_amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-gray-100 text-gray-800') }}">
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

    <!-- Churn Analysis Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Customer Churn Analysis</h3>
            <div class="flex space-x-2">
                <a href="{{ route('admin.churn.export') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export Churn Data
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- High Risk Customers -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-medium text-red-800">High Risk</h4>
                        <p class="text-sm text-red-600">30+ days inactive</p>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-semibold text-red-900">{{ $highRiskCustomers ?? 0 }}</p>
                    <p class="text-sm text-red-600">customers</p>
                </div>
            </div>

            <!-- Moderate Risk Customers -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-medium text-yellow-800">Moderate Risk</h4>
                        <p class="text-sm text-yellow-600">20-29 days inactive</p>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-semibold text-yellow-900">{{ $moderateRiskCustomers ?? 0 }}</p>
                    <p class="text-sm text-yellow-600">customers</p>
                </div>
            </div>

            <!-- Low Risk Customers -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-medium text-green-800">Low Risk</h4>
                        <p class="text-sm text-green-600">0-19 days inactive</p>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-semibold text-green-900">{{ $lowRiskCustomers ?? 0 }}</p>
                    <p class="text-sm text-green-600">customers</p>
                </div>
            </div>
        </div>

        <!-- Recent Churn Alerts -->
        <div class="mt-6">
            <h4 class="text-md font-medium text-gray-900 mb-4">Recent Churn Alerts</h4>
            <div class="space-y-4" id="churnAlerts">
                <!-- Alerts will be loaded here via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Top Products</h3>
            <div class="flex space-x-2">
                <a href="{{ route('admin.churn.export') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export Churn Data
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($topProducts as $product)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($product->price, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->order_items_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Load notifications for dashboard
    async function loadDashboardNotifications() {
        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('/admin/notifications/churn-risks', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                if (response.status === 401) {
                    throw new Error('Please log in to view notifications');
                }
                if (response.status === 403) {
                    throw new Error('You do not have permission to view notifications');
                }
                throw new Error(`Server error: ${response.status}`);
            }

            const data = await response.json();
            
            const container = document.getElementById('dashboardNotifications');
            container.innerHTML = '';
            
            if (!Array.isArray(data)) {
                throw new Error('Invalid response format');
            }
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-gray-500 py-4">
                        No new notifications
                    </div>
                `;
                return;
            }

            data.forEach(notification => {
                const notificationHtml = `
                    <div class="notification-card ${getNotificationClass(notification.type)} border rounded-lg p-3">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="${getNotificationIcon(notification.type)} text-xl"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <h4 class="text-sm font-semibold">${notification.title}</h4>
                                <p class="text-sm mt-1">${notification.message}</p>
                                <p class="text-xs mt-2 text-gray-500">${new Date(notification.timestamp).toLocaleString()}</p>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += notificationHtml;
            });
        } catch (error) {
            console.error('Error loading notifications:', error);
            document.getElementById('dashboardNotifications').innerHTML = `
                <div class="text-center text-red-500 py-4">
                    ${error.message || 'Error loading notifications'}
                </div>
            `;
        }
    }

    function getNotificationClass(type) {
        const classes = {
            'info': 'bg-blue-50 border-blue-200 text-blue-800',
            'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800',
            'danger': 'bg-red-50 border-red-200 text-red-800',
            'success': 'bg-green-50 border-green-200 text-green-800'
        };
        return classes[type] || classes.info;
    }

    function getNotificationIcon(type) {
        const icons = {
            'info': 'fas fa-info-circle text-blue-500',
            'warning': 'fas fa-exclamation-triangle text-yellow-500',
            'danger': 'fas fa-exclamation-circle text-red-500',
            'success': 'fas fa-check-circle text-green-500'
        };
        return icons[type] || icons.info;
    }

    // Load notifications when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboardNotifications();
        
        // Refresh notifications every 5 minutes
        setInterval(loadDashboardNotifications, 300000);
    });

    // Initialize charts
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');

    // Sales Chart
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($profitAnalysis->pluck('date')->reverse()) !!},
            datasets: [{
                label: 'Sales',
                data: {!! json_encode($profitAnalysis->pluck('total_profit')->reverse()) !!},
                borderColor: '#4F46E5',
                tension: 0.4,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Orders Chart
    new Chart(ordersCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($profitAnalysis->pluck('date')->reverse()) !!},
            datasets: [{
                label: 'Orders',
                data: {!! json_encode(\App\Models\Order::when($branch->id, function($query) use ($branch) {
                    return $query->where('branch_id', $branch->id);
                })
                ->where('status', 'completed')
                ->whereIn('created_at', $profitAnalysis->pluck('date'))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->pluck('count')) !!},
                borderColor: '#10B981',
                tension: 0.4,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Load churn alerts
    async function loadChurnAlerts() {
        try {
            const response = await fetch('/admin/notifications/churn-risks');
            const data = await response.json();
            
            const container = document.getElementById('churnAlerts');
            container.innerHTML = '';
            
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-gray-500 py-4">
                        No churn alerts at this time
                    </div>
                `;
                return;
            }

            data.forEach(alert => {
                const alertHtml = `
                    <div class="bg-${getAlertColor(alert.type)}-50 border border-${getAlertColor(alert.type)}-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="${getAlertIcon(alert.type)} text-${getAlertColor(alert.type)}-600"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <h4 class="text-sm font-medium text-${getAlertColor(alert.type)}-800">${alert.title}</h4>
                                <p class="mt-1 text-sm text-${getAlertColor(alert.type)}-600">${alert.message}</p>
                                <p class="mt-1 text-xs text-${getAlertColor(alert.type)}-500">${new Date(alert.timestamp).toLocaleString()}</p>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += alertHtml;
            });
        } catch (error) {
            console.error('Error loading churn alerts:', error);
            document.getElementById('churnAlerts').innerHTML = `
                <div class="text-center text-red-500 py-4">
                    Error loading churn alerts
                </div>
            `;
        }
    }

    function getAlertColor(type) {
        const colors = {
            'danger': 'red',
            'warning': 'yellow',
            'info': 'blue',
            'success': 'green'
        };
        return colors[type] || 'gray';
    }

    function getAlertIcon(type) {
        const icons = {
            'danger': 'fas fa-exclamation-circle text-xl',
            'warning': 'fas fa-exclamation-triangle text-xl',
            'info': 'fas fa-info-circle text-xl',
            'success': 'fas fa-check-circle text-xl'
        };
        return icons[type] || 'fas fa-bell text-xl';
    }

    // Load churn alerts when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadChurnAlerts();
        
        // Refresh churn alerts every 5 minutes
        setInterval(loadChurnAlerts, 300000);
    });
</script>
@endpush
