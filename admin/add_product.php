<?php
require_once '../includes/config.php';
requireAdmin();

// Initialize variables
$error = '';
$success = '';
$product = [
    'id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'stock' => '',
    'size' => '',
    'image_path' => '',
    'image_path2' => '',
    'image_path3' => ''
];

// Check if editing existing product
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    if ($product_id <= 0) {
        $error = "Invalid product ID.";
    } else {
        try {
            $sql = "SELECT * FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Database error: ".$conn->error);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $product = $result->fetch_assoc();
            } else {
                $error = "Product not found.";
            }
        } catch (Exception $e) {
            $error = "Error loading product: ".$e->getMessage();
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize inputs
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $size = sanitize($_POST['size'] ?? '');
        $image_path = $product['image_path'] ?? '';
        $image_path2 = $product['image_path2'] ?? '';
        $image_path3 = $product['image_path3'] ?? '';
        
        // Basic validation
        if (empty($name) || strlen($name) > 100) {
            throw new Exception("Product name must be between 1-100 characters.");
        }
        
        if (empty($description)) {
            throw new Exception("Product description is required.");
        }
        
        if ($price <= 0 || $price > 999999.99) {
            throw new Exception("Price must be between 0.01 and 999999.99");
        }
        
        if ($stock < 0 || $stock > 999999) {
            throw new Exception("Stock quantity must be between 0-999999");
        }
        
        if (strlen($size) > 50) {
            throw new Exception("Size must be less than 50 characters.");
        }
        
        // Function to handle image uploads
        function handleImageUpload($file_key, $current_path, $target_dir) {
            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
                // Create directory if it doesn't exist
                if (!file_exists($target_dir)) {
                    if (!mkdir($target_dir, 0755, true)) {
                        throw new Exception("Failed to create image directory.");
                    }
                }
                
                // Validate file
                $file_info = getimagesize($_FILES[$file_key]['tmp_name']);
                if (!$file_info) {
                    throw new Exception("Uploaded file is not an image.");
                }
                
                $file_ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                if (!in_array($file_ext, $allowed_ext)) {
                    throw new Exception("Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.");
                }
                
                if ($_FILES[$file_key]['size'] > $max_size) {
                    throw new Exception("Image size must be less than 2MB.");
                }
                
                // Generate unique filename
                $file_name = uniqid('prod_', true) . '.' . $file_ext;
                $target_file = $target_dir . $file_name;
                
                if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $target_file)) {
                    // Delete old image if it exists
                    if (!empty($current_path) && file_exists("../" . $current_path)) {
                        unlink("../" . $current_path);
                    }
                    return "assets/images/products/" . $file_name;
                } else {
                    throw new Exception("Failed to upload image.");
                }
            }
            return $current_path;
        }
        
        $target_dir = "../assets/images/products/";
        
        // Handle main image upload
        $image_path = handleImageUpload('image', $image_path, $target_dir);
        
        // Handle second image upload
        $image_path2 = handleImageUpload('image2', $image_path2, $target_dir);
        
        // Handle third image upload
        $image_path3 = handleImageUpload('image3', $image_path3, $target_dir);
        
        // Save to database
        if ($product_id > 0) {
            // Update existing product
            $sql = "UPDATE products SET 
                    name = ?, 
                    description = ?, 
                    price = ?, 
                    stock = ?, 
                    size = ?,
                    image_path = ?,
                    image_path2 = ?,
                    image_path3 = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Database error: ".$conn->error);
            $stmt->bind_param("ssdsssssi", $name, $description, $price, $stock, $size, $image_path, $image_path2, $image_path3, $product_id);
        } else {
            // Insert new product
            $sql = "INSERT INTO products (name, description, price, stock, size, image_path, image_path2, image_path3) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Database error: ".$conn->error);
            $stmt->bind_param("ssdsssss", $name, $description, $price, $stock, $size, $image_path, $image_path2, $image_path3);
        }
        
        if ($stmt->execute()) {
            $success = $product_id > 0 ? "Product updated successfully." : "Product added successfully.";
            if ($product_id === 0) {
                $product_id = $conn->insert_id;
                header("Location: add_product.php?id=$product_id&success=1");
                exit();
            }
            
            // Refresh product data
            $sql = "SELECT * FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
        } else {
            throw new Exception("Failed to save product. Database error.");
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        
        // Preserve form data on error
        $product = [
            'id' => $product_id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'size' => $size,
            'image_path' => $image_path,
            'image_path2' => $image_path2,
            'image_path3' => $image_path3
        ];
    }
}

// Display success message from redirect
if (isset($_GET['success'])) {
    $success = "Product added successfully.";
}
?>

<?php include_once '../includes/header.php'; ?>

<main class="container">
    <h2><?php echo $product['id'] ? 'Edit' : 'Add'; ?> Product</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <form action="add_product.php<?php echo $product['id'] ? '?id=' . $product['id'] : ''; ?>" method="post" enctype="multipart/form-data" class="product-form">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
            
            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price (₹) *</label>
                    <input type="number" id="price" name="price" min="0.01" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock Quantity *</label>
                    <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="size">Size</label>
                    <input type="text" id="size" name="size" value="<?php echo htmlspecialchars($product['size']); ?>" maxlength="50">
                    <small>e.g., S, M, L, XL or specific dimensions</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="image">Main Product Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Accepted formats: JPG, PNG, GIF, WEBP (Max 2MB)</small>
                
                <?php if (!empty($product['image_path'])): ?>
                    <div class="current-image">
                        <p>Current Main Image:</p>
                        <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" alt="Product Image">
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="image2">Additional Product Image 1</label>
                <input type="file" id="image2" name="image2" accept="image/*">
                <small>Optional second image</small>
                
                <?php if (!empty($product['image_path2'])): ?>
                    <div class="current-image">
                        <p>Current Additional Image 1:</p>
                        <img src="../<?php echo htmlspecialchars($product['image_path2']); ?>" alt="Product Image 2">
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="image3">Additional Product Image 2</label>
                <input type="file" id="image3" name="image3" accept="image/*">
                <small>Optional third image</small>
                
                <?php if (!empty($product['image_path3'])): ?>
                    <div class="current-image">
                        <p>Current Additional Image 2:</p>
                        <img src="../<?php echo htmlspecialchars($product['image_path3']); ?>" alt="Product Image 3">
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Product</button>
                <a href="products.php" class="btn btn-secondary">Back to products</a>
            </div>
        </form>
    </div>
</main>

<style>
.container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 0 15px;
}

.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 30px;
}

.product-form {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.form-group label {
    font-weight: 600;
    font-size: 18px;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="date"],
.form-group textarea,
.form-group input[type="file"] {
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 6px;
    font-size: 18px;
    width: 100%;
}

#name {
    height: 60px;
    font-size: 20px;
}

#price, #stock, #size {
    height: 50px;
    font-size: 18px;
}

.form-group textarea {
    min-height: 150px;
    resize: vertical;
    font-size: 16px;
    line-height: 1.5;
}

.form-row {
    display: flex;
    gap: 30px;
}

.form-row .form-group {
    flex: 1;
}

.current-image {
    margin-top: 15px;
}

.current-image p {
    margin-bottom: 5px;
    font-weight: 500;
}

.current-image img {
    max-width: 250px;
    max-height: 250px;
    border: 1px solid #eee;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn {
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    font-size: 18px;
    transition: all 0.3s ease;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.alert {
    padding: 15px 20px;
    margin-bottom: 25px;
    border-radius: 6px;
    font-size: 16px;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 20px;
    }
    
    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group input[type="date"] {
        height: 50px;
    }
    
    .btn {
        padding: 10px 15px;
        font-size: 16px;
    }
}
</style>

<?php include_once '../includes/footer.php'; ?>