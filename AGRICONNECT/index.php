<?php
session_start();

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role === 'admin') {
        header("Location: /agriconnect/pages/dashboard/admin-dashboard.php");
    } elseif ($role === 'farmer') {
        header("Location: /agriconnect/pages/dashboard/farmer-dashboard.php");
    } elseif ($role === 'buyer') {
        header("Location: /agriconnect/pages/dashboard/buyer-dashboard.php");
    }
} else {
    header("Location: /agriconnect/pages/login.php");
}
exit();
?>
