<?php
// Sidebar Navigation - varies by role
if (isset($current_user)) {
    $role = $current_user['role'];
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-leaf"></i>
            <span>AGRICONNECT</span>
        </div>
    </div>
    
    <nav class="sidebar-menu">
        <?php if ($role === 'admin'): ?>
            <div class="menu-title">Main Menu</div>
            <ul>
                <li><a href="/agriconnect/pages/dashboard/admin-dashboard.php"><i class="fas fa-chart-pie"></i> <span>Dashboard</span></a></li>
                <li><a href="/agriconnect/pages/users/manage-users.php"><i class="fas fa-users"></i> <span>Users</span></a></li>
                <li><a href="/agriconnect/pages/product/view-products.php"><i class="fas fa-box"></i> <span>Products</span></a></li>
                <li><a href="/agriconnect/pages/orders/view-orders.php"><i class="fas fa-shopping-cart"></i> <span>Orders</span></a></li>
                <li><a href="/agriconnect/pages/profile/user-profile.php"><i class="fas fa-user-circle"></i> <span>Profile</span></a></li>
            </ul>

        <?php elseif ($role === 'farmer'): ?>
            <div class="menu-title">Main Menu</div>
            <ul>
                <li><a href="/agriconnect/pages/dashboard/farmer-dashboard.php"><i class="fas fa-chart-line"></i> <span>Dashboard</span></a></li>
                <li><a href="/agriconnect/pages/products/manage-products.php"><i class="fas fa-leaf"></i> <span>My Products</span></a></li>
                <li><a href="/agriconnect/pages/products/add-products.php"><i class="fas fa-plus-circle"></i> <span>Add Product</span></a></li>
                <li><a href="/agriconnect/pages/orders/view-orders.php"><i class="fas fa-shopping-cart"></i> <span>Orders</span></a></li>
                <li><a href="/agriconnect/pages/profile/user-profile.php"><i class="fas fa-user-circle"></i> <span>Profile</span></a></li>
            </ul>

        <?php elseif ($role === 'buyer'): ?>
            <div class="menu-title">Main Menu</div>
            <ul>
                <li><a href="/agriconnect/pages/dashboard/buyer-dashboard.php"><i class="fas fa-chart-line"></i> <span>Dashboard</span></a></li>
                <li><a href="/agriconnect/pages/products/view-products.php"><i class="fas fa-store"></i> <span>Marketplace</span></a></li>
                <li><a href="/agriconnect/pages/orders/view-orders.php"><i class="fas fa-shopping-bag"></i> <span>My Orders</span></a></li>
                <li><a href="/agriconnect/pages/profile/user-profile.php"><i class="fas fa-user-circle"></i> <span>Profile</span></a></li>
            </ul>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="/agriconnect/pages/logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> LOG OUT
        </a>
    </div>
</aside>
<?php
}
?>
