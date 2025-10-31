<?php
$page_title = 'Shopping Cart';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/auth-check.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/header.php');

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cart_items = [];
$total_amount = 0;

if (count($cart) > 0) {
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
}
?>

<div class="main-wrapper">
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/sidebar.php'); ?>
    
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="top-bar-left">
                <h1 style="margin: 0; color: var(--primary-green); font-size: 28px;">
                    <i class="fas fa-shopping-cart" style="color: var(--accent-lime); margin-right: 10px;"></i>
                    Shopping Cart
                </h1>
            </div>
            <div class="top-bar-right">
                <a href="/agriconnect/pages/products/marketplace.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
            </div>
        </div>

        <?php if (count($cart_items) > 0): ?>
            <div style="display: grid; grid-template-columns: 1fr 320px; gap: 30px;">
                <!-- Cart Items -->
                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; gap: 12px; align-items: center;">
                                                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--accent-light-lime), #f0f9ff); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image" style="font-size: 24px; color: var(--accent-lime); opacity: 0.5;"></i>
                                                </div>
                                                <div>
                                                    <p style="margin: 0; font-weight: 600; color: var(--text-dark);">
                                                        <?php echo htmlspecialchars($item['product']['product_name']); ?>
                                                    </p>
                                                    <p style="margin: 4px 0 0 0; font-size: 12px; color: var(--text-gray);">
                                                        <?php echo htmlspecialchars($item['product']['category']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>₱<?php echo number_format($item['product']['price'], 2); ?></td>
                                        <td>
                                            <form method="POST" action="/agriconnect/pages/orders/update-cart.php" style="display: flex; gap: 5px;">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product']['product_id']; ?>">
                                                <button type="submit" name="action" value="decrease" class="btn" style="padding: 6px 8px; background: var(--bg-light); border: 1px solid var(--border-light); border-radius: 6px; color: var(--text-gray); cursor: pointer;">−</button>
                                                <span style="min-width: 40px; text-align: center; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                                    <?php echo $item['quantity']; ?>
                                                </span>
                                                <button type="submit" name="action" value="increase" class="btn" style="padding: 6px 8px; background: var(--bg-light); border: 1px solid var(--border-light); border-radius: 6px; color: var(--text-gray); cursor: pointer;">+</button>
                                            </form>
                                        </td>
                                        <td style="font-weight: 700; color: var(--primary-green);">
                                            ₱<?php echo number_format($item['subtotal'], 2); ?>
                                        </td>
                                        <td>
                                            <form method="POST" action="/agriconnect/pages/orders/update-cart.php" style="display: inline;">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product']['product_id']; ?>">
                                                <button type="submit" name="action" value="remove" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">
                                                    <i class="fas fa-trash"></i> Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="card" style="height: fit-content; position: sticky; top: 40px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: var(--text-dark); margin: 0 0 20px 0;">Order Summary</h3>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                        <span style="color: var(--text-gray);">Subtotal</span>
                        <span style="font-weight: 600;">₱<?php echo number_format($total_amount, 2); ?></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
                        <span style="color: var(--text-gray);">Shipping</span>
                        <span style="font-weight: 600; color: var(--accent-lime);">FREE</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid var(--border-light);">
                        <span style="font-weight: 700; font-size: 16px;">Total</span>
                        <span style="font-weight: 700; font-size: 18px; color: var(--primary-green);">₱<?php echo number_format($total_amount, 2); ?></span>
                    </div>

                    <form method="POST" action="/agriconnect/pages/orders/checkout.php">
                        <button type="submit" class="btn btn-primary btn-block" style="padding: 14px; margin-bottom: 10px; justify-content: center;">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </button>
                    </form>

                    <a href="/agriconnect/pages/products/marketplace.php" class="btn btn-secondary btn-block" style="padding: 12px; justify-content: center;">
                        Continue Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 80px 40px;">
                <i class="fas fa-shopping-cart" style="font-size: 80px; color: var(--text-gray); opacity: 0.2; display: block; margin-bottom: 20px;"></i>
                <h2 style="font-size: 24px; color: var(--text-gray); margin: 0 0 10px 0;">Your cart is empty</h2>
                <p style="margin: 0 0 30px 0; color: var(--text-gray); font-size: 15px;">Start shopping to add items to your cart</p>
                <a href="/agriconnect/pages/products/marketplace.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/footer.php'); ?>
