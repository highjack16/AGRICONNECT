<?php
$page_title = 'Manage Products';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/auth-check.php');
checkRole(['farmer']);
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/header.php');

// Get farmer ID
$query = "SELECT farmer_id FROM farmers WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_user['user_id']);
$stmt->execute();
$farmer = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get farmer's products
$query = "SELECT * FROM products WHERE farmer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $farmer['farmer_id']);
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();
?>

<div class="main-wrapper">
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/sidebar.php'); ?>
    
    <div class="main-content">
        <div class="top-bar">
            <div class="top-bar-left">
                <h1 style="margin: 0; color: var(--primary-green);">
                    <i class="fas fa-leaf" style="color: var(--accent-lime); margin-right: 10px;"></i>
                    My Products
                </h1>
            </div>
            <div class="top-bar-right">
                <a href="/agriconnect/pages/products/add-product.php" class="btn btn-primary" style="padding: 10px 20px; font-size: 14px;">
                    <i class="fas fa-plus-circle"></i> Add New
                </a>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?></div>
                    <div class="user-details">
                        <h3><?php echo $current_user['full_name']; ?></h3>
                        <p><?php echo ucfirst($current_user['role']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                                    <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo $product['stock_quantity']; ?> <?php echo $product['unit']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $product['is_active'] ? 'badge-success' : 'badge-danger'; ?>" style="padding: 6px 12px;">
                                            <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                    <td>
                                        <a href="/agriconnect/pages/products/edit-product.php?product_id=<?php echo $product['product_id']; ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    <i class="fas fa-inbox" style="font-size: 32px; color: var(--text-gray); opacity: 0.3; margin-bottom: 10px; display: block;"></i>
                                    <p style="color: var(--text-gray);">No products yet. <a href="/agriconnect/pages/products/add-product.php" style="color: var(--accent-lime); text-decoration: none; font-weight: 600;">Add your first product</a></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/footer.php'); ?>
