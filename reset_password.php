<?php
session_start();
require_once 'func.php';
$con = db_connect();

if (isset($_POST['reset_password'])) {
    $entered_otp = $_POST['otp'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords match
    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.'); window.location.href = 'reset_password.php';</script>";
        exit;
    }
    
    // Validate password strength
    if (strlen($new_password) < 8) {
        echo "<script>alert('Password must be at least 8 characters long.'); window.location.href = 'reset_password.php';</script>";
        exit;
    }
    
    $email = $_SESSION['reset_email'];
    
    // Check if OTP is valid and not expired
    $query = "SELECT * FROM customer_recovery WHERE email='$email' AND reset_token='$entered_otp' AND token_expiry > NOW()";
    $result = mysqli_query($con, $query);
    
    if (mysqli_num_rows($result) == 1) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $update_query = "UPDATE customer_recovery SET password='$hashed_password', 
                         reset_token=NULL, token_expiry=NULL WHERE email='$email'";
        $update_result = mysqli_query($con, $update_query);
        
        if ($update_result) {
            unset($_SESSION['otp']);
            unset($_SESSION['reset_email']);
            
            echo "<script>alert('Password successfully reset. Please login.');
            window.location.href = 'index1.php';</script>";
        } else {
            echo "<script>alert('Database update failed. Please try again.'); window.location.href = 'reset_password.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid or expired OTP. Try again.'); window.location.href = 'reset_password.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #3931af, #00c6ff);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            width: 400px;
        }
    </style>
</head>
<body>
    <div class="card p-4">
        <h3 class="text-center mb-4">Reset Password</h3>
        <form method="POST">
            <div class="mb-3">
                <label for="otp" class="form-label">Enter OTP</label>
                <input type="text" class="form-control" name="otp" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" name="confirm_password" required>
            </div>
            <div class="d-grid">
                <button type="submit" name="reset_password" class="btn btn-primary">Reset Password</button>
            </div>
        </form>
    </div>
</body>
</html>