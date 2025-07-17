<?php
require_once 'includes/header.php';
require_once 'includes/config.php';
requireLogin();

$message = '';
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Add to cart with size
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $size = isset($_POST['size']) ? sanitize($_POST['size']) : '';
    
    // Validate quantity
    if ($quantity <= 0) {
        $message = "Quantity must be greater than 0.";
    } else {
        // Check product stock
        $sql = "SELECT stock FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $product = $result->fetch_assoc();
            if ($quantity > $product['stock']) {
                $message = "Sorry, we only have " . $product['stock'] . " of this item in stock.";
            } else {
                // Add to cart or update quantity with size
                $cart_item_key = $product_id . '-' . $size;
                if (isset($cart[$cart_item_key])) {
                    $cart[$cart_item_key]['quantity'] += $quantity;
                } else {
                    $cart[$cart_item_key] = [
                        'quantity' => $quantity,
                        'size' => $size
                    ];
                }
                $_SESSION['cart'] = $cart;
                $message = "Product added to cart successfully.";
            }
        } else {
            $message = "Product not found.";
        }
    }
}

// Remove from cart
if (isset($_GET['remove'])) {
    $remove_key = $_GET['remove'];
    if (isset($cart[$remove_key])) {
        unset($cart[$remove_key]);
        $_SESSION['cart'] = $cart;
        $message = "Product removed from cart.";
    }
}

// Update cart quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $cart_key => $quantity) {
        $quantity = intval($quantity);
        
        if ($quantity <= 0) {
            unset($cart[$cart_key]);
        } else {
            // Extract product ID from cart key (format: productid-size)
            $parts = explode('-', $cart_key);
            $product_id = intval($parts[0]);
            
            // Check stock before updating
            $sql = "SELECT stock FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $product = $result->fetch_assoc();
                if ($quantity > $product['stock']) {
                    $message = "Sorry, we only have " . $product['stock'] . " of this item in stock.";
                    $quantity = $product['stock'];
                }
                if (isset($cart[$cart_key])) { // Check if cart item exists before updating
                    $cart[$cart_key]['quantity'] = $quantity;
                }
            }
        }
    }
    $_SESSION['cart'] = $cart;
}

// Calculate total
$total = 0;
$cart_items = [];
if (!empty($cart)) {
    // Get all product IDs from cart
    $product_ids = [];
    foreach ($cart as $key => $item) {
        // Skip if $item is not an array (legacy cart items)
        if (!is_array($item)) continue;
        
        $parts = explode('-', $key);
        $product_ids[] = intval($parts[0]);
    }
    $product_ids = array_unique($product_ids);
    
    if (!empty($product_ids)) {
        $product_ids_str = implode(',', $product_ids);
        $sql = "SELECT id, name, price, stock, size FROM products WHERE id IN ($product_ids_str)";
        $result = $conn->query($sql);
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[$row['id']] = $row;
        }
        
        foreach ($cart as $key => $item) {
            // Skip if $item is not an array (legacy cart items)
            if (!is_array($item)) continue;
            
            $parts = explode('-', $key);
            $product_id = intval($parts[0]);
            $size = isset($parts[1]) ? $parts[1] : '';
            
            if (isset($products[$product_id])) {
                $product = $products[$product_id];
                $cart_items[] = [
                    'id' => $product_id,
                    'key' => $key,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'quantity' => $item['quantity'],
                    'size' => $item['size'],
                    'subtotal' => $product['price'] * $item['quantity']
                ];
                $total += $product['price'] * $item['quantity'];
            }
        }
    }
}
?>

<!-- Rest of your HTML and styling remains the same -->

<h2>Your Shopping Cart</h2>

<?php if ($message): ?>
    <div class="alert <?php echo strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-error'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>



<?php if (empty($cart_items)): ?>
    <p>Your cart is empty. <a href="products.php">Browse products</a></p>
<?php else: ?>
    <form action="cart.php" method="post">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Size</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo $item['name']; ?></td>
                        <td>₹<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <?php if (!empty($item['size'])): ?>
                                <span class="size-badge"><?php echo htmlspecialchars($item['size']); ?></span>
                            <?php else: ?>
                                <span class="size-badge">One Size</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="number" name="quantities[<?php echo $item['key']; ?>]" 
                                   value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>">
                        </td>
                        <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
                        <td><a href="cart.php?remove=<?php echo $item['key']; ?>" class="remove-link">Remove</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="cart-total">
            <strong>Total: ₹<?php echo number_format($total, 2); ?></strong>
        </div>
        <div class="form-group">
            <button type="submit" name="update_cart" class="btn">Update Cart</button>
            <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        </div>
    </form>
<?php endif; ?>

<style>
/* Add to your existing styles */
.size-badge {
    display: inline-block;
    padding: 5px 10px;
    background-color: #f0f0f0;
    border-radius: 4px;
    font-size: 14px;
    min-width: 50px;
    text-align: center;
}

.remove-link {
    color: #dc3545;
    text-decoration: none;
}

.remove-link:hover {
    text-decoration: underline;
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