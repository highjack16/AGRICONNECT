<?php
$page_title = 'Order Details';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/auth-check.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/header.php');

if (!isset($_GET['order_id'])) {
    header("Location: /agriconnect/pages/orders/view-orders.php");
    exit();
}

$order_id = (int)$_GET['order_id'];

// Get order with buyer details
$query = "SELECT o.*, u.full_name, u.email, u.phone, b.business_name 
          FROM orders o 
          JOIN buyers b ON o.buyer_id = b.buyer_id 
          JOIN users u ON b.user_id = u.user_id 
          WHERE o.order_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: /agriconnect/pages/orders/view-orders.php");
    exit();
}

// Get order items
$items_query = "SELECT od.*, p.product_name, p.category, f.farm_name, u.full_name as farmer_name 
                FROM order_details od 
                JOIN products p ON od.product_id = p.product_id 
                JOIN farmers f ON p.farmer_id = f.farmer_id 
                JOIN users u ON f.user_id = u.user_id 
                WHERE od.order_id = ?";

$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

// Get payment info
$payment_query = "SELECT * FROM payments WHERE order_id = ?";
$stmt = $conn->prepare($payment_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment_result = $stmt->get_result();
$payment = $payment_result->fetch_assoc();
$stmt->close();
?>

<div class="main-wrapper">
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/sidebar.php'); ?>
    
    <div class="main-content">
        <!-- Breadcrumb -->
        <div style="margin-bottom: 25px; display: flex; align-items: center; gap: 10px; font-size: 14px;">
            <a href="/agriconnect/pages/orders/view-orders.php" style="color: var(--accent-lime); text-decoration: none; font-weight: 600;">My Orders</a>
            <i class="fas fa-chevron-right" style="color: var(--text-gray); font-size: 12px;"></i>
            <span style="color: var(--text-gray);">Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></span>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 320px; gap: 30px;">
            <!-- Order Details -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 25px; border-bottom: 1px solid var(--border-light);">
                    <div>
                        <h1 style="font-size: 28px; font-weight: 700; color: var(--primary-green); margin: 0;">
                            Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?>
                        </h1>
                        <p style="margin: 8px 0 0 0; color: var(--text-gray); font-size: 14px;">
                            Placed on <?php echo date('M d, Y \a\t g:i A', strtotime($order['order_date'])); ?>
                        </p>
                    </div>
                    <span class="badge <?php echo 'badge-' . ($order['order_status'] === 'delivered' ? 'success' : ($order['order_status'] === 'cancelled' ? 'danger' : 'info')); ?>" style="font-size: 12px; padding: 8px 16px;">
                        <?php echo ucfirst($order['order_status']); ?>
                    </span>
                </div>

                <!-- Order Items -->
                <h2 style="font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0 0 20px 0;">Items</h2>
                
                <div class="table-responsive" style="margin-bottom: 30px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Farmer</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <p style="margin: 0; font-weight: 600; color: var(--text-dark);">
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                            </p>
                                            <p style="margin: 4px 0 0 0; font-size: 12px; color: var(--text-gray);">
                                                <?php echo htmlspecialchars($item['category']); ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p style="margin: 0; font-weight: 600; color: var(--text-dark); font-size: 14px;">
                                                <?php echo htmlspecialchars($item['farmer_name']); ?>
                                            </p>
                                            <p style="margin: 4px 0 0 0; font-size: 12px; color: var(--text-gray);">
                                                <?php echo htmlspecialchars($item['farm_name']); ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td style="font-weight: 700; color: var(--primary-green);">
                                        ₱<?php echo number_format($item['subtotal'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Delivery Information -->
                <h2 style="font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0 0 20px 0;">Delivery Information</h2>
                
                <div style="background: var(--bg-light); padding: 20px; border-radius: 10px;">
                    <div style="margin-bottom: 16px;">
                        <p style="margin: 0; font-size: 12px; color: var(--text-gray); font-weight: 600; text-transform: uppercase; margin-bottom: 6px;">Delivery Address</p>
                        <p style="margin: 0; font-size: 14px; color: var(--text-dark); line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
                        </p>
                    </div>
                    <?php if ($order['estimated_delivery']): ?>
                        <div>
                            <p style="margin: 0; font-size: 12px; color: var(--text-gray); font-weight: 600; text-transform: uppercase; margin-bottom: 6px;">Estimated Delivery</p>
                            <p style="margin: 0; font-size: 14px; color: var(--text-dark);">
                                <?php echo date('M d, Y', strtotime($order['estimated_delivery'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Summary Sidebar -->
            <div>
                <!-- Customer Info -->
                <div class="card" style="margin-bottom: 20px;">
                    <h3 style="font-size: 14px; font-weight: 700; text-transform: uppercase; color: var(--text-gray); margin: 0 0 16px 0; letter-spacing: 0.5px;">Customer</h3>
                    <p style="margin: 0 0 4px 0; font-weight: 600; color: var(--text-dark);">
                        <?php echo htmlspecialchars($order['full_name']); ?>
                    </p>
                    <p style="margin: 0; font-size: 13px; color: var(--text-gray);">
                        <?php echo htmlspecialchars($order['email']); ?>
                    </p>
                    <p style="margin: 8px 0 0 0; font-size: 13px; color: var(--text-gray);">
                        <?php echo htmlspecialchars($order['phone']); ?>
                    </p>
                </div>

                <!-- Payment Info -->
                <?php if ($payment): ?>
                    <div class="card" style="margin-bottom: 20px;">
                        <h3 style="font-size: 14px; font-weight: 700; text-transform: uppercase; color: var(--text-gray); margin: 0 0 16px 0; letter-spacing: 0.5px;">Payment</h3>
                        <p style="margin: 0 0 8px 0; font-size: 13px;">
                            <span style="color: var(--text-gray);">Method:</span>
                            <span style="font-weight: 600; color: var(--text-dark);"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></span>
                        </p>
                        <p style="margin: 0; font-size: 13px;">
                            <span style="color: var(--text-gray);">Status:</span>
                            <span class="badge <?php echo 'badge-' . ($payment['payment_status'] === 'completed' ? 'success' : 'warning'); ?>">
                                <?php echo ucfirst($payment['payment_status']); ?>
                            </span>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Order Summary -->
                <div class="card">
                    <h3 style="font-size: 14px; font-weight: 700; text-transform: uppercase; color: var(--text-gray); margin: 0 0 16px 0; letter-spacing: 0.5px;">Summary</h3>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                        <span style="color: var(--text-gray); font-size: 13px;">Subtotal</span>
                        <span style="font-weight: 600; font-size: 13px;">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 2px solid var(--border-light);">
                        <span style="color: var(--text-gray); font-size: 13px;">Shipping</span>
                        <span style="font-weight: 600; color: var(--accent-lime); font-size: 13px;">FREE</span>
                    </div>

                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-weight: 700; font-size: 14px;">Total</span>
                        <span style="font-weight: 700; font-size: 16px; color: var(--primary-green);">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/footer.php'); ?>
