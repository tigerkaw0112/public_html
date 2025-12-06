<?php
// teacher/login.php
session_start();
require_once('../db_connect.php');

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ตรวจสอบรหัสผ่าน:
    // **คำเตือน:** นี่คือการตรวจสอบรหัสผ่านแบบ Plain Text ซึ่งไม่ปลอดภัยอย่างยิ่งในระบบจริง
    // ควรกลับไปใช้ password_verify($password, $user['password']) และเก็บรหัสผ่านแบบ Hashed
    // เมื่อคุณทดสอบระบบเสร็จสิ้นแล้ว
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit();
    } else {
        $message = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบสำหรับอาจารย์</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
            color: #f5f5f5;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255, 107, 53, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 140, 66, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 107, 53, 0.08) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(-30px, -30px) rotate(1deg); }
            66% { transform: translate(30px, -20px) rotate(-1deg); }
        }

        /* Floating particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 107, 53, 0.6);
            border-radius: 50%;
            animation: particleFloat 15s infinite linear;
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-10vh) translateX(100px);
                opacity: 0;
            }
        }

        .container {
            background: linear-gradient(145deg, #2d2d2d 0%, #404040 100%);
            padding: 50px 40px;
            border-radius: 25px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.5),
                0 0 40px rgba(255, 107, 53, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            max-width: 450px;
            width: 90%;
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(255, 107, 53, 0.2);
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.05), transparent);
            animation: shimmer 6s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* Enhanced Header */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 2;
        }

        .logo-container {
            margin-bottom: 25px;
            position: relative;
        }

        .logo-icon {
            font-size: 4em;
            color: #ff6b35;
            margin-bottom: 15px;
            display: inline-block;
            animation: logoGlow 3s ease-in-out infinite;
            filter: drop-shadow(0 4px 15px rgba(255, 107, 53, 0.4));
        }

        @keyframes logoGlow {
            0%, 100% { 
                transform: scale(1);
                filter: drop-shadow(0 4px 15px rgba(255, 107, 53, 0.4));
            }
            50% { 
                transform: scale(1.05);
                filter: drop-shadow(0 6px 20px rgba(255, 107, 53, 0.6));
            }
        }

        .page-title {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .page-subtitle {
            font-size: 1.1em;
            color: #ccc;
            font-weight: 400;
            opacity: 0.9;
        }

        /* Error Message */
        .error-message {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.9) 0%, rgba(220, 53, 69, 0.7) 100%);
            color: white;
            border: 2px solid #dc3545;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1em;
            box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3);
            animation: errorSlideIn 0.5s ease;
        }

        @keyframes errorSlideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .error-message i {
            font-size: 1.2em;
            animation: errorShake 0.5s ease;
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-3px); }
            75% { transform: translateX(3px); }
        }

        /* Form Styling */
        .login-form {
            position: relative;
            z-index: 2;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-container {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #ff8c42;
            font-size: 1.1em;
            z-index: 3;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 18px 18px 18px 55px;
            background: linear-gradient(145deg, #404040 0%, #2d2d2d 100%);
            border: 2px solid #555;
            border-radius: 12px;
            color: #f5f5f5;
            font-size: 1.1em;
            font-family: inherit;
            font-weight: 400;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #ff6b35;
            outline: none;
            box-shadow: 
                0 0 0 3px rgba(255, 107, 53, 0.3),
                inset 0 2px 8px rgba(0, 0, 0, 0.2);
            background: linear-gradient(145deg, #505050 0%, #3a3a3a 100%);
        }

        input[type="text"]:focus + .input-icon,
        input[type="password"]:focus + .input-icon {
            color: #ff6b35;
            transform: translateY(-50%) scale(1.1);
        }

        /* Floating Labels */
        .floating-label {
            position: absolute;
            top: 18px;
            left: 55px;
            color: #999;
            font-size: 1.1em;
            font-weight: 400;
            pointer-events: none;
            transition: all 0.3s ease;
            z-index: 2;
        }

        input:focus + .input-icon + .floating-label,
        input:not(:placeholder-shown) + .input-icon + .floating-label {
            top: -10px;
            left: 15px;
            font-size: 0.85em;
            color: #ff8c42;
            background: linear-gradient(135deg, #2d2d2d 0%, #404040 100%);
            padding: 0 8px;
            border-radius: 4px;
        }

        /* Enhanced Button */
        .btn {
            width: 100%;
            padding: 18px 25px;
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.2em;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 
                0 8px 25px rgba(255, 107, 53, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            margin-top: 15px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 15px 35px rgba(255, 107, 53, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn i {
            margin-right: 10px;
            font-size: 1.1em;
        }

        /* Loading State */
        .btn.loading {
            opacity: 0.8;
            cursor: not-allowed;
            transform: none;
        }

        .btn.loading::before {
            display: none;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #555;
            position: relative;
            z-index: 2;
        }

        .footer-text {
            color: #999;
            font-size: 0.9em;
            margin-bottom: 15px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .footer-link {
            color: #ff8c42;
            text-decoration: none;
            font-size: 0.9em;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 5px 10px;
            border-radius: 6px;
        }

        .footer-link:hover {
            color: #ff6b35;
            background: rgba(255, 107, 53, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 20px;
            }
            
            .container {
                padding: 40px 30px;
                border-radius: 20px;
            }
            
            .logo-icon {
                font-size: 3em;
            }
            
            .page-title {
                font-size: 1.7em;
            }
            
            .page-subtitle {
                font-size: 1em;
            }
            
            input[type="text"],
            input[type="password"] {
                padding: 16px 16px 16px 50px;
                font-size: 1em;
            }
            
            .btn {
                padding: 16px 20px;
                font-size: 1.1em;
            }
            
            .floating-label {
                font-size: 1em;
                left: 50px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
                max-width: 100%;
            }
            
            .page-title {
                font-size: 1.5em;
            }
            
            .logo-icon {
                font-size: 2.5em;
            }
        }

        /* Show password toggle */
        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
            font-size: 1.1em;
            transition: all 0.3s ease;
            z-index: 3;
        }

        .password-toggle:hover {
            color: #ff8c42;
        }
    </style>
</head>
<body>
    <!-- Floating particles -->
    <div class="particles"></div>

    <div class="container">
        <div class="page-header">
            <div class="logo-container">
                <i class="fas fa-chalkboard-teacher logo-icon"></i>
            </div>
            <h1 class="page-title">ระบบอาจารย์</h1>
            <p class="page-subtitle">เข้าสู่ระบบเพื่อจัดการห้องเรียน</p>
        </div>

        <?php if ($message): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST" class="login-form" id="loginForm">
            <div class="form-group">
                <div class="input-container">
                    <input type="text" id="username" name="username" placeholder=" " required>
                    <i class="fas fa-user input-icon"></i>
                    <label class="floating-label">ชื่อผู้ใช้</label>
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-container">
                    <input type="password" id="password" name="password" placeholder=" " required>
                    <i class="fas fa-lock input-icon"></i>
                    <label class="floating-label">รหัสผ่าน</label>
                    <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
                </div>
            </div>
            
            <button type="submit" class="btn" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i>เข้าสู่ระบบ
            </button>
        </form>

        <div class="login-footer">
            <p class="footer-text">ระบบจองที่นั่งห้องเรียน</p>
            <div class="footer-links">
                <a href="../" class="footer-link">
                    <i class="fas fa-home"></i> หน้าหลัก
                </a>
                <a href="#" class="footer-link">
                    <i class="fas fa-question-circle"></i> ช่วยเหลือ
                </a>
            </div>
        </div>
    </div>

    <script>
        // Create floating particles
        function createParticles() {
            const particles = document.querySelector('.particles');
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                particles.appendChild(particle);
            }
        }

        // Password toggle functionality
        document.getElementById('passwordToggle').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const icon = this;
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>กำลังเข้าสู่ระบบ...';
            btn.disabled = true;
        });

        // Enhanced input focus effects
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Initialize particles on load
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            // Add entrance animation
            document.querySelector('.container').style.opacity = '0';
            document.querySelector('.container').style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                document.querySelector('.container').style.transition = 'all 0.8s ease';
                document.querySelector('.container').style.opacity = '1';
                document.querySelector('.container').style.transform = 'translateY(0)';
            }, 100);
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt + L to focus username
            if (e.altKey && e.key === 'l') {
                e.preventDefault();
                document.getElementById('username').focus();
            }
            
            // Escape to clear form
            if (e.key === 'Escape') {
                document.getElementById('loginForm').reset();
                document.querySelector('input').blur();
            }
        });

        // Auto-clear error message
        <?php if ($message): ?>
        setTimeout(() => {
            const errorMsg = document.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.style.animation = 'errorSlideIn 0.5s ease reverse';
                setTimeout(() => {
                    errorMsg.style.display = 'none';
                }, 500);
            }
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>