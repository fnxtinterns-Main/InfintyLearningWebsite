<?php
declare(strict_types=1);

use App\FutureNxt\Auth;
use App\Helper;

if (Auth::check()) {
    Helper::redirect('/futurenxt/dashboard');
}

$error = $_GET['error'] ?? $_SESSION['fnx_error'] ?? null;
$success = $_GET['success'] ?? $_SESSION['fnx_success'] ?? null;
unset($_SESSION['fnx_error'], $_SESSION['fnx_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutureNxt Enterprise Login — Portal</title>
    <!-- FutureNxt Brand Favicons -->
    <link rel="icon" type="image/png" sizes="64x64" href="/assets/img/futurenxt-favicon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/futurenxt-favicon.png">
    <link rel="apple-touch-icon" href="/assets/img/futurenxt-icon.png">
    <link rel="shortcut icon" href="/assets/img/futurenxt-favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        :root {
            --fnx-bg: var(--surface-0, #FFF9F4);
            --fnx-surface: #FFFFFF;
            --fnx-border: var(--il-border, #F3DECB);
            --fnx-text: var(--il-text, #2B1B12);
            --fnx-muted: var(--il-muted, #7A5C4A);
            --fnx-orange: var(--il-primary, #FF7A18);
            --fnx-orange-hover: #E05300;
            --fnx-orange-soft: var(--il-primary-soft, #FFE7D2);
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--fnx-bg);
            color: var(--fnx-text);
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .login-wrap {
            width: 100%;
            max-width: 460px;
        }
        .card {
            background: var(--fnx-surface);
            border: 1.5px solid var(--fnx-border);
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 10px 30px rgba(43, 27, 18, 0.08);
        }
        .brand-header {
            text-align: center;
            margin-bottom: 26px;
        }
        .brand-logo-img {
            height: 54px;
            width: auto;
            object-fit: contain;
            margin: 0 auto 14px;
            display: block;
        }
        .card-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--fnx-text);
            margin: 0 0 6px;
            letter-spacing: -0.02em;
        }
        .card-desc {
            font-size: 13.5px;
            color: var(--fnx-muted);
            margin: 0;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 7px;
            color: var(--fnx-text);
        }
        .form-input {
            width: 100%;
            background: #FFFFFF;
            border: 1.5px solid var(--fnx-border);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14.5px;
            color: var(--fnx-text);
            font-family: inherit;
            transition: all 0.2s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--fnx-orange);
            box-shadow: 0 0 0 3px rgba(255, 122, 24, 0.15);
        }
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #FF7A18 0%, #FF9F45 100%);
            color: #fff;
            border: none;
            border-radius: 999px;
            padding: 13px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(255, 122, 24, 0.32);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            margin-top: 8px;
            font-family: inherit;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(255, 122, 24, 0.42);
        }
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        .alert-error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }
        .alert-success {
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }
        .quick-roles {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--fnx-border);
            font-size: 12px;
            color: var(--fnx-muted);
            text-align: center;
            line-height: 1.6;
        }
        .quick-roles code {
            background: var(--fnx-orange-soft);
            color: var(--fnx-orange);
            padding: 2px 5px;
            border-radius: 4px;
            font-weight: 700;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: var(--fnx-muted);
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover { color: var(--fnx-orange); }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="card">
            <div class="brand-header">
                <img src="/assets/img/futurenxt-logo.jpg" alt="FutureNxt" class="brand-logo-img">
                <h1 class="card-title">Enterprise Management</h1>
                <p class="card-desc">Sign in with your Email or Partner ID</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <span>⚠️</span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <span>✓</span>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="/futurenxt-handler.php?action=login" autocomplete="off">
                <div class="form-group">
                    <label class="form-label" for="login_identifier">Corporate Email or Partner ID</label>
                    <input type="text" id="login_identifier" name="login_identifier" class="form-input" 
                           placeholder="e.g. fnxt.interns@gmail.com or FNX-CEO" required autofocus>
                </div>

                <div class="form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:7px;">
                        <label class="form-label" for="password" style="margin:0;">Password</label>
                    </div>
                    <div style="position:relative; display:flex; align-items:center;">
                        <input type="password" id="password" name="password" class="form-input" 
                               placeholder="••••••••••••" style="padding-right:44px;" required>
                        <button type="button" onclick="fnxTogglePassword('password', this)" title="Show password"
                                style="position:absolute; right:12px; background:none; border:none; cursor:pointer; font-size:17px; padding:0; color:#4B5563;">
                            👁️
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    Sign In to Portal →
                </button>
            </form>

            <div class="quick-roles">
                <strong>Executive Access:</strong><br>
                Admin (<code>fnxt.interns@gmail.com</code>) · CEO (<code>FNX-CEO</code>) · CTO (<code>FNX-CTO</code>)<br>
                <span style="font-size:11px; color:var(--fnx-muted); margin-top:4px; display:inline-block;">
                    🔒 All partners can update their password anytime inside <strong>My Profile</strong>.
                </span>
            </div>
        </div>

        <a href="https://infintylearning.net" class="back-link">
            ← Return to Infinity Learning Main Site
        </a>
    </div>
    <script>
    function fnxTogglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '🙈';
            btn.title = 'Hide password';
        } else {
            input.type = 'password';
            btn.innerHTML = '👁️';
            btn.title = 'Show password';
        }
    }
    </script>
</body>
</html>
