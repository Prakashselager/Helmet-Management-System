<?php
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/lakesh/';
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helmet World</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Navigation Bar Styles Only */
        header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 10px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        .logo img {
            height: 80px;
            transition: transform 0.3s ease;
        }
        
        
        
        .logo h1 {
            color: white;
            font-size: 1.8rem;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 15px;
        }
        
        nav li {
            position: relative;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        nav a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-3px);
        }
        
        nav a.active {
            font-weight: bold;
            text-decoration: underline;
            text-underline-offset: 5px;
            background: rgba(255,255,255,0.1);
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
            }
            
            nav ul {
                flex-wrap: wrap;
                justify-content: center;
                margin-top: 15px;
                gap: 10px;
            }
            
            nav a {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="<?php echo $base_url; ?>assets/images/logo1.jpg" alt="Helmet World Logo">
                <h1>Helmet World</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="<?php echo $base_url; ?>index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Home
                    </a></li>
                    <li><a href="<?php echo $base_url; ?>products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                        <i class="fas fa-helmet-safety"></i> Products
                    </a></li>
                    <li><a href="<?php echo $base_url; ?>about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">
                        <i class="fas fa-info-circle"></i> About
                    </a></li>
                    <li><a href="<?php echo $base_url; ?>contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i> Contact
                    </a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?php echo $base_url; ?>profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i> Profile
                        </a></li>
                        <li><a href="<?php echo $base_url; ?>cart.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-cart"></i> Cart
                        </a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="<?php echo $base_url; ?>admin/" class="<?php echo strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? 'active' : ''; ?>">
                                <i class="fas fa-lock"></i> Admin
                            </a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo $base_url; ?>logout.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'logout.php' ? 'active' : ''; ?>">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $base_url; ?>login.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a></li>
                        <li><a href="<?php echo $base_url; ?>register.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user-plus"></i> Register
                        </a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">