<?php
// Start session and include config first
require_once 'includes/config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$password_changed = false;

// Check for session messages
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch user details
$sql = "SELECT username, email, full_name, address, phone FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) die("Database error: ".$conn->error);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Process form submission before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $address = sanitize($_POST['address']);
    $phone = sanitize($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($full_name) || empty($email)) {
        $error = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (!empty($new_password) && strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Check if email is already taken
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already in use by another account.";
        } else {
            // Verify current password if changing password
            if (!empty($new_password)) {
                $sql = "SELECT password FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user_data = $result->fetch_assoc();
                
                if (!password_verify($current_password, $user_data['password'])) {
                    $error = "Current password is incorrect.";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }
            
            if (empty($error)) {
                // Update user details
                if (isset($hashed_password)) {
                    $sql = "UPDATE users SET full_name = ?, email = ?, address = ?, phone = ?, password = ? 
                            WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssssi", $full_name, $email, $address, $phone, $hashed_password, $user_id);
                } else {
                    $sql = "UPDATE users SET full_name = ?, email = ?, address = ?, phone = ? 
                            WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssi", $full_name, $email, $address, $phone, $user_id);
                }
                
                if ($stmt->execute()) {
                    $success = "Profile updated successfully.";
                    // Refresh user data
                    $sql = "SELECT username, email, full_name, address, phone FROM users WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                    
                    $password_changed = isset($hashed_password);
                } else {
                    $error = "Failed to update profile. Please try again.";
                }
            }
        }
    }
}

// Regenerate session ID if password was changed (before any output)
if ($password_changed) {
    session_regenerate_id(true);
}

// Now include header which outputs HTML
require_once 'includes/header.php';

// Fetch user orders (limited to 5 most recent)
$sql = "SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<h2>My Profile</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="profile-container">
    <div class="profile-form">
        <form action="profile.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address"><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>
            
            <h3>Change Password</h3>
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password">
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password">
                <small>Minimum 8 characters</small>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
            
            <button type="submit" name="update_profile" class="btn">Update Profile</button>
        </form>
    </div>
    
    <div class="recent-orders">
        <h3>Recent Orders</h3>
        <?php if ($orders->num_rows > 0): ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                            <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><span class="status-<?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></td>
                            <td class="order-actions">
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn-view">View</a>
                                <?php if ($order['status'] === 'Pending' || $order['status'] === 'Processing'): ?>
                                    <a href="cancel_order.php?id=<?php echo $order['id']; ?>" class="btn-cancel" onclick="return confirm('Are you sure you want to cancel this order?')">Cancel</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <a href="order_history.php" class="btn-view-all">View All Orders</a>
        <?php else: ?>
            <p>You have no recent orders. <a href="products.php">Browse products</a></p>
        <?php endif; ?>
    </div>
</div>

<style>
.profile-container {
    display: flex;
    gap: 30px;
    margin-top: 20px;
}

.profile-form {
    flex: 1;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recent-orders {
    flex: 1;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.order-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.order-table th, .order-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.order-table th {
    background-color: #f5f5f5;
    font-weight: 600;
}

.status-pending {
    color: #FF9800;
    font-weight: 500;
}

.status-processing {
    color: #2196F3;
    font-weight: 500;
}

.status-completed {
    color: #4CAF50;
    font-weight: 500;
}

.status-cancelled {
    color: #F44336;
    font-weight: 500;
}

.order-actions {
    display: flex;
    gap: 8px;
}

.btn-view {
    padding: 5px 10px;
    background: #f0f0f0;
    color: #333;
    border-radius: 3px;
    text-decoration: none;
    font-size: 14px;
}

.btn-view:hover {
    background: #e0e0e0;
}

.btn-cancel {
    padding: 5px 10px;
    background: #ffebee;
    color: #F44336;
    border-radius: 3px;
    text-decoration: none;
    font-size: 14px;
}

.btn-cancel:hover {
    background: #F44336;
    color: white;
}

.btn-view-all {
    display: inline-block;
    margin-top: 15px;
    padding: 8px 15px;
    background: #f5f5f5;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
}

.btn-view-all:hover {
    background: #e0e0e0;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-error {
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #ef9a9a;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.form-group textarea {
    min-height: 100px;
}

.btn {
    padding: 10px 20px;
    background: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.btn:hover {
    background: #3e8e41;
}

@media (max-width: 768px) {
    .profile-container {
        flex-direction: column;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?>