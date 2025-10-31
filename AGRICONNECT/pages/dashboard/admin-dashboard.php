<?php
$page_title = 'Dashboard';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/auth-check.php');
checkRole(['admin']);
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/header.php');

// Get dashboard statistics
$stats = [];

$query = "SELECT COUNT(*) as total FROM users";
$result = $conn->query($query);
$stats['total_users'] = $result->fetch_assoc()['total'];

$query = "SELECT COUNT(*) as total FROM farmers";
$result = $conn->query($query);
$stats['total_farmers'] = $result->fetch_assoc()['total'];

$query = "SELECT COUNT(*) as total FROM buyers";
$result = $conn->query($query);
$stats['total_buyers'] = $result->fetch_assoc()['total'];

$query = "SELECT COUNT(*) as total FROM products";
$result = $conn->query($query);
$stats['total_products'] = $result->fetch_assoc()['total'];

$query = "SELECT COUNT(*) as total FROM orders";
$result = $conn->query($query);
$stats['total_orders'] = $result->fetch_assoc()['total'];

$query = "SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE order_status = 'delivered'";
$result = $conn->query($query);
$stats['total_revenue'] = $result->fetch_assoc()['revenue'];
?>

<div class="main-wrapper">
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/sidebar.php'); ?>

    <div class="main-content">
        <div class="top-bar">
            <div class="top-bar-left">
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search here...">
                </div>
            </div>
            <div class="top-bar-right">
                <div class="message-icon">
                    <i class="fas fa-comment"></i>
                </div>
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?></div>
                    <div class="user-details">
                        <h3><?php echo $current_user['full_name']; ?></h3>
                        <p><?php echo ucfirst($current_user['role']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-content">
                    <h3><?php echo $stats['total_users']; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #4a7c2c, #2d5016);">
                <div class="stat-card-content">
                    <h3><?php echo $stats['total_farmers']; ?></h3>
                    <p>Farmers</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="stat-card-content">
                    <h3><?php echo $stats['total_buyers']; ?></h3>
                    <p>Buyers</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                <div class="stat-card-content">
                    <h3><?php echo $stats['total_products']; ?></h3>
                    <p>Products</p>
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header">
                    <h2>Recent Orders</h2>
                    <a href="/agriconnect/pages/orders/view-orders.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">View All</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Buyer</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT o.order_id, o.total_amount, o.order_status, u.full_name 
                                      FROM orders o 
                                      JOIN buyers b ON o.buyer_id = b.buyer_id 
                                      JOIN users u ON b.user_id = u.user_id 
                                      ORDER BY o.order_date DESC LIMIT 5";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()):
                            ?>
                                <tr>
                                    <td>#<?php echo $row['order_id']; ?></td>
                                    <td><?php echo $row['full_name']; ?></td>
                                    <td>₱<?php echo number_format($row['total_amount'], 0); ?></td>
                                    <td><span class="badge badge-info"><?php echo ucfirst($row['order_status']); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="illustration-card">
                <div class="illustration-card-header">
                    <h3>System Overview</h3>
                    <p class="illustration-card-description">Total platform revenue and transaction statistics.</p>
                </div>
                <div style="margin-top: 15px;">
                    <p style="font-size: 28px; font-weight: 700; margin: 10px 0;">₱<?php echo number_format($stats['total_revenue'], 0); ?></p>
                    <p style="font-size: 13px; opacity: 0.9;">Completed Transactions</p>
                </div>
                <p style="margin-top: 20px; font-size: 13px; line-height: 1.6; opacity: 0.95;">
                    Monitor all system activities, manage users, oversee transactions, and maintain platform integrity.
                </p>
            </div>
        </div>

        <!-- Products Overview -->
        <div class="card">
            <div class="card-header">
                <h2>Active Products</h2>
                <a href="/agriconnect/pages/products/view-products.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">Manage</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Farmer</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT p.*, u.full_name FROM products p 
                                  JOIN farmers f ON p.farmer_id = f.farmer_id 
                                  JOIN users u ON f.user_id = u.user_id 
                                  WHERE p.is_active = 1 ORDER BY p.created_at DESC LIMIT 5";
                        $result = $conn->query($query);
                        while ($row = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?php echo $row['product_name']; ?></td>
                                <td><?php echo $row['full_name']; ?></td>
                                <td><?php echo $row['category']; ?></td>
                                <td>₱<?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo $row['stock_quantity']; ?> <?php echo $row['unit']; ?></td>
                                <td><span class="badge badge-success">Active</span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/footer.php'); ?>
