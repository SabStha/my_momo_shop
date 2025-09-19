@php
    $currentBranch = session('selected_branch_id') ? \App\Models\Branch::find(session('selected_branch_id')) : null;
@endphp

<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>

        <!-- Orders -->
        <li class="nav-item">
            <a href="#" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-shopping-cart"></i>
                <p>
                    Orders
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>All Orders</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.orders.pending') }}" class="nav-link {{ request()->routeIs('admin.orders.pending') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Pending Orders</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Products -->
        <li class="nav-item">
            <a href="#" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-box"></i>
                <p>
                    Products
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>All Products</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.products.create') }}" class="nav-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Add New Product</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Bulk Packages -->
        <li class="nav-item">
            <a href="#" class="nav-link {{ request()->routeIs('admin.bulk-packages.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-layer-group"></i>
                <p>
                    Bulk Packages
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('admin.bulk-packages.index') }}" class="nav-link {{ request()->routeIs('admin.bulk-packages.index') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>All Packages</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.bulk-packages.create') }}" class="nav-link {{ request()->routeIs('admin.bulk-packages.create') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Create Package</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Customer Analytics -->
        <li class="nav-item">
            <a href="#" class="nav-link {{ request()->routeIs('admin.customer-analytics.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-line"></i>
                <p>
                    Customer Analytics
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('admin.customer-analytics.index') }}" class="nav-link {{ request()->routeIs('admin.customer-analytics.index') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.customer-analytics.segments') }}" class="nav-link {{ request()->routeIs('admin.customer-analytics.segments') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Segments</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.customer-analytics.churn') }}" class="nav-link {{ request()->routeIs('admin.customer-analytics.churn') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Churn Risk</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Sales Analytics -->
        <li class="nav-item">
            <a href="{{ route('admin.sales.overview') }}" class="nav-link {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-bar"></i>
                <p>Sales Analytics</p>
            </a>
        </li>

        <!-- Settings -->
        <li class="nav-item">
            <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                <i class="nav-icon fas fa-cog"></i>
                <p>Settings</p>
            </a>
        </li>
    </ul>
</nav> 