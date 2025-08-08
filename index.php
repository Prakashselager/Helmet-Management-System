<?php
require_once '../includes/config.php';
requireAdmin();

// Get counts for dashboard
$products_count = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$orders_count = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$users_count = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$revenue = $conn->query("SELECT SUM(total_amount) FROM orders WHERE status = 'Delivered'")->fetch_row()[0];
$revenue = $revenue ? $revenue : 0;

// Get recent orders
$recent_orders = $conn->query("
    SELECT o.id, u.full_name AS customer, o.created_at AS date, o.total_amount AS amount, o.status
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg: #2c3e50;
            --sidebar-active: #3498db;
            --primary: #3498db;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --dark: #34495e;
            --light: #ecf0f1;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        

        body {
            display: flex;
            min-height: 100vh;
            background-color: #f5f7fa;
            transition: all 0.3s;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            height: 100vh;
            transition: all 0.3s;
            z-index: 100;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-brand {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand i {
            font-size: 24px;
            margin-right: 10px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu ul {
            list-style: none;
        }
        
        .sidebar-menu li {
            position: relative;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu li a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-menu li a i {
            margin-right: 10px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }
        
        .sidebar-menu li.active a {
            color: white;
            background: var(--sidebar-active);
        }
        
        .sidebar-menu li.active a:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: white;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 30px;
            transition: all 0.3s;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: var(--dark);
            font-size: 28px;
        }
        
        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            border-left: 4px solid var(--primary);
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .dashboard-card h3 {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .dashboard-card p {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .dashboard-card i {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 40px;
            opacity: 0.2;
            color: var(--primary);
        }
        
        .card-products {
            border-left-color: var(--success);
        }
        
        .card-products i {
            color: var(--success);
        }
        
        .card-orders {
            border-left-color: var(--warning);
        }
        
        .card-orders i {
            color: var(--warning);
        }
        
        .card-users {
            border-left-color: var(--danger);
        }
        
        .card-users i {
            color: var(--danger);
        }
        
        /* Recent Orders Table */
        .recent-orders {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .recent-orders h2 {
            margin-bottom: 20px;
            color: var(--dark);
        }
        
        .order-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-table th, .order-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .order-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #7f8c8d;
        }
        
        .order-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: var(--primary);
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-view-all {
            margin-top: 20px;
            display: inline-block;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-brand span, 
            .sidebar-menu li a span {
                display: none;
            }
            
            .sidebar-menu li a {
                justify-content: center;
            }
            
            .sidebar-menu li a i {
                margin-right: 0;
                font-size: 20px;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animated {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>
  

    <!-- Sidebar Navigation -->
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-tachometer-alt"></i>
        <span>Admin Panel</span>
    </div>
    
    <div class="sidebar-menu">
        <ul>
            <li class="active">
                <a href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="products.php">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
            </li>
            <li>
                <a href="orders.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>
            <li>
                <a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="sales_report.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Sales Report</span>
                </a>
            </li>
            <li>
                <a href="../index.php">
                    <i class="fas fa-eye"></i>
                    <span>View Site</span>
                </a>
            </li>
            <li>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</div>
    
    <!-- Main Content -->
    <div class="main-content animated">
        <div class="header">
            <h1>Dashboard Overview</h1>
        </div>
        
        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card card-products">
                <h3>Total Products</h3>
                <p><?php echo $products_count; ?></p>
                <i class="fas fa-box"></i>
            </div>
            
            <div class="dashboard-card card-orders">
                <h3>Total Orders</h3>
                <p><?php echo $orders_count; ?></p>
                <i class="fas fa-shopping-cart"></i>
            </div>
            
            <div class="dashboard-card card-users">
                <h3>Total Users</h3>
                <p><?php echo $users_count; ?></p>
                <i class="fas fa-users"></i>
            </div>
            
            <div class="dashboard-card">
                <h3>Revenue</h3>
                <p>₹<?php echo number_format($revenue, 2); ?></p>
                <i class="fas fa-rupee-sign"></i>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="recent-orders">
            <h2>Recent Orders</h2>
            
            <?php if ($recent_orders->num_rows > 0): ?>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer']); ?></td>
                                <td><?php echo date('d M Y', strtotime($order['date'])); ?></td>
                                <td>₹<?php echo number_format($order['amount'], 2); ?></td>
                                <td>
                                    <span class="status status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="../view_order.php?id=<?php echo $order['id']; ?>" class="btn">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <a href="orders.php" class="btn btn-view-all">View All Orders</a>
            <?php else: ?>
                <p>No recent orders found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Add active class to current page
        document.addEventListener('DOMContentLoaded', function() {
            const current = location.pathname.split('/').pop();
            const links = document.querySelectorAll('.sidebar-menu li a');
            
            links.forEach(link => {
                if (link.getAttribute('href') === current) {
                    link.parentElement.classList.add('active');
                }
            });
            
            // Animate cards on hover
            const cards = document.querySelectorAll('.dashboard-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-5px)';
                    card.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = '';
                    card.style.boxShadow = '';
                });
            });
        });
    </script>
</body>
</html>