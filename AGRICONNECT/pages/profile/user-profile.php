<?php
$page_title = 'My Profile';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/auth-check.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/header.php');

// Handle profile update
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    $query = "UPDATE users SET full_name = ?, phone = ?, address = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $full_name, $phone, $address, $current_user['user_id']);
    
    if ($stmt->execute()) {
        $success_message = "Profile updated successfully!";
        $current_user['full_name'] = $full_name;
        $current_user['phone'] = $phone;
        $current_user['address'] = $address;
        logActivity($current_user['user_id'], 'profile_update', 'User updated their profile information');
    } else {
        $error_message = "Error updating profile. Please try again.";
    }
    $stmt->close();
}

// Get role-specific information
$role_info = '';
if ($current_user['role'] === 'farmer') {
    $query = "SELECT * FROM farmers WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $current_user['user_id']);
    $stmt->execute();
    $role_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} elseif ($current_user['role'] === 'buyer') {
    $query = "SELECT * FROM buyers WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $current_user['user_id']);
    $stmt->execute();
    $role_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<div class="main-wrapper">
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/sidebar.php'); ?>
    
    <div class="main-content">
        <div class="top-bar">
            <div class="top-bar-left">
                <h1 style="margin: 0; color: var(--primary-green);">
                    <i class="fas fa-user-circle" style="color: var(--accent-lime); margin-right: 10px;"></i>
                    My Profile
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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
            <!-- Personal Information -->
            <div class="card">
                <div class="card-header">
                    <h2>Personal Information</h2>
                </div>
                <form method="POST" style="display: grid; gap: 15px;">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($current_user['full_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" disabled style="background-color: var(--bg-light); cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" style="resize: vertical; min-height: 100px;"><?php echo htmlspecialchars($current_user['address'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>

            <!-- Account Information -->
            <div class="card">
                <div class="card-header">
                    <h2>Account Information</h2>
                </div>
                <div style="display: grid; gap: 15px;">
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">USERNAME</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo htmlspecialchars($current_user['username']); ?>
                        </p>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">ROLE</p>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="badge" style="background: linear-gradient(135deg, var(--accent-lime), #9ccc65); color: var(--primary-green); padding: 8px 16px; border-radius: 6px; font-weight: 600; text-transform: uppercase; font-size: 12px;">
                                <?php echo ucfirst($current_user['role']); ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">ACCOUNT STATUS</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo $current_user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </p>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">MEMBER SINCE</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo date('M d, Y', strtotime($current_user['created_at'])); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role-Specific Information -->
        <?php if ($current_user['role'] === 'farmer' && isset($role_data)): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Farm Information</h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">FARM NAME</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo htmlspecialchars($role_data['farm_name'] ?? 'Not provided'); ?>
                        </p>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">FARM LOCATION</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo htmlspecialchars($role_data['farm_location'] ?? 'Not provided'); ?>
                        </p>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">FARM SIZE</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo $role_data['farm_size'] ?? '0'; ?> hectares
                        </p>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">YEARS OF EXPERIENCE</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo $role_data['years_of_experience'] ?? '0'; ?> years
                        </p>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">SPECIALIZATION</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo htmlspecialchars($role_data['specialization'] ?? 'Not specified'); ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php elseif ($current_user['role'] === 'buyer' && isset($role_data)): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Business Information</h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">BUSINESS NAME</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo htmlspecialchars($role_data['business_name'] ?? 'Not provided'); ?>
                        </p>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">BUSINESS TYPE</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo htmlspecialchars($role_data['business_type'] ?? 'Not provided'); ?>
                        </p>
                    </div>
                    <div>
                        <p style="font-size: 12px; color: var(--text-gray); margin-bottom: 5px;">ESTABLISHED YEAR</p>
                        <p style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin: 0;">
                            <?php echo $role_data['established_year'] ?? 'Not provided'; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/footer.php'); ?>
