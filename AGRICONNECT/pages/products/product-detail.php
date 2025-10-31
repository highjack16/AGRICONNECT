<?php
$page_title = 'Product Details';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/auth-check.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/header.php');

if (!isset($_GET['product_id'])) {
    header("Location: /agriconnect/pages/products/marketplace.php");
    exit();
}

$product_id = (int)$_GET['product_id'];

// Get product details
$query = "SELECT p.*, f.farm_name, f.farm_location, f.farm_size, f.years_of_experience, f.specialization, f.farmer_id, u.full_name, u.phone, u.address, u.user_id 
          FROM products p 
          JOIN farmers f ON p.farmer_id = f.farmer_id 
          JOIN users u ON f.user_id = u.user_id 
          WHERE p.product_id = ? AND p.is_active = 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: /agriconnect/pages/products/marketplace.php");
    exit();
}

// Get related products from same farmer
$related_query = "SELECT * FROM products WHERE farmer_id = ? AND product_id != ? AND is_active = 1 LIMIT 4";
$stmt = $conn->prepare($related_query);
$stmt->bind_param("ii", $product['farmer_id'], $product_id);
$stmt->execute();
$related_result = $stmt->get_result();
$related_products = [];
while ($row = $related_result->fetch_assoc()) {
    $related_products[] = $row;
}
$stmt->close();

// Get reviews
$review_query = "SELECT r.*, u.full_name FROM reviews r 
                 JOIN buyers b ON r.buyer_id = b.buyer_id 
                 JOIN users u ON b.user_id = u.user_id 
                 WHERE r.product_id = ? 
                 ORDER BY r.review_date DESC";
$stmt = $conn->prepare($review_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$reviews_result = $stmt->get_result();
$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt->close();

// Get average rating
$rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = ?";
$stmt = $conn->prepare($rating_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$rating_result = $stmt->get_result();
$rating_data = $rating_result->fetch_assoc();
$stmt->close();
?>

<div class="main-wrapper">
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/sidebar.php'); ?>
    
    <div class="main-content">
        <!-- Breadcrumb -->
        <div style="margin-bottom: 25px; display: flex; align-items: center; gap: 10px; font-size: 14px;">
            <a href="/agriconnect/pages/products/marketplace.php" style="color: var(--accent-lime); text-decoration: none; font-weight: 600;">Marketplace</a>
            <i class="fas fa-chevron-right" style="color: var(--text-gray); font-size: 12px;"></i>
            <span style="color: var(--text-gray);"><?php echo htmlspecialchars($product['product_name']); ?></span>
        </div>

        <!-- Product Details Card -->
        <div class="card">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px;">
                <!-- Product Image -->
                <div>
                    <div style="width: 100%; aspect-ratio: 1; background: linear-gradient(135deg, var(--accent-light-lime), #f0f9ff); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <i class="fas fa-image" style="font-size: 100px; color: var(--accent-lime); opacity: 0.5;"></i>
                    </div>
                    <div style="background: var(--bg-light); padding: 15px; border-radius: 10px; text-align: center;">
                        <p style="margin: 0; font-size: 13px; color: var(--text-gray); font-weight: 600;">STOCK AVAILABLE</p>
                        <p style="margin: 8px 0 0 0; font-size: 28px; font-weight: 700; color: var(--accent-lime);"><?php echo $product['stock_quantity']; ?></p>
                        <p style="margin: 5px 0 0 0; font-size: 12px; color: var(--text-gray);"><?php echo htmlspecialchars($product['unit']); ?></p>
                    </div>
                </div>

                <!-- Product Info -->
                <div>
                    <span style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: var(--accent-lime); font-weight: 700;">
                        <?php echo htmlspecialchars($product['category']); ?>
                    </span>
                    
                    <h1 style="font-size: 32px; font-weight: 700; color: var(--text-dark); margin: 12px 0 20px 0;">
                        <?php echo htmlspecialchars($product['product_name']); ?>
                    </h1>

                    <!-- Price -->
                    <div style="background: var(--accent-light-lime); padding: 20px; border-radius: 12px; margin-bottom: 30px;">
                        <p style="margin: 0; font-size: 13px; color: var(--text-gray); font-weight: 600;">PRICE PER <?php echo strtoupper($product['unit']); ?></p>
                        <p style="margin: 10px 0 0 0; font-size: 42px; font-weight: 700; color: var(--primary-green);">
                            ₱<?php echo number_format($product['price'], 2); ?>
                        </p>
                    </div>

                    <!-- Description -->
                    <div style="margin-bottom: 30px;">
                        <h3 style="font-size: 16px; font-weight: 700; color: var(--text-dark); margin: 0 0 12px 0;">Description</h3>
                        <p style="color: var(--text-gray); line-height: 1.7; margin: 0;">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </p>
                    </div>

                    <!-- Add to Cart -->
                    <form method="POST" action="/agriconnect/pages/orders/add-to-cart.php" style="margin-bottom: 30px;">
                        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px; margin-bottom: 20px;">
                            <div>
                                <label style="display: block; font-weight: 600; font-size: 13px; color: var(--text-gray); margin-bottom: 8px;">Quantity</label>
                                <input type="number" name="quantity" min="1" max="<?php echo $product['stock_quantity']; ?>" value="1" style="width: 100%; padding: 12px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 14px;">
                            </div>
                            <div style="display: flex; gap: 10px; align-items: flex-end;">
                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center; padding: 14px;">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                                <button type="button" class="btn btn-secondary" style="flex: 1; justify-content: center; padding: 14px;" onclick="alert('Added to wishlist!')">
                                    <i class="fas fa-heart"></i> Wishlist
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Divider -->
            <div style="height: 1px; background: var(--border-light); margin-bottom: 40px;"></div>

            <!-- Farmer Information -->
            <div style="margin-bottom: 40px;">
                <h2 style="font-size: 20px; font-weight: 700; color: var(--text-dark); margin: 0 0 20px 0;">About the Farmer</h2>
                
                <div style="background: var(--bg-light); padding: 25px; border-radius: 12px; display: grid; grid-template-columns: auto 1fr auto; gap: 25px; align-items: center;">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-lime), var(--light-green)); display: flex; align-items: center; justify-content: center; color: var(--primary-green); font-weight: 700; font-size: 32px;">
                        <?php echo strtoupper(substr($product['full_name'], 0, 1)); ?>
                    </div>

                    <div>
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0 0 5px 0;">
                            <?php echo htmlspecialchars($product['full_name']); ?>
                        </h3>
                        <p style="font-size: 14px; color: var(--text-gray); margin: 0 0 12px 0;">
                            <i class="fas fa-leaf" style="color: var(--accent-lime); margin-right: 8px;"></i>
                            <?php echo htmlspecialchars($product['farm_name']); ?>
                        </p>
                        <p style="font-size: 13px; color: var(--text-gray); margin: 0;">
                            <i class="fas fa-map-marker-alt" style="color: var(--accent-lime); margin-right: 8px;"></i>
                            <?php echo htmlspecialchars($product['farm_location']); ?>
                        </p>
                    </div>

                    <a href="/agriconnect/pages/products/farmer-profile.php?user_id=<?php echo $product['user_id']; ?>" class="btn btn-primary">
                        View Farm Profile
                    </a>
                </div>
            </div>

            <!-- Reviews Section -->
            <div style="margin-bottom: 40px;">
                <h2 style="font-size: 20px; font-weight: 700; color: var(--text-dark); margin: 0 0 20px 0;">Customer Reviews</h2>

                <?php if ($rating_data['total_reviews'] > 0): ?>
                    <div style="background: var(--bg-light); padding: 20px; border-radius: 12px; margin-bottom: 30px; display: flex; align-items: center; gap: 30px;">
                        <div style="text-align: center;">
                            <p style="margin: 0; font-size: 42px; font-weight: 700; color: var(--primary-green);">
                                <?php echo round($rating_data['avg_rating'], 1); ?>
                            </p>
                            <p style="margin: 8px 0 0 0; color: var(--text-gray); font-size: 13px;">out of 5</p>
                        </div>
                        <div>
                            <div style="display: flex; gap: 5px; margin-bottom: 8px;">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <i class="fas fa-star" style="color: <?php echo $i < round($rating_data['avg_rating']) ? 'var(--accent-lime)' : 'var(--border-light)'; ?>; font-size: 16px;"></i>
                                <?php endfor; ?>
                            </div>
                            <p style="margin: 0; font-size: 13px; color: var(--text-gray);">
                                <?php echo $rating_data['total_reviews']; ?> customer <?php echo $rating_data['total_reviews'] == 1 ? 'review' : 'reviews'; ?>
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <?php foreach ($reviews as $review): ?>
                            <div style="border-left: 3px solid var(--accent-lime); padding-left: 20px; padding-top: 15px; padding-bottom: 15px;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                    <div>
                                        <h4 style="font-weight: 700; color: var(--text-dark); margin: 0 0 4px 0;">
                                            <?php echo htmlspecialchars($review['full_name']); ?>
                                        </h4>
                                        <div style="display: flex; gap: 5px; margin-bottom: 8px;">
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <i class="fas fa-star" style="color: <?php echo $i < $review['rating'] ? 'var(--accent-lime)' : 'var(--border-light)'; ?>; font-size: 14px;"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <span style="font-size: 12px; color: var(--text-gray);">
                                        <?php echo date('M d, Y', strtotime($review['review_date'])); ?>
                                    </span>
                                </div>
                                <p style="margin: 0; color: var(--text-gray); line-height: 1.6; font-size: 14px;">
                                    <?php echo htmlspecialchars($review['comment']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; background: var(--bg-light); border-radius: 12px;">
                        <i class="fas fa-comments" style="font-size: 48px; color: var(--text-gray); opacity: 0.3; display: block; margin-bottom: 15px;"></i>
                        <p style="margin: 0; color: var(--text-gray); font-size: 16px;">No reviews yet</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Related Products -->
            <?php if (count($related_products) > 0): ?>
                <div style="border-top: 1px solid var(--border-light); padding-top: 40px;">
                    <h2 style="font-size: 20px; font-weight: 700; color: var(--text-dark); margin: 0 0 25px 0;">More from this Farmer</h2>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">
                        <?php foreach ($related_products as $related): ?>
                            <a href="/agriconnect/pages/products/product-detail.php?product_id=<?php echo $related['product_id']; ?>" style="text-decoration: none; display: flex; flex-direction: column; gap: 12px; padding: 15px; background: var(--bg-light); border-radius: 10px; transition: all 0.3s ease; color: var(--text-dark);">
                                <div style="width: 100%; height: 120px; background: linear-gradient(135deg, var(--accent-light-lime), #f0f9ff); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image" style="font-size: 32px; color: var(--accent-lime); opacity: 0.5;"></i>
                                </div>
                                <h4 style="margin: 0; font-weight: 600; font-size: 14px;">
                                    <?php echo htmlspecialchars(substr($related['product_name'], 0, 40)); ?>
                                </h4>
                                <p style="margin: 0; font-size: 14px; font-weight: 700; color: var(--primary-green);">
                                    ₱<?php echo number_format($related['price'], 2); ?>
                                </p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/footer.php'); ?>
