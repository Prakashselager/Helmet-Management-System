<?php
require_once 'includes/header.php';
require_once 'includes/config.php';

$name = $email = $subject = $message = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $success = 'Thank you for your message! We will get back to you soon.';
        $name = $email = $subject = $message = '';
    }
}
?>

<style>
    /* Contact Page Styles */
    .contact-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 40px;
        animation: fadeIn 1s ease;
    }
    
    .contact-info, .location-map {
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .contact-info:hover, .location-map:hover {
        transform: translateY(-5px);
    }
    
    .contact-info h3, .location-map h3 {
        color: #2874f0;
        font-size: 1.8rem;
        margin-bottom: 20px;
        border-bottom: 3px solid #FF5733;
        padding-bottom: 10px;
        display: inline-block;
    }
    
    .contact-method {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px;
        background: rgba(255,255,255,0.7);
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .contact-method:hover {
        background: white;
        transform: translateX(10px);
    }
    
    .contact-method i {
        color: #2874f0;
        font-size: 1.5rem;
        min-width: 30px;
        text-align: center;
    }
    
    .contact-method p {
        margin: 0;
        color: #333;
        line-height: 1.6;
    }
    
    .contact-method strong {
        color: #2874f0;
    }
    
    .map-container {
        height: 400px;
        width: 100%;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .map-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    
    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
        animation: slideDown 0.5s ease;
    }
    
    .alert-error {
        background-color: #ffebee;
        color: #c62828;
        border-left: 5px solid #c62828;
    }
    
    .alert-success {
        background-color: #e8f5e9;
        color: #2e7d32;
        border-left: 5px solid #2e7d32;
    }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @media (max-width: 768px) {
        .contact-container {
            grid-template-columns: 1fr;
        }
        
        .map-container {
            height: 300px;
        }
    }
</style>

<h2>Contact Us</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="contact-container">
    <div class="contact-info">
        <h3><i class="fas fa-envelope-open-text"></i> Get in Touch</h3>
        <p>We'd love to hear from you! Here's how you can reach us:</p>
        
        <div class="contact-method">
            <i class="fas fa-map-marker-alt"></i>
            <p><strong>Address:</strong> Onyx Complex, Tahsildar office, S P Road, UB Hills, Narayanpura, Dharwad, Karnataka 580008</p>
        </div>
        
        <div class="contact-method">
            <i class="fas fa-phone"></i>
            <p><strong>Phone:</strong> +91 74111 71871</p>
        </div>
        
        <div class="contact-method">
            <i class="fas fa-envelope"></i>
            <p><strong>Email:</strong> Rohank@gmail.com</p>
        </div>
        
        <div class="contact-method">
            <i class="fas fa-clock"></i>
            <p><strong>Business Hours:</strong><br>
            Monday-Friday: 9:00 AM - 6:00 PM<br>
            Saturday: 10:00 AM - 4:00 PM<br>
            Sunday: Closed</p>
        </div>
    </div>
    
    <div class="location-map">
        <h3><i class="fas fa-map-marked-alt"></i> Our Location</h3>
        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d491406.42408862594!2d75.06599665234377!3d15.798011021165301!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bb8cdfa3f4772bd%3A0x19005dd255297c10!2sBike%20Barber!5e0!3m2!1sen!2sin!4v1749532540584!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <p style="margin-top: 15px; text-align: center;">
            <i class="fas fa-car"></i> Ample parking available | <i class="fas fa-wheelchair"></i> Wheelchair accessible
        </p>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>