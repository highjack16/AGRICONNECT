<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /agriconnect/pages/login.php");
        exit();
    }

    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Validate product exists and has stock
    $query = "SELECT * FROM products WHERE product_id = ? AND is_active = 1 AND stock_quantity > 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        header("Location: /agriconnect/pages/products/marketplace.php?error=product_not_found");
        exit();
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add or update product in cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    // Log activity
    logActivity($_SESSION['user_id'], 'add_to_cart', "Added product $product_id to cart");

    // Redirect back
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '/agriconnect/pages/products/marketplace.php';
    header("Location: $redirect");
}
?>
