<?php
$page_title = 'Checkout';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/auth-check.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/header.php');

if (!isset($current_user['user_id']) || $current_user['role'] !== 'buyer') {
    header("Location: /agriconnect/pages/products/marketplace.php");
    exit();
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (count($cart) === 0) {
    header("Location: /agriconnect/pages/orders/view-cart.php");
    exit();
}

// Get buyer ID
$query = "SELECT buyer_id FROM buyers WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_user['user_id']);
$stmt->execute();
$buyer_result = $stmt->get_result();
$buyer = $buyer_result->fetch_assoc();
$stmt->close();

$total_amount = 0;
$cart_items = [];

$placeholders = implode(',', array_keys($cart));
$query = "SELECT * FROM products WHERE product_id IN ($placeholders)";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $quantity = $cart[$row['product_id']];
    $subtotal = $row['price'] * $quantity;
    $total_amount += $subtotal;
    
    $cart_items[] = [
        'product' => $row,
        'quantity' => $quantity,
        'subtotal' => $subtotal
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_address = sanitize($_POST['delivery_address']);
    $estimated_delivery = sanitize($_POST['estimated_delivery']);

    // Create order
    $order_query = "INSERT INTO orders (buyer_id, total_amount, delivery_address, estimated_delivery, order_status) 
                    VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("idss", $buyer['buyer_id'], $total_amount, $delivery_address, $estimated_delivery);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Add order details
    foreach ($cart_items as $item) {
        $detail_query = "INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal) 
                         VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($detail_query);
        $stmt->bind_param("iiidd", $order_id, $item['product']['product_id'], $item['quantity'], $item['product']['price'], $item['subtotal']);
        $stmt->execute();
        $stmt->close();

        // Update product stock
        $stock_query = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
        $stock_stmt = $conn->prepare($stock_query);
        $stock_stmt->bind_param("ii", $item['quantity'], $item['product']['product_id']);
        $stock_stmt->execute();
        $stock_stmt->close();

        // Log inventory
        $new_quantity = $item['product']['stock_quantity'] - $item['quantity'];
        $inventory_query = "INSERT INTO inventory_log (product_id, change_quantity, change_type, previous_quantity, new_quantity, notes) 
                            VALUES (?, ?, 'sold', ?, ?, ?)";
        $stmt = $conn->prepare($inventory_query);
        $note = "Order #$order_id";
        $stmt->bind_param("iiiis", $item['product']['product_id'], $item['quantity'], $item['product']['stock_quantity'], $new_quantity, $note);
        $stmt->execute();
        $stmt->close();
    }

    // Clear cart
    unset($_SESSION['cart']);

    // Log activity
    logActivity($current_user['user_id'], 'order_placed', "Placed order #$order_id");

    header("Location: /agriconnect/pages/orders/order-confirmation.php?order_id=$order_id");
    exit();
}
?>

<div class="main-wrapper">
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/sidebar.php'); ?>
    
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="top-bar-left">
                <h1 style="margin: 0; color: var(--primary-green); font-size: 28px;">
                    <i class="fas fa-lock" style="color: var(--accent-lime); margin-right: 10px;"></i>
                    Secure Checkout
                </h1>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 320px; gap: 30px;">
            <!-- Checkout Form -->
            <form method="POST" class="card">
                <h2 style="font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0 0 25px 0;">Delivery Information</h2>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($current_user['full_name']); ?>" disabled style="background-color: var(--bg-light);">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" disabled style="background-color: var(--bg-light);">
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" value="<?php echo htmlspecialchars($current_user['phone']); ?>" disabled style="background-color: var(--bg-light);">
                </div>

                <div class="form-group">
                    <label>Delivery Address *</label>
                    <textarea name="delivery_address" placeholder="Enter your complete delivery address" required style="min-height: 100px;">
<?php echo htmlspecialchars($current_user['address']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Estimated Delivery Date *</label>
                    <input type="date" name="estimated_delivery" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                </div>

                <h2 style="font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 30px 0 25px 0;">Payment Method</h2>

                <div style="background: var(--bg-light); padding: 20px; border-radius: 10px; margin-bottom: 30px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="radio" name="payment_method" value="cash_on_delivery" checked>
                        <span style="font-weight: 600; color: var(--text-dark);">Cash on Delivery</span>
                    </label>
                    <p style="margin: 10px 0 0 30px; font-size: 13px; color: var(--text-gray);">Pay when your order arrives</p>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="padding: 14px; justify-content: center; font-size: 16px;">
                    <i class="fas fa-check"></i> Place Order
                </button>
            </form>

            <!-- Order Summary -->
            <div>
                <div class="card" style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: var(--text-dark); margin: 0 0 20px 0;">Order Items (<?php echo count($cart_items); ?>)</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php foreach ($cart_items as $item): ?>
                            <div style="border-bottom: 1px solid var(--border-light); padding-bottom: 15px;">
                                <p style="margin: 0 0 8px 0; font-weight: 600; color: var(--text-dark); font-size: 14px;">
                                    <?php echo htmlspecialchars(substr($item['product']['product_name'], 0, 30)); ?>
                                </p>
                                <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--text-gray); margin-bottom: 6px;">
                                    <span><?php echo $item['quantity']; ?> × <?php echo $item['product']['unit']; ?></span>
                                    <span>₱<?php echo number_format($item['product']['price'], 2); ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-weight: 600; color: var(--primary-green);">
                                    <span>Subtotal</span>
                                    <span>₱<?php echo number_format($item['subtotal'], 2); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                        <span style="color: var(--text-gray);">Subtotal</span>
                        <span>₱<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid var(--border-light);">
                        <span style="color: var(--text-gray);">Shipping</span>
                        <span style="color: var(--accent-lime); font-weight: 600;">FREE</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-weight: 700; font-size: 16px;">Total</span>
                        <span style="font-weight: 700; font-size: 18px; color: var(--primary-green);">₱<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/footer.php'); ?>
