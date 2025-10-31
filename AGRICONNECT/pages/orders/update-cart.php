<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)$_POST['product_id'];
    $action = sanitize($_POST['action']);

    if (!isset($_SESSION['cart'][$product_id])) {
        header("Location: /agriconnect/pages/orders/view-cart.php");
        exit();
    }

    switch ($action) {
        case 'increase':
            $_SESSION['cart'][$product_id]++;
            break;
        case 'decrease':
            if ($_SESSION['cart'][$product_id] > 1) {
                $_SESSION['cart'][$product_id]--;
            }
            break;
        case 'remove':
            unset($_SESSION['cart'][$product_id]);
            break;
    }

    logActivity($_SESSION['user_id'], 'cart_updated', "Cart updated: $action for product $product_id");
}

header("Location: /airconnect/pages/orders/view-cart.php");
exit();
?>
