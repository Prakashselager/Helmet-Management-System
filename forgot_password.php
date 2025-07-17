<?php
ob_start();
session_start();

require_once 'includes/header.php';
require_once 'includes/config.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    ob_end_flush();
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify passwords match
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if email exists
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Update password in database
            $user = $result->fetch_assoc();
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_sql = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_password, $user['id']);
            
            if ($update_stmt->execute()) {
                $success = "Password successfully updated!";
            } else {
                $error = "Failed to update password. Please try again.";
            }
        } else {
            $error = "No account found with that email address";
        }
    }
}
?>

<style>
/* Cybernetic HUD Theme */
:root {
    --hud-primary: #00ff9d;
    --hud-secondary: rgb(240, 243, 245);
    --hud-accent: #ff2a6d;
    --hud-bg: #0a0a12;
    --hud-border: rgba(0, 255, 157, 0.3);
    --hud-text: #e0e0e0;
    --hud-grid: rgba(0, 161, 255, 0.05);
    --hud-success: #00ff9d;
}

body {
    background-color: var(--hud-bg);
    color: var(--hud-text);
    font-family: 'Courier New', monospace;
    overflow-x: hidden;
}

.reset-main-content {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    position: relative;
    background: 
        radial-gradient(circle at 20% 30%, rgba(0, 255, 157, 0.05) 0%, transparent 30%),
        radial-gradient(circle at 80% 70%, rgba(0, 161, 255, 0.05) 0%, transparent 30%);
}

.hud-grid {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
        linear-gradient(var(--hud-grid) 1px, transparent 1px),
        linear-gradient(90deg, var(--hud-grid) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
    z-index: 0;
}

.reset-container {
    background: rgba(10, 10, 18, 0.8);
    border: 1px solid var(--hud-border);
    border-radius: 0;
    box-shadow: 
        0 0 20px rgba(0, 255, 157, 0.1),
        inset 0 0 10px rgba(0, 161, 255, 0.1);
    padding: 40px;
    width: 100%;
    max-width: 500px;
    position: relative;
    z-index: 1;
    backdrop-filter: blur(5px);
    border-image: linear-gradient(45deg, var(--hud-primary), var(--hud-secondary)) 1;
    animation: hudPulse 8s infinite alternate;
}

.reset-header {
    text-align: center;
    margin-bottom: 40px;
    position: relative;
}

.reset-header::after {
    content: '';
    position: absolute;
    bottom: -20px;
    left: 25%;
    width: 50%;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--hud-primary), transparent);
}

.reset-header h2 {
    color: var(--hud-primary);
    font-size: 2rem;
    font-weight: 400;
    letter-spacing: 4px;
    text-transform: uppercase;
    margin-bottom: 10px;
    text-shadow: 0 0 10px var(--hud-primary);
}

.reset-header p {
    color: var(--hud-secondary);
    font-size: 0.9rem;
    letter-spacing: 2px;
}

.reset-alert {
    padding: 15px;
    border-left: 3px solid var(--hud-accent);
    background: rgba(255, 42, 109, 0.1);
    margin-bottom: 30px;
    color: var(--hud-accent);
    font-size: 0.9rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: alertPulse 2s infinite;
}

.reset-success {
    padding: 15px;
    border-left: 3px solid var(--hud-success);
    background: rgba(0, 255, 157, 0.1);
    margin-bottom: 30px;
    color: var(--hud-success);
    font-size: 0.9rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 10px;
}

.reset-form-group {
    margin-bottom: 25px;
    position: relative;
}

.reset-form-group label {
    display: block;
    margin-bottom: 10px;
    color: var(--hud-secondary);
    font-size: 0.9rem;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.reset-form-group input {
    width: 100%;
    padding: 15px;
    background: rgba(0, 161, 255, 0.05);
    border: 1px solid var(--hud-border);
    border-radius: 0;
    color: var(--hud-text);
    font-family: 'Courier New', monospace;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.reset-form-group input:focus {
    outline: none;
    border-color: var(--hud-primary);
    box-shadow: 0 0 10px var(--hud-primary);
    background: rgba(0, 255, 157, 0.05);
}

.reset-btn {
    width: 100%;
    padding: 15px;
    background: transparent;
    color: var(--hud-primary);
    border: 1px solid var(--hud-primary);
    border-radius: 0;
    font-size: 1rem;
    font-weight: 400;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    margin-top: 20px;
}

.reset-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(0, 255, 157, 0.2), transparent);
    transition: 0.5s;
}

.reset-btn:hover {
    background: rgba(0, 255, 157, 0.1);
    box-shadow: 0 0 20px var(--hud-primary);
    text-shadow: 0 0 10px var(--hud-primary);
}

.reset-btn:hover::before {
    left: 100%;
}

.reset-footer-links {
    text-align: center;
    margin-top: 30px;
    color: var(--hud-secondary);
    font-size: 0.9rem;
    letter-spacing: 1px;
}

.reset-footer-links a {
    color: var(--hud-primary);
    text-decoration: none;
    transition: all 0.3s ease;
}

.reset-footer-links a:hover {
    text-shadow: 0 0 10px var(--hud-primary);
}

/* Password strength meter */
.password-strength {
    height: 5px;
    background: #333;
    margin-top: 10px;
    position: relative;
    overflow: hidden;
}

.password-strength::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 0;
    background: #ff2a6d;
    transition: width 0.3s ease, background 0.3s ease;
}

.password-strength.weak::before {
    width: 30%;
    background: #ff2a6d;
}

.password-strength.medium::before {
    width: 60%;
    background: #ffcc00;
}

.password-strength.strong::before {
    width: 100%;
    background: #00ff9d;
}

/* Animations */
@keyframes hudPulse {
    0%, 100% {
        box-shadow: 
            0 0 20px rgba(0, 255, 157, 0.1),
            inset 0 0 10px rgba(0, 161, 255, 0.1);
    }
    50% {
        box-shadow: 
            0 0 30px rgba(0, 255, 157, 0.2),
            inset 0 0 15px rgba(0, 161, 255, 0.2);
    }
}

@keyframes alertPulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.5s ease forwards;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .reset-container {
        padding: 30px 20px;
        margin: 0 15px;
    }
    
    .reset-header h2 {
        font-size: 1.5rem;
    }
}
</style>

<div class="reset-main-content">
    <!-- HUD Grid Background -->
    <div class="hud-grid"></div>
    
    <div class="reset-container">
        <div class="reset-header">
            <h2>PASSWORD RECOVERY</h2>
            <p>ENTER YOUR EMAIL AND NEW PASSWORD</p>
        </div>
        
        <?php if ($error): ?>
            <div class="reset-alert fade-in">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="reset-success fade-in">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
            <div class="fade-in" style="text-align: center; margin-top: 30px;">
                <a href="login.php" class="reset-btn" style="display: inline-block; width: auto; padding: 10px 30px;">
                    RETURN TO LOGIN
                </a>
            </div>
        <?php else: ?>
            <form action="forgot_password.php" method="post" class="fade-in">
                <div class="reset-form-group">
                    <label for="email">REGISTERED EMAIL</label>
                    <input type="email" id="email" name="email" required placeholder="ENTER YOUR REGISTERED EMAIL">
                </div>
                
                <div class="reset-form-group">
                    <label for="new_password">NEW PASSWORD</label>
                    <input type="password" id="new_password" name="new_password" required placeholder="ENTER NEW PASSWORD" oninput="checkPasswordStrength(this.value)">
                    <div id="password-strength" class="password-strength"></div>
                </div>
                
                <div class="reset-form-group">
                    <label for="confirm_password">CONFIRM PASSWORD</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="RE-ENTER NEW PASSWORD">
                </div>
                
                <button type="submit" class="reset-btn">
                    RESET PASSWORD
                </button>
            </form>
        <?php endif; ?>
        
        <div class="reset-footer-links">
            <p>REMEMBERED YOUR PASSWORD? <a href="login.php">LOGIN</a></p>
        </div>
    </div>
</div>

<script>
// Password strength indicator
function checkPasswordStrength(password) {
    const strengthBar = document.getElementById('password-strength');
    let strength = 0;
    
    // Length check
    if (password.length >= 8) strength += 1;
    if (password.length >= 12) strength += 1;
    
    // Character type checks
    if (password.match(/[a-z]/)) strength += 1;
    if (password.match(/[A-Z]/)) strength += 1;
    if (password.match(/[0-9]/)) strength += 1;
    if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
    
    // Update strength meter
    strengthBar.className = 'password-strength';
    if (password.length > 0) {
        if (strength <= 2) {
            strengthBar.classList.add('weak');
        } else if (strength <= 4) {
            strengthBar.classList.add('medium');
        } else {
            strengthBar.classList.add('strong');
        }
    }
}

// Add terminal cursor effect to inputs
document.querySelectorAll('input').forEach(input => {
    input.style.caretColor = 'var(--hud-primary)';
    
    input.addEventListener('focus', function() {
        this.style.animation = 'blink 1s step-end infinite';
    });
    
    input.addEventListener('blur', function() {
        this.style.animation = 'none';
    });
});

// Add loading state to form submission
document.querySelector('form')?.addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    if (btn) {
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> PROCESSING...';
        btn.disabled = true;
    }
});

// Add styles for blinking cursor
const style = document.createElement('style');
style.textContent = `
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0; }
    }
`;
document.head.appendChild(style);
</script>

<?php
require_once 'includes/footer.php';
ob_end_flush();
?>