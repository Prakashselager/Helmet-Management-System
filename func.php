<?php
// Database connection function
function db_connect() {
    $con = mysqli_connect("localhost", "root", "", "a1helmet_world");
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $con;
}

// Email sending function (placeholder)
function send_email($to, $subject, $message) {
    // In production, implement actual email sending here
    error_log("Email to: $to, Subject: $subject, Message: $message");
    return true;
}

// Generate random OTP
function generate_otp() {
    return rand(100000, 999999);
}
?>