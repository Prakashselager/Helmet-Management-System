<?php
ob_start();
session_start();

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
    $confirm_password = $_POST['confirm_password'];
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    
    if (empty($username) || empty($password) || empty($email) || empty($full_name)) {
        $error = "ALL FIELDS ARE REQUIRED";
    } elseif ($password !== $confirm_password) {
        $error = "PASSWORD VERIFICATION FAILED";
    } elseif (strlen($password) < 6) {
        $error = "PASSWORD MUST BE AT LEAST 6 CHARACTERS";
    } else {
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "USER ID OR EMAIL ALREADY EXISTS";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (username, password, email, full_name) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $hashed_password, $email, $full_name);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "REGISTRATION COMPLETE. PLEASE LOGIN.";
                header("Location: login.php");
                ob_end_flush();
                exit();
            } else {
                $error = "REGISTRATION FAILED. TRY AGAIN.";
            }
        }
    }
}

require_once 'includes/header.php';
?>

<style>
/* Cybernetic HUD Theme */
:root {
    --hud-primary: #00ff9d;
    --hud-secondary:rgb(216, 222, 225);
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

.register-main-content {
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

.register-container {
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

.register-header {
    text-align: center;
    margin-bottom: 40px;
    position: relative;
}

.register-header::after {
    content: '';
    position: absolute;
    bottom: -20px;
    left: 25%;
    width: 50%;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--hud-primary), transparent);
}

.register-header h2 {
    color: var(--hud-primary);
    font-size: 2rem;
    font-weight: 400;
    letter-spacing: 4px;
    text-transform: uppercase;
    margin-bottom: 10px;
    text-shadow: 0 0 10px var(--hud-primary);
}

.register-header i {
    font-size: 3rem;
    color: var(--hud-secondary);
    margin-bottom: 15px;
    text-shadow: 0 0 10px var(--hud-secondary);
}

.register-alert {
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

.register-form-group {
    margin-bottom: 25px;
    position: relative;
}

.register-form-group label {
    display: block;
    margin-bottom: 10px;
    color: var(--hud-secondary);
    font-size: 0.9rem;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.register-form-group input {
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

.register-form-group input:focus {
    outline: none;
    border-color: var(--hud-primary);
    box-shadow: 0 0 10px var(--hud-primary);
    background: rgba(0, 255, 157, 0.05);
}

.register-btn {
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

.register-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(0, 255, 157, 0.2), transparent);
    transition: 0.5s;
}

.register-btn:hover {
    background: rgba(0, 255, 157, 0.1);
    box-shadow: 0 0 20px var(--hud-primary);
    text-shadow: 0 0 10px var(--hud-primary);
}

.register-btn:hover::before {
    left: 100%;
}

.register-footer-links {
    text-align: center;
    margin-top: 30px;
    color: var(--hud-secondary);
    font-size: 0.9rem;
    letter-spacing: 1px;
}

.register-footer-links a {
    color: var(--hud-primary);
    text-decoration: none;
    transition: all 0.3s ease;
}

.register-footer-links a:hover {
    text-shadow: 0 0 10px var(--hud-primary);
}

.password-strength {
    margin-top: 10px;
    font-size: 0.8rem;
    letter-spacing: 1px;
    color: var(--hud-secondary);
}

.strength-indicator {
    display: flex;
    gap: 5px;
    margin-top: 5px;
}

.strength-bar {
    height: 4px;
    flex-grow: 1;
    background: rgba(255, 42, 109, 0.3);
    transition: all 0.3s ease;
}

.strength-bar.active {
    background: var(--hud-primary);
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
    .register-container {
        padding: 30px 20px;
        margin: 0 15px;
    }
    
    .register-header h2 {
        font-size: 1.5rem;
    }
}
</style>

<div class="register-main-content">
    <!-- HUD Grid Background -->
    <div class="hud-grid"></div>
    
    <div class="register-container">
        <div class="register-header">
            <i class="fas fa-user-plus"></i>
            <h2>NEW USER REGISTRATION</h2>
            <p>CREATE YOUR ACCESS CREDENTIALS</p>
        </div>
        
        <?php if ($error): ?>
            <div class="register-alert">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form action="register.php" method="post" id="registrationForm">
            <div class="register-form-group">
                <label for="username"><i class="fas fa-id-card"></i> USER NAME</label>
                <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required placeholder="ENTER USER NAME">
            </div>
            
            <div class="register-form-group">
                <label for="email"><i class="fas fa-at"></i> EMAIL</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required placeholder="ENTER EMAIL">
            </div>
            
            <div class="register-form-group">
                <label for="full_name"><i class="fas fa-user-tag"></i> FULL NAME</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required placeholder="ENTER FULL NAME">
            </div>
            
            <div class="register-form-group">
                <label for="password"><i class="fas fa-key"></i> ENTER PASSWORD</label>
                <input type="password" id="password" name="password" required placeholder="ENTER PASSWORD">
                <div class="password-strength">
                    <div>SECURITY LEVEL: <span id="strengthText">LOW</span></div>
                    <div class="strength-indicator">
                        <div class="strength-bar" id="bar1"></div>
                        <div class="strength-bar" id="bar2"></div>
                        <div class="strength-bar" id="bar3"></div>
                        <div class="strength-bar" id="bar4"></div>
                    </div>
                </div>
            </div>
            
            <div class="register-form-group">
                <label for="confirm_password"><i class="fas fa-key"></i> RE-ENTER PASSWORD</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="RE-ENTER PASSWORD">
                <div id="passwordMatch" style="display:none;color:var(--hud-accent);font-size:0.8rem;margin-top:5px;"></div>
            </div>
            
            <button type="submit" class="register-btn">
                <i class="fas fa-user-plus"></i> INITIATE REGISTRATION
            </button>
        </form>
        
        <div class="register-footer-links">
            <p>EXISTING USER? <a href="login.php"><i class="fas fa-sign-in-alt"></i> LOGIN HERE</a></p>
        </div>
    </div>
</div>

<script>
// Terminal cursor effect
document.querySelectorAll('input').forEach(input => {
    input.style.caretColor = 'var(--hud-primary)';
    
    input.addEventListener('focus', function() {
        this.style.animation = 'blink 1s step-end infinite';
    });
    
    input.addEventListener('blur', function() {
        this.style.animation = 'none';
    });
});

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthText = document.getElementById('strengthText');
    const bars = [
        document.getElementById('bar1'),
        document.getElementById('bar2'),
        document.getElementById('bar3'),
        document.getElementById('bar4')
    ];
    let strength = 0;
    
    // Reset bars
    bars.forEach(bar => bar.classList.remove('active'));
    
    // Check password length
    if (password.length >= 6) {
        strength++;
        bars[0].classList.add('active');
    }
    if (password.length >= 8) {
        strength++;
        bars[1].classList.add('active');
    }
    
    // Check for mixed case
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
        strength++;
        bars[2].classList.add('active');
    }
    
    // Check for numbers and special chars
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    // Update strength text
    if (strength <= 2) {
        strengthText.textContent = 'LOW';
        strengthText.style.color = 'var(--hud-accent)';
    } else if (strength <= 4) {
        strengthText.textContent = 'MEDIUM';
        strengthText.style.color = 'var(--hud-secondary)';
        bars[3].classList.add('active');
    } else {
        strengthText.textContent = 'HIGH';
        strengthText.style.color = 'var(--hud-primary)';
        bars[3].classList.add('active');
    }
});

// Password match validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const matchDiv = document.getElementById('passwordMatch');
    
    if (confirmPassword && password !== confirmPassword) {
        matchDiv.textContent = 'ACCESS CODE MISMATCH!';
        matchDiv.style.display = 'block';
    } else {
        matchDiv.style.display = 'none';
    }
});

// Add loading state to form submission
document.getElementById('registrationForm').addEventListener('submit', function(e) {
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