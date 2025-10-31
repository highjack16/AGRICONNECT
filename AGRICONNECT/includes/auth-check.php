<?php
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /agriconnect/pages/login.php");
    exit();
}

// Get user information
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/config/db.php');

$user_id = $_SESSION['user_id'];
$query = "SELECT u.* FROM users u WHERE u.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_user = $result->fetch_assoc();
$stmt->close();

if (!$current_user || !$current_user['is_active']) {
    session_destroy();
    header("Location: /agriconnect/pages/login.php");
    exit();
}

// Check role-based access
function checkRole($allowed_roles) {
    global $current_user;
    if (!in_array($current_user['role'], $allowed_roles)) {
        header("Location: /agriconnect/index.php?error=unauthorized");
        exit();
    }
}
?>
