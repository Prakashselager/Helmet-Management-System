<?php
ob_start();
session_start();

require_once 'includes/header.php';
require_once 'includes/config.php';

if (isLoggedIn()) {
    header("Location: index.php");
    ob_end_flush();
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $captcha = $_POST['captcha'];
    
    if ($captcha !== $_SESSION['captcha']) {
        $error = "SECURITY VERIFICATION FAILED";
    } else {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                header("Location: index.php");
                ob_end_flush();
                exit();
            } else {
                $error = "ACCESS DENIED: INVALID CREDENTIALS";
            }
        } else {
            $error = "ACCESS DENIED: INVALID CREDENTIALS";
        }
    }
}

$captcha_code = strtoupper(substr(md5(rand()), 0, 6));
$_SESSION['captcha'] = $captcha_code;
?>

<style>
/* Cybernetic HUD Theme */
:root {
    --hud-primary: #00ff9d;
    --hud-secondary:rgb(240, 243, 245);
    --hud-accent: #ff2a6d;
    --hud-bg: #0a0a12;
    --hud-border: rgba(0, 255, 157, 0.3);
    --hud-text: #e0e0e0;
    --hud-grid: rgba(0, 161, 255, 0.05);
}

body {
    background-color: var(--hud-bg);
    color: var(--hud-text);
    font-family: 'Courier New', monospace;
    overflow-x: hidden;
}

.login-main-content {
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

.login-container {
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

.login-header {
    text-align: center;
    margin-bottom: 40px;
    position: relative;
}

.login-header::after {
    content: '';
    position: absolute;
    bottom: -20px;
    left: 25%;
    width: 50%;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--hud-primary), transparent);
}

.login-header h2 {
    color: var(--hud-primary);
    font-size: 2rem;
    font-weight: 400;
    letter-spacing: 4px;
    text-transform: uppercase;
    margin-bottom: 10px;
    text-shadow: 0 0 10px var(--hud-primary);
}

.login-header p {
    color: var(--hud-secondary);
    font-size: 0.9rem;
    letter-spacing: 2px;
}

.login-alert {
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

.login-form-group {
    margin-bottom: 25px;
    position: relative;
}

.login-form-group label {
    display: block;
    margin-bottom: 10px;
    color: var(--hud-secondary);
    font-size: 0.9rem;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.login-form-group input {
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

.login-form-group input:focus {
    outline: none;
    border-color: var(--hud-primary);
    box-shadow: 0 0 10px var(--hud-primary);
    background: rgba(0, 255, 157, 0.05);
}

.login-btn {
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

.login-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(0, 255, 157, 0.2), transparent);
    transition: 0.5s;
}

.login-btn:hover {
    background: rgba(0, 255, 157, 0.1);
    box-shadow: 0 0 20px var(--hud-primary);
    text-shadow: 0 0 10px var(--hud-primary);
}

.login-btn:hover::before {
    left: 100%;
}

.login-footer-links {
    text-align: center;
    margin-top: 30px;
    color: var(--hud-secondary);
    font-size: 0.9rem;
    letter-spacing: 1px;
}

.login-footer-links a {
    color: var(--hud-primary);
    text-decoration: none;
    transition: all 0.3s ease;
}

.login-footer-links a:hover {
    text-shadow: 0 0 10px var(--hud-primary);
}

.forgot-password {
    text-align: right;
    margin-bottom: 20px;
}

.forgot-password a {
    color: var(--hud-secondary);
    text-decoration: none;
    font-size: 0.8rem;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.forgot-password a:hover {
    color: var(--hud-primary);
}

.captcha-display {
    font-family: 'Courier New', monospace;
    letter-spacing: 5px;
    color: var(--hud-primary);
    background: rgba(0, 255, 157, 0.1);
    padding: 10px 15px;
    display: inline-block;
    margin-bottom: 10px;
    font-weight: 700;
    font-size: 1.2rem;
    border: 1px solid var(--hud-primary);
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .login-container {
        padding: 30px 20px;
        margin: 0 15px;
    }
    
    .login-header h2 {
        font-size: 1.5rem;
    }
}
</style>

<div class="login-main-content">
    <!-- HUD Grid Background -->
    <div class="hud-grid"></div>
    
    <div class="login-container">
        <div class="login-header">
            <h2>LOGIN</h2>
            <p>IDENTIFICATION REQUIRED</p>
        </div>
        
        <?php if ($error): ?>
            <div class="login-alert">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="post">
            <div class="login-form-group">
                <label for="username">USER NAME</label>
                <input type="text" id="username" name="username" required placeholder="ENTER USER NAME">
            </div>
            
            <div class="login-form-group">
                <label for="password">PASSWORD</label>
                <input type="password" id="password" name="password" required placeholder="ENTER YOUR PASSWORD">
            </div>
            
            <div class="forgot-password">
                <a href="forgot_password.php">FORGOT PASSWORD</a>
            </div>
            
            <div class="login-form-group">
                <label>SECURITY VERIFICATION</label>
                <div class="captcha-display"><?php echo $captcha_code; ?></div>
                <input type="text" id="captcha" name="captcha" required placeholder="ENTER VERIFICATION CODE">
            </div>
            
            <button type="submit" class="login-btn">
                INITIATE LOGIN SEQUENCE
            </button>
        </form>
        
        <div class="login-footer-links">
            <p>NEW USER? <a href="register.php">REGISTER</a></p>
        </div>
    </div>
</div>

<script>
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
document.querySelector('form').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    if (btn) {
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> AUTHENTICATING...';
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