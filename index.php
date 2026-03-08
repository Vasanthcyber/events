
Copy

<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$error       = '';
$success     = '';
$open_tab    = 'splash';
$open_subtab = 'signin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['login'])) {
        $open_tab = 'login'; $open_subtab = 'signin';
        $email    = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->bind_param("s", $email); $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
<<<<<<< HEAD
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['user_email'] = $user['email'];
                
=======
                $_SESSION['user_id'] = $user['id']; $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type']; $_SESSION['user_email'] = $user['email'];
>>>>>>> 7c77f6d (Updated project files)
                switch ($user['user_type']) {
                    case 'admin':  redirect('admin/dashboard.php');  break;
                    case 'vendor': redirect('vendor/dashboard.php'); break;
                    default:       redirect('user/dashboard.php');   break;
                }
<<<<<<< HEAD
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
=======
            } else { $error = 'Invalid email or password.'; }
        } else { $error = 'Invalid email or password.'; }
>>>>>>> 7c77f6d (Updated project files)
    }

    if (isset($_POST['register'])) {
        $open_tab = 'login'; $open_subtab = 'join';
        $name = sanitize_input($_POST['name']); $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
<<<<<<< HEAD
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered.';
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, user_type) VALUES (?, ?, ?, ?, 'user')");
            $stmt->bind_param("ssss", $name, $email, $phone, $password);
            
            if ($stmt->execute()) {
                $success = 'Welcome aboard. Please sign in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
=======
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param("s", $email); $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'Email already registered.';
        } else {
            $ins = $conn->prepare("INSERT INTO users (name, email, phone, password, user_type) VALUES (?, ?, ?, ?, 'user')");
            $ins->bind_param("ssss", $name, $email, $phone, $password);
            if ($ins->execute()) { $success = 'Welcome aboard. Please sign in.'; $open_subtab = 'signin'; }
            else                 { $error   = 'Registration failed. Please try again.'; }
        }
    }

    if (isset($_POST['vendor_login'])) {
        $open_tab = 'vendor'; $open_subtab = 'vlogin';
        $email = sanitize_input($_POST['vendor_email']); $password = $_POST['vendor_password'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'vendor' AND status = 'active'");
        $stmt->bind_param("s", $email); $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id']; $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type']; $_SESSION['user_email'] = $user['email'];
                redirect('vendor/dashboard.php');
            } else { $error = 'Invalid email or password.'; }
        } else { $error = 'No active vendor account found.'; }
    }

    if (isset($_POST['vendor_apply'])) {
        $open_tab = 'vendor'; $open_subtab = 'vapply';
        $name = sanitize_input($_POST['vname']); $email = sanitize_input($_POST['vemail']);
        $phone = sanitize_input($_POST['vphone']); $biz = sanitize_input($_POST['business_name']);
        $svc_type = sanitize_input($_POST['service_type']);
        $password = password_hash($_POST['vpassword'], PASSWORD_DEFAULT);
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param("s", $email); $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'An account with that email already exists.';
        } else {
            $ins = $conn->prepare("INSERT INTO users (name, email, phone, password, user_type) VALUES (?, ?, ?, ?, 'vendor')");
            $ins->bind_param("ssss", $name, $email, $phone, $password);
            if ($ins->execute()) {
                $uid = $conn->insert_id;
                $vins = $conn->prepare("INSERT INTO vendors (user_id, business_name, service_type, email, contact_number, status) VALUES (?, ?, ?, ?, ?, 'inactive')");
                $vins->bind_param("issss", $uid, $biz, $svc_type, $email, $phone);
                $vins->execute();
                $success = 'Application submitted. Awaiting admin approval.'; $open_subtab = 'vlogin';
            } else { $error = 'Registration failed. Please try again.'; }
>>>>>>> 7c77f6d (Updated project files)
        }
    }
}

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_mobile_ua = (bool) preg_match('/(Android|iPhone|iPad|iPod|Mobile|webOS)/i', $ua);

$svc_opts = [
    'catering'    => ['fa-utensils',            'Catering'],
    'decoration'  => ['fa-wand-magic-sparkles', 'Decor'],
    'photography' => ['fa-camera',              'Photo'],
    'music'       => ['fa-music',               'Music'],
    'other'       => ['fa-star',                'Other'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<<<<<<< HEAD
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventique — Exclusive Event Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Barlow:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --ink: #0e0c0a;
            --ivory: #f5f0e8;
            --gold: #c9a84c;
            --gold-light: #e8d08a;
            --gold-pale: #f5ead0;
            --warm-grey: #8a8078;
            --card-bg: rgba(245, 240, 232, 0.04);
            --border: rgba(201, 168, 76, 0.25);
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Barlow', sans-serif;
            background-color: var(--ink);
            color: var(--ivory);
            display: flex;
            align-items: stretch;
            min-height: 100vh;
        }

        /* ---- Noise texture overlay ---- */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 100;
        }

        /* ---- Left Panel ---- */
        .left-panel {
            flex: 1.1;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 56px 64px;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 80%, rgba(201,168,76,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 20%, rgba(201,168,76,0.06) 0%, transparent 50%);
        }

        /* Decorative large letterform */
        .left-panel::after {
            content: 'E';
            position: absolute;
            font-family: 'Cormorant Garamond', serif;
            font-size: 42vw;
            font-weight: 300;
            color: rgba(201,168,76,0.04);
            right: -12vw;
            top: 50%;
            transform: translateY(-50%);
            line-height: 1;
            pointer-events: none;
            user-select: none;
        }

        .brand {
            position: relative;
            z-index: 2;
        }

        .brand-mark {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 8px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            border: 1px solid var(--gold);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gold);
            font-size: 16px;
            transform: rotate(45deg);
            flex-shrink: 0;
        }

        .brand-icon i {
            transform: rotate(-45deg);
        }

        .brand-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 400;
            letter-spacing: 0.12em;
            color: var(--ivory);
            text-transform: uppercase;
        }

        .brand-tagline {
            font-size: 11px;
            letter-spacing: 0.3em;
            color: var(--gold);
            text-transform: uppercase;
            font-weight: 300;
            padding-left: 56px;
        }

        .hero-copy {
            position: relative;
            z-index: 2;
        }

        .hero-eyebrow {
            font-size: 10px;
            letter-spacing: 0.35em;
            text-transform: uppercase;
            color: var(--gold);
            font-weight: 400;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .hero-eyebrow::before {
            content: '';
            display: block;
            width: 32px;
            height: 1px;
            background: var(--gold);
        }

        .hero-headline {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(42px, 4.5vw, 72px);
            font-weight: 300;
            line-height: 1.08;
            color: var(--ivory);
            margin-bottom: 28px;
        }

        .hero-headline em {
            font-style: italic;
            color: var(--gold-light);
        }

        .hero-body {
            font-size: 14px;
            line-height: 1.8;
            color: var(--warm-grey);
            font-weight: 300;
            max-width: 380px;
        }

        .left-footer {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .stat {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-num {
            font-family: 'Cormorant Garamond', serif;
            font-size: 30px;
            font-weight: 300;
            color: var(--ivory);
        }

        .stat-label {
            font-size: 10px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--warm-grey);
            font-weight: 300;
        }

        .stat-divider {
            width: 1px;
            height: 36px;
            background: var(--border);
        }

        /* ---- Right Panel ---- */
        .right-panel {
            width: 440px;
            flex-shrink: 0;
            background: rgba(245, 240, 232, 0.04);
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 56px 52px;
            position: relative;
            overflow-y: auto;
        }

        .right-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            opacity: 0.4;
        }

        /* ---- Tabs ---- */
        .auth-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 44px;
            border-bottom: 1px solid var(--border);
            position: relative;
        }

        .tab-btn {
            flex: 1;
            background: none;
            border: none;
            color: var(--warm-grey);
            font-family: 'Barlow', sans-serif;
            font-size: 11px;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            font-weight: 400;
            padding: 0 0 18px 0;
            cursor: pointer;
            transition: color 0.3s;
            position: relative;
        }

        .tab-btn.active {
            color: var(--ivory);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--gold);
        }

        /* ---- Form Heading ---- */
        .form-heading {
            margin-bottom: 36px;
        }

        .form-heading h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 34px;
            font-weight: 300;
            color: var(--ivory);
            line-height: 1.15;
            margin-bottom: 8px;
        }

        .form-heading p {
            font-size: 13px;
            color: var(--warm-grey);
            font-weight: 300;
        }

        /* ---- Alert ---- */
        .alert {
            padding: 12px 16px;
            margin-bottom: 24px;
            font-size: 12px;
            font-weight: 400;
            letter-spacing: 0.03em;
            border-left: 2px solid;
        }

        .alert-danger {
            background: rgba(180, 60, 60, 0.1);
            border-color: #b43c3c;
            color: #e89090;
        }

        .alert-success {
            background: rgba(80, 160, 100, 0.1);
            border-color: #50a064;
            color: #90d0a0;
        }

        /* ---- Form Fields ---- */
        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            font-size: 10px;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--warm-grey);
            margin-bottom: 10px;
            font-weight: 400;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            color: var(--warm-grey);
            font-size: 13px;
            transition: color 0.2s;
        }

        .form-control {
            width: 100%;
            background: none;
            border: none;
            border-bottom: 1px solid var(--border);
            color: var(--ivory);
            font-family: 'Barlow', sans-serif;
            font-size: 15px;
            font-weight: 300;
            padding: 10px 0 10px 26px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control::placeholder {
            color: rgba(138, 128, 120, 0.4);
        }

        .form-control:focus {
            border-bottom-color: var(--gold);
        }

        .form-control:focus + .focus-line,
        .input-wrap:focus-within i {
            color: var(--gold);
        }

        /* ---- Submit Button ---- */
        .btn-submit {
            width: 100%;
            background: none;
            border: 1px solid var(--gold);
            color: var(--gold);
            font-family: 'Barlow', sans-serif;
            font-size: 11px;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            font-weight: 400;
            padding: 16px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            margin-top: 12px;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gold);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-submit:hover::before {
            transform: scaleX(1);
        }

        .btn-submit:hover {
            color: var(--ink);
        }

        .btn-submit span {
            position: relative;
            z-index: 1;
        }

        /* ---- Ornament divider ---- */
        .ornament {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 28px 0;
            color: var(--warm-grey);
            font-size: 10px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        .ornament::before,
        .ornament::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ---- Panel slide animation ---- */
        .auth-panel {
            animation: fadeUp 0.4s ease forwards;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ---- Responsive ---- */
        @media (max-width: 900px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; border-left: none; padding: 48px 36px; }
        }

        /* ---- Scroll styling ---- */
        .right-panel::-webkit-scrollbar { width: 4px; }
        .right-panel::-webkit-scrollbar-track { background: transparent; }
        .right-panel::-webkit-scrollbar-thumb { background: var(--border); }
    </style>
</head>
<body>

    <!-- LEFT PANEL -->
    <div class="left-panel">
        <div class="brand">
            <div class="brand-mark">
                <div class="brand-icon"><i class="fas fa-gem"></i></div>
                <span class="brand-name">Eventique</span>
            </div>
            <div class="brand-tagline">Curated Event Experiences</div>
        </div>

        <div class="hero-copy">
            <div class="hero-eyebrow">Est. 2024</div>
            <h1 class="hero-headline">
                Craft moments<br>
                <em>worth</em><br>
                remembering.
            </h1>
            <p class="hero-body">
                From intimate gatherings to grand celebrations — we connect you with the finest venues and artisan vendors to bring your vision to life.
            </p>
        </div>

        <div class="left-footer">
            <div class="stat">
                <span class="stat-num">4+</span>
                <span class="stat-label">Venues</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat">
                <span class="stat-num">∞</span>
                <span class="stat-label">Possibilities</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat">
                <span class="stat-num">1</span>
                <span class="stat-label">Platform</span>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">

        <!-- Tabs -->
        <div class="auth-tabs">
            <button class="tab-btn active" id="tabLogin" onclick="showPanel('login')">Sign In</button>
            <button class="tab-btn" id="tabRegister" onclick="showPanel('register')">Create Account</button>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-circle-exclamation" style="margin-right:8px;"></i><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-circle-check" style="margin-right:8px;"></i><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- LOGIN -->
        <div id="panelLogin" class="auth-panel">
            <div class="form-heading">
                <h2>Welcome<br>back.</h2>
                <p>Sign in to access your events.</p>
            </div>
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" name="login" class="btn-submit"><span>Sign In &nbsp;→</span></button>
            </form>
        </div>

        <!-- REGISTER -->
        <div id="panelRegister" class="auth-panel" style="display:none;">
            <div class="form-heading">
                <h2>Join<br>Eventique.</h2>
                <p>Create your account in seconds.</p>
            </div>
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <div class="input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" name="name" class="form-control" placeholder="Your name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <div class="input-wrap">
                        <i class="fas fa-phone"></i>
                        <input type="tel" name="phone" class="form-control" placeholder="+1 000 000 0000" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required minlength="6">
                    </div>
                </div>
                <button type="submit" name="register" class="btn-submit"><span>Create Account &nbsp;→</span></button>
            </form>
        </div>

    </div>

    <script>
        function showPanel(panel) {
            const login    = document.getElementById('panelLogin');
            const register = document.getElementById('panelRegister');
            const tabL     = document.getElementById('tabLogin');
            const tabR     = document.getElementById('tabRegister');

            if (panel === 'login') {
                login.style.display    = 'block';
                register.style.display = 'none';
                tabL.classList.add('active');
                tabR.classList.remove('active');
            } else {
                login.style.display    = 'none';
                register.style.display = 'block';
                tabR.classList.add('active');
                tabL.classList.remove('active');
            }

            // re-trigger animation
            const active = panel === 'login' ? login : register;
            active.style.animation = 'none';
            active.offsetHeight;
            active.style.animation = '';
        }

        <?php if ($success): ?>
            showPanel('login');
        <?php endif; ?>
        <?php if ($error && isset($_POST['register'])): ?>
            showPanel('register');
        <?php endif; ?>
    </script>
=======
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Eventique</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Barlow:wght@300;400;500;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* =========================================================
   BASE
   ========================================================= */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --ink:#0b0908; --ink2:#111009; --ink3:#1a1610; --ink4:#222018;
  --ivory:#f3ede1; --ivory2:#b8b0a4; --ivory3:#6c6460;
  --gold:#c9a84c; --goldl:#e8d08a; --goldp:rgba(201,168,76,.13);
  --green:#4dbd8a; --rose:#d06878; --warm-grey:#8a8078;
  --bsub:rgba(255,255,255,.06); --border:rgba(201,168,76,.22);
}
html,body{height:100%;margin:0;padding:0;background:var(--ink);overflow:hidden}
#layout-desktop,#layout-mobile{display:none}

/* =========================================================
   DESKTOP
   ========================================================= */
#layout-desktop{
  font-family:'Barlow',sans-serif; color:var(--ivory);
  width:100vw; height:100vh;
  display:flex; align-items:stretch;
  position:fixed; top:0; left:0; overflow:hidden;
}
#layout-desktop::after{
  content:''; position:fixed; inset:0; pointer-events:none; z-index:1000;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
}
.d-left{
  flex:1; position:relative; display:flex; flex-direction:column;
  justify-content:space-between; padding:56px 64px;
  height:100vh; overflow:hidden; min-width:0;
}
.d-left::before{
  content:''; position:absolute; inset:0;
  background:
    radial-gradient(ellipse 80% 60% at 20% 80%,rgba(201,168,76,.12) 0%,transparent 60%),
    radial-gradient(ellipse 60% 80% at 80% 20%,rgba(201,168,76,.06) 0%,transparent 50%);
}
.d-left::after{
  content:'E'; position:absolute;
  font-family:'Cormorant Garamond',serif; font-size:42vw; font-weight:300;
  color:rgba(201,168,76,.04); right:-12vw; top:50%; transform:translateY(-50%);
  line-height:1; pointer-events:none; user-select:none;
}
.d-brand{position:relative;z-index:2}
.d-brand-mark{display:flex;align-items:center;gap:14px;margin-bottom:8px}
.d-brand-icon{width:42px;height:42px;border:1px solid var(--gold);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:16px;transform:rotate(45deg);flex-shrink:0}
.d-brand-icon i{transform:rotate(-45deg)}
.d-brand-name{font-family:'Cormorant Garamond',serif;font-size:28px;font-weight:400;letter-spacing:.12em;color:var(--ivory);text-transform:uppercase}
.d-brand-tag{font-size:11px;letter-spacing:.3em;color:var(--gold);text-transform:uppercase;font-weight:300;padding-left:56px}
.d-hero{position:relative;z-index:2}
.d-eyebrow{font-size:10px;letter-spacing:.35em;text-transform:uppercase;color:var(--gold);margin-bottom:20px;display:flex;align-items:center;gap:12px}
.d-eyebrow::before{content:'';display:block;width:32px;height:1px;background:var(--gold)}
.d-headline{font-family:'Cormorant Garamond',serif;font-size:clamp(42px,4.5vw,72px);font-weight:300;line-height:1.08;color:var(--ivory);margin-bottom:28px}
.d-headline em{font-style:italic;color:var(--goldl)}
.d-body{font-size:14px;line-height:1.8;color:var(--warm-grey);font-weight:300;max-width:380px}
.d-footer{position:relative;z-index:2;display:flex;align-items:center;gap:32px}
.d-stat{display:flex;flex-direction:column;gap:4px}
.d-stat-num{font-family:'Cormorant Garamond',serif;font-size:30px;font-weight:300;color:var(--ivory)}
.d-stat-label{font-size:10px;letter-spacing:.2em;text-transform:uppercase;color:var(--warm-grey);font-weight:300}
.d-divider{width:1px;height:36px;background:var(--border)}
.d-right{
  width:440px; flex-shrink:0;
  background:rgba(245,240,232,.04);
  border-left:1px solid var(--border);
  display:flex; flex-direction:column;
  padding:56px 52px;
  position:relative;
  height:100vh;
  max-height:100vh;
  overflow-y:scroll;
  overflow-x:hidden;
}
.d-right::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,var(--gold),transparent);opacity:.4}
.d-right::-webkit-scrollbar{width:4px}
.d-right::-webkit-scrollbar-thumb{background:rgba(201,168,76,.35);border-radius:2px}
.d-right::-webkit-scrollbar-track{background:transparent}
/* show scrollbar hint when vendor panel open */
#d-p-vendor ~ * , #layout-desktop:has(#d-p-vendor[style*="block"]) .d-right{scrollbar-width:thin}
.d-tabs{display:flex;margin-bottom:44px;border-bottom:1px solid var(--border)}
.d-tab{flex:1;background:none;border:none;color:var(--warm-grey);font-family:'Barlow',sans-serif;font-size:11px;letter-spacing:.25em;text-transform:uppercase;font-weight:400;padding:0 0 18px;cursor:pointer;transition:color .3s;position:relative}
.d-tab.active{color:var(--ivory)}
.d-tab.active::after{content:'';position:absolute;bottom:-1px;left:0;right:0;height:1px;background:var(--gold)}
.d-heading{margin-bottom:36px}
.d-heading h2{font-family:'Cormorant Garamond',serif;font-size:34px;font-weight:300;color:var(--ivory);line-height:1.15;margin-bottom:8px}
.d-heading p{font-size:13px;color:var(--warm-grey);font-weight:300}
.d-alert{padding:12px 16px;margin-bottom:24px;font-size:12px;border-left:2px solid;display:flex;align-items:center;gap:8px}
.d-alert.err{background:rgba(180,60,60,.1);border-color:#b43c3c;color:#e89090}
.d-alert.ok {background:rgba(80,160,100,.1);border-color:#50a064;color:#90d0a0}
.d-fg{margin-bottom:22px}
.d-label{display:block;font-size:10px;letter-spacing:.25em;text-transform:uppercase;color:var(--warm-grey);margin-bottom:10px;font-weight:400}
.d-iwrap{position:relative}
.d-iwrap i{position:absolute;left:0;top:50%;transform:translateY(-50%);color:var(--warm-grey);font-size:13px;transition:color .2s}
.d-input{width:100%;background:none;border:none;border-bottom:1px solid var(--border);color:var(--ivory);font-family:'Barlow',sans-serif;font-size:15px;font-weight:300;padding:10px 0 10px 26px;outline:none;transition:border-color .2s}
.d-input::placeholder{color:rgba(138,128,120,.4)}
.d-input:focus{border-bottom-color:var(--gold)}
.d-input option{background:var(--ink3)}
.d-iwrap:focus-within i{color:var(--gold)}
.d-btn{width:100%;background:none;border:1px solid var(--gold);color:var(--gold);font-family:'Barlow',sans-serif;font-size:11px;letter-spacing:.3em;text-transform:uppercase;font-weight:400;padding:16px;cursor:pointer;transition:all .3s;position:relative;overflow:hidden;margin-top:12px}
.d-btn::before{content:'';position:absolute;inset:0;background:var(--gold);transform:scaleX(0);transform-origin:left;transition:transform .35s cubic-bezier(.4,0,.2,1)}
.d-btn:hover::before{transform:scaleX(1)}
.d-btn:hover{color:var(--ink)}
.d-btn span{position:relative;z-index:1}
.d-back{background:none;border:none;color:var(--warm-grey);font-family:'Barlow',sans-serif;font-size:11px;letter-spacing:.1em;cursor:pointer;margin-bottom:24px;display:flex;align-items:center;gap:6px;padding:0;transition:color .2s}
.d-back:hover{color:var(--gold)}
.d-vlink{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:28px;padding-top:22px;border-top:1px solid var(--border);text-decoration:none}
.d-vlink-l{display:flex;align-items:center;gap:13px}
.d-vicon{width:36px;height:36px;border:1px solid rgba(201,168,76,.28);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:12px;transform:rotate(45deg);flex-shrink:0;transition:border-color .25s,background .25s}
.d-vicon i{transform:rotate(-45deg)}
.d-vlink:hover .d-vicon{background:rgba(201,168,76,.08);border-color:var(--gold)}
.d-vlink-label{font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:var(--warm-grey);margin-bottom:3px}
.d-vlink-name{font-family:'Cormorant Garamond',serif;font-size:17px;font-weight:400;color:var(--ivory);line-height:1}
.d-varrow{font-size:11px;color:var(--warm-grey);flex-shrink:0;transition:color .2s,transform .22s}
.d-vlink:hover .d-varrow{color:var(--gold);transform:translateX(4px)}
.d-panel{animation:dUp .4s ease both}
/* login + register panels: vertically center with auto margins */
#d-p-login,#d-p-register{margin-top:auto;margin-bottom:auto}
/* vendor panel: top-aligned, just flows down naturally */
#d-p-vendor{margin-top:0;padding-bottom:32px}
.d-pending{background:var(--goldp);border:1px solid rgba(201,168,76,.2);padding:12px 14px;margin-bottom:20px}
.d-pending strong{font-size:9px;letter-spacing:.16em;text-transform:uppercase;color:var(--goldl);display:block;margin-bottom:4px}
.d-pending p{font-size:11.5px;color:var(--ivory3);line-height:1.65;font-weight:300}
@keyframes dUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:900px){
  .d-left{display:none}
  .d-right{width:100%;height:100vh;border-left:none;padding:48px 36px}
}

/* =========================================================
   MOBILE
   ========================================================= */
#layout-mobile{
  font-family:'DM Sans',sans-serif; color:var(--ivory);
  align-items:center; justify-content:center;
  position:fixed; top:0; left:0; width:100vw; height:100vh;
}
.m-shell{
  width:390px; height:844px; background:var(--ink); border-radius:48px;
  overflow:hidden; position:relative;
  box-shadow:0 0 0 10px #1a1612,0 0 0 12px #0f0e0c,0 40px 80px rgba(0,0,0,.75),inset 0 1px 0 rgba(255,255,255,.08);
}
.m-shell::before{content:'';position:absolute;left:-14px;top:120px;width:4px;height:34px;background:#1a1612;border-radius:2px 0 0 2px;box-shadow:0 50px 0 #1a1612,0 96px 0 #1a1612;z-index:9999}
.m-shell::after{content:'';position:absolute;right:-14px;top:160px;width:4px;height:64px;background:#1a1612;border-radius:0 2px 2px 0;z-index:9999}
.m-island{position:absolute;top:12px;left:50%;transform:translateX(-50%);width:120px;height:34px;background:#000;border-radius:20px;z-index:20}
.m-status{height:50px;display:flex;align-items:center;justify-content:space-between;padding:14px 28px 0;position:relative;z-index:10;flex-shrink:0}
.m-time{font-size:15px;font-weight:500;color:var(--ivory);letter-spacing:-.01em}
.m-icons{display:flex;align-items:center;gap:6px}
.m-icons i{font-size:12px;color:var(--ivory)}
.m-app{height:calc(844px - 50px);display:flex;flex-direction:column;overflow:hidden;position:relative}
.m-app::after{content:'';position:absolute;inset:0;pointer-events:none;z-index:999;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.028'/%3E%3C/svg%3E")}
.m-screen{position:absolute;inset:0;display:flex;flex-direction:column;overflow:hidden;transition:transform .38s cubic-bezier(.4,0,.2,1),opacity .3s}
/* vendor screen needs scroll — override overflow so the whole screen scrolls */
#m-vendor{overflow-y:auto;overflow-x:hidden;-webkit-overflow-scrolling:touch;padding-bottom:44px}
#m-vendor::-webkit-scrollbar{display:none}
/* freeze the home indicator at bottom for vendor screen */
#m-vendor .m-home{position:sticky;bottom:0;background:var(--ink);z-index:10;margin-top:8px}
.m-screen.hidden{transform:translateX(100%);opacity:0;pointer-events:none}
.m-screen.slide-l{transform:translateX(-30%);opacity:0;pointer-events:none}
/* splash */
#m-splash{background:var(--ink);justify-content:space-between;padding:0 0 40px}
.m-hero{flex:1;position:relative;overflow:hidden}
.m-hero-bg{position:absolute;inset:0;background:radial-gradient(ellipse 120% 80% at 50% 100%,rgba(201,168,76,.22) 0%,transparent 55%),radial-gradient(ellipse 60% 60% at 80% 20%,rgba(201,168,76,.07) 0%,transparent 50%),var(--ink2)}
.m-deco{position:absolute;inset:0;overflow:hidden}
.m-dd{position:absolute;border:1px solid rgba(201,168,76,.12);transform:rotate(45deg)}
.m-splash-brand{position:absolute;top:60px;left:0;right:0;display:flex;flex-direction:column;align-items:center;animation:mDown .7s ease both}
.m-gem{width:52px;height:52px;border:1px solid var(--gold);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:18px;transform:rotate(45deg);margin-bottom:16px}
.m-gem i{transform:rotate(-45deg)}
.m-splash-name{font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:400;letter-spacing:.16em;text-transform:uppercase;color:var(--ivory)}
.m-splash-tag{font-size:9px;letter-spacing:.38em;text-transform:uppercase;color:var(--gold);margin-top:5px}
.m-splash-copy{position:absolute;bottom:40px;left:32px;right:32px;animation:mUp .7s ease .2s both}
.m-eyebrow{font-size:10px;letter-spacing:.3em;text-transform:uppercase;color:var(--gold);margin-bottom:12px;display:flex;align-items:center;gap:10px}
.m-eyebrow::before{content:'';width:24px;height:1px;background:var(--gold);display:block}
.m-h{font-family:'Cormorant Garamond',serif;font-size:42px;font-weight:300;line-height:1.08;color:var(--ivory);margin-bottom:14px}
.m-h em{font-style:italic;color:var(--goldl)}
.m-p{font-size:13px;color:var(--ivory3);line-height:1.7;font-weight:300}
.m-splash-btns{padding:0 28px;animation:mUp .6s ease .4s both}
.m-cta-main{width:100%;background:none;border:1px solid var(--gold);color:var(--gold);font-family:'DM Sans',sans-serif;font-size:12px;font-weight:500;letter-spacing:.22em;text-transform:uppercase;padding:18px;cursor:pointer;position:relative;overflow:hidden;margin-bottom:16px;transition:color .3s}
.m-cta-main::before{content:'';position:absolute;inset:0;background:var(--gold);transform:scaleX(0);transform-origin:left;transition:transform .35s cubic-bezier(.4,0,.2,1)}
.m-cta-main:active::before{transform:scaleX(1)}
.m-cta-main:active{color:var(--ink)}
.m-cta-main span{position:relative;z-index:1}
.m-vbtn{display:flex;align-items:center;justify-content:center;gap:8px;font-size:12px;color:var(--ivory3);cursor:pointer;padding:10px;background:none;border:none;font-family:'DM Sans',sans-serif;width:100%;transition:color .2s}
.m-vbtn:active{color:var(--gold)}
.m-vbtn i{color:var(--gold);font-size:10px}
/* nav */
.m-nav{display:flex;align-items:center;padding:16px 22px 0;flex-shrink:0}
.m-back{width:40px;height:40px;display:flex;align-items:center;justify-content:center;background:var(--bsub);border-radius:50%;border:none;color:var(--ivory2);font-size:14px;cursor:pointer;transition:background .2s}
.m-back:active{background:rgba(201,168,76,.15)}
.m-nav-title{flex:1;text-align:center;font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:400;color:var(--ivory);letter-spacing:.04em}
.m-spacer{width:40px}
/* tabs */
.m-tabs{display:flex;background:var(--ink3);padding:4px;border-radius:10px;flex-shrink:0}
.m-tab{flex:1;padding:10px;text-align:center;border:none;background:none;font-family:'DM Sans',sans-serif;font-size:12px;font-weight:500;letter-spacing:.08em;text-transform:uppercase;color:var(--ivory3);cursor:pointer;border-radius:8px;transition:all .25s}
.m-tab.active{background:var(--ink4);color:var(--ivory);box-shadow:0 2px 8px rgba(0,0,0,.35)}
/* form body */
.m-body{flex:1;overflow-y:auto;padding:24px 28px;-webkit-overflow-scrolling:touch}
.m-body::-webkit-scrollbar{display:none}
/* inside vendor screen the parent scrolls, so body shouldn't flex-grow or scroll independently */
#m-vendor .m-body{flex:none;overflow:visible;padding-bottom:8px}
.m-ftitle{font-family:'Cormorant Garamond',serif;font-size:36px;font-weight:300;line-height:1.1;color:var(--ivory);margin-bottom:8px}
.m-ftitle em{font-style:italic;color:var(--goldl)}
.m-fsub{font-size:13px;color:var(--ivory3);font-weight:300;margin-bottom:24px;line-height:1.6}
.m-alert{padding:12px 14px;margin-bottom:18px;font-size:12px;border-left:2px solid;display:flex;align-items:center;gap:8px}
.m-alert.err{background:rgba(208,104,120,.09);border-color:var(--rose);color:var(--rose)}
.m-alert.ok {background:rgba(77,189,138,.09); border-color:var(--green);color:var(--green)}
.m-fg{margin-bottom:20px}
.m-fg label{font-size:9.5px;letter-spacing:.24em;text-transform:uppercase;color:var(--ivory3);display:block;margin-bottom:10px;font-weight:400}
.m-iwrap{display:flex;align-items:center;border-bottom:1px solid var(--bsub);transition:border-color .22s;padding-bottom:12px}
.m-iwrap:focus-within{border-bottom-color:var(--gold)}
.m-iwrap i{font-size:13px;color:var(--ivory3);margin-right:12px;flex-shrink:0;transition:color .2s}
.m-iwrap:focus-within i{color:var(--gold)}
.m-input{flex:1;background:none;border:none;outline:none;color:var(--ivory);font-family:'DM Sans',sans-serif;font-size:16px;font-weight:300;padding:0;-webkit-appearance:none}
.m-input::placeholder{color:rgba(108,100,96,.45);font-size:15px}
.m-row{display:grid;grid-template-columns:1fr 1fr;gap:0 18px}
.m-cta{width:100%;background:none;border:1px solid var(--gold);color:var(--gold);font-family:'DM Sans',sans-serif;font-size:12px;font-weight:500;letter-spacing:.2em;text-transform:uppercase;padding:18px;cursor:pointer;position:relative;overflow:hidden;margin-top:8px;transition:color .3s}
.m-cta::before{content:'';position:absolute;inset:0;background:var(--gold);transform:scaleY(0);transform-origin:bottom;transition:transform .3s cubic-bezier(.4,0,.2,1)}
.m-cta:active::before{transform:scaleY(1)}
.m-cta:active{color:var(--ink)}
.m-cta span{position:relative;z-index:1;display:flex;align-items:center;justify-content:center;gap:9px}
/* vendor band */
.m-vband{height:165px;position:relative;overflow:hidden;flex-shrink:0}
.m-vband-bg{position:absolute;inset:0;background:radial-gradient(ellipse 100% 100% at 50% 120%,rgba(201,168,76,.25) 0%,transparent 60%),var(--ink2)}
.m-vband-content{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;padding-bottom:22px}
.m-vring{width:50px;height:50px;border:1px solid var(--gold);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:16px;transform:rotate(45deg);margin-bottom:10px}
.m-vring i{transform:rotate(-45deg)}
.m-veye{font-size:9px;letter-spacing:.3em;text-transform:uppercase;color:var(--gold)}
/* chips */
.m-chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:6px}
.m-chip{background:none;border:1px solid var(--bsub);color:var(--ivory3);font-family:'DM Sans',sans-serif;font-size:10px;letter-spacing:.08em;text-transform:uppercase;padding:7px 12px;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:6px}
.m-chip i{font-size:9px}
.m-chip.sel{border-color:var(--gold);color:var(--gold);background:var(--goldp)}
/* pending */
.m-pending{background:var(--goldp);border:1px solid rgba(201,168,76,.2);padding:12px 14px;margin-bottom:18px}
.m-pending strong{font-size:9px;letter-spacing:.16em;text-transform:uppercase;color:var(--goldl);display:block;margin-bottom:4px}
.m-pending p{font-size:11.5px;color:var(--ivory3);line-height:1.65;font-weight:300}
.m-home{height:34px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.m-home-bar{width:130px;height:5px;background:rgba(255,255,255,.22);border-radius:3px}
@keyframes mUp  {from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes mDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
@keyframes mIn  {from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}
@media(min-width:500px){#layout-mobile{background:#111;padding:40px 0}}
@media(max-width:499px){
  #layout-mobile{padding:0}
  .m-shell{width:100%;height:100dvh;border-radius:0;box-shadow:none}
  .m-shell::before,.m-shell::after{display:none}
  .m-app{height:calc(100dvh - 50px)}
}
</style>
</head>
<body>

<!-- =============================================================
     DESKTOP
     ============================================================= -->
<div id="layout-desktop">
  <div class="d-left">
    <div class="d-brand">
      <div class="d-brand-mark">
        <div class="d-brand-icon"><i class="fas fa-gem"></i></div>
        <span class="d-brand-name">Eventique</span>
      </div>
      <div class="d-brand-tag">Curated Event Experiences</div>
    </div>
    <div class="d-hero">
      <div class="d-eyebrow">Est. 2024</div>
      <h1 class="d-headline">Craft moments<br><em>worth</em><br>remembering.</h1>
      <p class="d-body">From intimate gatherings to grand celebrations — we connect you with the finest venues and artisan vendors to bring your vision to life.</p>
    </div>
    <div class="d-footer">
      <div class="d-stat"><span class="d-stat-num">4+</span><span class="d-stat-label">Venues</span></div>
      <div class="d-divider"></div>
      <div class="d-stat"><span class="d-stat-num">&#8734;</span><span class="d-stat-label">Possibilities</span></div>
      <div class="d-divider"></div>
      <div class="d-stat"><span class="d-stat-num">1</span><span class="d-stat-label">Platform</span></div>
    </div>
  </div>

  <div class="d-right">
    <div class="d-tabs" id="d-main-tabs">
      <button class="d-tab active" id="d-tl" onclick="dTab('login')">Sign In</button>
      <button class="d-tab"        id="d-tr" onclick="dTab('register')">Create Account</button>
    </div>

    <?php if ($error && in_array($open_tab,['splash','login'])): ?>
      <div class="d-alert err"><i class="fas fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success && in_array($open_tab,['splash','login'])): ?>
      <div class="d-alert ok"><i class="fas fa-circle-check"></i><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Sign In -->
    <div id="d-p-login" class="d-panel">
      <div class="d-heading"><h2>Welcome<br>back.</h2><p>Sign in to access your events.</p></div>
      <form method="POST" action="">
        <div class="d-fg"><label class="d-label">Email</label>
          <div class="d-iwrap"><i class="fas fa-envelope"></i>
            <input type="email" name="email" class="d-input" placeholder="you@example.com" required autocomplete="email">
          </div></div>
        <div class="d-fg"><label class="d-label">Password</label>
          <div class="d-iwrap"><i class="fas fa-lock"></i>
            <input type="password" name="password" class="d-input" placeholder="&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;" required autocomplete="current-password">
          </div></div>
        <button type="submit" name="login" class="d-btn"><span>Sign In &#8194;&#8594;</span></button>
      </form>
      <a href="#" class="d-vlink" onclick="dVendorShow();return false">
        <div class="d-vlink-l">
          <div class="d-vicon"><i class="fas fa-store"></i></div>
          <div><div class="d-vlink-label">Service Provider?</div><div class="d-vlink-name">Vendor Portal</div></div>
        </div>
        <i class="fas fa-arrow-right d-varrow"></i>
      </a>
    </div>

    <!-- Register -->
    <div id="d-p-register" class="d-panel" style="display:none">
      <div class="d-heading"><h2>Join<br>Eventique.</h2><p>Create your account in seconds.</p></div>
      <form method="POST" action="">
        <div class="d-fg"><label class="d-label">Full Name</label>
          <div class="d-iwrap"><i class="fas fa-user"></i><input type="text" name="name" class="d-input" placeholder="Your name" required></div></div>
        <div class="d-fg"><label class="d-label">Email</label>
          <div class="d-iwrap"><i class="fas fa-envelope"></i><input type="email" name="email" class="d-input" placeholder="you@example.com" required autocomplete="email"></div></div>
        <div class="d-fg"><label class="d-label">Phone</label>
          <div class="d-iwrap"><i class="fas fa-phone"></i><input type="tel" name="phone" class="d-input" placeholder="+1 000 000 0000" required></div></div>
        <div class="d-fg"><label class="d-label">Password</label>
          <div class="d-iwrap"><i class="fas fa-lock"></i><input type="password" name="password" class="d-input" placeholder="Min. 6 characters" required minlength="6" autocomplete="new-password"></div></div>
        <button type="submit" name="register" class="d-btn"><span>Create Account &#8194;&#8594;</span></button>
      </form>
    </div>

    <!-- Vendor panel -->
    <div id="d-p-vendor" class="d-panel" style="display:none">
      <button class="d-back" onclick="dTab('login')"><i class="fas fa-chevron-left" style="font-size:10px"></i> Back</button>
      <div class="d-tabs" style="margin-bottom:28px">
        <button class="d-tab active" id="d-vtl" onclick="dVTab('login')">Sign In</button>
        <button class="d-tab"        id="d-vta" onclick="dVTab('apply')">Apply</button>
      </div>
      <?php if ($error && $open_tab==='vendor'): ?>
        <div class="d-alert err"><i class="fas fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
      <?php elseif ($success && $open_tab==='vendor'): ?>
        <div class="d-alert ok"><i class="fas fa-circle-check"></i><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <div id="d-vp-login">
        <div class="d-heading"><h2>Vendor<br><em style="font-style:italic;color:var(--goldl)">Portal.</em></h2><p>Access your dashboard and bookings.</p></div>
        <form method="POST" action="">
          <div class="d-fg"><label class="d-label">Email</label>
            <div class="d-iwrap"><i class="fas fa-envelope"></i><input type="email" name="vendor_email" class="d-input" placeholder="business@example.com" required autocomplete="email"></div></div>
          <div class="d-fg"><label class="d-label">Password</label>
            <div class="d-iwrap"><i class="fas fa-lock"></i><input type="password" name="vendor_password" class="d-input" placeholder="&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;" required autocomplete="current-password"></div></div>
          <button type="submit" name="vendor_login" class="d-btn"><span>Sign In &#8194;&#8594;</span></button>
        </form>
      </div>
      <div id="d-vp-apply" style="display:none">
        <div class="d-heading"><h2>Join our<br><em style="font-style:italic;color:var(--goldl)">network.</em></h2><p>Submit your application for admin review.</p></div>
        <div class="d-pending"><strong>How it works</strong><p>Fill in your details and our team will review and activate your account.</p></div>
        <form method="POST" action="">
          <div class="d-fg"><label class="d-label">Full Name</label>
            <div class="d-iwrap"><i class="fas fa-user"></i><input type="text" name="vname" class="d-input" placeholder="Your name" required></div></div>
          <div class="d-fg"><label class="d-label">Business Name</label>
            <div class="d-iwrap"><i class="fas fa-store"></i><input type="text" name="business_name" class="d-input" placeholder="e.g. Le Bon Traiteur" required></div></div>
          <div class="d-fg"><label class="d-label">Service Type</label>
            <select name="service_type" class="d-input" style="padding-left:0" required>
              <option value="">Select a service...</option>
              <?php foreach(['catering'=>'Catering','decoration'=>'Decoration','photography'=>'Photography','music'=>'Music / DJ','other'=>'Other'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= (isset($_POST['service_type'])&&$_POST['service_type']===$v)?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="d-fg"><label class="d-label">Email</label>
            <div class="d-iwrap"><i class="fas fa-envelope"></i><input type="email" name="vemail" class="d-input" placeholder="business@example.com" required autocomplete="email"></div></div>
          <div class="d-fg"><label class="d-label">Phone</label>
            <div class="d-iwrap"><i class="fas fa-phone"></i><input type="tel" name="vphone" class="d-input" placeholder="+1 000 000 0000" required></div></div>
          <div class="d-fg"><label class="d-label">Password</label>
            <div class="d-iwrap"><i class="fas fa-lock"></i><input type="password" name="vpassword" class="d-input" placeholder="Create a password" required minlength="6" autocomplete="new-password"></div></div>
          <button type="submit" name="vendor_apply" class="d-btn"><span>Submit Application &#8194;&#8594;</span></button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- =============================================================
     MOBILE
     ============================================================= -->
<div id="layout-mobile">
  <div class="m-shell">
    <div class="m-status">
      <span class="m-time" id="m-clock">9:41</span>
      <div class="m-island"></div>
      <div class="m-icons"><i class="fas fa-signal"></i><i class="fas fa-wifi"></i><i class="fas fa-battery-three-quarters"></i></div>
    </div>
    <div class="m-app">

      <!-- SPLASH -->
      <div class="m-screen" id="m-splash">
        <div class="m-hero">
          <div class="m-hero-bg"></div>
          <div class="m-deco">
            <div class="m-dd" style="width:200px;height:200px;top:40px;left:-60px"></div>
            <div class="m-dd" style="width:340px;height:340px;top:80px;right:-120px"></div>
            <div class="m-dd" style="width:160px;height:160px;bottom:20px;left:40px;border-color:rgba(201,168,76,.07)"></div>
          </div>
          <div class="m-splash-brand">
            <div class="m-gem"><i class="fas fa-gem"></i></div>
            <div class="m-splash-name">Eventique</div>
            <div class="m-splash-tag">Curated Event Experiences</div>
          </div>
          <div class="m-splash-copy">
            <div class="m-eyebrow">Est. 2024</div>
            <h1 class="m-h">Craft moments<br><em>worth</em><br>remembering.</h1>
            <p class="m-p">Premium venues, artisan vendors, unforgettable events.</p>
          </div>
        </div>
        <div class="m-splash-btns">
          <button class="m-cta-main" onclick="mGo('login')"><span>Get Started &#8194;&#8594;</span></button>
          <button class="m-vbtn" onclick="mGo('vendor')"><i class="fas fa-store"></i> Are you a vendor? Sign in here</button>
        </div>
      </div>

      <!-- USER LOGIN / REGISTER -->
      <div class="m-screen hidden" id="m-login">
        <div class="m-nav">
          <button class="m-back" onclick="mBack('splash')"><i class="fas fa-chevron-left"></i></button>
          <div class="m-nav-title">Eventique</div><div class="m-spacer"></div>
        </div>
        <div class="m-tabs" style="margin:18px 28px 0">
          <button class="m-tab <?= ($open_tab==='login'&&$open_subtab==='signin')?'active':'' ?>" id="m-tsi" onclick="mTab('u','signin')">Sign In</button>
          <button class="m-tab <?= ($open_tab==='login'&&$open_subtab==='join')  ?'active':'' ?>" id="m-tjn" onclick="mTab('u','join')">Join</button>
        </div>
        <?php if ($open_tab==='login' && ($error||$success)): ?>
        <div style="padding:14px 28px 0">
          <?php if ($error): ?><div class="m-alert err"><i class="fas fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
          <?php else:         ?><div class="m-alert ok"><i class="fas fa-circle-check"></i><?= htmlspecialchars($success) ?></div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="m-body" id="m-b-si" <?= ($open_tab==='login'&&$open_subtab==='join')?'style="display:none"':'' ?>>
          <div class="m-ftitle">Welcome<br><em>back.</em></div>
          <div class="m-fsub">Sign in to access your bookings and events.</div>
          <form method="POST" action="">
            <div class="m-fg"><label>Email Address</label><div class="m-iwrap"><i class="fas fa-envelope"></i><input class="m-input" type="email" name="email" placeholder="you@example.com" autocomplete="email" required></div></div>
            <div class="m-fg"><label>Password</label><div class="m-iwrap"><i class="fas fa-lock"></i><input class="m-input" type="password" name="password" placeholder="&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;" autocomplete="current-password" required></div></div>
            <button type="submit" name="login" class="m-cta"><span><i class="fas fa-arrow-right-to-bracket"></i> Sign In</span></button>
          </form>
        </div>
        <div class="m-body" id="m-b-jn" <?= ($open_tab!=='login'||$open_subtab!=='join')?'style="display:none"':'' ?>>
          <div class="m-ftitle">Join<br><em>Eventique.</em></div>
          <div class="m-fsub">Create your free account in seconds.</div>
          <form method="POST" action="">
            <div class="m-row">
              <div class="m-fg"><label>Full Name</label><div class="m-iwrap"><i class="fas fa-user"></i><input class="m-input" type="text" name="name" placeholder="Your name" required></div></div>
              <div class="m-fg"><label>Phone</label><div class="m-iwrap"><i class="fas fa-phone"></i><input class="m-input" type="tel" name="phone" placeholder="+1 000" required></div></div>
            </div>
            <div class="m-fg"><label>Email Address</label><div class="m-iwrap"><i class="fas fa-envelope"></i><input class="m-input" type="email" name="email" placeholder="you@example.com" autocomplete="email" required></div></div>
            <div class="m-fg"><label>Password</label><div class="m-iwrap"><i class="fas fa-lock"></i><input class="m-input" type="password" name="password" placeholder="Min. 6 characters" minlength="6" autocomplete="new-password" required></div></div>
            <button type="submit" name="register" class="m-cta"><span><i class="fas fa-check"></i> Create Account</span></button>
          </form>
        </div>
        <div class="m-home"><div class="m-home-bar"></div></div>
      </div>

      <!-- VENDOR -->
      <div class="m-screen hidden" id="m-vendor">
        <div class="m-nav">
          <button class="m-back" onclick="mBack('splash')"><i class="fas fa-chevron-left"></i></button>
          <div class="m-nav-title">Vendor Portal</div><div class="m-spacer"></div>
        </div>
        <div class="m-vband">
          <div class="m-vband-bg"></div>
          <div class="m-vband-content">
            <div class="m-vring"><i class="fas fa-store"></i></div>
            <div class="m-veye">Service Provider Portal</div>
          </div>
        </div>
        <div class="m-tabs" style="margin:16px 28px 0">
          <button class="m-tab <?= ($open_tab==='vendor'&&$open_subtab==='vlogin')?'active':'' ?>" id="m-tvl" onclick="mTab('v','vlogin')">Sign In</button>
          <button class="m-tab <?= ($open_tab==='vendor'&&$open_subtab==='vapply')?'active':'' ?>" id="m-tva" onclick="mTab('v','vapply')">Apply</button>
        </div>
        <?php if ($open_tab==='vendor' && ($error||$success)): ?>
        <div style="padding:14px 28px 0">
          <?php if ($error): ?><div class="m-alert err"><i class="fas fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
          <?php else:         ?><div class="m-alert ok"><i class="fas fa-circle-check"></i><?= htmlspecialchars($success) ?></div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="m-body" id="m-b-vl" <?= ($open_tab==='vendor'&&$open_subtab==='vapply')?'style="display:none"':'' ?>>
          <div class="m-ftitle" style="font-size:30px">Partner<br><em>Portal.</em></div>
          <div class="m-fsub">Access your dashboard and bookings.</div>
          <form method="POST" action="">
            <div class="m-fg"><label>Email Address</label><div class="m-iwrap"><i class="fas fa-envelope"></i><input class="m-input" type="email" name="vendor_email" placeholder="business@example.com" autocomplete="email" required></div></div>
            <div class="m-fg"><label>Password</label><div class="m-iwrap"><i class="fas fa-lock"></i><input class="m-input" type="password" name="vendor_password" placeholder="&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;" autocomplete="current-password" required></div></div>
            <button type="submit" name="vendor_login" class="m-cta"><span><i class="fas fa-arrow-right-to-bracket"></i> Sign In</span></button>
          </form>
        </div>
        <div class="m-body" id="m-b-va" <?= ($open_tab!=='vendor'||$open_subtab!=='vapply')?'style="display:none"':'' ?>>
          <div class="m-ftitle" style="font-size:30px">Join our<br><em>network.</em></div>
          <div class="m-fsub">Submit your application for review.</div>
          <div class="m-pending"><strong>How it works</strong><p>Fill in your details and our team will review and activate your account.</p></div>
          <form method="POST" action="">
            <div class="m-row">
              <div class="m-fg"><label>Your Name</label><div class="m-iwrap"><i class="fas fa-user"></i><input class="m-input" type="text" name="vname" placeholder="Full name" required></div></div>
              <div class="m-fg"><label>Phone</label><div class="m-iwrap"><i class="fas fa-phone"></i><input class="m-input" type="tel" name="vphone" placeholder="+1 000" required></div></div>
            </div>
            <div class="m-fg"><label>Business Name</label><div class="m-iwrap"><i class="fas fa-store"></i><input class="m-input" type="text" name="business_name" placeholder="e.g. Le Bon Traiteur" required></div></div>
            <div class="m-fg">
              <label>Service Type</label>
              <div class="m-chips">
<?php foreach($svc_opts as $v=>[$ico,$lbl]): $sel=(isset($_POST['service_type'])&&$_POST['service_type']===$v)?'sel':''; ?>
                <button type="button" class="m-chip <?= $sel ?>" onclick="mChip(this,'<?= $v ?>')"><i class="fas <?= $ico ?>"></i> <?= $lbl ?></button>
              <?php endforeach; ?>
              </div>
              <input type="hidden" name="service_type" id="m-svc" value="<?= htmlspecialchars($_POST['service_type']??'') ?>">
            </div>
            <div class="m-fg"><label>Email Address</label><div class="m-iwrap"><i class="fas fa-envelope"></i><input class="m-input" type="email" name="vemail" placeholder="business@example.com" autocomplete="email" required></div></div>
            <div class="m-fg"><label>Password</label><div class="m-iwrap"><i class="fas fa-lock"></i><input class="m-input" type="password" name="vpassword" placeholder="Create a password" autocomplete="new-password" required></div></div>
            <button type="submit" name="vendor_apply" class="m-cta"><span><i class="fas fa-paper-plane"></i> Submit Application</span></button>
          </form>
        </div>
        <div class="m-home"><div class="m-home-bar"></div></div>
      </div>

    </div>
  </div>
</div>

<script>
const PHP_MOBILE = <?= $is_mobile_ua ? 'true' : 'false' ?>;
function detect() {
  const mob = window.innerWidth <= 768 || PHP_MOBILE;
  document.getElementById('layout-desktop').style.display = mob ? 'none' : 'flex';
  document.getElementById('layout-mobile').style.display  = mob ? 'flex' : 'none';
  document.body.style.overflow = 'hidden';
}
detect();
window.addEventListener('resize', detect);

const OPEN_TAB    = '<?= $open_tab ?>';
const OPEN_SUBTAB = '<?= $open_subtab ?>';

window.addEventListener('DOMContentLoaded', () => {
  if (OPEN_TAB === 'login')  mGo('login');
  if (OPEN_TAB === 'vendor') mGo('vendor');
  if (OPEN_TAB === 'login'   && OPEN_SUBTAB === 'join')   dTab('register');
  if (OPEN_TAB === 'vendor')  { dVendorShow(); if (OPEN_SUBTAB === 'vapply') dVTab('apply'); }
  <?php if ($success && in_array($open_tab,['splash','login'])): ?> dTab('login'); <?php endif; ?>
  <?php if ($error && isset($_POST['register'])): ?> dTab('register'); <?php endif; ?>
});

/* DESKTOP */
function dTab(p) {
  ['login','register','vendor'].forEach(n => document.getElementById('d-p-'+n).style.display='none');
  document.getElementById('d-tl').classList.remove('active');
  document.getElementById('d-tr').classList.remove('active');
  document.getElementById('d-p-'+p).style.display='block';
  if(p==='login')    document.getElementById('d-tl').classList.add('active');
  if(p==='register') document.getElementById('d-tr').classList.add('active');
}
function dVendorShow() {
  ['login','register','vendor'].forEach(n => document.getElementById('d-p-'+n).style.display='none');
  document.getElementById('d-tl').classList.remove('active');
  document.getElementById('d-tr').classList.remove('active');
  document.getElementById('d-p-vendor').style.display='block';
  document.querySelector('.d-right').scrollTop = 0;
}
function dVTab(t) {
  const L=document.getElementById('d-vp-login'), A=document.getElementById('d-vp-apply');
  const TL=document.getElementById('d-vtl'),     TA=document.getElementById('d-vta');
  if(t==='login'){L.style.display='block';A.style.display='none'; TL.classList.add('active');TA.classList.remove('active')}
  else           {L.style.display='none'; A.style.display='block';TA.classList.add('active');TL.classList.remove('active')}
}

/* MOBILE */
function mGo(id) {
  const cur=document.querySelector('.m-screen:not(.hidden):not(.slide-l)');
  if(cur) cur.classList.add('slide-l');
  const next=document.getElementById('m-'+id);
  next.classList.remove('hidden','slide-l');
  next.style.animation='mIn .35s ease both';
  setTimeout(()=>next.style.animation='',350);
}
function mBack(id) {
  document.querySelector('.m-screen:not(.hidden)')?.classList.add('hidden');
  document.getElementById('m-'+id).classList.remove('hidden','slide-l');
}
function mTab(g,t) {
  if(g==='u'){
    const si=document.getElementById('m-b-si'), jn=document.getElementById('m-b-jn');
    const ts=document.getElementById('m-tsi'),  tj=document.getElementById('m-tjn');
    if(t==='signin'){si.style.display='block';jn.style.display='none'; ts.classList.add('active');tj.classList.remove('active')}
    else            {si.style.display='none'; jn.style.display='block';tj.classList.add('active');ts.classList.remove('active')}
  } else {
    const vl=document.getElementById('m-b-vl'), va=document.getElementById('m-b-va');
    const tl=document.getElementById('m-tvl'),  ta=document.getElementById('m-tva');
    if(t==='vlogin'){vl.style.display='block';va.style.display='none'; tl.classList.add('active');ta.classList.remove('active')}
    else            {vl.style.display='none'; va.style.display='block';ta.classList.add('active');tl.classList.remove('active')}
  }
}
function mChip(el,val) {
  document.querySelectorAll('.m-chip').forEach(b=>b.classList.remove('sel'));
  el.classList.add('sel');
  document.getElementById('m-svc').value=val;
}
function tick(){
  const d=new Date(),h=d.getHours()%12||12,m=String(d.getMinutes()).padStart(2,'0');
  const el=document.getElementById('m-clock'); if(el) el.textContent=h+':'+m;
}
tick(); setInterval(tick,10000);
</script>
<style>@keyframes mIn{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}</style>
>>>>>>> 7c77f6d (Updated project files)
</body>
</html>