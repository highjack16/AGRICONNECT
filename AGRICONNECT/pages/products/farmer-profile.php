<?php
$page_title = 'Farmer Profile';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/auth-check.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/header.php');

if (!isset($_GET['user_id'])) {
    header("Location: /agriconnect/pages/products/marketplace.php");
    exit();
}

$user_id = (int)$_GET['user_id'];

// Get farmer details
$query = "SELECT f.*, u.full_name, u.email, u.phone, u.address 
          FROM farmers f 
          JOIN users u ON f.user_id = u.user_id 
          WHERE u.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$farmer = $result->fetch_assoc();
$stmt->close();

if (!$farmer) {
    header("Location: /agriconnect/pages/products/marketplace.php");
    exit();
}

// Get farmer's products
$products_query = "SELECT * FROM products WHERE farmer_id = ? AND is_active = 1 ORDER BY created_at DESC";
$stmt = $conn->prepare($products_query);
$stmt->bind_param("i", $farmer['farmer_id']);
$stmt->execute();
$products_result = $stmt->get_result();
$products = [];
while ($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

// Get farmer statistics
$stats_query = "SELECT 
    COUNT(DISTINCT p.product_id) as total_products,
    COUNT(DISTINCT o.order_id) as total_orders,
    COALESCE(SUM(od.subtotal), 0) as total_sales,
    COALESCE(AVG(r.rating), 0) as avg_rating,
    COUNT(r.review_id) as total_reviews
    FROM farmers f
    LEFT JOIN products p ON f.farmer_id = p.farmer_id
    LEFT JOIN order_details od ON p.product_id = od.product_id
    LEFT JOIN orders o ON od.order_id = o.order_id
    LEFT JOIN reviews r ON p.product_id = r.product_id
    WHERE f.farmer_id = ?";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $farmer['farmer_id']);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stmt->close();
?>

<div class="main-wrapper">
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/sidebar.php'); ?>
    
    <div class="main-content">
        <!-- Breadcrumb -->
        <div style="margin-bottom: 25px; display: flex; align-items: center; gap: 10px; font-size: 14px;">
            <a href="/agriconnect/pages/products/marketplace.php" style="color: var(--accent-lime); text-decoration: none; font-weight: 600;">Marketplace</a>
            <i class="fas fa-chevron-right" style="color: var(--text-gray); font-size: 12px;"></i>
            <span style="color: var(--text-gray);">Farmer Profile</span>
        </div>

        <!-- Farmer Header -->
        <div class="card" style="margin-bottom: 30px;">
            <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 30px; align-items: center;">
                <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-lime), var(--light-green)); display: flex; align-items: center; justify-content: center; color: var(--primary-green); font-weight: 700; font-size: 48px;">
                    <?php echo strtoupper(substr($farmer['full_name'], 0, 1)); ?>
                </div>

                <div>
                    <h1 style="font-size: 28px; font-weight: 700; color: var(--text-dark); margin: 0 0 8px 0;">
                        <?php echo htmlspecialchars($farmer['full_name']); ?>
                    </h1>
                    <p style="margin: 0; font-size: 16px; color: var(--accent-lime); font-weight: 600; margin-bottom: 12px;">
                        <i class="fas fa-leaf"></i> <?php echo htmlspecialchars($farmer['farm_name']); ?>
                    </p>
                    <div style="display: flex; gap: 25px; margin-bottom: 12px;">
                        <div>
                            <p style="margin: 0; font-size: 12px; color: var(--text-gray); font-weight: 600;">LOCATION</p>
                            <p style="margin: 5px 0 0 0; font-size: 14px; color: var(--text-dark);">
                                <i class="fas fa-map-marker-alt" style="color: var(--accent-lime); margin-right: 8px;"></i>
                                <?php echo htmlspecialchars($farmer['farm_location']); ?>
                            </p>
                        </div>
                        <div>
                            <p style="margin: 0; font-size: 12px; color: var(--text-gray); font-weight: 600;">EXPERIENCE</p>
                            <p style="margin: 5px 0 0 0; font-size: 14px; color: var(--text-dark);">
                                <?php echo $farmer['years_of_experience']; ?> years
                            </p>
                        </div>
                        <div>
                            <p style="margin: 0; font-size: 12px; color: var(--text-gray); font-weight: 600;">RATING</p>
                            <div style="margin: 5px 0 0 0; display: flex; gap: 4px;">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <i class="fas fa-star" style="color: <?php echo $i < round($stats['avg_rating']) ? 'var(--accent-lime)' : 'var(--border-light)'; ?>; font-size: 14px;"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="text-align: right;">
                    <a href="mailto:<?php echo htmlspecialchars($farmer['email']); ?>" class="btn btn-primary" style="margin-bottom: 10px; display: block;">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                    <a href="tel:<?php echo htmlspecialchars($farmer['phone']); ?>" class="btn btn-secondary" style="display: block;">
                        <i class="fas fa-phone"></i> Call
                    </a>
                </div>
            </div>
        </div>

        <!-- Farm Statistics -->
        <div class="stats-grid" style="margin-bottom: 30px;">
            <div class="stat-card">
                <div class="stat-card-content">
                    <h3><?php echo $stats['total_products']; ?></h3>
                    <p>Products Listed</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #20c997, #0fa674);">
                <div class="stat-card-content">
                    <h3><?php echo $stats['total_orders']; ?></h3>
                    <p>Orders Fulfilled</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #ffc107, #ff9800);">
                <div class="stat-card-content">
                    <h3>₱<?php echo number_format($stats['total_sales'], 0); ?></h3>
                    <p>Total Sales</p>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #28a745, #218838);">
                <div class="stat-card-content">
                    <h3><?php echo $stats['total_reviews']; ?></h3>
                    <p>Reviews</p>
                </div>
            </div>
        </div>

        <!-- Farm Information -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2>Farm Information</h2>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div>
                    <h4 style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-gray); margin: 0 0 8px 0; letter-spacing: 0.5px;">Specialization</h4>
                    <p style="margin: 0; font-size: 15px; color: var(--text-dark);">
                        <?php echo htmlspecialchars($farmer['specialization']); ?>
                    </p>
                </div>
                <div>
                    <h4 style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-gray); margin: 0 0 8px 0; letter-spacing: 0.5px;">Farm Size</h4>
                    <p style="margin: 0; font-size: 15px; color: var(--text-dark);">
                        <?php echo number_format($farmer['farm_size'], 2); ?> hectares
                    </p>
                </div>
                <div>
                    <h4 style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-gray); margin: 0 0 8px 0; letter-spacing: 0.5px;">Contact Email</h4>
                    <p style="margin: 0; font-size: 15px; color: var(--text-dark);">
                        <?php echo htmlspecialchars($farmer['email']); ?>
                    </p>
                </div>
                <div>
                    <h4 style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-gray); margin: 0 0 8px 0; letter-spacing: 0.5px;">Phone</h4>
                    <p style="margin: 0; font-size: 15px; color: var(--text-dark);">
                        <?php echo htmlspecialchars($farmer['phone']); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="card">
            <div class="card-header">
                <h2>Featured Products</h2>
                <span style="font-size: 13px; color: var(--text-gray); font-weight: 600;">
                    <?php echo count($products); ?> products
                </span>
            </div>

            <?php if (count($products) > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                    <?php foreach ($products as $product): ?>
                        <a href="/agriconnect/pages/products/product-detail.php?product_id=<?php echo $product['product_id']; ?>" class="card" style="text-decoration: none; color: var(--text-dark); cursor: pointer; transition: all 0.3s ease;">
                            <div style="width: 100%; height: 150px; background: linear-gradient(135deg, var(--accent-light-lime), #f0f9ff); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                                <i class="fas fa-image" style="font-size: 40px; color: var(--accent-lime); opacity: 0.5;"></i>
                            </div>
                            <h4 style="margin: 0 0 8px 0; font-weight: 600; font-size: 14px;">
                                <?php echo htmlspecialchars($product['product_name']); ?>
                            </h4>
                            <p style="margin: 0 0 12px 0; font-size: 12px; color: var(--text-gray); line-height: 1.5;">
                                <?php echo substr(htmlspecialchars($product['description']), 0, 60); ?>...
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 12px; border-top: 1px solid var(--border-light);">
                                <div>
                                    <p style="margin: 0; font-size: 12px; color: var(--text-gray);">Price</p>
                                    <p style="margin: 0; font-size: 16px; font-weight: 700; color: var(--primary-green);">
                                        ₱<?php echo number_format($product['price'], 2); ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <p style="margin: 0; font-size: 12px; color: var(--text-gray);">Stock</p>
                                    <p style="margin: 0; font-size: 16px; font-weight: 700; color: var(--accent-lime);">
                                        <?php echo $product['stock_quantity']; ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-inbox" style="font-size: 48px; color: var(--text-gray); opacity: 0.3; display: block; margin-bottom: 15px;"></i>
                    <p style="margin: 0; color: var(--text-gray);">No products available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/footer.php'); ?>
