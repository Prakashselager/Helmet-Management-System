<?php
require_once 'includes/header.php';
require_once 'includes/config.php';

// Fetch all products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<h2>Our Products</h2>

<div class="product-grid">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="product-card">
            <div class="product-image">
                <img src="<?php echo $row['image_path']; ?>" alt="<?php echo $row['name']; ?>">
                <div class="product-actions">
                    <a href="view_details.php?id=<?php echo $row['id']; ?>" class="btn btn-view">View Details</a>
                </div>
            </div>
            <div class="product-info">
                <div class="product-name-container">
                    <h3><?php echo $row['name']; ?></h3>
                </div>
               
                <div class="product-price">₹<?php echo number_format($row['price'], 2); ?></div>
                <div class="product-actions-bottom">
                    <?php if (isLoggedIn()): ?>
                        <form action="cart.php" method="post" class="product-form">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <div class="form-group">
                                <label for="quantity-<?php echo $row['id']; ?>">Quantity:</label>
                                <input type="number" name="quantity" id="quantity-<?php echo $row['id']; ?>" 
                                       min="1" max="<?php echo $row['stock']; ?>" value="1" required>
                            </div>
                            <button type="submit" class="btn">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <div class="login-prompt">
                            <a href="login.php">LOGIN TO PURCHASE</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php
require_once 'includes/footer.php';
?>