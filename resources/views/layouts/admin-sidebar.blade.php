<nav class="navbar navbar-vertical navbar-expand-lg navbar-light">
    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
        <div class="navbar-vertical-content">
            <ul class="navbar-nav flex-column" id="navbarVerticalNav">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-home me-2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Analytics Section -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.customer-analytics.*') || request()->routeIs('admin.sales.*') ? 'active' : '' }}" href="#analyticsSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-chart-line me-2"></i>
                        <span>Analytics</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.customer-analytics.*') || request()->routeIs('admin.sales.*') ? 'show' : '' }}" id="analyticsSubmenu">
                        <ul class="nav flex-column ms-3">
                            <!-- Customer Analytics -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.customer-analytics.*') ? 'active' : '' }}" href="#customerAnalyticsSubmenu" data-bs-toggle="collapse">
                                    <i class="fas fa-users me-2"></i>
                                    <span>Customer Analytics</span>
                                    <i class="fas fa-chevron-down ms-auto"></i>
                                </a>
                                <div class="collapse {{ request()->routeIs('admin.customer-analytics.*') ? 'show' : '' }}" id="customerAnalyticsSubmenu">
                                    <ul class="nav flex-column ms-3">
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('admin.customer-analytics.index') ? 'active' : '' }}" href="{{ route('admin.customer-analytics.index') }}">
                                                <i class="fas fa-tachometer-alt me-2"></i>
                                                <span>Dashboard</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('admin.customer-analytics.segments') ? 'active' : '' }}" href="{{ route('admin.customer-analytics.segments') }}">
                                                <i class="fas fa-layer-group me-2"></i>
                                                <span>Segments</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('admin.customer-analytics.churn') ? 'active' : '' }}" href="{{ route('admin.customer-analytics.churn') }}">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                <span>Churn Risk</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <!-- Sales Analytics -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}" href="{{ route('admin.sales.overview') }}">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    <span>Sales Analytics</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Orders Management -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="#ordersSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-shopping-cart me-2"></i>
                        <span>Orders</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.orders.*') ? 'show' : '' }}" id="ordersSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                                    <i class="fas fa-list me-2"></i>
                                    <span>All Orders</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Products Management -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="#productsSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-box me-2"></i>
                        <span>Products</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.products.*') ? 'show' : '' }}" id="productsSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                                    <i class="fas fa-list me-2"></i>
                                    <span>All Products</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}" href="{{ route('admin.products.create') }}">
                                    <i class="fas fa-plus me-2"></i>
                                    <span>Add Product</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Employee Management -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.employees.*') || request()->routeIs('admin.clock.*') ? 'active' : '' }}" href="#employeeSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-users me-2"></i>
                        <span>Employees</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.employees.*') || request()->routeIs('admin.clock.*') ? 'show' : '' }}" id="employeeSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.employees.index') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}">
                                    <i class="fas fa-list me-2"></i>
                                    <span>All Employees</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.clock.index') ? 'active' : '' }}" href="{{ route('admin.clock.index') }}">
                                    <i class="fas fa-clock me-2"></i>
                                    <span>Clock In/Out</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Creator Management -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.creators.*') ? 'active' : '' }}" href="#creatorsSubmenu" data-bs-toggle="collapse">
                        <i class="fas fa-users me-2"></i>
                        <span>Creators</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.creators.*') ? 'show' : '' }}" id="creatorsSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.creators.index') ? 'active' : '' }}" href="{{ route('admin.creators.index') }}">
                                    <i class="fas fa-list me-2"></i>
                                    <span>All Creators</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.creators.referrals') ? 'active' : '' }}" href="{{ route('admin.creators.referrals') }}">
                                    <i class="fas fa-user-plus me-2"></i>
                                    <span>Referrals</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.creators.payouts') ? 'active' : '' }}" href="{{ route('admin.creators.payouts') }}">
                                    <i class="fas fa-money-bill me-2"></i>
                                    <span>Payouts</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.creators.rewards') ? 'active' : '' }}" href="{{ route('admin.creators.rewards') }}">
                                    <i class="fas fa-trophy me-2"></i>
                                    <span>Rewards</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Settings -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                        <i class="fas fa-cog me-2"></i>
                        <span>Settings</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.referral-settings.index') }}" class="nav-link {{ request()->routeIs('admin.referral-settings.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-gift"></i>
                        <p>Referral Settings</p>
                    </a>
                </li>

                <!-- Activity Logs -->
                <li class="nav-item">
                    <a href="{{ route('admin.activity-logs.index') }}" class="nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                        <i class="fas fa-history me-2"></i>
                        <span>Activity Logs</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav> 