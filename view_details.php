<?php
require_once 'includes/header.php';
require_once 'includes/config.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header("Location: products.php");
    exit();
}

// Fetch product details
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: products.php");
    exit();
}

$product = $result->fetch_assoc();

// Parse available sizes
$available_sizes = !empty($product['size']) ? explode(',', $product['size']) : [];

// Collect all product images
$product_images = [];
if (!empty($product['image_path'])) $product_images[] = $product['image_path'];
if (!empty($product['image_path2'])) $product_images[] = $product['image_path2'];
if (!empty($product['image_path3'])) $product_images[] = $product['image_path3'];

// Fetch related products
$related_products = [];
$sql = "SELECT id, name, price, image_path FROM products 
        WHERE id != ? 
        ORDER BY RAND() LIMIT 4";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$related_result = $stmt->get_result();
$related_products = $related_result->fetch_all(MYSQLI_ASSOC);
?>

<div class="product-detail-container">
    <div class="product-images">
        <!-- Main large image display -->
        <div class="main-image">
            <img id="main-product-image" src="<?php echo htmlspecialchars($product_images[0]); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="fade-in">
        </div>
        
        <!-- Thumbnail gallery -->
        <?php if (count($product_images) > 1): ?>
        <div class="thumbnail-gallery">
            <?php foreach ($product_images as $index => $image): ?>
                <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                     onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>', this)">
                    <img src="<?php echo htmlspecialchars($image); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?> - Image <?php echo $index + 1; ?>"
                         class="fade-in">
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="product-info">
        <h1 class="product-title slide-in"><?php echo htmlspecialchars($product['name']); ?></h1>
        
        <div class="info-card fade-in">
            <div class="price-section">
                <div class="info-label">Price:</div>
                <div class="price">₹<?php echo number_format($product['price'], 2); ?></div>
            </div>
            
            <div class="stock-section">
                <div class="info-label">Availability:</div>
                <?php if ($product['stock'] > 0): ?>
                    <div class="stock in-stock pulse">
                        <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?> available)
                    </div>
                <?php else: ?>
                    <div class="stock out-of-stock">
                        <i class="fas fa-times-circle"></i> Out of Stock
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="info-card description-box slide-in">
            <div class="section-header">
                <i class="fas fa-align-left"></i>
                <h3>Description</h3>
            </div>
            <div class="description-content">
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
        </div>
        
        <?php if (!empty($available_sizes)): ?>
        <div class="info-card size-selection fade-in">
            <div class="section-header">
                <i class="fas fa-ruler-combined"></i>
                <h3>Available Sizes</h3>
            </div>
            <div class="size-options">
                <?php foreach ($available_sizes as $size): ?>
                    <?php $size = trim($size); ?>
                    <label class="size-option">
                        <input type="radio" name="size" value="<?php echo htmlspecialchars($size); ?>" 
                               required <?php echo ($size === reset($available_sizes)) ? 'checked' : ''; ?>>
                        <span class="size-label"><?php echo htmlspecialchars($size); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isLoggedIn()): ?>
            <?php if ($product['stock'] > 0): ?>
                <form action="cart.php" method="post" class="add-to-cart-form slide-up">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    
                    <div class="quantity-box">
                        <div class="form-group">
                            <label for="quantity" class="section-header">
                                <i class="fas fa-cart-plus"></i>
                                <span>Quantity:</span>
                            </label>
                            <input type="number" name="quantity" id="quantity" 
                                   min="1" max="<?php echo $product['stock']; ?>" value="1" required>
                        </div>
                        <button type="submit" class="btn add-to-cart-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="info-card out-of-stock-box fade-in">
                    <div class="section-header">
                        <i class="fas fa-bell"></i>
                        <h3>Notification</h3>
                    </div>
                    <p class="out-of-stock-message">This product is currently out of stock.</p>
                    <button class="btn notify-btn">
                        <i class="fas fa-bell"></i> Notify When Available
                    </button>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="info-card login-prompt fade-in">
                <div class="section-header">
                    <i class="fas fa-user-lock"></i>
                    <h3>Login Required</h3>
                </div>
                <p>You need to login to purchase this product</p>
                <a href="login.php" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Login Now
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($related_products)): ?>
    <div class="related-products-section">
        <h2 class="section-title"><i class="fas fa-random"></i> You May Also Like</h2>
        <div class="related-products-grid">
            <?php foreach ($related_products as $index => $related): ?>
                <div class="related-product-card" style="animation-delay: <?php echo ($index * 0.1) + 0.1; ?>s">
                    <div class="product-image-container">
                        <?php if (!empty($related['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($related['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>"
                                 class="product-image">
                        <?php else: ?>
                            <div class="image-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        <div class="product-overlay">
                            <a href="view_details.php?id=<?php echo $related['id']; ?>" class="view-details-btn">
                                <i class="fas fa-eye"></i> Quick View
                            </a>
                        </div>
                    </div>
                    <div class="product-details">
                        <h3><?php echo htmlspecialchars($related['name']); ?></h3>
                        <div class="price">₹<?php echo number_format($related['price'], 2); ?></div>
                        <a href="view_details.php?id=<?php echo $related['id']; ?>" class="details-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<script>
function changeMainImage(imageSrc, clickedElement) {
    // Change the main image with fade effect
    const mainImg = document.getElementById('main-product-image');
    mainImg.style.opacity = 0;
    
    setTimeout(() => {
        mainImg.src = imageSrc;
        mainImg.style.opacity = 1;
    }, 200);
    
    // Update active class on thumbnails
    const thumbnails = document.querySelectorAll('.thumbnail-item');
    thumbnails.forEach(thumb => thumb.classList.remove('active'));
    clickedElement.classList.add('active');
}

// Add animation on scroll
document.addEventListener('DOMContentLoaded', () => {
    const animatedElements = document.querySelectorAll('.fade-in, .slide-in, .slide-up');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, { threshold: 0.1 });
    
    animatedElements.forEach(el => observer.observe(el));
});
</script>

<style>
/* Font Awesome */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

/* Base Styles */
.product-detail-container {
    display: flex;
    gap: 40px;
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 20px;
}

/* Image Section */
.product-images {
    flex: 1;
    max-width: 50%;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.main-image {
    border: 1px solid #eee;
    padding: 20px;
    border-radius: 12px;
    background: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 450px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    overflow: hidden;
}

.main-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: opacity 0.3s ease;
}

.thumbnail-gallery {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    justify-content: center;
}

.thumbnail-item {
    width: 80px;
    height: 80px;
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.thumbnail-item:hover {
    border-color: #4a6bff;
    transform: translateY(-3px);
}

.thumbnail-item.active {
    border-color: #4a6bff;
    border-width: 3px;
    box-shadow: 0 0 0 2px rgba(74, 107, 255, 0.2);
}

.thumbnail-item img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

/* Product Info Section */
.product-info {
    flex: 1;
    max-width: 50%;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.product-title {
    font-size: 32px;
    margin: 0 0 10px;
    color: #2c3e50;
    font-weight: 700;
    opacity: 0;
    transform: translateX(-20px);
    transition: all 0.6s ease;
}

.product-title.animate {
    opacity: 1;
    transform: translateX(0);
}

/* Info Card Styles */
.info-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border: 1px solid #eee;
    opacity: 0;
    transition: all 0.6s ease;
}

.fade-in {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.6s ease;
}

.fade-in.animate {
    opacity: 1;
    transform: translateY(0);
}

.slide-in {
    opacity: 0;
    transform: translateX(20px);
    transition: all 0.6s ease;
}

.slide-in.animate {
    opacity: 1;
    transform: translateX(0);
}

.slide-up {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease;
}

.slide-up.animate {
    opacity: 1;
    transform: translateY(0);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    color: #4a6bff;
}

.section-header i {
    font-size: 20px;
}

.section-header h3 {
    margin: 0;
    font-size: 20px;
    color: #2c3e50;
}

/* Price Section */
.price-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.info-label {
    font-weight: 600;
    color: #7f8c8d;
    font-size: 16px;
}

.price {
    font-size: 28px;
    font-weight: 700;
    color: #e74c3c;
}

/* Stock Section */
.stock-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.stock {
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.in-stock {
    background-color: rgba(46, 204, 113, 0.1);
    color: #2ecc71;
}

.out-of-stock {
    background-color: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.03); }
    100% { transform: scale(1); }
}

/* Description Box */
.description-box {
    line-height: 1.7;
}

.description-content {
    color: #34495e;
    font-size: 16px;
}

.description-content p {
    margin: 0;
}

/* Size Selection */
.size-options {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.size-option {
    position: relative;
}

.size-option input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.size-label {
    display: inline-block;
    padding: 10px 20px;
    border: 1px solid #ddd;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 15px;
    min-width: 40px;
    text-align: center;
    background: #f9f9f9;
}

.size-option input[type="radio"]:checked + .size-label {
    background-color: #4a6bff;
    color: white;
    border-color: #4a6bff;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(74, 107, 255, 0.3);
}

.size-option input[type="radio"]:hover + .size-label {
    border-color: #4a6bff;
    transform: translateY(-2px);
}

/* Quantity Box */
.quantity-box {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    color: #7f8c8d;
}

.form-group input[type="number"] {
    width: 80px;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 16px;
    transition: all 0.3s;
}

.form-group input[type="number"]:focus {
    border-color: #4a6bff;
    box-shadow: 0 0 0 2px rgba(74, 107, 255, 0.2);
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s;
    text-decoration: none;
}

.add-to-cart-btn {
    background-color: #4a6bff;
    color: white;
}

.add-to-cart-btn:hover {
    background-color: #3a5bef;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(74, 107, 255, 0.3);
}

.notify-btn {
    background-color: #f39c12;
    color: white;
    margin-top: 15px;
}

.notify-btn:hover {
    background-color: #e67e22;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
}

/* Out of Stock Box */
.out-of-stock-box {
    text-align: center;
}

.out-of-stock-message {
    color: #e74c3c;
    font-weight: 500;
    margin-bottom: 15px;
}

/* Login Prompt */
.login-prompt {
    text-align: center;
}

.login-prompt p {
    margin-bottom: 15px;
    color: #7f8c8d;
}

/* Related Products Section */
.related-products-section {
    max-width: 1200px;
    margin: 80px auto 40px;
    padding: 0 20px;
}

.section-title {
    font-size: 28px;
    margin-bottom: 30px;
    color: #2c3e50;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    position: relative;
    padding-bottom: 15px;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, #4a6bff, #3a5bef);
    border-radius: 3px;
}

.related-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
}

.related-product-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    position: relative;
    animation: fadeInUp 0.8s ease forwards;
    opacity: 0;
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

.related-product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.12);
}

.product-image-container {
    height: 220px;
    position: relative;
    overflow: hidden;
    background: #f8f9fa;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    padding: 20px;
}

.image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ddd;
    font-size: 50px;
}

.related-product-card:hover .product-image {
    transform: scale(1.1);
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.related-product-card:hover .product-overlay {
    opacity: 1;
}

.view-details-btn {
    padding: 12px 20px;
    background: #4a6bff;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.view-details-btn:hover {
    background: #3a5bef;
    transform: translateY(-2px);
}

.product-details {
    padding: 20px;
    text-align: center;
}

.product-details h3 {
    margin: 0 0 10px;
    font-size: 18px;
    color: #2c3e50;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.price {
    font-size: 20px;
    font-weight: 700;
    color: #e74c3c;
    margin-bottom: 15px;
}

.details-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #f8f9fa;
    color: #4a6bff;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.details-btn:hover {
    background: #4a6bff;
    color: white;
}

/* Responsive Design */
@media (max-width: 992px) {
    .product-detail-container {
        flex-direction: column;
    }
    
    .product-images,
    .product-info {
        max-width: 100%;
    }
    
    .main-image {
        height: 400px;
    }
    
    .related-products-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }
}

@media (max-width: 768px) {
    .main-image {
        height: 350px;
    }
    
    .product-title {
        font-size: 28px;
    }
    
    .price {
        font-size: 24px;
    }
    
    .section-title {
        font-size: 24px;
    }
    
    .related-products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
}

@media (max-width: 576px) {
    .main-image {
        height: 300px;
    }
    
    .product-title {
        font-size: 24px;
    }
    
    .info-card {
        padding: 16px;
    }
    
    .btn {
        padding: 12px 20px;
        font-size: 15px;
    }
    
    .related-products-grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 15px;
    }
    
    .product-image-container {
        height: 180px;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?>