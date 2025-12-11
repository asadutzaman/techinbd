<div class="bg-light p-30">
    <h5 class="section-title position-relative text-uppercase mb-3">
        <span class="bg-secondary pr-3">My Account</span>
    </h5>
    <div class="nav nav-pills flex-column">
        <a class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}" 
           href="{{ route('customer.dashboard') }}">
            <i class="fa fa-tachometer-alt mr-2"></i>Dashboard
        </a>
        <a class="nav-link {{ request()->routeIs('customer.orders.*') ? 'active' : '' }}" 
           href="{{ route('customer.orders.index') }}">
            <i class="fa fa-shopping-bag mr-2"></i>Orders
        </a>
        <a class="nav-link {{ request()->routeIs('customer.addresses.*') ? 'active' : '' }}" 
           href="{{ route('customer.addresses.index') }}">
            <i class="fa fa-map-marker-alt mr-2"></i>Addresses
        </a>
        <a class="nav-link {{ request()->routeIs('customer.wishlist.*') ? 'active' : '' }}" 
           href="{{ route('customer.wishlist.index') }}">
            <i class="fa fa-heart mr-2"></i>Wishlist
        </a>
        <a class="nav-link {{ request()->routeIs('customer.profile') ? 'active' : '' }}" 
           href="{{ route('customer.profile') }}">
            <i class="fa fa-user mr-2"></i>Profile
        </a>
        <a class="nav-link" href="{{ route('logout') }}" 
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fa fa-sign-out-alt mr-2"></i>Logout
        </a>
    </div>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>