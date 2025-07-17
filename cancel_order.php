<?php
// Start session and include config FIRST
require_once 'includes/config.php';
requireLogin();

// All header redirects must come BEFORE including header.php
if (!isset($_GET['id'])) {
    header("Location: profile.php");
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Verify the order belongs to the user
$sql = "SELECT id, status FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['error'] = "Order not found or you don't have permission to cancel it.";
    header("Location: profile.php");
    exit();
}

$order = $result->fetch_assoc();

// Check if order can be cancelled
if ($order['status'] !== 'Pending' && $order['status'] !== 'Processing') {
    $_SESSION['error'] = "This order cannot be cancelled as it's already ".$order['status'];
    header("Location: profile.php");
    exit();
}

// Process the cancellation
try {
    $conn->begin_transaction();
    
    // Update order status
    $sql = "UPDATE orders SET status = 'Cancelled' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Restore product stock
    $sql = "SELECT oi.product_id, oi.quantity, p.stock 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result();

    while ($item = $items->fetch_assoc()) {
        $new_stock = $item['stock'] + $item['quantity'];
        $sql = "UPDATE products SET stock = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $new_stock, $item['product_id']);
        $stmt->execute();
    }

    $conn->commit();
    $_SESSION['success'] = "Order #$order_id has been cancelled successfully.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Failed to cancel order. Please try again.";
}

// Final redirect
header("Location: profile.php");
exit();

// Only include header.php if you're NOT redirecting
// require_once 'includes/header.php';