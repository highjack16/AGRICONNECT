<?php
$page_title = 'Manage Users';
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/auth-check.php');
checkRole(['admin']);
include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/header.php');

$role_filter = isset($_GET['role']) ? sanitize($_GET['role']) : '';

$query = "SELECT * FROM users WHERE 1=1";
if ($role_filter) {
    $role_filter_safe = $conn->real_escape_string($role_filter);
    $query .= " AND role = '$role_filter_safe'";
}
$query .= " ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<div class="main-wrapper">
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/sidebar.php'); ?>
    
    <div class="main-content">
        <div class="top-bar">
            <div class="top-bar-left">
                <h1 style="margin: 0; color: var(--primary-green);">
                    <i class="fas fa-users" style="color: var(--accent-lime); margin-right: 10px;"></i>
                    Manage Users
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

        <div class="card">
            <div style="margin-bottom: 20px;">
                <label style="font-weight: 600; font-size: 13px; color: var(--text-gray); display: block; margin-bottom: 8px;">FILTER BY ROLE</label>
                <select onchange="window.location.href='?role=' + this.value" style="padding: 10px 15px; border-radius: 8px; border: 1px solid var(--border-light); font-size: 14px;">
                    <option value="">All Users</option>
                    <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="farmer" <?php echo $role_filter === 'farmer' ? 'selected' : ''; ?>>Farmer</option>
                    <option value="buyer" <?php echo $role_filter === 'buyer' ? 'selected' : ''; ?>>Buyer</option>
                </select>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td>
                                    <span class="badge" style="background: linear-gradient(135deg, var(--accent-lime), #9ccc65); color: var(--primary-green); padding: 6px 12px; border-radius: 6px; font-weight: 600; text-transform: uppercase; font-size: 11px;">
                                        <?php echo ucfirst($row['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $row['is_active'] ? 'badge-success' : 'badge-danger'; ?>" style="padding: 6px 12px;">
                                        <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/agriconnect/includes/footer.php'); ?>
