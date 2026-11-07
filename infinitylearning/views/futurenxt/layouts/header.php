<?php
declare(strict_types=1);

use App\FutureNxt\Auth;
use App\Helper;

$me = Auth::user();
$empCode = $me['emp_code'] ?? 'FNX-USER';
$fullName = $me['full_name'] ?? 'Partner';
$role = $me['role'] ?? 'employee';
$designation = $me['designation'] ?? ucfirst($role);
$photo = !empty($me['photo_path']) ? $me['photo_path'] : '/assets/img/avatar-default.png';
$activePage = $activePage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light" style="color-scheme: light !important;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'FutureNxt Portal') ?> — FutureNxt Enterprise</title>
    
    <!-- FutureNxt Brand Favicons -->
    <link rel="icon" type="image/png" sizes="64x64" href="/assets/img/futurenxt-favicon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/futurenxt-favicon.png">
    <link rel="apple-touch-icon" href="/assets/img/futurenxt-icon.png">
    <link rel="shortcut icon" href="/assets/img/futurenxt-favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- STRICT LIGHT MODE ENFORCEMENT -->
    <script>
    document.documentElement.setAttribute('data-theme', 'light');
    document.documentElement.style.colorScheme = 'light';
    try { localStorage.setItem('il-theme', 'light'); } catch(e) {}
    </script>

    <style>
        /* Force Light Mode regardless of OS/Browser dark mode */
        :root, html, html[data-theme="dark"], body {
            color-scheme: light !important;
            --surface-0: #FFF9F4 !important;
            --surface-1: #FFFFFF !important;
            --surface-2: #FFF7F0 !important;
            --surface-3: #FFEDDE !important;
            --il-text: #111827 !important;
            --il-text-2: #1F2937 !important;
            --il-muted: #4B5563 !important;
            --il-border: #F3DECB !important;
            --il-border-strong: #E8CDAF !important;
            --il-primary: #FF7A18 !important;
            --il-primary-soft: #FFE7D2 !important;

            --fnx-bg: #FFF9F4 !important;
            --fnx-surface: #FFFFFF !important;
            --fnx-border: #F3DECB !important;
            --fnx-text: #111827 !important;
            --fnx-muted: #4B5563 !important;
            --fnx-orange: #FF7A18 !important;
            --fnx-orange-hover: #E05300 !important;
            --fnx-orange-soft: #FFE7D2 !important;
            --fnx-black: #111827 !important;
            --fnx-navy: #111827 !important;
            
            background-color: #FFF9F4 !important;
            color: #111827 !important;
        }

        @media (prefers-color-scheme: dark) {
            :root, html, html[data-theme="dark"], body {
                color-scheme: light !important;
                background-color: #FFF9F4 !important;
                color: #111827 !important;
                --surface-0: #FFF9F4 !important;
                --surface-1: #FFFFFF !important;
                --surface-2: #FFF7F0 !important;
                --il-text: #111827 !important;
                --il-muted: #4B5563 !important;
                --il-border: #F3DECB !important;
                --fnx-bg: #FFF9F4 !important;
                --fnx-surface: #FFFFFF !important;
                --fnx-text: #111827 !important;
                --fnx-border: #F3DECB !important;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
            background: #FFF9F4 !important;
            color: #111827 !important;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navigation Header */
        .fnx-header {
            background: rgba(255, 255, 255, 0.98) !important;
            border-bottom: 1.5px solid var(--fnx-border) !important;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 4px rgba(43, 27, 18, 0.06);
        }
        .fnx-nav-container {
            max-width: 1380px;
            margin: 0 auto;
            padding: 0 20px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Top Left Logo */
        .fnx-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }
        .fnx-brand-img {
            height: 42px;
            width: auto;
            object-fit: contain;
            display: block;
        }
        .fnx-brand-tag {
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 999px;
            background: var(--fnx-orange-soft);
            color: var(--fnx-orange) !important;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* Navigation Links - Crisp Black, Active Orange */
        .fnx-nav-links {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .fnx-nav-links a {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13.5px;
            font-weight: 700 !important;
            color: #111827 !important;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .fnx-nav-links a:hover {
            color: var(--fnx-orange) !important;
            background: var(--fnx-orange-soft);
        }
        .fnx-nav-links a.active {
            color: #FFFFFF !important;
            background: linear-gradient(135deg, #FF7A18 0%, #FF9F45 100%) !important;
            box-shadow: 0 3px 10px rgba(255, 122, 24, 0.35);
        }

        /* Top Right User Profile Trigger */
        .fnx-user-menu {
            position: relative;
        }
        .fnx-user-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #FFFFFF !important;
            border: 1.5px solid var(--fnx-border) !important;
            padding: 5px 14px 5px 6px;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.2s;
            color: #111827 !important;
            box-shadow: 0 1px 3px rgba(43,27,18,0.06);
        }
        .fnx-user-btn:hover {
            border-color: var(--fnx-orange) !important;
            background: #FFFDFB !important;
        }
        .fnx-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--fnx-orange);
            background: #f1f5f9;
        }
        .fnx-user-info {
            display: flex;
            flex-direction: column;
            text-align: left;
            line-height: 1.2;
        }
        .fnx-user-name {
            font-size: 13px;
            font-weight: 800;
            color: #111827 !important;
        }
        .fnx-user-badge {
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            color: var(--fnx-orange) !important;
        }

        /* Dropdown Menu */
        .fnx-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 280px;
            background: #FFFFFF !important;
            border: 1.5px solid var(--fnx-border) !important;
            border-radius: 16px;
            box-shadow: 0 12px 32px rgba(43,27,18,0.12);
            display: none;
            flex-direction: column;
            z-index: 1000;
            overflow: hidden;
            animation: fnxDrop 0.2s ease-out;
        }
        @keyframes fnxDrop {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fnx-dropdown.show {
            display: flex;
        }
        .fnx-dropdown-head {
            padding: 16px;
            background: #FFF9F4 !important;
            border-bottom: 1px solid var(--fnx-border) !important;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .fnx-dropdown-head img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--fnx-orange);
        }
        .fnx-dropdown-items {
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            background: #FFFFFF !important;
        }
        .fnx-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 700;
            color: #111827 !important;
            text-decoration: none;
            transition: all 0.15s;
        }
        .fnx-dropdown-item:hover {
            background: #FFF9F4 !important;
            color: var(--fnx-orange) !important;
        }
        .fnx-dropdown-divider {
            height: 1px;
            background: var(--fnx-border);
            margin: 4px 8px;
        }
        .fnx-dropdown-logout {
            color: #DC2626 !important;
        }
        .fnx-dropdown-logout:hover {
            background: #FEF2F2 !important;
            color: #DC2626 !important;
        }

        /* Corporate Layout */
        .fnx-container {
            max-width: 1380px;
            margin: 24px auto 40px;
            padding: 0 20px;
            flex: 1;
            width: 100%;
            box-sizing: border-box;
        }

        /* Unified Component Card System */
        .fnx-card {
            background: #FFFFFF !important;
            border: 1.5px solid var(--fnx-border) !important;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(43,27,18,0.06);
            position: relative;
            color: #111827 !important;
        }
        .fnx-card h1, .fnx-card h2, .fnx-card h3, .fnx-card h4, .fnx-card strong {
            color: #111827 !important;
        }
        .fnx-grid-4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }
        .fnx-grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .fnx-grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }

        /* Unified Buttons */
        .fnx-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 999px;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.18s ease;
            font-family: inherit;
        }
        .fnx-btn-orange {
            background: linear-gradient(135deg, #FF7A18 0%, #FF9F45 100%) !important;
            color: #FFFFFF !important;
            box-shadow: 0 3px 10px rgba(255, 122, 24, 0.28);
        }
        .fnx-btn-orange:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(255, 122, 24, 0.38);
            color: #FFFFFF !important;
        }
        .fnx-btn-dark {
            background: #111827 !important;
            color: #FFFFFF !important;
        }
        .fnx-btn-dark:hover {
            background: var(--fnx-orange) !important;
            color: #FFFFFF !important;
        }
        .fnx-btn-outline {
            background: #FFFFFF !important;
            border: 1.5px solid #111827 !important;
            color: #111827 !important;
        }
        .fnx-btn-outline:hover {
            border-color: var(--fnx-orange) !important;
            color: var(--fnx-orange) !important;
            background: #FFFDFB !important;
        }

        /* Form Inputs */
        .fnx-input, .fnx-select, .fnx-textarea {
            width: 100%;
            background: #FFFFFF !important;
            border: 1.5px solid var(--fnx-border) !important;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13.5px;
            color: #111827 !important;
            font-family: inherit;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .fnx-input:focus, .fnx-select:focus, .fnx-textarea:focus {
            outline: none;
            border-color: var(--fnx-orange) !important;
            box-shadow: 0 0 0 3px rgba(255, 122, 24, 0.15) !important;
        }
        .fnx-form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #111827 !important;
            margin-bottom: 6px;
        }

        /* Tables */
        .fnx-table-wrap {
            overflow-x: auto;
            width: 100%;
        }
        .fnx-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
            background: #FFFFFF !important;
        }
        .fnx-table th {
            padding: 12px 14px;
            background: #FFF9F4 !important;
            color: #4B5563 !important;
            font-weight: 800;
            border-bottom: 1.5px solid var(--fnx-border) !important;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
        }
        .fnx-table td {
            padding: 14px;
            border-bottom: 1px solid var(--fnx-border) !important;
            vertical-align: middle;
            color: #111827 !important;
        }
        .fnx-table tr:hover td {
            background: #FFF9F4 !important;
        }

        /* Status Badges */
        .fnx-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 800;
            line-height: 1.2;
        }
        .fnx-badge-orange { background: var(--fnx-orange-soft); color: var(--fnx-orange) !important; }
        .fnx-badge-success { background: #DCFCE7; color: #16A34A !important; }
        .fnx-badge-danger { background: #FEE2E2; color: #DC2626 !important; }
        .fnx-badge-warning { background: #FEF3C7; color: #D97706 !important; }
        .fnx-badge-info { background: #DBEAFE; color: #2563EB !important; }
        
        /* Modal System - Guaranteed Viewport Fit & Internal Scroll */
        .fnx-modal-backdrop {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(17, 24, 39, 0.65);
            backdrop-filter: blur(4px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            overflow-y: auto;
            box-sizing: border-box;
        }
        @media (max-height: 800px) {
            .fnx-modal-backdrop {
                align-items: flex-start;
                padding-top: 16px;
                padding-bottom: 24px;
            }
        }
        .fnx-modal-dialog {
            background: #FFFFFF !important;
            border-radius: 18px;
            border: 1.5px solid var(--fnx-border) !important;
            box-shadow: 0 20px 50px rgba(17, 24, 39, 0.3);
            width: 100%;
            max-height: calc(100vh - 36px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: fnxDrop 0.2s ease-out;
            color: #111827 !important;
            position: relative;
            margin: auto 0;
        }
        .fnx-modal-dialog form {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            flex: 1 1 auto;
            min-height: 0;
            margin: 0;
        }
        .fnx-modal-header {
            padding: 16px 24px;
            border-bottom: 1px solid var(--fnx-border) !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #FFF9F4 !important;
            flex-shrink: 0;
        }
        .fnx-modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: #111827 !important;
        }
        .fnx-modal-close {
            background: none;
            border: none;
            font-size: 26px;
            cursor: pointer;
            color: #4B5563;
            line-height: 1;
            padding: 0 4px;
            transition: color 0.15s;
        }
        .fnx-modal-close:hover { color: #111827; }
        .fnx-modal-body {
            padding: 18px 24px;
            background: #FFFFFF !important;
            overflow-y: auto;
            flex: 1 1 auto;
            min-height: 0;
            max-height: calc(100vh - 170px);
            box-sizing: border-box;
        }
        .fnx-modal-footer {
            padding: 14px 24px;
            border-top: 1px solid var(--fnx-border) !important;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            background: #FFF9F4 !important;
            flex-shrink: 0;
        }
        .badge-fnx-code {
            font-family: 'JetBrains Mono', monospace;
            background: var(--fnx-orange-soft);
            color: var(--fnx-orange) !important;
            padding: 2px 7px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 11px;
        }

        /* Eye Mask Component */
        .fnx-mask-wrap {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .fnx-eye-btn {
            background: #FFFFFF;
            border: 1px solid var(--fnx-border);
            border-radius: 6px;
            padding: 2px 7px;
            font-size: 13px;
            cursor: pointer;
            color: #4B5563;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            user-select: none;
        }
        .fnx-eye-btn:hover {
            border-color: var(--fnx-orange);
            color: var(--fnx-orange);
            background: #FFFDFB;
        }
        .fnx-pw-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .fnx-pw-wrap .fnx-input {
            padding-right: 42px;
        }
        .fnx-pw-eye {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #4B5563;
            padding: 0;
            display: flex;
            align-items: center;
        }
        .fnx-pw-eye:hover { color: var(--fnx-orange); }

        /* Global Footer */
        .fnx-footer {
            background: #FFFFFF !important;
            border-top: 1.5px solid var(--fnx-border) !important;
            padding: 20px 0;
            margin-top: auto;
            font-size: 13px;
            color: #4B5563 !important;
        }
        .fnx-footer-inner {
            max-width: 1380px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .fnx-footer-links {
            display: flex;
            gap: 16px;
        }
        .fnx-footer-links a {
            color: #4B5563 !important;
            text-decoration: none;
            font-weight: 700;
        }
        .fnx-footer-links a:hover {
            color: var(--fnx-orange) !important;
        }

        /* Responsive Mobile & Tablet View System */
        @media (max-width: 991px) {
            .fnx-nav-container {
                height: auto !important;
                min-height: 56px;
                padding: 10px 14px 6px !important;
                flex-wrap: wrap !important;
                gap: 8px !important;
                justify-content: space-between !important;
            }
            .fnx-brand-tag {
                display: none !important;
            }
            .fnx-brand-img {
                height: 34px !important;
            }
            .fnx-user-btn {
                padding: 4px 10px 4px 4px !important;
            }
            .fnx-user-name {
                display: none !important;
            }
            .fnx-nav-links {
                order: 3;
                width: 100%;
                margin-top: 4px;
                display: flex !important;
                flex-wrap: nowrap !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
                gap: 6px !important;
                padding: 4px 2px 8px !important;
                scrollbar-width: none;
            }
            .fnx-nav-links::-webkit-scrollbar {
                display: none;
            }
            .fnx-nav-links a {
                padding: 6px 12px !important;
                font-size: 12.5px !important;
                white-space: nowrap !important;
                flex-shrink: 0 !important;
            }
            .fnx-container {
                margin: 14px auto 32px !important;
                padding: 0 12px !important;
            }
            .fnx-main-columns {
                grid-template-columns: 1fr !important;
                gap: 16px !important;
            }
            .fnx-welcome-banner {
                padding: 18px 16px !important;
                gap: 14px !important;
            }
            .fnx-welcome-actions {
                width: 100% !important;
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 8px !important;
            }
            .fnx-welcome-actions .fnx-btn {
                width: 100% !important;
                box-sizing: border-box !important;
                justify-content: center !important;
                font-size: 12px !important;
                padding: 8px 10px !important;
            }
            .fnx-grid-4 {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 12px !important;
            }
            .fnx-grid-2 {
                grid-template-columns: 1fr !important;
            }
            .fnx-card {
                padding: 16px !important;
                border-radius: 12px !important;
            }
        }

        @media (max-width: 520px) {
            .fnx-welcome-actions {
                grid-template-columns: 1fr !important;
            }
            .fnx-grid-4 {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>
<body>

<header class="fnx-header">
    <div class="fnx-nav-container">
        <!-- Top Left: Official FutureNxt Logo -->
        <a href="/futurenxt/dashboard" class="fnx-brand">
            <img src="/assets/img/futurenxt-logo.jpg" alt="FutureNxt" class="fnx-brand-img">
            <span class="fnx-brand-tag">Operations</span>
        </a>

        <!-- Middle: Navigation Tabs -->
        <ul class="fnx-nav-links">
            <li><a href="/futurenxt/dashboard" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a></li>
            <li><a href="/futurenxt/employees" class="<?= $activePage === 'employees' ? 'active' : '' ?>">👥 Partners &amp; Team</a></li>
            <li><a href="/futurenxt/tasks" class="<?= $activePage === 'tasks' ? 'active' : '' ?>">📋 Tasks</a></li>
            <li><a href="/futurenxt/daily-updates" class="<?= $activePage === 'daily_updates' ? 'active' : '' ?>">📝 Daily Updates</a></li>
            <li><a href="/futurenxt/notices" class="<?= $activePage === 'notices' ? 'active' : '' ?>">📢 Notices</a></li>
            <li><a href="/futurenxt/leaves" class="<?= $activePage === 'leaves' ? 'active' : '' ?>">🏖️ Leaves</a></li>
            <li><a href="/futurenxt/wfh" class="<?= $activePage === 'wfh' ? 'active' : '' ?>">🏠 WFH</a></li>
        </ul>

        <!-- Top Right: Notifications & Partner Photo Menu -->
        <div style="display:flex; align-items:center; gap:10px;">
            <a href="/futurenxt/notices" class="fnx-btn fnx-btn-outline" style="padding:6px 12px; font-size:13.5px; background:#FFFFFF; border-color:var(--fnx-border); display:inline-flex; align-items:center; gap:6px; font-weight:700; color:#111827 !important;" title="Company Notices & Announcements">
                <span>🔔</span> <span style="font-size:12px;">Notices</span>
            </a>

            <div class="fnx-user-menu">
            <div class="fnx-user-btn" id="fnxProfileTrigger">
                <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($fullName) ?>" class="fnx-avatar" onerror="this.onerror=null; this.src='/assets/img/avatar-default.png';">
                <div class="fnx-user-info">
                    <span class="fnx-user-name"><?= htmlspecialchars($fullName) ?></span>
                    <span class="fnx-user-badge"><?= htmlspecialchars($empCode) ?></span>
                </div>
                <span style="font-size: 10px; color:#4B5563;">▼</span>
            </div>

            <!-- Dropdown Menu -->
            <div class="fnx-dropdown" id="fnxProfileMenu">
                <div class="fnx-dropdown-head">
                    <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($fullName) ?>" onerror="this.onerror=null; this.src='/assets/img/avatar-default.png';">
                    <div style="overflow:hidden;">
                        <strong style="font-size: 14px; color:#111827; display:block; white-space:nowrap; text-overflow:ellipsis;"><?= htmlspecialchars($fullName) ?></strong>
                        <div style="display:flex; align-items:center; gap:6px; margin-top:2px;">
                            <span class="badge-fnx-code"><?= htmlspecialchars($empCode) ?></span>
                            <span style="font-size: 11px; color:#4B5563; font-weight:700; text-transform:uppercase;"><?= htmlspecialchars($role) ?></span>
                        </div>
                        <div style="font-size: 11.5px; color:#4B5563; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($designation) ?></div>
                    </div>
                </div>
                <div class="fnx-dropdown-items">
                    <a href="/futurenxt/profile" class="fnx-dropdown-item">
                        <span>👤</span> My Profile &amp; Documents
                    </a>
                    <a href="/futurenxt/tasks?my=1" class="fnx-dropdown-item">
                        <span>📋</span> My Active Tasks
                    </a>
                    <a href="/futurenxt/daily-updates" class="fnx-dropdown-item">
                        <span>📝</span> Write Daily Update
                    </a>
                    <a href="/futurenxt/notices" class="fnx-dropdown-item">
                        <span>📢</span> Notice Board &amp; Holidays
                    </a>
                    <a href="/futurenxt/leaves" class="fnx-dropdown-item">
                        <span>🏖️</span> Request Leave
                    </a>
                    <a href="/futurenxt/wfh" class="fnx-dropdown-item">
                        <span>🏠</span> Request WFH
                    </a>
                    <div class="fnx-dropdown-divider"></div>
                    <a href="/futurenxt-handler.php?action=logout" class="fnx-dropdown-item fnx-dropdown-logout">
                        <span>🚪</span> Log Out
                    </a>
                </div>
            </div>
        </div>
        </div>
    </div>
</header>
