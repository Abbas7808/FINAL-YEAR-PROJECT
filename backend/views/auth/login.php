<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="assets/js/theme.js?v=1780419041"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #eaf2ff;
            --primary-dark: #0f172a;
            --text-grey: #64748b;
            --bg-body: #f7faff;
            --bg-card: rgba(255, 255, 255, 0.92);
            --text-main: #0f172a;
        }

        [data-theme="dark"] {
            --bg-body: #0b1020;
            --bg-card: rgba(17, 24, 39, 0.92);
            --text-main: #e2e8f0;
            --primary-light: rgba(37, 99, 235, 0.14);
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(191, 219, 254, 0.55) 0%, transparent 35%),
                radial-gradient(circle at bottom right, rgba(167, 243, 208, 0.35) 0%, transparent 32%),
                linear-gradient(135deg, #f8fbff 0%, var(--bg-body) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            transition: 0.3s;
            padding: 24px;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(rgba(148, 163, 184, 0.16) 1px, transparent 1px);
            background-size: 28px 28px;
            opacity: 0.35;
            pointer-events: none;
        }
        .login-wrapper {
            display: flex;
            max-width: 1020px;
            width: 100%;
            background: var(--bg-card);
            border-radius: 30px;
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.14);
            overflow: hidden;
            margin: 0;
            position: relative;
            border: 1px solid rgba(148, 163, 184, 0.18);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }
        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background: var(--primary-light);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--primary);
            transition: 0.3s;
            z-index: 10;
        }
        .theme-toggle:hover {
            transform: rotate(15deg) scale(1.1);
        }
        .login-branding {
            flex: 1;
            background: linear-gradient(160deg, #04214f 0%, #0b2e69 52%, #2563eb 100%);
            padding: 72px 64px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            border-right: 1px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
        }
        .login-branding::before,
        .login-branding::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            opacity: 0.35;
        }
        .login-branding::before {
            width: 260px;
            height: 260px;
            background: rgba(255, 255, 255, 0.14);
            top: -80px;
            right: -80px;
        }
        .login-branding::after {
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.08);
            bottom: -50px;
            left: -40px;
        }
        .login-branding h1 {
            font-size: 38px;
            font-weight: 800;
            margin-bottom: 20px;
            color: #ffffff;
            line-height: 1.05;
        }
        .login-branding p {
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.88;
            max-width: 28rem;
        }
        .login-branding .logo-circle {
            width: 148px;
            height: 148px;
            background: rgba(255,255,255,0.96);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 40px;
            font-size: 40px;
            box-shadow: 0 18px 40px rgba(0,0,0,0.18);
            padding: 10px;
        }
        .login-form-side {
            flex: 1;
            padding: 72px 64px;
            background: rgba(255,255,255,0.98);
            color: var(--text-main);
        }
        .login-header {
            margin-bottom: 36px;
        }
        .login-header h2 {
            font-size: 30px;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 8px;
        }
        .login-header p {
            color: var(--text-grey);
            font-size: 15px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--primary-dark);
        }
        .input-group {
            position: relative;
            margin-bottom: 24px;
        }
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 15px;
        }
        .input-group input,
        .input-group select {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 1.5px solid rgba(148, 163, 184, 0.28);
            background: #ffffff;
            color: var(--text-main);
            border-radius: 16px;
            font-size: 15px;
            transition: 0.3s;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        .input-group input:focus,
        .input-group select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #0284c7 100%);
            transform: translateY(-2px);
            box-shadow: 0 14px 24px rgba(37, 99, 235, 0.22);
        }
        .error-alert {
            background: #fff7f7;
            color: #ff4757;
            padding: 12px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 24px;
            border: 1px solid #ffd6de;
        }
        @media (max-width: 768px) {
            body {
                padding: 12px;
            }
            .login-wrapper {
                flex-direction: column;
                border-radius: 24px;
            }
            .login-branding {
                padding: 36px 28px;
                text-align: center;
                align-items: center;
                border-right: none;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }
            .login-branding .logo-circle {
                margin-bottom: 20px;
                width: 110px;
                height: 110px;
            }
            .login-form-side {
                padding: 34px 28px 38px;
            }
            .login-branding h1 {
                font-size: 30px;
            }
            .login-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark Mode">
            <i class="fas fa-moon"></i>
        </div>
        <div class="login-branding">
            <div class="logo-circle">
                <?php if(isset($hospital) && !empty($hospital['image'])): ?>
                    <img src="<?= $hospital['image'] ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 16px;">
                <?php else: ?>
                    <img src="assets/logo.jpeg" alt="Mid-Nova Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 16px;">
                <?php endif; ?>
            </div>
            <h1>Mid-Nova Admin</h1>
            <p>Welcome back! Securely manage your healthcare facility with our comprehensive administration tools.</p>
        </div>
        <div class="login-form-side">
            <div class="login-header">
                <h2>Secure Login</h2>
                <p>Enter your credentials to access the portal</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="?route=auth/login<?= isset($_GET['slug']) ? '&slug='.$_GET['slug'] : '' ?>" method="POST">
                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label">Portal Role</label>
                    <div class="input-group">
                        <i class="fas fa-user-shield"></i>
                        <select name="role" required>
                            <option value="admin">Super Admin</option>
                            <option value="hospital_admin">Hospital Admin</option>
                            <option value="doctor">Doctor</option>
                            <option value="receptionist">Receptionist</option>
                            <option value="patient">Patient</option>
                        </select>
                        <i class="fas fa-chevron-down" style="left: auto; right: 15px; pointer-events: none;"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Username, Email or Phone Number</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="email" placeholder="Username, Email or Phone Number" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Sign In</button>
            </form>

            <div style="margin-top: 30px; text-align: center; color: var(--text-grey); font-size: 13px;">
                &copy; <?= date('Y') ?> Mid-Nova Smart Healthcare System. <br>All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
