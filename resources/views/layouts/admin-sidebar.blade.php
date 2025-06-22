<nav class="w-64 h-screen bg-white border-r shadow flex flex-col">
    <!-- Header/Brand section (fixed at top) -->
    <div class="p-4 border-b border-gray-200 flex-shrink-0">
        <div class="text-center">
            <h1 class="text-xl font-bold text-gray-800">🍲 Momo Admin</h1>
            <p class="text-sm text-gray-600">Control Center</p>
        </div>
    </div>
    
    <!-- Scrollable navigation content -->
    <div class="flex-1 overflow-y-auto">
        <div class="p-4">
            <ul class="space-y-2" x-data="{ 
                payments: {{ request()->routeIs('admin.payments.*') ? 'true' : 'false' }},
                analytics: {{ (request()->routeIs('admin.customer-analytics.*') || request()->routeIs('admin.sales.*')) ? 'true' : 'false' }},
                customerAnalytics: {{ request()->routeIs('admin.customer-analytics.*') ? 'true' : 'false' }},
                orders: {{ request()->routeIs('admin.orders.*') ? 'true' : 'false' }},
                products: {{ request()->routeIs('admin.products.*') ? 'true' : 'false' }},
                employees: {{ (request()->routeIs('admin.employees.*') || request()->routeIs('admin.clock.*')) ? 'true' : 'false' }},
                creators: {{ request()->routeIs('admin.creators.*') ? 'true' : 'false' }},
            }">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-200 font-bold' : '' }}">
                        <i class="fas fa-home mr-2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Payments -->
                <li>
                    <button @click="payments = !payments"
                            class="flex items-center w-full px-4 py-2 rounded hover:bg-gray-100 focus:outline-none"
                            :class="payments ? 'bg-gray-200 font-bold' : ''">
                        <i class="fas fa-credit-card mr-2"></i>
                        <span>Payments</span>
                        <i class="fas fa-chevron-down ml-auto" :class="payments ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="payments" class="pl-6 mt-1 space-y-1" x-cloak>
                        <a href="{{ route('admin.payments.index') }}"
                           class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.payments.index') ? 'bg-gray-200 font-bold' : '' }}">
                            <i class="fas fa-list mr-2"></i>
                            <span>All Payments</span>
                        </a>
                        <a href="{{ route('admin.payments.methods') }}"
                           class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.payments.methods') ? 'bg-gray-200 font-bold' : '' }}">
                            <i class="fas fa-cog mr-2"></i>
                            <span>Payment Methods</span>
                        </a>
                        <a href="{{ route('admin.payments.sessions') }}"
                           class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.payments.sessions') ? 'bg-gray-200 font-bold' : '' }}">
                            <i class="fas fa-clock mr-2"></i>
                            <span>Payment Sessions</span>
                        </a>
                    </div>
                </li>

                <!-- Analytics -->
                <li>
                    <button @click="analytics = !analytics"
                            class="flex items-center w-full px-4 py-2 rounded hover:bg-gray-100 focus:outline-none"
                            :class="analytics ? 'bg-gray-200 font-bold' : ''">
                        <i class="fas fa-chart-line mr-2"></i>
                        <span>Analytics</span>
                        <i class="fas fa-chevron-down ml-auto" :class="analytics ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="analytics" class="pl-6 mt-1 space-y-1" x-cloak>
                        <!-- Customer Analytics -->
                        <div>
                            <button @click="customerAnalytics = !customerAnalytics"
                                    class="flex items-center w-full px-4 py-2 rounded hover:bg-gray-100 focus:outline-none"
                                    :class="customerAnalytics ? 'bg-gray-100 font-bold' : ''">
                                <i class="fas fa-users mr-2"></i>
                                <span>Customer Analytics</span>
                                <i class="fas fa-chevron-down ml-auto" :class="customerAnalytics ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="customerAnalytics" class="pl-6 mt-1 space-y-1" x-cloak>
                                <a href="{{ route('admin.customer-analytics.index') }}"
                                   class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.customer-analytics.index') ? 'bg-gray-200 font-bold' : '' }}">
                                    <i class="fas fa-tachometer-alt mr-2"></i>
                                    <span>Dashboard</span>
                                </a>
                                <a href="{{ route('admin.customer-analytics.segments') }}"
                                   class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.customer-analytics.segments') ? 'bg-gray-200 font-bold' : '' }}">
                                    <i class="fas fa-layer-group mr-2"></i>
                                    <span>Segments</span>
                                </a>
                                <a href="{{ route('admin.customer-analytics.churn') }}"
                                   class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.customer-analytics.churn') ? 'bg-gray-200 font-bold' : '' }}">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <span>Churn Risk</span>
                                </a>
                            </div>
                        </div>
                        <!-- Sales Analytics -->
                        <a href="{{ route('admin.sales.overview') }}"
                           class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.sales.*') ? 'bg-gray-200 font-bold' : '' }}">
                            <i class="fas fa-chart-bar mr-2"></i>
                            <span>Sales Analytics</span>
                        </a>
                    </div>
                </li>

                <!-- Orders -->
                <li>
                    <button @click="orders = !orders"
                            class="flex items-center w-full px-4 py-2 rounded hover:bg-gray-100 focus:outline-none"
                            :class="orders ? 'bg-gray-200 font-bold' : ''">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        <span>Orders</span>
                        <i class="fas fa-chevron-down ml-auto" :class="orders ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="orders" class="pl-6 mt-1 space-y-1" x-cloak>
                        <a href="{{ route('admin.orders.index') }}"
                           class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.orders.index') ? 'bg-gray-200 font-bold' : '' }}">
                            <i class="fas fa-list mr-2"></i>
                            <span>All Orders</span>
                        </a>
                    </div>
                </li>

                <!-- Products -->
                <li>
                    <button @click="products = !products"
                            class="flex items-center w-full px-4 py-2 rounded hover:bg-gray-100 focus:outline-none"
                            :class="products ? 'bg-gray-200 font-bold' : ''">
                        <i class="fas fa-box mr-2"></i>
                        <span>Products</span>
                        <i class="fas fa-chevron-down ml-auto" :class="products ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="products" class="pl-6 mt-1 space-y-1" x-cloak>
                        <a href="{{ route('admin.products.index') }}"
                           class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.products.index') ? 'bg-gray-200 font-bold' : '' }}">
                            <i class="fas fa-list mr-2"></i>
                            <span>All Products</span>
                        </a>
                        <a href="{{ route('admin.products.create') }}"
                           class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.products.create') ? 'bg-gray-200 font-bold' : '' }}">
                            <i class="fas fa-plus mr-2"></i>
                            <span>Add Product</span>
                        </a>
                    </div>
                </li>

                <!-- Employees -->
                <li>
                    <button @click="employees = !employees"
                            class="flex items-center w-full px-4 py-2 rounded hover:bg-gray-100 focus:outline-none"
                            :class="employees ? 'bg-gray-200 font-bold' : ''">
                        <i class="fas fa-users mr-2"></i>
                        <span>Employees</span>
                        <i class="fas fa-chevron-down ml-auto" :class="employees ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="employees" class="pl-6 mt-1 space-y-1" x-cloak>
                        <a href="{{ route('admin.employees.index') }}"
                           class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.employees.index') ? 'bg-gray-200 font-bold' : '' }}">
                            <i class="fas fa-list mr-2"></i>
                            <span>All Employees</span>
                        </a>
                        <a href="{{ route('admin.clock.index') }}"
                           class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.clock.index') ? 'bg-gray-200 font-bold' : '' }}">
                            <i class="fas fa-clock mr-2"></i>
                            <span>Clock In/Out</span>
                        </a>
                    </div>
                </li>

                <!-- Creators -->
                <li>
                    <button @click="creators = !creators"
                            class="flex items-center w-full px-4 py-2 rounded hover:bg-gray-100 focus:outline-none"
                            :class="creators ? 'bg-gray-200 font-bold' : ''">
                        <i class="fas fa-users mr-2"></i>
                        <span>Creators</span>
                        <i class="fas fa-chevron-down ml-auto" :class="creators ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="creators" class="pl-6 mt-1 space-y-1" x-cloak>
                        <a href="{{ route('admin.creators.index') }}"
                           class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.creators.index') ? 'bg-gray-200 font-bold' : '' }}">
                            <i class="fas fa-list mr-2"></i>
                            <span>All Creators</span>
                        </a>
                        <a href="{{ route('admin.creators.referrals') }}"
                           class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.creators.referrals') ? 'bg-gray-200 font-bold' : '' }}">
                            <i class="fas fa-user-plus mr-2"></i>
                            <span>Referrals</span>
                        </a>
                        <a href="{{ route('admin.creators.payouts') }}"
                           class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.creators.payouts') ? 'bg-gray-200 font-bold' : '' }}">
                            <i class="fas fa-money-bill mr-2"></i>
                            <span>Payouts</span>
                        </a>
                        <a href="{{ route('admin.creators.rewards') }}"
                           class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.creators.rewards') ? 'bg-gray-200 font-bold' : '' }}">
                            <i class="fas fa-trophy mr-2"></i>
                            <span>Rewards</span>
                        </a>
                    </div>
                </li>

                <!-- Settings -->
                <li>
                    <a href="{{ route('admin.settings.index') }}"
                       class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.settings.*') ? 'bg-gray-200 font-bold' : '' }}">
                        <i class="fas fa-cog mr-2"></i>
                        <span>Settings</span>
                    </a>
                </li>

                <!-- Referral Settings -->
                <li>
                    <a href="{{ route('admin.referral-settings.index') }}"
                       class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.referral-settings.*') ? 'bg-gray-200 font-bold' : '' }}">
                        <i class="fas fa-gift mr-2"></i>
                        <span>Referral Settings</span>
                    </a>
                </li>

                <!-- Activity Logs -->
                <li>
                    <a href="{{ route('admin.activity-logs.index') }}"
                       class="flex items-center px-4 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.activity-logs.*') ? 'bg-gray-200 font-bold' : '' }}">
                        <i class="fas fa-history mr-2"></i>
                        <span>Activity Logs</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav> 