<?php
$page_title = 'My Orders';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/auth-check.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/header.php');

$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

if ($current_user['role'] === 'buyer') {
    // Buyer view - their orders
    $query = "SELECT o.* FROM orders o 
              JOIN buyers b ON o.buyer_id = b.buyer_id 
              WHERE b.user_id = ?";
    
    if ($status_filter) {
        $status_filter_safe = $conn->real_escape_string($status_filter);
        $query .= " AND o.order_status = '$status_filter_safe'";
    }
    
    $query .= " ORDER BY o.order_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $current_user['user_id']);
} else if ($current_user['role'] === 'farmer') {
    // Farmer view - orders for their products
    $query = "SELECT DISTINCT o.* FROM orders o 
              JOIN order_details od ON o.order_id = od.order_id 
              JOIN products p ON od.product_id = p.product_id 
              JOIN farmers f ON p.farmer_id = f.farmer_id 
              WHERE f.user_id = ?";
    
    if ($status_filter) {
        $status_filter_safe = $conn->real_escape_string($status_filter);
        $query .= " AND o.order_status = '$status_filter_safe'";
    }
    
    $query .= " ORDER BY o.order_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $current_user['user_id']);
} else {
    // Admin view - all orders
    $query = "SELECT * FROM orders WHERE 1=1";
    
    if ($status_filter) {
        $status_filter_safe = $conn->real_escape_string($status_filter);
        $query .= " AND order_status = '$status_filter_safe'";
    }
    
    $query .= " ORDER BY order_date DESC";
    $stmt = null;
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query($query);
}

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>

<div class="main-wrapper">
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/sidebar.php'); ?>
    
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="top-bar-left">
                <h1 style="margin: 0; color: var(--primary-green); font-size: 28px;">
                    <i class="fas fa-list" style="color: var(--accent-lime); margin-right: 10px;"></i>
                    My Orders
                </h1>
            </div>
            <div class="top-bar-right">
                <a href="/agriconnect/pages/products/marketplace.php" class="btn btn-secondary">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
            </div>
        </div>

        <!-- Filter -->
        <div class="card" style="margin-bottom: 25px;">
            <label style="font-weight: 600; font-size: 13px; color: var(--text-gray); display: block; margin-bottom: 8px;">FILTER BY STATUS</label>
            <select onchange="window.location.href='?status=' + this.value" style="padding: 10px 15px; border-radius: 8px; border: 1px solid var(--border-light); font-size: 14px; background: white;">
                <option value="">All Orders</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <?php if (count($orders) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Order Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Delivery Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td style="font-weight: 600; color: var(--primary-green);">
                                        #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td style="font-weight: 600; color: var(--text-dark);">
                                        ₱<?php echo number_format($order['total_amount'], 2); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo 'badge-' . ($order['order_status'] === 'delivered' ? 'success' : ($order['order_status'] === 'cancelled' ? 'danger' : 'info')); ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $order['estimated_delivery'] ? date('M d, Y', strtotime($order['estimated_delivery'])) : '—'; ?>
                                    </td>
                                    <td>
                                        <a href="/agriconnect/pages/orders/order-details.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 40px;">
                    <i class="fas fa-inbox" style="font-size: 64px; color: var(--text-gray); opacity: 0.3; display: block; margin-bottom: 20px;"></i>
                    <p style="font-size: 18px; color: var(--text-gray); margin: 0 0 10px 0;">No orders found</p>
                    <p style="font-size: 14px; color: var(--text-gray); margin: 0;">
                        <?php echo $current_user['role'] === 'buyer' ? "Start shopping to place your first order" : "No orders to display"; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/footer.php'); ?>
