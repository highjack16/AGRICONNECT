<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Leave empty if no password
define('DB_NAME', 'agriconnect');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Function to log activity
function logActivity($user_id, $activity_type, $activity_description) {
    global $conn;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $query = "INSERT INTO user_activity_log (user_id, activity_type, activity_description, ip_address) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $user_id, $activity_type, $activity_description, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// Security: Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
