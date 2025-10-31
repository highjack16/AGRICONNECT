<?php
$page_title = 'Marketplace';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/config/db.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/auth-check.php');

if ($current_user['role'] === 'admin') {
    header("Location: /agriconnect/pages/dashboard/admin-dashboard.php?error=unauthorized");
    exit();
}

include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/header.php');

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Build query
$query = "SELECT p.*, f.farm_name, f.farm_location, u.full_name, u.user_id 
          FROM products p 
          JOIN farmers f ON p.farmer_id = f.farmer_id 
          JOIN users u ON f.user_id = u.user_id 
          WHERE p.is_active = 1 AND p.stock_quantity > 0";

if ($search) {
    $search_safe = $conn->real_escape_string($search);
    $query .= " AND (p.product_name LIKE '%$search_safe%' OR p.description LIKE '%$search_safe%')";
}

if ($category) {
    $category_safe = $conn->real_escape_string($category);
    $query .= " AND p.category = '$category_safe'";
}

// Apply sorting
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'popular':
        $query .= " ORDER BY (SELECT COUNT(*) FROM orders o JOIN order_details od ON o.order_id = od.order_id WHERE od.product_id = p.product_id) DESC";
        break;
    default:
        $query .= " ORDER BY p.created_at DESC";
}

$result = $conn->query($query);
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Get categories
$cat_query = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND is_active = 1 ORDER BY category ASC";
$cat_result = $conn->query($cat_query);
$categories = [];
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Get cart count
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<div class="main-wrapper">
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/sidebar.php'); ?>
    
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="top-bar-left">
                <h1 style="margin: 0; color: var(--primary-green); font-size: 28px;">
                    <i class="fas fa-shopping-bag" style="color: var(--accent-lime); margin-right: 10px;"></i>
                    Fresh Market
                </h1>
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <form method="GET" style="width: 100%;">
                        <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                        <input type="hidden" name="category" value="<?php echo $category; ?>">
                        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>" onchange="this.form.submit()">
                    </form>
                </div>
            </div>
            <div class="top-bar-right">
                <a href="/agriconnect/pages/orders/view-cart.php" class="notification-bell" title="Shopping Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cart_count > 0): ?>
                        <span style="position: absolute; top: -5px; right: -5px; background: var(--accent-lime); color: var(--primary-green); width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;">
                            <?php echo $cart_count; ?>
                        </span>
                    <?php endif; ?>
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

        <!-- Filters Card -->
        <div class="card">
            <div style="display: grid; grid-template-columns: auto auto auto auto 1fr; gap: 20px; align-items: center;">
                <div>
                    <label style="font-weight: 600; font-size: 13px; color: var(--text-gray); display: block; margin-bottom: 8px;">CATEGORY</label>
                    <select onchange="window.location.href='?search=<?php echo urlencode($search); ?>&category=' + this.value + '&sort=<?php echo $sort; ?>'" style="padding: 10px 15px; border-radius: 8px; border: 1px solid var(--border-light); font-size: 14px;">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="font-weight: 600; font-size: 13px; color: var(--text-gray); display: block; margin-bottom: 8px;">SORT BY</label>
                    <select onchange="window.location.href='?search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&sort=' + this.value" style="padding: 10px 15px; border-radius: 8px; border: 1px solid var(--border-light); font-size: 14px;">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                    </select>
                </div>
                <div style="padding-top: 20px;">
                    <span style="font-size: 13px; color: var(--text-gray); font-weight: 600;">
                        <?php echo count($products); ?> Products Found
                    </span>
                </div>
                <div></div>
                <div></div>
            </div>
        </div>

        <!-- Products Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; margin-bottom: 40px;">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="card" style="overflow: hidden; display: flex; flex-direction: column; transition: all 0.3s ease;">
                        <!-- Product Image -->
                        <div style="width: 100%; height: 220px; background: linear-gradient(135deg, var(--accent-light-lime), #f0f9ff); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; position: relative; overflow: hidden;">
                            <i class="fas fa-image" style="font-size: 60px; color: var(--accent-lime); opacity: 0.5;"></i>
                            <div style="position: absolute; top: 10px; right: 10px; background: var(--accent-lime); color: var(--primary-green); padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 12px;">
                                In Stock
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div style="flex-grow: 1; display: flex; flex-direction: column;">
                            <div style="margin-bottom: 10px;">
                                <span style="font-size: 12px; color: var(--text-gray); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                                    <?php echo htmlspecialchars($product['category']); ?>
                                </span>
                            </div>

                            <h3 style="font-size: 16px; font-weight: 700; color: var(--text-dark); margin: 0 0 8px 0; line-height: 1.4;">
                                <?php echo htmlspecialchars($product['product_name']); ?>
                            </h3>

                            <p style="font-size: 13px; color: var(--text-gray); margin: 0 0 12px 0; line-height: 1.5;">
                                <?php echo strlen($product['description']) > 80 ? substr(htmlspecialchars($product['description']), 0, 80) . '...' : htmlspecialchars($product['description']); ?>
                            </p>

                            <!-- Farmer Info -->
                            <a href="/agriconnect/pages/products/farmer-profile.php?user_id=<?php echo $product['user_id']; ?>" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; padding: 10px; background: var(--bg-light); border-radius: 8px; text-decoration: none; transition: all 0.3s ease;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-lime), var(--light-green)); display: flex; align-items: center; justify-content: center; color: var(--primary-green); font-weight: 700; font-size: 13px;">
                                    <?php echo strtoupper(substr($product['full_name'], 0, 1)); ?>
                                </div>
                                <div style="flex-grow: 1;">
                                    <p style="margin: 0; font-size: 13px; font-weight: 600; color: var(--text-dark);">
                                        <?php echo htmlspecialchars($product['full_name']); ?>
                                    </p>
                                    <p style="margin: 0; font-size: 12px; color: var(--text-gray);">
                                        <?php echo htmlspecialchars($product['farm_name']); ?>
                                    </p>
                                </div>
                            </a>

                            <!-- Price & Stock -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-top: 12px; border-top: 1px solid var(--border-light);">
                                <div>
                                    <p style="margin: 0; font-size: 12px; color: var(--text-gray); font-weight: 600;">PRICE PER <?php echo strtoupper($product['unit']); ?></p>
                                    <p style="margin: 5px 0 0 0; font-size: 20px; font-weight: 700; color: var(--primary-green);">
                                        ₱<?php echo number_format($product['price'], 2); ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <p style="margin: 0; font-size: 12px; color: var(--text-gray); font-weight: 600;">STOCK</p>
                                    <p style="margin: 5px 0 0 0; font-size: 18px; font-weight: 700; color: var(--accent-lime);">
                                        <?php echo $product['stock_quantity']; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: auto;">
                                <a href="/agriconnect/pages/products/product-detail.php?product_id=<?php echo $product['product_id']; ?>" class="btn btn-secondary" style="justify-content: center; background-color: var(--bg-light); color: var(--primary-green); border: 1px solid var(--border-light);">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <form method="POST" action="/agriconnect/pages/orders/add-to-cart.php" style="width: 100%;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                                        <i class="fas fa-cart-plus"></i> Add
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <i class="fas fa-inbox" style="font-size: 64px; color: var(--text-gray); opacity: 0.3; margin-bottom: 20px; display: block;"></i>
                    <p style="font-size: 18px; color: var(--text-gray); margin: 0;">No products found</p>
                    <p style="font-size: 14px; color: var(--text-gray); margin-top: 8px;">Try adjusting your filters or search terms</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/footer.php'); ?>
