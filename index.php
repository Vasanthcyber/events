
Copy

<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['user_email'] = $user['email'];
                
                switch ($user['user_type']) {
                    case 'admin':
                        redirect('admin/dashboard.php');
                        break;
                    case 'vendor':
                        redirect('vendor/dashboard.php');
                        break;
                    default:
                        redirect('user/dashboard.php');
                        break;
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
    
    if (isset($_POST['register'])) {
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
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
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
</body>
</html>
