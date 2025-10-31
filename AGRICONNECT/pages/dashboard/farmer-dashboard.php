<?php
$page_title = 'Dashboard';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/auth-check.php');
checkRole(['farmer']);
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/header.php');

$query = "SELECT f.* FROM farmers f JOIN users u ON f.user_id = u.user_id WHERE u.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_user['user_id']);
$stmt->execute();
$farmer = $stmt->get_result()->fetch_assoc();
$stmt->close();

$query = "SELECT COUNT(*) as total FROM products WHERE farmer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $farmer['farmer_id']);
$stmt->execute();
$products_count = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$query = "SELECT COUNT(DISTINCT o.order_id) as total FROM orders o 
          JOIN order_details od ON o.order_id = od.order_id 
          JOIN products p ON od.product_id = p.product_id 
          WHERE p.farmer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $farmer['farmer_id']);
$stmt->execute();
$orders_count = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$query = "SELECT COALESCE(SUM(od.subtotal), 0) as revenue FROM order_details od 
          JOIN products p ON od.product_id = p.product_id 
          WHERE p.farmer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $farmer['farmer_id']);
$stmt->execute();
$revenue = $stmt->get_result()->fetch_assoc()['revenue'];
$stmt->close();

$query = "SELECT COALESCE(SUM(stock_quantity), 0) as total_stock FROM products WHERE farmer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $farmer['farmer_id']);
$stmt->execute();
$total_stock = $stmt->get_result()->fetch_assoc()['total_stock'];
$stmt->close();
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
                    <h3><?php echo $products_count; ?></h3>
                    <p>My Products</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="stat-card-content">
                    <h3><?php echo $total_stock; ?></h3>
                    <p>Total Stock</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #CDDC39, #9ccc65);">
                <div class="stat-card-content">
                    <h3><?php echo $orders_count; ?></h3>
                    <p>Orders Received</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="stat-card-content">
                    <h3>₱<?php echo number_format($revenue, 0); ?></h3>
                    <p>Total Sales</p>
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
            <!-- Farm Info -->
            <div class="card">
                <div class="card-header">
                    <h2>Farm Information</h2>
                </div>
                <div style="display: grid; gap: 15px;">
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">Farm Name</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo $farmer['farm_name'] ?? 'Not provided'; ?>
                        </p>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">Location</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo $farmer['farm_location'] ?? 'Not provided'; ?>
                        </p>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">Experience</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo ($farmer['years_of_experience'] ?? '0') . ' years'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="illustration-card">
                <div class="illustration-card-header">
                    <h3>Quick Actions</h3>
                </div>
                <div style="display: grid; gap: 10px; margin-top: 20px;">
                    <a href="/agriconnect/pages/products/add-product.php" class="btn btn-success btn-block">
                        <i class="fas fa-plus-circle"></i> Add New Product
                    </a>
                    <a href="/agriconnect/pages/products/manage-products.php" class="btn btn-success btn-block">
                        <i class="fas fa-leaf"></i> Manage Products
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Products -->
        <div class="card">
            <div class="card-header">
                <h2>Recent Products</h2>
                <a href="/agriconnect/pages/products/add-product.php" class="btn btn-success" style="padding: 8px 16px; font-size: 13px;">
                    <i class="fas fa-plus-circle"></i> Add
                </a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM products WHERE farmer_id = ? ORDER BY created_at DESC LIMIT 5";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $farmer['farmer_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?php echo $row['product_name']; ?></td>
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
