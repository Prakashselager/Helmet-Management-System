<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Check if there's an order success message in session
if (!isset($_SESSION['order_success'])) {
    header("Location: products.php");
    exit();
}

// Get order details from session
$order_id = $_SESSION['order_success']['order_id'];
$total = $_SESSION['order_success']['total'];
$payment_method = $_SESSION['order_success']['payment_method'];

// Clear the session message
unset($_SESSION['order_success']);
?>

<div class="order-confirmation-container">
    <div class="confirmation-card">
        <div class="confirmation-header">
            <svg class="confirmation-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="#4CAF50" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase</p>
        </div>
        
        <div class="confirmation-details">
            <div class="detail-row">
                <span class="detail-label">Order Number:</span>
                <span class="detail-value">#<?php echo htmlspecialchars($order_id); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Amount:</span>
                <span class="detail-value">₹<?php echo number_format($total, 2); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value"><?php echo htmlspecialchars($payment_method); ?></span>
            </div>
        </div>
        
        <div class="confirmation-message">
            <p>Your order has been successfully placed and is being processed.</p>
            <p>To see the order process click on Go to Profile button below.</p>
        </div>
        
        <div class="confirmation-actions">
            <a href="profile.php" class="btn btn-primary">Go to Profile</a>
            <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>
</div>

<style>
.order-confirmation-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
    padding: 20px;
    background-color: #f5f5f5;
}

.confirmation-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    padding: 40px;
    max-width: 600px;
    width: 100%;
    text-align: center;
    animation: fadeIn 0.5s ease-in-out;
}

.confirmation-header {
    margin-bottom: 30px;
}

.confirmation-icon {
    width: 80px;
    height: 80px;
    margin-bottom: 20px;
}

.confirmation-header h1 {
    color: #4CAF50;
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.confirmation-header p {
    color: #666;
    font-size: 1.2rem;
}

.confirmation-details {
    background: #f9f9f9;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    text-align: left;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #555;
}

.detail-value {
    color: #333;
}

.confirmation-message {
    margin: 25px 0;
    color: #666;
    line-height: 1.6;
}

.confirmation-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 30px;
}

.btn {
    padding: 12px 25px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #4CAF50;
    color: white;
    border: 2px solid #4CAF50;
}

.btn-primary:hover {
    background-color: #3e8e41;
    border-color: #3e8e41;
}

.btn-secondary {
    background-color: white;
    color: #4CAF50;
    border: 2px solid #4CAF50;
}

.btn-secondary:hover {
    background-color: #f5f5f5;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 600px) {
    .confirmation-card {
        padding: 25px;
    }
    
    .confirmation-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?>