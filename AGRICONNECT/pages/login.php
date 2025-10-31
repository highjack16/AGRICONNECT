<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/config/db.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ? AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && md5($password) === $user['password']) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        logActivity($user['user_id'], 'login', 'User logged in');

        if ($user['role'] === 'admin') {
            header("Location: /agriconnect/pages/dashboard/admin-dashboard.php");
        } elseif ($user['role'] === 'farmer') {
            header("Location: /agriconnect/pages/dashboard/farmer-dashboard.php");
        } elseif ($user['role'] === 'buyer') {
            header("Location: /agriconnect/pages/dashboard/buyer-dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AGRICONNECT</title>
    <link rel="stylesheet" href="/agriconnect/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="login-container">
    <div class="login-box">
        <div class="login-box-header">
            <div class="logo">
                <i class="fas fa-leaf"></i>
                AGRICONNECT
            </div>
            <h1>Welcome</h1>
            <p>Agriculture Direct Connection Platform</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="demo-credentials">
            <p><strong>Demo Credentials:</strong></p>
            <p>Admin: <strong>admin01</strong> / admin123<br>
            Farmer: <strong>farmer01</strong> / farmer123<br>
            Buyer: <strong>buyer01</strong> / buyer123</p>
        </div>
    </div>
</div>
</body>
</html>
