<?php
require_once 'includes/header.php';
require_once 'includes/config.php';
requireLogin();

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Fetch order details
$sql = "SELECT o.id, o.total_amount, o.status, o.created_at, 
               u.full_name, u.email, u.address, u.phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND (o.user_id = ? OR ?)";
$is_admin = isAdmin() ? 1 : 0;
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $order_id, $user_id, $is_admin);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: profile.php");
    exit();
}

$order = $result->fetch_assoc();

// Fetch order items with size information
$sql = "SELECT p.name, p.price, oi.quantity, oi.size, (p.price * oi.quantity) AS subtotal
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<h2>Order #<?php echo $order['id']; ?></h2>

<div class="order-details">
    <div class="order-info">
        <p><strong>Order Date:</strong> <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></p>
        <p><strong>Status:</strong> <span class="status-badge <?php echo strtolower($order['status']); ?>">
            <?php echo $order['status']; ?>
        </span></p>
        <p><strong>Total Amount:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?></p>
    </div>
    
    <div class="customer-info">
        <h3>Customer Information</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
        <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
    </div>
    
    <div class="order-items">
        <h3>Order Items</h3>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Size</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>₹<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <?php if (!empty($item['size'])): ?>
                                <span class="size-badge"><?php echo htmlspecialchars($item['size']); ?></span>
                            <?php else: ?>
                                <span class="size-badge">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right;"><strong>Total:</strong></td>
                    <td><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php if (isAdmin()): ?>
<div class="status-update-form">
    <h3>Update Order Status</h3>
    <form action="update_order_status.php" method="post">
        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
        
        <div class="form-group">
            <label for="status">Status:</label>
            <select name="status" id="status" class="form-control" required>
                <option value="Pending" <?= $order['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Processing" <?= $order['status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                <option value="Shipped" <?= $order['status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="Delivered" <?= $order['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Update Status</button>
    </form>
</div>
<?php endif; ?>

<p><a href="<?php echo isAdmin() ? 'admin/orders.php' : 'profile.php'; ?>" class="btn">Back to orders</a></p>

<style>
.order-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.order-info, .customer-info {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.order-items {
    grid-column: span 2;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
}

.cart-table th, .cart-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.cart-table th {
    background-color: #f8f9fa;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
}

.status-badge.pending {
    background-color: #fff3cd;
    color: #856404;
}

.status-badge.processing {
    background-color: #cce5ff;
    color: #004085;
}

.status-badge.shipped {
    background-color: #d4edda;
    color: #155724;
}

.status-badge.delivered {
    background-color: #d1ecf1;
    color: #0c5460;
}

.status-badge.cancelled {
    background-color: #f8d7da;
    color: #721c24;
}

.size-badge {
    display: inline-block;
    padding: 4px 8px;
    background-color: #f0f0f0;
    border-radius: 4px;
    font-size: 14px;
}

.status-update-form {
    margin: 20px 0;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 15px;
}

.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn {
    display: inline-block;
    padding: 8px 16px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
}

.btn-primary {
    background-color: #28a745;
}

.btn-primary:hover {
    background-color: #218838;
}
</style>

<?php
require_once 'includes/footer.php';
?>