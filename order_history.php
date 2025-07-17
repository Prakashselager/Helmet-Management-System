<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$orders = [];

// Fetch all orders for the user
try {
    $sql = "SELECT o.id, o.total_amount, o.status, o.created_at, 
                   COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Database error: ".$conn->error);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
} catch (Exception $e) {
    $error = "Error loading order history: ".$e->getMessage();
}
?>

<div class="order-history-container">
    <h1>Your Order History</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (empty($orders)): ?>
        <div class="empty-orders">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="#FF9800" d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94.63 1.5 1.98 2.63 3.61 2.96V19H7v2h10v-2h-4v-3.1c1.63-.33 2.98-1.46 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/>
            </svg>
            <h2>No Orders Found</h2>
            <p>You haven't placed any orders yet.</p>
            <a href="products.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card animate__animated animate__fadeInUp">
                    <div class="order-header">
                        <div class="order-id">Order #<?php echo htmlspecialchars($order['id']); ?></div>
                        <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail">
                            <span class="label">Date:</span>
                            <span class="value"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                        </div>
                        <div class="detail">
                            <span class="label">Items:</span>
                            <span class="value"><?php echo htmlspecialchars($order['item_count']); ?></span>
                        </div>
                        <div class="detail">
                            <span class="label">Total:</span>
                            <span class="value">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-view">
                            View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.order-history-container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 20px;
}

h1 {
    color: #333;
    text-align: center;
    margin-bottom: 30px;
    font-size: 2.2rem;
}

.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.alert-error {
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #ef9a9a;
}

.empty-orders {
    text-align: center;
    padding: 40px;
    background: #fff8e1;
    border-radius: 10px;
    max-width: 500px;
    margin: 0 auto;
}

.empty-orders svg {
    width: 80px;
    height: 80px;
    margin-bottom: 20px;
}

.empty-orders h2 {
    color: #FF9800;
    margin-bottom: 10px;
}

.empty-orders p {
    color: #666;
    margin-bottom: 20px;
}

.orders-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.order-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.order-id {
    font-weight: bold;
    color: #333;
    font-size: 1.1rem;
}

.order-status {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: bold;
}

.status-pending {
    background-color: #fff3e0;
    color: #FF9800;
}

.status-processing {
    background-color: #e3f2fd;
    color: #2196F3;
}

.status-shipped {
    background-color: #e8f5e9;
    color: #4CAF50;
}

.status-delivered {
    background-color: #e8f5e9;
    color: #2E7D32;
}

.status-cancelled {
    background-color: #ffebee;
    color: #c62828;
}

.order-details {
    margin-bottom: 15px;
}

.detail {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.detail .label {
    color: #666;
}

.detail .value {
    color: #333;
    font-weight: 500;
}

.order-actions {
    display: flex;
    justify-content: flex-end;
}

.btn {
    padding: 8px 16px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-view {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ddd;
}

.btn-view:hover {
    background-color: #e0e0e0;
}

.btn-primary {
    background-color: #FF9800;
    color: white;
    border: none;
    padding: 10px 20px;
}

.btn-primary:hover {
    background-color: #F57C00;
}

.animate__animated {
    animation-duration: 0.5s;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate__fadeInUp {
    animation-name: fadeInUp;
}

@media (max-width: 768px) {
    .orders-list {
        grid-template-columns: 1fr;
    }
    
    .order-card {
        padding: 15px;
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<script>
// Add staggered animation delay to order cards
document.addEventListener('DOMContentLoaded', function() {
    const orderCards = document.querySelectorAll('.order-card');
    orderCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>