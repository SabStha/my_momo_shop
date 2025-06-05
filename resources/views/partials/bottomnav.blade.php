<div class="bottom-nav">
    <a href="{{ route('home') }}" class="nav-item {{ request()->is('/') ? 'active' : '' }}">
        <i class="fas fa-home"></i>
        <div>Home</div>
    </a>
    <a href="{{ route('offers') }}" class="nav-item {{ request()->is('offers') ? 'active' : '' }}">
        <i class="fas fa-gift"></i>
        <div>Offers</div>
    </a>
    <a href="{{ route('menu') }}" class="nav-item {{ request()->is('menu') ? 'active' : '' }}">
        <i class="fas fa-utensils"></i>
        <div>Menu</div>
    </a>
    <a href="{{ route('cart') }}" class="nav-item {{ request()->is('cart') ? 'active' : '' }}">
        <i class="fas fa-shopping-cart"></i>
        <div>Cart</div>
    </a>
</div> 