<?php
ob_start();
require_once 'includes/header.php';
require_once 'includes/config.php';
requireLogin();

// Initialize all variables with default values
$user_id = $_SESSION['user_id'] ?? 0;
$error = '';
$success = '';
$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$cart_total = 0;
$user = [
    'full_name' => '',
    'email' => '',
    'address' => '',
    'phone' => ''
];

// Redirect if cart is empty
if (empty($cart)) {
    header("Location: cart.php");
    exit();
}

// Fetch user details
try {
    $sql = "SELECT full_name, email, address, phone FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Database error: ".$conn->error);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
    } else {
        throw new Exception("User not found");
    }
} catch (Exception $e) {
    $error = "Error loading user data: ".$e->getMessage();
}

// Process checkout form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize inputs
        $required_fields = ['name', 'email', 'address', 'phone'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All fields are required");
            }
        }

        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $address = sanitize($_POST['address']);
        $phone = sanitize($_POST['phone']);
        $payment_method = 'Cash on Delivery';

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
            throw new Exception("Invalid phone number format");
        }

        // Calculate cart total and verify stock
        $total = 0;
        $product_details = [];
        
        foreach ($cart as $cart_key => $item) {
            $parts = explode('-', $cart_key);
            $product_id = intval($parts[0]);
            $size = isset($parts[1]) ? $parts[1] : null;
            
            $sql = "SELECT id, price, stock, name FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Database error: ".$conn->error);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows !== 1) {
                throw new Exception("Product not found");
            }
            
            $product = $result->fetch_assoc();
            $quantity = $item['quantity'];
            
            if ($quantity <= 0) {
                throw new Exception("Invalid quantity for product: ".$product['name']);
            }
            
            if ($quantity > $product['stock']) {
                throw new Exception("Insufficient stock for ".$product['name'].". Only ".$product['stock']." available");
            }
            
            $subtotal = $product['price'] * $quantity;
            $total += $subtotal;
            
            $product_details[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'size' => $size,
                'subtotal' => $subtotal
            ];
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            // Create order record
            $sql = "INSERT INTO orders (user_id, total_amount, payment_method, status) 
                    VALUES (?, ?, ?, 'Pending')";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Database error: ".$conn->error);
            $stmt->bind_param("ids", $user_id, $total, $payment_method);
            if (!$stmt->execute()) {
                throw new Exception("Failed to create order");
            }
            $order_id = $conn->insert_id;

            // Add order items with size information
            foreach ($product_details as $product) {
                $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, name, size) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) throw new Exception("Database error: ".$conn->error);
                $stmt->bind_param("iiidss", $order_id, $product['id'], $product['quantity'], 
                                  $product['price'], $product['name'], $product['size']);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to add order items");
                }

                // Update product stock
                $sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) throw new Exception("Database error: ".$conn->error);
                $stmt->bind_param("ii", $product['quantity'], $product['id']);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update product stock");
                }
            }

            // Update user details if changed
            if ($name !== $user['full_name'] || $email !== $user['email'] || 
                $address !== $user['address'] || $phone !== $user['phone']) {
                $sql = "UPDATE users SET full_name = ?, email = ?, address = ?, phone = ? 
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) throw new Exception("Database error: ".$conn->error);
                $stmt->bind_param("ssssi", $name, $email, $address, $phone, $user_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update user details");
                }
            }

            // Commit transaction
            $conn->commit();

            // Clear cart and set success message
            unset($_SESSION['cart']);
            $_SESSION['order_success'] = [
                'order_id' => $order_id,
                'total' => $total,
                'payment_method' => $payment_method
            ];

            header("Location: order_confirmation.php");
            ob_end_flush();
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Calculate cart totals for display
try {
    foreach ($cart as $cart_key => $item) {
        $parts = explode('-', $cart_key);
        $product_id = intval($parts[0]);
        $size = isset($parts[1]) ? $parts[1] : null;
        
        $sql = "SELECT id, name, price, stock FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Database error: ".$conn->error);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $product = $result->fetch_assoc();
            $quantity = $item['quantity'];
            $subtotal = $product['price'] * $quantity;
            $cart_total += $subtotal;
            
            $cart_items[] = [
                'id' => $product['id'],
                'key' => $cart_key,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'size' => $size,
                'subtotal' => $subtotal,
                'stock' => $product['stock']
            ];
        }
    }
} catch (Exception $e) {
    $error = "Error loading cart items: ".$e->getMessage();
}
?>

<div class="checkout-page">
    <h2 class="checkout-title">Checkout</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="checkout-grid">
        <!-- Shipping Information Section -->
        <div class="checkout-section">
            <form action="checkout.php" method="post" id="checkoutForm">
                <div class="section-header">
                    <h3>Shipping Information</h3>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? $user['full_name'] ?? ''); ?>" 
                               class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email'] ?? ''); ?>" 
                               class="form-control" required>
                        <span class="form-hint">We'll send your order confirmation here</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Shipping Address *</label>
                        <textarea id="address" name="address" class="form-control" required><?php 
                            echo htmlspecialchars($_POST['address'] ?? $user['address'] ?? ''); 
                        ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? $user['phone'] ?? ''); ?>" 
                               class="form-control" required pattern="[0-9]{10,15}">
                        <span class="form-hint">10-15 digits only</span>
                    </div>
                </div>

                <!-- Payment Method Section -->
                <div class="section-header">
                    <h3>Payment Method</h3>
                </div>
                
                <div class="payment-methods">
                    <div class="payment-method selected">
                        <input type="radio" id="cod" name="payment_method" value="Cash on Delivery" checked disabled>
                        <label for="cod">
                            <span class="payment-icon">💰</span>
                            <span class="payment-name">Cash on Delivery</span>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Place Order</button>
                    <a href="cart.php" class="btn btn-outline">Back to Cart</a>
                </div>
            </form>
        </div>

        <!-- Order Summary Section -->
        <div class="order-summary">
            <div class="section-header">
                <h3>Order Summary</h3>
            </div>
            
            <?php if (!empty($cart_items)): ?>
                <div class="order-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <?php if (!empty($item['size'])): ?>
                                    <div class="item-size">Size: <?php echo htmlspecialchars($item['size']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="item-meta">
                                <div class="item-quantity"><?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price'], 2); ?></div>
                                <div class="item-price">₹<?php echo number_format($item['subtotal'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-totals">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Shipping</span>
                        <span>Free</span>
                    </div>
                    <div class="total-row grand-total">
                        <span>Total</span>
                        <span>₹<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-cart-message">Your cart is empty</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<style>
/* Add this CSS to only increase input field sizes */
.form-control {
    padding: 15px 20px;  /* Increased from typical 8px-12px */
    font-size: 18px;     /* Increased from typical 14px-16px */
    height: auto;        /* Ensure height adjusts to content */
    min-height: 40px;    /* Set minimum height for consistency */
}

/* Specifically make the textarea taller */
textarea.form-control {
    min-height: 120px;   /* Increased height for address field */
}

/* Keep all other existing styles the same */
</style>

<!-- [CSS and JavaScript remain exactly the same as in previous example] -->
<style>
/* Base Styles */
.checkout-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.checkout-title {
    font-size: 32px;
    margin-bottom: 30px;
    color: #2c3e50;
    text-align: center;
    position: relative;
    padding-bottom: 15px;
}

.checkout-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 3px;
    background: linear-gradient(90deg, #3498db, #9b59b6);
    border-radius: 3px;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 30px;
}

/* Form Section */
.checkout-section {
    background: #ffffff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.checkout-section:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.12);
}

.section-header {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ecf0f1;
}

.section-header h3 {
    font-size: 22px;
    color: #34495e;
    position: relative;
    display: inline-block;
}

.section-header h3::after {
    content: '';
    position: absolute;
    bottom: -16px;
    left: 0;
    width: 50px;
    height: 3px;
    background: #3498db;
    border-radius: 3px;
}

/* Form Elements */
.form-row {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    color: #7f8c8d;
    font-size: 16px;
    transition: color 0.3s;
}

.form-control {
    padding: 16px 20px;
    border: 2px solid #ecf0f1;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s;
    width: 100%;
    box-sizing: border-box;
    background-color: #f9f9f9;
}

.form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    outline: none;
    background-color: #fff;
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

.form-hint {
    font-size: 14px;
    color: #95a5a6;
    margin-top: 5px;
}

/* Payment Methods */
.payment-methods {
    margin: 25px 0;
}

.payment-method {
    display: flex;
    align-items: center;
    padding: 18px;
    border: 2px solid #ecf0f1;
    border-radius: 8px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s;
    background-color: #f9f9f9;
}

.payment-method.selected {
    border-color: #3498db;
    background-color: #eaf5ff;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(52, 152, 219, 0); }
    100% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0); }
}

.payment-method:hover {
    background-color: #f1f1f1;
}

.payment-method input {
    margin-right: 15px;
    transform: scale(1.2);
}

.payment-icon {
    margin-right: 12px;
    font-size: 24px;
    color: #3498db;
}

.payment-name {
    font-size: 16px;
    font-weight: 600;
    color: #34495e;
}

/* Buttons */
.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn {
    padding: 16px 30px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
    text-decoration: none;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-primary {
    background-color: #3498db;
    color: white;
    border: none;
    position: relative;
    overflow: hidden;
}

.btn-primary:hover {
    background-color: #2980b9;
    transform: translateY(-2px);
}

.btn-primary::after {
    content: '→';
    margin-left: 10px;
    transition: transform 0.3s;
}

.btn-primary:hover::after {
    transform: translateX(5px);
}

.btn-outline {
    background-color: white;
    color: #3498db;
    border: 2px solid #3498db;
}

.btn-outline:hover {
    background-color: #eaf5ff;
    transform: translateY(-2px);
}

/* Order Summary */
.order-summary {
    background: #ffffff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: transform 0.3s ease;
    position: sticky;
    top: 20px;
}

.order-summary:hover {
    transform: translateY(-5px);
}

.order-items {
    margin-bottom: 20px;
    max-height: 300px;
    overflow-y: auto;
    padding-right: 10px;
}

.order-item {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    border-bottom: 1px solid #ecf0f1;
    transition: transform 0.3s;
}

.order-item:hover {
    transform: translateX(5px);
}

.item-info {
    flex: 1;
}

.item-name {
    font-weight: 600;
    margin-bottom: 5px;
    font-size: 16px;
    color: #2c3e50;
}

.item-size {
    font-size: 14px;
    color: #7f8c8d;
}

.item-meta {
    text-align: right;
}

.item-quantity {
    font-size: 14px;
    color: #7f8c8d;
    margin-bottom: 5px;
}

.item-price {
    font-weight: 600;
    color: #2c3e50;
}

.order-totals {
    margin-top: 20px;
    border-top: 2px solid #ecf0f1;
    padding-top: 20px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #ecf0f1;
}

.total-row:last-child {
    border-bottom: none;
}

.grand-total {
    font-weight: 700;
    font-size: 18px;
    margin-top: 15px;
    color: #2c3e50;
}

.grand-total span:last-child {
    color: #e74c3c;
    font-size: 20px;
}

.empty-cart-message {
    text-align: center;
    padding: 30px;
    color: #95a5a6;
    font-size: 16px;
}

/* Alerts */
.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 25px;
    animation: slideDown 0.5s ease-out;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert-error {
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #ef9a9a;
}

/* Responsive */
@media (max-width: 768px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    
    .order-summary {
        position: static;
        margin-top: 30px;
    }
}

/* Scrollbar styling */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #bdc3c7;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #95a5a6;
}
</style>

<style>
/* Specific styling for the input fields you want to enlarge */
#name, #email, #phone {
    padding: 18px 25px;  /* Increased padding for taller/wider fields */
    font-size: 18px;     /* Larger font size */
    min-height: 60px;    /* Minimum height to ensure consistency */
    width: 100%;         /* Full width of container */
    border: 2px solid #ddd; /* Clear border definition */
    border-radius: 8px;  /* Slightly rounded corners */
    box-sizing: border-box; /* Include padding in width calculation */
    transition: all 0.3s ease; /* Smooth transitions for interactions */
}

/* Focus state for better usability */
#name:focus, #email:focus, #phone:focus {
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
    outline: none;
}

/* Make the text slightly bolder */
#name, #email, #phone {
    font-weight: 500;
}

/* Keep all other styles the same */
</style>

<?php
require_once 'includes/footer.php';
?>