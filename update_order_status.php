<?php
require_once 'includes/config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $status = sanitize($_POST['status']);
    
    // Validate inputs
    if ($order_id <= 0) {
        $_SESSION['error'] = "Invalid order ID";
        header("Location: orders.php");
        exit();
    }

    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        $_SESSION['error'] = "Invalid status selected";
        // Use one of these formats:
header("Location: /lakesh/view_order.php?id=".$order_id);  // Absolute path
        exit();
    }

    // Update order status
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: view_order.php?id=$order_id");
        exit();
    }

    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        // Log this status change
        $admin_id = $_SESSION['user_id'];
        $log_sql = "INSERT INTO order_logs (order_id, admin_id, action, details) 
                   VALUES (?, ?, 'status_update', ?)";
        $log_stmt = $conn->prepare($log_sql);
        if ($log_stmt) {
            $action_details = "Status changed to: $status";
            $log_stmt->bind_param("iis", $order_id, $admin_id, $action_details);
            $log_stmt->execute();
        }
        
        $_SESSION['success'] = "Order #$order_id status updated to $status successfully.";
    } else {
        $_SESSION['error'] = "Failed to update order status: " . $stmt->error;
    }

    header("Location: view_order.php?id=$order_id");
    exit();
} else {
    // If not a POST request, redirect to orders page
    header("Location: orders.php");
    exit();
}
?>