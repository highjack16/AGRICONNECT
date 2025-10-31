<?php
$page_title = 'Add Product';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/config/db.php');
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

if (!$farmer) {
    header("Location: /agriconnect/pages/dashboard/farmer-dashboard.php?error=farmer_not_found");
    exit();
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = sanitize($_POST['product_name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $stock_quantity = isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : 0;
    $unit = sanitize($_POST['unit'] ?? '');
    
    if ($product_name && $category && $price > 0 && $stock_quantity >= 0 && $unit) {
        $query = "INSERT INTO products (farmer_id, product_name, description, category, price, stock_quantity, unit, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            $error_message = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("isssdis", $farmer['farmer_id'], $product_name, $description, $category, $price, $stock_quantity, $unit);
            
            if ($stmt->execute()) {
                logActivity($current_user['user_id'], 'product_added', "Added product: $product_name");
                $success_message = "Product added successfully!";
                // Clear form
                $_POST = [];
            } else {
                $error_message = "Error adding product: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $error_message = "Please fill in all required fields with valid values.";
    }
}
?>

<div class="main-wrapper">
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/sidebar.php'); ?>
    
    <div class="main-content">
        <div class="top-bar">
            <div class="top-bar-left">
                <h1 style="margin: 0; color: var(--primary-green);">
                    <i class="fas fa-plus-circle" style="color: var(--accent-lime); margin-right: 10px;"></i>
                    Add New Product
                </h1>
            </div>
            <div class="top-bar-right">
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($current_user['full_name'], 0, 1)); ?></div>
                    <div class="user-details">
                        <h3><?php echo $current_user['full_name']; ?></h3>
                        <p><?php echo ucfirst($current_user['role']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width: 600px;">
            <div class="card-header">
                <h2>Product Details</h2>
            </div>
            <form method="POST" style="display: grid; gap: 15px;">
                <div class="form-group">
                    <label>Product Name <span style="color: var(--danger-red);">*</span></label>
                    <input type="text" name="product_name" placeholder="e.g., Fresh Tomatoes" value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Describe your product..." style="resize: vertical; min-height: 100px;"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Category <span style="color: var(--danger-red);">*</span></label>
                        <select name="category" required>
                            <option value="">Select Category</option>
                            <option value="Vegetables" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Vegetables') ? 'selected' : ''; ?>>Vegetables</option>
                            <option value="Fruits" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Fruits') ? 'selected' : ''; ?>>Fruits</option>
                            <option value="Grains" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Grains') ? 'selected' : ''; ?>>Grains</option>
                            <option value="Dairy" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Dairy') ? 'selected' : ''; ?>>Dairy</option>
                            <option value="Meat" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Meat') ? 'selected' : ''; ?>>Meat & Poultry</option>
                            <option value="Herbs" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Herbs') ? 'selected' : ''; ?>>Herbs</option>
                            <option value="Other" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Unit <span style="color: var(--danger-red);">*</span></label>
                        <select name="unit" required>
                            <option value="">Select Unit</option>
                            <option value="kg" <?php echo (isset($_POST['unit']) && $_POST['unit'] === 'kg') ? 'selected' : ''; ?>>Kilogram (kg)</option>
                            <option value="lb" <?php echo (isset($_POST['unit']) && $_POST['unit'] === 'lb') ? 'selected' : ''; ?>>Pound (lb)</option>
                            <option value="pcs" <?php echo (isset($_POST['unit']) && $_POST['unit'] === 'pcs') ? 'selected' : ''; ?>>Piece (pcs)</option>
                            <option value="bundle" <?php echo (isset($_POST['unit']) && $_POST['unit'] === 'bundle') ? 'selected' : ''; ?>>Bundle</option>
                            <option value="box" <?php echo (isset($_POST['unit']) && $_POST['unit'] === 'box') ? 'selected' : ''; ?>>Box</option>
                            <option value="liters" <?php echo (isset($_POST['unit']) && $_POST['unit'] === 'liters') ? 'selected' : ''; ?>>Liters</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Price per Unit (₱) <span style="color: var(--danger-red);">*</span></label>
                        <input type="number" name="price" placeholder="0.00" step="0.01" min="0" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity <span style="color: var(--danger-red);">*</span></label>
                        <input type="number" name="stock_quantity" placeholder="0" min="0" value="<?php echo isset($_POST['stock_quantity']) ? htmlspecialchars($_POST['stock_quantity']) : ''; ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;">
                    <i class="fas fa-save"></i> Add Product
                </button>
            </form>
        </div>
    </div>
</div>

<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/footer.php'); ?>
