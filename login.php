<?php
session_start();
require_once 'includes/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password']));

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Disanayaka Ayurveda Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a4731;
            --primary-dark: #0d2b1e;
            --primary-light: #2d6a4f;
            --accent: #52b788;
            --accent-hover: #40916c;
            --gold: #c9a84c;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; font-family: 'Inter', sans-serif; }

        body {
            background: #f0f5f1;
            display: flex;
            align-items: stretch;
            min-height: 100vh;
        }

        /* ---- Layout ---- */
        .login-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ---- Left decorative panel ---- */
        .login-left {
            flex: 1;
            background: linear-gradient(150deg, #1c4d35 0%, #0f2d1e 50%, #081a11 100%);
            padding: 50px 52px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            min-height: 100vh;
        }

        /* Decorative circles */
        .deco-circle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }
        .deco-c1 { width: 340px; height: 340px; top: -100px; right: -80px; background: rgba(82,183,136,0.10); }
        .deco-c2 { width: 200px; height: 200px; bottom: 80px; right: 40px; background: rgba(201,168,76,0.07); }
        .deco-c3 { width: 120px; height: 120px; top: 40%; left: -40px; background: rgba(82,183,136,0.06); }

        /* Decorative leaf SVG watermark */
        .deco-leaf {
            position: absolute;
            opacity: 0.05;
            pointer-events: none;
        }
        .deco-leaf-1 { bottom: -20px; left: -20px; width: 320px; }
        .deco-leaf-2 { top: 30%; right: -10px; width: 160px; transform: rotate(45deg); }

        .panel-top { position: relative; z-index: 2; }

        .panel-logo-wrap {
            width: 56px; height: 56px;
            background: rgba(82,183,136,0.18);
            border: 1.5px solid rgba(82,183,136,0.35);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 30px;
        }

        .panel-title {
            font-size: 2.1rem;
            font-weight: 800;
            color: white;
            line-height: 1.15;
            margin-bottom: 10px;
            letter-spacing: -0.03em;
        }

        .panel-gold-bar {
            width: 44px; height: 3px;
            background: linear-gradient(90deg, var(--gold), #f0d080);
            border-radius: 99px;
            margin-bottom: 18px;
        }

        .panel-desc {
            font-size: 0.92rem;
            color: rgba(255,255,255,0.52);
            line-height: 1.65;
            max-width: 380px;
            margin-bottom: 44px;
        }

        /* Features list */
        .feature-list { list-style: none; position: relative; z-index: 2; }
        .feature-list li {
            display: flex;
            align-items: center;
            gap: 13px;
            color: rgba(255,255,255,0.68);
            font-size: 0.875rem;
            margin-bottom: 13px;
        }
        .feature-list li .fi {
            width: 30px; height: 30px;
            background: rgba(82,183,136,0.14);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: var(--accent);
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .panel-bottom {
            position: relative; z-index: 2;
            font-size: 0.70rem;
            color: rgba(255,255,255,0.25);
            letter-spacing: 0.02em;
        }

        .panel-motto {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.28);
            font-style: italic;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
        }

        /* ---- Right form panel ---- */
        .login-right {
            width: 470px;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 56px 52px;
            position: relative;
        }

        .form-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 36px;
        }

        .form-brand-icon {
            width: 38px; height: 38px;
            background: #d8f3dc;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }

        .form-brand-name {
            font-size: 0.82rem;
            font-weight: 800;
            color: var(--primary);
        }

        .form-brand-sub {
            font-size: 0.68rem;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .form-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: #1a2e1a;
            margin-bottom: 6px;
            letter-spacing: -0.03em;
        }

        .form-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 34px;
            line-height: 1.5;
        }

        /* Error box */
        .error-box {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px;
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            color: #991b1b;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 24px;
            animation: errSlide 0.28s ease;
        }

        @keyframes errSlide {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Input groups */
        .input-group-label {
            display: block;
            font-size: 0.79rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 7px;
        }

        .input-field-wrap {
            position: relative;
            margin-bottom: 20px;
        }

        .input-icon-left {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1rem;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1.5px solid #d1d5db;
            border-radius: 10px;
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            color: #1a2e1a;
            background: #fafafa;
            outline: none;
            transition: all 0.2s;
        }

        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(82,183,136,0.14);
            background: white;
        }

        .form-input::placeholder { color: #c4cac4; }

        .toggle-pwd-btn {
            position: absolute;
            right: 11px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            font-size: 1rem;
            line-height: 1;
        }

        .toggle-pwd-btn:hover { color: var(--accent); }

        /* Submit btn */
        .btn-sign-in {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            border-radius: 11px;
            font-size: 0.935rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            transition: all 0.25s;
            letter-spacing: 0.01em;
            margin-top: 6px;
        }

        .btn-sign-in:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            box-shadow: 0 10px 28px rgba(26,71,49,0.32);
            transform: translateY(-2px);
        }

        .btn-sign-in:active { transform: translateY(0); }

        .login-hint {
            margin-top: 26px;
            text-align: center;
            font-size: 0.77rem;
            color: #9ca3af;
            padding-top: 22px;
            border-top: 1px solid #f3f4f6;
        }

        .login-hint b { color: #6b7280; }

        .secure-note {
            position: absolute;
            bottom: 26px;
            left: 0; right: 0;
            text-align: center;
            font-size: 0.68rem;
            color: #d1d5db;
            letter-spacing: 0.03em;
        }

        /* ---- Responsive ---- */
        @media (max-width: 900px) {
            .login-left { display: none; }
            .login-right { width: 100%; max-width: 460px; margin: auto; min-height: 100vh; }
        }

        @media (max-width: 480px) {
            .login-right { padding: 36px 22px; }
        }
    </style>
</head>
<body>

<div class="login-container">

    <!-- ======== LEFT PANEL ======== -->
    <div class="login-left">
        <!-- Decorative circles -->
        <div class="deco-circle deco-c1"></div>
        <div class="deco-circle deco-c2"></div>
        <div class="deco-circle deco-c3"></div>

        <!-- Decorative leaf watermarks -->
        <svg class="deco-leaf deco-leaf-1" viewBox="0 0 300 300" fill="white">
            <path d="M150,10 C230,10 290,70 290,150 C290,230 230,290 150,290 C70,290 10,230 10,150 C10,70 70,10 150,10Z M150,30 C80,30 30,80 30,150 C30,220 80,270 150,270 C220,270 270,220 270,150 C270,80 220,30 150,30Z"/>
            <line x1="150" y1="10" x2="150" y2="290" stroke="white" stroke-width="6"/>
            <line x1="10"  y1="150" x2="290" y2="150" stroke="white" stroke-width="3"/>
        </svg>
        <svg class="deco-leaf deco-leaf-2" viewBox="0 0 160 200" fill="white">
            <path d="M80,5 C130,5 155,80 80,195 C5,80 30,5 80,5Z"/>
            <line x1="80" y1="5" x2="80" y2="195" stroke="white" stroke-width="4"/>
        </svg>

        <!-- Top content -->
        <div class="panel-top">
            <div class="panel-logo-wrap">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="#52b788">
                    <path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z"/>
                </svg>
            </div>

            <h1 class="panel-title">Disanayaka<br>Ayurveda Hospital</h1>
            <div class="panel-gold-bar"></div>
            <p class="panel-desc">
                A secure, government-grade Ayurveda Inventory Management System — tracking medicinal stock, suppliers, and departmental usage with full transparency.
            </p>

            <ul class="feature-list">
                <li><div class="fi"><i class="bi bi-box-seam"></i></div> Complete herbal inventory tracking</li>
                <li><div class="fi"><i class="bi bi-arrow-left-right"></i></div> Real-time stock in &amp; out monitoring</li>
                <li><div class="fi"><i class="bi bi-exclamation-triangle"></i></div> Automated low stock &amp; expiry alerts</li>
                <li><div class="fi"><i class="bi bi-file-earmark-bar-graph"></i></div> Detailed reports &amp; print-ready analytics</li>
                <li><div class="fi"><i class="bi bi-shield-lock"></i></div> Role-based access (Admin / Staff)</li>
            </ul>
        </div>

        <!-- Bottom -->
        <div class="panel-bottom">
            <div class="panel-motto">ආරෝග්‍ය පරම භාග්‍යං — Health is the greatest fortune</div>
            &copy; <?= date('Y') ?> Disanayaka Ayurveda Hospital &middot; Department of Ayurveda
        </div>
    </div>

    <!-- ======== RIGHT FORM PANEL ======== -->
    <div class="login-right">
        <!-- Mini brand -->
        <div class="form-brand">
            <div class="form-brand-icon">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="#1a4731">
                    <path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z"/>
                </svg>
            </div>
            <div>
                <div class="form-brand-name">Disanayaka Ayurveda</div>
                <div class="form-brand-sub">Inventory System</div>
            </div>
        </div>

        <h2 class="form-title">Welcome back</h2>
        <p class="form-subtitle">Sign in with your credentials to access the inventory management system.</p>

        <?php if($error): ?>
        <div class="error-box">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label class="input-group-label" for="username">Username</label>
            <div class="input-field-wrap">
                <i class="bi bi-person input-icon-left"></i>
                <input
                    type="text"
                    name="username"
                    id="username"
                    class="form-input"
                    placeholder="Enter your username"
                    required
                    autocomplete="username">
            </div>

            <label class="input-group-label" for="password">Password</label>
            <div class="input-field-wrap">
                <i class="bi bi-lock input-icon-left"></i>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-input"
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password">
                <button type="button" class="toggle-pwd-btn" onclick="togglePassword()" title="Show/hide password">
                    <i class="bi bi-eye" id="pwdToggleIcon"></i>
                </button>
            </div>

            <button type="submit" class="btn-sign-in">
                <i class="bi bi-box-arrow-in-right"></i>
                Sign In to System
            </button>
        </form>

        <p class="login-hint">
            Default credentials: <b>admin</b> / <b>1234</b>
        </p>

        <div class="secure-note">
            <i class="bi bi-shield-check" style="color:#52b788; margin-right:4px;"></i>
            Secure government system &mdash; authorised personnel only
        </div>
    </div>

</div>

<script>
function togglePassword() {
    const pwd  = document.getElementById('password');
    const icon = document.getElementById('pwdToggleIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        pwd.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>