<?php
$page_title = 'Dashboard';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/auth-check.php');
checkRole(['buyer']);
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/header.php');

$query = "SELECT b.* FROM buyers b JOIN users u ON b.user_id = u.user_id WHERE u.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_user['user_id']);
$stmt->execute();
$buyer = $stmt->get_result()->fetch_assoc();
$stmt->close();

$query = "SELECT COUNT(*) as total FROM orders WHERE buyer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $buyer['buyer_id']);
$stmt->execute();
$orders_count = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$query = "SELECT COALESCE(SUM(total_amount), 0) as total_spent FROM orders WHERE buyer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $buyer['buyer_id']);
$stmt->execute();
$total_spent = $stmt->get_result()->fetch_assoc()['total_spent'];
$stmt->close();

$query = "SELECT COUNT(*) as total FROM products WHERE is_active = 1";
$result = $conn->query($query);
$products_count = $result->fetch_assoc()['total'];
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
                    <h3><?php echo $orders_count; ?></h3>
                    <p>My Orders</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #CDDC39, #9ccc65);">
                <div class="stat-card-content">
                    <h3>₱<?php echo number_format($total_spent, 0); ?></h3>
                    <p>Total Spent</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="stat-card-content">
                    <h3><?php echo $products_count; ?></h3>
                    <p>Available Products</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                <div class="stat-card-content">
                    <h3><?php echo $orders_count > 0 ? round($total_spent / $orders_count, 2) : 0; ?></h3>
                    <p>Avg Order Value</p>
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
            <!-- Business Info -->
            <div class="card">
                <div class="card-header">
                    <h2>Business Information</h2>
                </div>
                <div style="display: grid; gap: 15px;">
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">Business Name</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo $buyer['business_name'] ?? 'Not provided'; ?>
                        </p>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">Business Type</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo $buyer['business_type'] ?? 'Not provided'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="illustration-card">
                <div class="illustration-card-header">
                    <h3>Start Shopping</h3>
                    <p class="illustration-card-description">Browse our marketplace for fresh products</p>
                </div>
                <a href="/agriconnect/pages/products/view-products.php" class="play-btn" style="margin: 25px 0 0 0;">
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h2>Recent Orders</h2>
                <a href="/agriconnect/pages/products/view-products.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">
                    <i class="fas fa-shopping-cart"></i> Shop
                </a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM orders WHERE buyer_id = ? ORDER BY order_date DESC LIMIT 5";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $buyer['buyer_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td>#<?php echo $row['order_id']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                                <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                <td><span class="badge badge-info"><?php echo ucfirst($row['order_status']); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/footer.php'); ?>
