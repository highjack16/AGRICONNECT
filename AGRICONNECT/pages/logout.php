<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/config/db.php');

if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], 'logout', 'User logged out');
}

session_destroy();
header("Location: /agriconnect/pages/login.php");
exit();
?>
