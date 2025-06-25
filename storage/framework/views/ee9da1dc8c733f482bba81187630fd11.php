<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full px-0 py-0 mx-auto max-w-7xl">
    <!-- Branch Selection -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <form action="<?php echo e(route('admin.branches.select')); ?>" method="POST" class="flex items-center space-x-3">
            <?php echo csrf_field(); ?>
            <div class="flex-1">
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Select Branch</label>
                <select name="branch_id" id="branch_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">Select a branch</option>
                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($branch->id); ?>" <?php echo e(session('selected_branch_id') == $branch->id ? 'selected' : ''); ?>>
                            <?php echo e($branch->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="pt-5">
                <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Select Branch
                </button>
            </div>
        </form>
    </div>

    <?php if(session('selected_branch_id')): ?>
        <!-- KEY METRICS OVERVIEW -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Total Customers</h3>
                <div class="text-3xl font-bold text-indigo-600"><?php echo e($totalCustomers); ?></div>
                <p class="text-sm text-gray-500 mt-2">Active customers</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Total Orders</h3>
                <div class="text-3xl font-bold text-indigo-600"><?php echo e($totalOrders); ?></div>
                <p class="text-sm text-gray-500 mt-2">This month</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Total Revenue</h3>
                <div class="text-3xl font-bold text-indigo-600">Rs <?php echo e(number_format($totalRevenue, 2)); ?></div>
                <p class="text-sm text-gray-500 mt-2">This month</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Active Campaigns</h3>
                <div class="text-3xl font-bold text-indigo-600"><?php echo e($activeCampaigns); ?></div>
                <p class="text-sm text-gray-500 mt-2">Running campaigns</p>
            </div>
        </div>

        <!-- SALES PERFORMANCE -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Sales Growth -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales Growth</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-indigo-600">Rs <?php echo e(number_format($salesAnalytics['current_month']['revenue'], 0)); ?></div>
                        <div class="text-xs text-gray-500">Revenue</div>
                        <div class="text-xs <?php echo e($salesAnalytics['growth']['revenue'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                            <?php echo e($salesAnalytics['growth']['revenue'] >= 0 ? '+' : ''); ?><?php echo e(number_format($salesAnalytics['growth']['revenue'], 1)); ?>%
                                    </div>
                                </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-indigo-600"><?php echo e($salesAnalytics['current_month']['orders']); ?></div>
                        <div class="text-xs text-gray-500">Orders</div>
                        <div class="text-xs <?php echo e($salesAnalytics['growth']['orders'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                            <?php echo e($salesAnalytics['growth']['orders'] >= 0 ? '+' : ''); ?><?php echo e(number_format($salesAnalytics['growth']['orders'], 1)); ?>%
                                    </div>
                                </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-indigo-600">Rs <?php echo e(number_format($salesAnalytics['current_month']['avg_order'], 0)); ?></div>
                        <div class="text-xs text-gray-500">Avg Order</div>
                        <div class="text-xs <?php echo e($salesAnalytics['growth']['avg_order'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                            <?php echo e($salesAnalytics['growth']['avg_order'] >= 0 ? '+' : ''); ?><?php echo e(number_format($salesAnalytics['growth']['avg_order'], 1)); ?>%
                            </div>
                    </div>
                </div>
            </div>

            <!-- Today's Performance -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Today</h3>
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-3xl font-bold text-blue-600">Rs <?php echo e(number_format($salesAnalytics['today']['revenue'], 0)); ?></div>
                    <div class="text-sm text-gray-600">Revenue</div>
                    <div class="text-xs text-gray-500"><?php echo e($salesAnalytics['today']['orders']); ?> orders</div>
                </div>
            </div>

            <!-- This Week's Performance -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">This Week</h3>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-3xl font-bold text-green-600">Rs <?php echo e(number_format($salesAnalytics['this_week']['revenue'], 0)); ?></div>
                    <div class="text-sm text-gray-600">Revenue</div>
                    <div class="text-xs text-gray-500"><?php echo e($salesAnalytics['this_week']['orders']); ?> orders</div>
                </div>
            </div>
        </div>

        <!-- 3. SALES TRENDS & PATTERNS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Sales & Order Trends -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales & Order Trends (30 Days)</h3>
                <div class="h-64">
                    <canvas id="salesTrendChart"></canvas>
                </div>
                                    </div>

            <!-- Sales by Hour -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales by Hour (This Month)</h3>
                <div class="h-64">
                    <canvas id="salesByHourChart"></canvas>
                                    </div>
                                </div>
                            </div>

        <!-- 4. REVENUE DISTRIBUTION & CUSTOMER INSIGHTS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Revenue by Day of Week -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue by Day of Week</h3>
                <div class="h-64">
                    <canvas id="revenueDistributionChart"></canvas>
                            </div>
                    </div>

            <!-- Customer Segments -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Segments</h3>
                <div class="h-64">
                    <canvas id="customerSegmentsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 5. PRODUCT & PAYMENT ANALYTICS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Top Products -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Top Products This Month</h3>
                    <a href="<?php echo e(route('admin.products.index')); ?>" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
                </div>
                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $topProducts->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900"><?php echo e($product->name); ?></div>
                                <div class="text-sm text-gray-500"><?php echo e($product->total_quantity); ?> units sold</div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">Rs <?php echo e(number_format($product->total_revenue, 2)); ?></div>
                                <div class="text-xs text-gray-500">Revenue</div>
                            </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-4 text-gray-500">No product sales data available</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Methods Distribution -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Methods Distribution</h3>
                    <div class="h-64">
                    <canvas id="paymentMethodsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 6. PRODUCT CATEGORIES -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales by Product Category</h3>
            <div class="h-64">
                <canvas id="productCategoriesChart"></canvas>
            </div>
        </div>

        <!-- 7. MARKETING & AUTOMATION -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Campaign Performance -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Campaign Performance</h3>
                    <a href="<?php echo e(route('admin.campaigns.performance')); ?>" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600"><?php echo e($campaignMetrics['total_redemptions']); ?></div>
                        <div class="text-sm text-gray-600">Total Redemptions</div>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600"><?php echo e(number_format($campaignMetrics['average_open_rate'], 1)); ?>%</div>
                        <div class="text-sm text-gray-600">Avg Open Rate</div>
                    </div>
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600"><?php echo e(number_format($campaignMetrics['average_engagement_rate'], 1)); ?>%</div>
                        <div class="text-sm text-gray-600">Avg Engagement</div>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <div class="text-2xl font-bold <?php echo e($campaignMetrics['average_roi'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                            <?php echo e(number_format($campaignMetrics['average_roi'], 1)); ?>%
                        </div>
                        <div class="text-sm text-gray-600">Avg ROI</div>
                    </div>
                </div>
            </div>

            <!-- Active Campaigns -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Active Campaigns</h3>
                    <a href="<?php echo e(route('admin.campaigns.create')); ?>" class="text-sm text-indigo-600 hover:text-indigo-900">Create New</a>
                </div>
                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $campaigns->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900"><?php echo e($campaign->name); ?></div>
                                <div class="text-sm text-gray-500"><?php echo e(ucfirst($campaign->status)); ?></div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">
                                    <?php echo e(\Carbon\Carbon::parse($campaign->start_date)->format('M d')); ?> - <?php echo e(\Carbon\Carbon::parse($campaign->end_date)->format('M d')); ?>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-4 text-gray-500">No active campaigns</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 8. AUTOMATION RULES -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Automation Rules</h3>
                <a href="<?php echo e(route('admin.rules.create')); ?>" class="text-sm text-indigo-600 hover:text-indigo-900">Create New Rule</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php $__empty_1 = true; $__currentLoopData = $rules->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-medium text-gray-900"><?php echo e($rule->name); ?></h4>
                            <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo e($rule->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?>">
                                <?php echo e($rule->is_active ? 'Active' : 'Inactive'); ?>

                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mb-2"><?php echo e(Str::limit($rule->description, 60)); ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">Priority: <?php echo e($rule->priority); ?></span>
                            <a href="<?php echo e(route('admin.rules.edit', $rule)); ?>" class="text-xs text-indigo-600 hover:text-indigo-900">Edit</a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-full text-center py-8 text-gray-500">No automation rules found</div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900">Please Select a Branch</h3>
                <p class="mt-2 text-sm text-gray-500">Select a branch to view its dashboard and manage its operations.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Combined Sales & Order Trends Chart
        const salesCtx = document.getElementById('salesTrendChart');
        if (salesCtx) {
            new Chart(salesCtx.getContext('2d'), {
            type: 'line',
            data: {
                    labels: <?php echo json_encode($salesTrend->pluck('date') ?? []); ?>,
                datasets: [{
                        label: 'Sales ($)',
                        data: <?php echo json_encode($salesTrend->pluck('amount') ?? []); ?>,
                    borderColor: 'rgb(79, 70, 229)',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        tension: 0.1,
                        yAxisID: 'y'
                    }, {
                        label: 'Orders',
                        data: <?php echo json_encode($salesTrend->pluck('count') ?? []); ?>,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.1,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.dataset.label === 'Sales ($)') {
                                        return `Sales: $${context.parsed.y.toFixed(2)}`;
                                    } else {
                                        return `Orders: ${context.parsed.y}`;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Sales ($)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Orders'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Sales by Hour Chart
        const salesByHourCtx = document.getElementById('salesByHourChart');
        if (salesByHourCtx) {
            new Chart(salesByHourCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($salesByHour->pluck('hour')->map(function($hour) { return $hour . ':00'; }) ?? []); ?>,
                    datasets: [{
                        label: 'Revenue',
                        data: <?php echo json_encode($salesByHour->pluck('total_amount') ?? []); ?>,
                        backgroundColor: 'rgba(79, 70, 229, 0.8)',
                        borderColor: 'rgb(79, 70, 229)',
                        borderWidth: 1
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
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Hour of Day'
                            }
                        }
                    }
                }
            });
        }

        // Payment Methods Pie Chart
        const paymentMethodsCtx = document.getElementById('paymentMethodsChart');
        if (paymentMethodsCtx) {
            new Chart(paymentMethodsCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($paymentMethods->pluck('payment_method')->map(function($method) { return ucfirst($method); }) ?? []); ?>,
                    datasets: [{
                        data: <?php echo json_encode($paymentMethods->pluck('total_amount') ?? []); ?>,
                        backgroundColor: [
                            'rgba(79, 70, 229, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(139, 92, 246, 0.8)'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: $${value.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        }

        // Product Categories Pie Chart
        const productCategoriesCtx = document.getElementById('productCategoriesChart');
        if (productCategoriesCtx) {
            new Chart(productCategoriesCtx.getContext('2d'), {
                type: 'pie',
            data: {
                    labels: <?php echo json_encode($productCategories->pluck('category') ?? []); ?>,
                datasets: [{
                        data: <?php echo json_encode($productCategories->pluck('total_revenue') ?? []); ?>,
                        backgroundColor: [
                            'rgba(79, 70, 229, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: $${value.toFixed(2)} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Customer Segments Pie Chart
        const customerSegmentsCtx = document.getElementById('customerSegmentsChart');
        if (customerSegmentsCtx) {
            new Chart(customerSegmentsCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($customerSegments->pluck('segment') ?? []); ?>,
                    datasets: [{
                        data: <?php echo json_encode($customerSegments->pluck('count') ?? []); ?>,
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(156, 163, 175, 0.8)'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} customers (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Revenue Distribution Chart
        const revenueDistributionCtx = document.getElementById('revenueDistributionChart');
        if (revenueDistributionCtx) {
            new Chart(revenueDistributionCtx.getContext('2d'), {
                type: 'polarArea',
                data: {
                    labels: <?php echo json_encode($revenueDistribution->pluck('day') ?? []); ?>,
                    datasets: [{
                        data: <?php echo json_encode($revenueDistribution->pluck('revenue') ?? []); ?>,
                        backgroundColor: [
                            'rgba(79, 70, 229, 0.6)',
                            'rgba(16, 185, 129, 0.6)',
                            'rgba(245, 158, 11, 0.6)',
                            'rgba(239, 68, 68, 0.6)',
                            'rgba(139, 92, 246, 0.6)',
                            'rgba(236, 72, 153, 0.6)',
                            'rgba(34, 197, 94, 0.6)'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    return `${label}: $${value.toFixed(2)}`;
                                }
                        }
                    }
                }
            }
        });
        }
    });
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>