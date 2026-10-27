<?php
use App\Session;
use App\Helper;
use App\Auth;
use App\Notification;

$user = Auth::user();
$role = Auth::role();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$isActive = fn(string $p) => $currentPath === $p ? ' class="active"' : '';

$displayName = '';
$avatarUrl   = null;
if ($user) {
    if ($role === 'student') {
        $p = Auth::findStudentByUserId((int)$user['id']);
        $displayName = $p['full_name'] ?? 'Student';
        $avatarUrl   = $p['profile_image'] ?? null;
    } elseif ($role === 'teacher') {
        $p = Auth::findTeacherByUserId((int)$user['id']);
        $displayName = $p['full_name'] ?? 'Teacher';
        $avatarUrl   = $p['profile_image'] ?? null;
    } else {
        $displayName = Auth::isSchoolAdmin() ? 'Admin' : 'Administrator';
    }
}
$initial = $displayName ? mb_strtoupper(mb_substr($displayName, 0, 1)) : '?';

$unread = 0;
$notifications = [];
if ($user) {
    $unread = Notification::unreadCount((int)$user['id']);
    $notifications = Notification::latest((int)$user['id'], 10);
}
$schoolBranding = null;
$isAiverse = false;
if ($user && !empty($user['school_id'])) {
    try {
        $sDb = \App\Database::connect();
        $sStmt = $sDb->prepare("SELECT * FROM schools WHERE id = ? LIMIT 1");
        $sStmt->execute([(int)$user['school_id']]);
        $schoolBranding = $sStmt->fetch() ?: null;
        if ($schoolBranding) {
            $sName = strtoupper(trim($schoolBranding['name'] ?? ''));
            $sCode = strtoupper(trim($schoolBranding['code'] ?? ''));
            $isAiverse = ($sName === 'AIVERSE' || $sCode === 'AIVERSE' || str_contains($sName, 'AIVERSE'));
        }
    } catch (\Throwable $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= Helper::e($pageTitle ?? 'Infinity Learning — Learn. Grow. Achieve.') ?></title>
<meta name="description" content="Infinity Learning — unified platform for academics, AI learning, activities, competitions and achievements.">
<link rel="icon" href="/assets/img/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,400&family=JetBrains+Mono:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,400&family=JetBrains+Mono:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</noscript>
<link rel="stylesheet" href="/assets/css/style.css?v=3.0">
<?php if (!empty($schoolBranding['brand_primary_color'])): ?>
<style>
:root {
    --brand-primary: <?= htmlspecialchars($schoolBranding['brand_primary_color']) ?> !important;
    --il-primary: <?= htmlspecialchars($schoolBranding['brand_primary_color']) ?> !important;
    <?php if (!empty($schoolBranding['brand_secondary_color'])): ?>
    --brand-secondary: <?= htmlspecialchars($schoolBranding['brand_secondary_color']) ?> !important;
    --il-secondary: <?= htmlspecialchars($schoolBranding['brand_secondary_color']) ?> !important;
    <?php endif; ?>
}
</style>
<?php endif; ?>
<script>
(function() {
    try {
        const saved = localStorage.getItem('il-theme');
        if (saved === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    } catch(e) {}
})();
</script>
<style>
/* Prevent document-level horizontal overflow and navbar blowout */
html, body {
    max-width: 100vw;
    overflow-x: hidden !important;
}
header,
.navbar {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    height: 64px !important;
    min-height: 64px !important;
    max-height: 64px !important;
    width: 100% !important;
    max-width: 100vw !important;
    box-sizing: border-box !important;
    padding: 0 1rem !important;
    overflow: hidden !important;
    overscroll-behavior: none !important;
}
.nav-brand {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    flex-shrink: 0 !important;
}
.nav-actions {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    flex-shrink: 0 !important;
    margin-left: auto !important;
}
@media (min-width: 821px) {
    .nav-links {
        display: flex !important;
        align-items: center !important;
        gap: 0.2rem !important;
        flex: 1 1 auto !important;
        min-width: 0 !important;
        margin: 0 0.5rem !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
        white-space: nowrap !important;
        height: 100% !important;
    }
    .nav-links::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    .nav-links a {
        padding: 0.32rem 0.62rem !important;
        font-size: 0.84rem !important;
        font-weight: 600 !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }
}

/* Light / Dark Mode Toggle Button */
.theme-toggle-btn {
    background: var(--surface-1);
    border: 1.5px solid var(--il-border);
    border-radius: 999px;
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.2s ease;
    color: var(--il-text);
    padding: 0;
    margin: 0 4px;
    outline: none;
    flex-shrink: 0;
}
.theme-toggle-btn:hover {
    border-color: var(--il-primary);
    transform: scale(1.08);
}
[data-theme="dark"] .theme-sun-icon { display: inline-block; }
[data-theme="dark"] .theme-moon-icon { display: none; }
[data-theme="light"] .theme-sun-icon { display: none; }
[data-theme="light"] .theme-moon-icon { display: inline-block; }
html:not([data-theme]) .theme-sun-icon { display: none; }
html:not([data-theme]) .theme-moon-icon { display: inline-block; }

/* School Name Pill in Top Navbar (High Contrast in both Light & Dark modes) */
.school-nav-pill {
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.03em;
    padding: 4px 12px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    line-height: 1.4;
    transition: all 0.2s ease;
}
html[data-theme="light"] .school-nav-pill,
:root:not([data-theme="dark"]) .school-nav-pill {
    background: #FFFFFF !important;
    color: #111827 !important;
    border: 2px solid #D1D5DB !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
}
html[data-theme="dark"] .school-nav-pill {
    background: rgba(255, 255, 255, 0.16) !important;
    color: #FFFFFF !important;
    border: 1.5px solid rgba(255, 255, 255, 0.35) !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3) !important;
}

/* School Crest in Navbar (Guaranteed Visibility against Light/Dark Backgrounds) */
.school-crest-nav {
    height: 38px;
    max-width: 140px;
    border-radius: 8px;
    object-fit: contain;
    padding: 3px 6px;
    background: #FFFFFF;
    border: 1.5px solid #E5E7EB;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
    display: inline-block;
    vertical-align: middle;
}
html[data-theme="dark"] .school-crest-nav {
    background: #1E293B;
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
}

/* Nav Actions Container (theme toggle, cmd palette, user menu, notif bell) */
.nav-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    margin-left: auto;
}
@media (max-width: 820px) {
    .nav-actions {
        margin-left: auto;
        gap: 6px;
    }
}


/* Enhanced Form Styles Across All Pages & Modals */
.form-control, .input, input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="date"], input[type="time"], input[type="file"], select, textarea {
    background: var(--surface-1) !important;
    color: var(--il-text) !important;
    border: 1.5px solid var(--il-border) !important;
    border-radius: 10px !important;
    padding: 8px 12px !important;
    font-size: 13.5px !important;
    transition: border-color 0.15s, box-shadow 0.15s !important;
    box-sizing: border-box !important;
}
.form-control:focus, .input:focus, select:focus, textarea:focus {
    border-color: var(--il-primary) !important;
    box-shadow: 0 0 0 3px rgba(255, 122, 24, 0.2) !important;
    background: var(--surface-2) !important;
}
select option {
    background: var(--surface-1) !important;
    color: var(--il-text) !important;
}

/* 4.A: Universal Command Palette (Ctrl + K) */
.cmd-trigger-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--surface-1);
    border: 1.5px solid var(--il-border);
    border-radius: 999px;
    padding: 5px 10px;
    cursor: pointer;
    font-size: 13px;
    color: var(--il-muted);
    transition: all 0.2s ease;
    margin: 0 4px;
}
.cmd-trigger-btn:hover {
    border-color: var(--il-primary);
    color: var(--il-text);
    background: var(--surface-2);
}
.cmd-kbd-badge {
    font-size: 10px;
    font-weight: 700;
    font-family: inherit;
    background: var(--surface-2);
    border: 1px solid var(--il-border);
    border-radius: 4px;
    padding: 1px 5px;
    color: var(--il-muted);
}
.cmd-palette-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    z-index: 10000;
    align-items: flex-start;
    justify-content: center;
    padding-top: 10vh;
    backdrop-filter: blur(4px);
}
.cmd-palette-modal {
    width: 100%;
    max-width: 620px;
    background: var(--surface-0);
    border: 1px solid var(--il-border);
    border-radius: 16px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.35);
    overflow: hidden;
    animation: cmdPop 0.15s ease-out;
}
@keyframes cmdPop {
    from { opacity: 0; transform: scale(0.96) translateY(-10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.cmd-palette-search-box {
    display: flex;
    align-items: center;
    padding: 14px 18px;
    border-bottom: 1px solid var(--il-border);
    gap: 12px;
}
.cmd-palette-input {
    flex: 1;
    background: transparent !important;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    font-size: 16px !important;
    color: var(--il-text) !important;
    padding: 0 !important;
}
.cmd-palette-results {
    max-height: 380px;
    overflow-y: auto;
    padding: 8px;
}
.cmd-group-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--il-muted);
    padding: 8px 12px 4px;
}
.cmd-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    border-radius: 10px;
    text-decoration: none;
    color: var(--il-text);
    font-size: 13.5px;
    transition: background 0.12s ease;
    cursor: pointer;
}
.cmd-item:hover, .cmd-item.selected {
    background: var(--surface-2);
    color: var(--il-primary);
}
.cmd-item-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.cmd-item-icon {
    font-size: 17px;
    width: 24px;
    text-align: center;
}
.cmd-palette-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    border-top: 1px solid var(--il-border);
    background: var(--surface-1);
    font-size: 11px;
    color: var(--il-muted);
}

/* 4.B: Breadcrumbs */
.breadcrumb-bar {
    background: var(--surface-0);
    border-bottom: 1px solid var(--il-border);
    padding: 8px 0;
    font-size: 12px;
}
.breadcrumb-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 16px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
}
.breadcrumb-item {
    color: var(--il-muted);
    text-decoration: none;
    transition: color 0.15s ease;
}
.breadcrumb-item:hover {
    color: var(--il-primary);
}
.breadcrumb-item.active {
    color: var(--il-text);
    font-weight: 600;
}
.breadcrumb-sep {
    color: var(--il-muted);
    opacity: 0.5;
    user-select: none;
}

/* 4.B: Skeleton Loaders */
.skeleton {
    background: linear-gradient(90deg, var(--surface-2) 25%, var(--surface-1) 50%, var(--surface-2) 75%);
    background-size: 200% 100%;
    animation: skeletonGlow 1.6s infinite ease-in-out;
    border-radius: 6px;
}
@keyframes skeletonGlow {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.skeleton-text {
    height: 14px;
    margin-bottom: 8px;
    width: 100%;
}
.skeleton-text.short { width: 55%; }
.skeleton-box { height: 120px; width: 100%; }
.skeleton-circle { width: 44px; height: 44px; border-radius: 50%; }
</style>
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">
        <a href="/" aria-label="Infinity Learning home" class="brand-link" style="display:flex; align-items:center; gap:8px;">
            <?php if (!empty($schoolBranding['crest_logo'])): ?>
                <img src="<?= htmlspecialchars($schoolBranding['crest_logo']) ?>" alt="<?= htmlspecialchars($schoolBranding['name']) ?>" class="school-crest-nav" onerror="this.style.display='none'">
            <?php endif; ?>
            <img src="/assets/img/logo.png" alt="Infinity Learning"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
            <span class="brand-fallback" style="display:none">
                <span class="brand-infinity">Infinity</span>
                <span class="brand-learning">L E A R N I N G</span>
            </span>
            <?php if (!empty($schoolBranding['name'])): ?>
                <span class="school-nav-pill">
                    <?= htmlspecialchars($schoolBranding['name']) ?>
                </span>
            <?php endif; ?>
        </a>
    </div>
    <button class="nav-toggle" aria-label="Toggle menu">☰</button>
    <div class="nav-links">
        <?php if ($isAiverse): ?>
            <?php if ($role === 'admin'): ?>
                <a href="/school"<?= $isActive('/school') ?>>📊 Console</a>
                <a href="/school/courses"<?= $isActive('/school/courses') ?>>🤖 AI Courses</a>
                <a href="/school/bundles"<?= $isActive('/school/bundles') ?>>📦 Bundles &amp; Offers</a>
                <a href="/school/banners"<?= $isActive('/school/banners') ?>>📢 Promo Banners</a>
                <a href="/school/fees"<?= $isActive('/school/fees') ?>>💳 Fees &amp; Payments</a>
                <a href="/school/students"<?= $isActive('/school/students') ?>>👩‍🎓 Scholars</a>
            <?php else: ?>
                <a href="/dashboard"<?= $isActive('/dashboard') ?>>🚀 AIverse Hub</a>
                <a href="/dashboard#bundles">🎁 Bundles &amp; Offers</a>
                <a href="/dashboard#courses">🤖 AI Courses</a>
                <a href="/student/fees"<?= $isActive('/student/fees') ?>>💳 Course Fees</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="/chess"<?= $isActive('/chess') ?>>♟️ Chess</a>
            <a href="/counseling"<?= $isActive('/counseling') ?>>Counseling</a>
            <a href="/activities"<?= $isActive('/activities') ?>>Activities</a>
            <a href="/lessons"<?= $isActive('/lessons') ?>>Learn</a>

            <?php if ($user): ?>
                <a href="/dashboard"<?= $isActive('/dashboard') ?>>Dashboard</a>
                <?php if ($role === 'student'): ?>
                    <a href="/student/assessments"<?= $isActive('/student/assessments') ?>>📝 Tests & HW</a>
                    <a href="/student/timetable"<?= $isActive('/student/timetable') ?>>🗓️ Weekly Routine</a>
                    <a href="/student/doubts"<?= $isActive('/student/doubts') ?>>💬 Doubts</a>
                    <a href="/student/fees"<?= $isActive('/student/fees') ?>>💳 Fees</a>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
                    <a href="<?= Auth::isSchoolAdmin() ? '/school' : '/admin' ?>"<?= $isActive('/admin') ?>>
                        <?= Auth::isSchoolAdmin() ? 'School' : 'Admin' ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="nav-actions" style="display:flex; align-items:center; gap:8px; flex-shrink:0; margin-left:auto;">
        <?php if ($user): ?>
            <div class="user-menu notif-menu" id="notifMenu">
                <button class="notif-trigger" aria-haspopup="true" aria-expanded="false" type="button" aria-label="Notifications">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <?php if ($unread > 0): ?>
                        <span class="notif-badge"><?= $unread > 9 ? '9+' : $unread ?></span>
                    <?php endif; ?>
                </button>

                <div class="user-dropdown notif-dropdown" role="menu">
                    <div class="notif-head">
                        <div class="notif-head-title">
                            <strong>Notifications</strong>
                            <?php if ($unread > 0): ?>
                                <span class="notif-count"><?= $unread > 99 ? '99+' : $unread ?> new</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($unread > 0): ?>
                            <form method="post" action="/notifications-mark-all-read-handler.php" class="notif-mark-all-form">
                                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                <button type="submit" class="notif-mark-all" title="Mark all as read">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2.5"
                                         stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                        <polyline points="22 12 12 22 8 18"></polyline>
                                    </svg>
                                    <span>Mark all read</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if (!$notifications): ?>
                        <div class="notif-empty">
                            <div style="font-size:1.8rem">🔔</div>
                            <p class="muted" style="font-size:.85rem">No notifications yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="notif-list">
                            <?php foreach ($notifications as $n): ?>
                                <a class="notif-item <?= $n['is_read'] ? '' : 'notif-unread' ?>"
                                   href="<?= $n['link'] ? Helper::e($n['link']) : '#' ?>"
                                   data-nid="<?= (int)$n['id'] ?>">
                                    <span class="notif-icon"><?= Notification::iconFor($n['type']) ?></span>
                                    <div class="notif-body">
                                        <div class="notif-title"><?= Helper::e($n['title']) ?></div>
                                        <?php if (!empty($n['body'])): ?>
                                            <div class="notif-text"><?= Helper::e(mb_substr($n['body'], 0, 100)) ?></div>
                                        <?php endif; ?>
                                        <div class="notif-time"><?= Helper::timeAgo($n['created_at']) ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="user-menu" id="userMenu">
                <button class="user-trigger" aria-haspopup="true" aria-expanded="false" type="button">
                    <?php if ($avatarUrl): ?>
                        <img class="avatar" src="<?= Helper::e($avatarUrl) ?>" alt="">
                    <?php else: ?>
                        <span class="avatar avatar-initial" data-role="<?= Helper::e($role) ?>"><?= Helper::e($initial) ?></span>
                    <?php endif; ?>
                    <svg class="chev" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>

                <div class="user-dropdown" role="menu">
                    <div class="user-head">
                        <div class="user-head-avatar">
                            <?php if ($avatarUrl): ?>
                                <img class="avatar avatar-lg" src="<?= Helper::e($avatarUrl) ?>" alt="">
                            <?php else: ?>
                                <span class="avatar avatar-lg avatar-initial" data-role="<?= Helper::e($role) ?>"><?= Helper::e($initial) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="user-head-text">
                            <div class="user-name"><?= Helper::e($displayName) ?></div>
                            <div class="user-email"><?= Helper::e($user['email']) ?></div>
                            <span class="badge badge-brand user-role" <?= $isAiverse ? 'style="background:linear-gradient(135deg,#6366f1,#a855f7); color:#fff; border:none;"' : '' ?>>
                                <?= $isAiverse ? '🚀 AIVERSE Scholar' : Helper::e(ucfirst($role)) ?>
                            </span>
                        </div>
                    </div>
                    <div class="user-sep"></div>
                    <?php if ($isAiverse): ?>
                        <a class="user-item" href="/dashboard" role="menuitem"><span class="ico">🚀</span> AIverse Hub</a>
                        <a class="user-item" href="/dashboard#courses" role="menuitem"><span class="ico">🤖</span> My AI Courses</a>
                        <a class="user-item" href="/student/fees" role="menuitem"><span class="ico">💳</span> Course Fees & Receipts</a>
                        <a class="user-item" href="/profile" role="menuitem"><span class="ico">👤</span> My Profile</a>
                    <?php else: ?>
                        <a class="user-item" href="/profile" role="menuitem"><span class="ico">👤</span> My Profile</a>
                        <a class="user-item" href="/dashboard" role="menuitem"><span class="ico">📊</span> Dashboard</a>
                        <a class="user-item" href="/chess" role="menuitem"><span class="ico">♟️</span> Chess Academy</a>
                        <?php if ($role === 'student'): ?>
                            <a class="user-item" href="/student/doubts" role="menuitem"><span class="ico">💬</span> My Doubts Portal</a>
                            <a class="user-item" href="/student?id=<?= (int)$user['id'] ?>" role="menuitem"><span class="ico">🌱</span> Development Profile</a>
                            <a class="user-item" href="/parent-pass" role="menuitem"><span class="ico">📱</span> Parent Progress Pass</a>
                            <a class="user-item" href="/my-certificates" role="menuitem"><span class="ico">🏅</span> My Certificates</a>
                            <a class="user-item" href="/my-counseling" role="menuitem"><span class="ico">🧭</span> My Counseling</a>
                        <?php endif; ?>
                        <?php if ($role === 'admin'): ?>
                            <a class="user-item" href="<?= Auth::isSchoolAdmin() ? '/school' : '/admin' ?>" role="menuitem">
                                <span class="ico">⚙️</span> <?= Auth::isSchoolAdmin() ? 'School Panel' : 'Admin Panel' ?>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="user-sep"></div>
                    <a class="user-item user-item-danger" href="/logout" role="menuitem"><span class="ico">↩️</span> Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="/login"<?= $isActive('/login') ?>>Login</a>
            <a href="/register?type=student" class="btn btn-primary btn-sm">Sign Up</a>
        <?php endif; ?>

        <button type="button" class="cmd-trigger-btn" id="cmdPaletteBtn" onclick="openCommandPalette()" title="Quick Command Palette & Search (Ctrl + K)">
            <span>🔍</span>
            <span class="cmd-kbd-badge">Ctrl K</span>
        </button>

        <button type="button" class="theme-toggle-btn" id="themeToggleBtn" aria-label="Toggle theme mode" title="Toggle Light / Dark Mode">
            <span class="theme-sun-icon">☀️</span>
            <span class="theme-moon-icon">🌙</span>
        </button>
    </div>
</nav>

<?php
// 4.B: Breadcrumbs Engine
$crumbs = [];
$cleanUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$rawSegments = array_values(array_filter(explode('/', trim($cleanUri, '/'))));
if (!empty($rawSegments) && $cleanUri !== '/') {
    $crumbs[] = ['title' => '🏠 Home', 'url' => '/'];
    $segLabels = [
        'teacher'        => 'Teacher Hub',
        'school'         => 'Admin',
        'student'        => 'Student Portal',
        'admin'          => 'Admin Panel',
        'questions'      => 'Question Bank',
        'question-paper' => 'Question Paper Builder',
        'timetable'      => 'Timetable & Routine',
        'fees'           => 'Fee Management',
        'fee-receipt'    => 'Fee Receipt',
        'report-card'    => 'Report Card',
        'parent-pass'    => 'Parent Progress Pass',
        'assessments'    => 'Assessments & Tests',
        'batches'        => 'Batches',
        'whiteboard'     => 'Digital Whiteboard',
        'live'           => 'Live Room',
        'chess'          => 'Rising Rooks Chess',
        'branding'       => 'School White-Label',
        'profile'        => 'My Profile',
        'dashboard'      => 'Dashboard',
        'doubts'         => 'Ask a Doubt',
    ];
    $accum = '';
    $tot = count($rawSegments);
    foreach ($rawSegments as $idx => $seg) {
        $accum .= '/' . $seg;
        $title = $segLabels[$seg] ?? ucwords(str_replace(['-', '_'], ' ', $seg));
        $crumbs[] = [
            'title' => $title,
            'url'   => ($idx === $tot - 1) ? null : $accum
        ];
    }
}
?>
<?php if (!empty($crumbs)): ?>
<nav class="breadcrumb-bar no-print" aria-label="Breadcrumb">
    <div class="breadcrumb-container">
        <?php foreach ($crumbs as $ci => $c): ?>
            <?php if ($ci > 0): ?><span class="breadcrumb-sep">›</span><?php endif; ?>
            <?php if ($c['url']): ?>
                <a href="<?= Helper::e($c['url']) ?>" class="breadcrumb-item"><?= Helper::e($c['title']) ?></a>
            <?php else: ?>
                <span class="breadcrumb-item active"><?= Helper::e($c['title']) ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</nav>
<?php endif; ?>

<!-- 4.A: UNIVERSAL COMMAND PALETTE (Ctrl + K) -->
<div id="cmdPaletteBackdrop" class="cmd-palette-backdrop" onclick="handleCmdBackdropClick(event)">
    <div class="cmd-palette-modal">
        <div class="cmd-palette-search-box">
            <span style="font-size:18px;">🔍</span>
            <input type="text" id="cmdSearchInput" class="cmd-palette-input" placeholder="Type a command or jump to feature (e.g. Questions, Routine, Whiteboard, Fees)..." autocomplete="off" oninput="filterCmdList()">
            <span class="cmd-kbd-badge" style="font-size:11px; cursor:pointer;" onclick="closeCommandPalette()">ESC</span>
        </div>
        <div class="cmd-palette-results" id="cmdResultsList">
            <!-- Items populated via JS based on role -->
        </div>
        <div class="cmd-palette-footer">
            <span>Navigation: <kbd>↑</kbd> <kbd>↓</kbd> to move, <kbd>↵ Enter</kbd> to select</span>
            <span><kbd>ESC</kbd> to close</span>
        </div>
    </div>
</div>

<script>
const CMD_ITEMS = [
    // Teacher Commands
    <?php if ($role === 'teacher'): ?>
    { icon: '✨', title: 'AI Question Generator', desc: 'Create curriculum-aligned exam questions instantly', url: '/teacher/questions?action=ai', group: 'Actions', keywords: 'ai gemini exam test mcq question generator' },
    { icon: '📄', title: 'Question Paper Builder', desc: 'Printable editable question paper with 1-click grade switcher', url: '/teacher/question-paper', group: 'Actions', keywords: 'paper exam test print class format template' },
    { icon: '❓', title: 'Teacher Question Bank', desc: 'Curriculum-wise question vault and repository', url: '/teacher/questions', group: 'Academics', keywords: 'questions bank vault mcq repository' },
    { icon: '📝', title: 'Assessments & Homework', desc: 'Assign unit tests, mid-terms, and homework', url: '/teacher/assessments', group: 'Academics', keywords: 'tests exam homework unit terms' },
    { icon: '🗓️', title: 'Class Routine & Timetable', desc: 'Weekly school timetable schedule', url: '/teacher/timetable', group: 'Academics', keywords: 'timetable routine schedule classes periods' },
    { icon: '💳', title: 'Student Fees', desc: 'Track fee payment status and monthly collection', url: '/teacher/fees', group: 'Academics', keywords: 'fee payment receipt money tuition' },
    { icon: '💬', title: 'Student Doubts Feed', desc: 'Answer student questions and academic queries', url: '/teacher/doubts', group: 'Academics', keywords: 'doubt feed questions help student' },
    { icon: '🎨', title: 'Interactive Live Whiteboard', desc: 'Canvas for drawing, diagrams, and live problem-solving', url: '/live/whiteboard', group: 'Live Rooms', keywords: 'whiteboard draw canvas math diagram teaching' },
    { icon: '♟️', title: 'Chess Coaching Hub', desc: 'Rising Rooks Chess Academy exercises and games', url: '/teacher/chess', group: 'Live Rooms', keywords: 'chess coach rooks academy live' },
    <?php endif; ?>

    // School Admin Commands
    <?php if (in_array($role, ['school', 'admin'], true)): ?>
    { icon: '🗓️', title: 'School Timetable Planner', desc: 'Manage class schedule, periods, and timing', url: '/school/timetable', group: 'Academics', keywords: 'timetable routine schedule periods school planner' },
    { icon: '🔄', title: 'Teacher Substitution Finder', desc: 'Find free teachers for any period instantly', url: '/school/timetable?action=sub', group: 'Academics', keywords: 'substitution free teachers relief period' },
    { icon: '💳', title: 'Fee Management & Invoices', desc: 'Student tuition fees, payments, and invoices', url: '/school/fees', group: 'Finance', keywords: 'fee invoice receipt tuition payment bank' },
    { icon: '📊', title: 'Report Cards & Gradebook', desc: 'Generate printable term marksheet and certificates', url: '/school/students', group: 'Academics', keywords: 'report card marksheet term grades student' },
    { icon: '🎨', title: 'White-Label Branding', desc: 'Customize school name, crest, logo, and theme color', url: '/school/branding', group: 'Admin', keywords: 'branding white label logo theme crest school' },
    { icon: '👨‍🏫', title: 'Manage Teachers', desc: 'Staff directory and teacher assignments', url: '/school/teachers', group: 'Admin', keywords: 'teachers staff faculty users' },
    { icon: '🎓', title: 'Manage Students', desc: 'Enrollment, classes, sections, and student roster', url: '/school/students', group: 'Admin', keywords: 'students admission classes roster enroll' },
    { icon: '🎨', title: 'Interactive Live Whiteboard', desc: 'Canvas for diagramming and teaching', url: '/live/whiteboard', group: 'Tools', keywords: 'whiteboard draw canvas live' },
    <?php endif; ?>

    // Student Commands
    <?php if ($role === 'student'): ?>
    <?php if ($isAiverse): ?>
    { icon: '🚀', title: 'AIverse Hub', desc: 'Return to AI course overview and enrolled programs', url: '/dashboard', group: 'Navigation', keywords: 'aiverse dashboard home artificial intelligence courses' },
    { icon: '🤖', title: 'Explore AI Courses', desc: 'Browse Generative AI, Python, Agents, Deep Learning', url: '/dashboard#courses', group: 'Academics', keywords: 'ai courses catalog prompt engineering agentic deep learning' },
    { icon: '💳', title: 'Course Fees & Receipts', desc: 'View payment status and download fee receipts', url: '/student/fees', group: 'Finance', keywords: 'fee receipt payment invoices course balance' },
    <?php else: ?>
    { icon: '📱', title: 'Parent Progress Pass', desc: 'Mobile-first student progress pass for parents', url: '/parent-pass', group: 'Growth', keywords: 'parent pass progress attendance report card mobile share' },
    { icon: '🗓️', title: 'My Class Routine', desc: 'View today\'s periods and weekly timetable', url: '/student/timetable', group: 'Academics', keywords: 'timetable routine classes schedule' },
    { icon: '📝', title: 'My Tests & Homework', desc: 'Take unit tests, practice quizzes, and homework', url: '/student/assessments', group: 'Academics', keywords: 'tests exam homework quizzes marks' },
    { icon: '💳', title: 'My Fees & Receipts', desc: 'View payment status and download fee receipts', url: '/student/fees', group: 'Academics', keywords: 'fee receipt payment invoices' },
    { icon: '🎨', title: 'Digital Whiteboard', desc: 'Open blank sketch and problem solving canvas', url: '/live/whiteboard', group: 'Tools', keywords: 'whiteboard draw sketch canvas' },
    { icon: '♟️', title: 'Rising Rooks Chess Academy', desc: 'Live chess games, puzzles, and rating', url: '/chess/live', group: 'Co-Curricular', keywords: 'chess game play puzzles rooks rating' },
    { icon: '🏅', title: 'My Certificates', desc: 'Download verifiable achievement certificates', url: '/my-certificates', group: 'Growth', keywords: 'certificates awards achievements' },
    <?php endif; ?>
    <?php endif; ?>

    // Universal / Authenticated Commands
    <?php if ($user): ?>
    { icon: '📊', title: 'My Dashboard', desc: 'Return to primary activity overview', url: '/dashboard', group: 'Navigation', keywords: 'dashboard home overview stats' },
    { icon: '👤', title: 'My Profile', desc: 'Update personal details, password, and preferences', url: '/profile', group: 'Navigation', keywords: 'profile account settings password photo' },
    { icon: '🚪', title: 'Logout', desc: 'End current session safely', url: '/logout', group: 'Navigation', keywords: 'logout sign out exit' },
    <?php else: ?>
    { icon: '📚', title: 'Explore Lessons & Curriculum', desc: 'Browse courses, chapters, and study materials', url: '/lessons', group: 'Explore', keywords: 'lessons courses subjects chapters study curriculum' },
    { icon: '🔑', title: 'Sign In / Login', desc: 'Access your student, teacher, or school portal', url: '/login', group: 'Access', keywords: 'login sign in access account' },
    { icon: '✨', title: 'Create Free Account', desc: 'Sign up as a student, parent, or teacher', url: '/register', group: 'Access', keywords: 'register signup create account join' },
    <?php endif; ?>
    { icon: '🌓', title: 'Toggle Theme (Light / Dark)', desc: 'Switch visual theme between light and dark mode', action: 'toggleTheme', group: 'Appearance', keywords: 'theme dark light mode color contrast' },
];

let selectedCmdIndex = 0;
let filteredCmdItems = [];

function openCommandPalette() {
    const backdrop = document.getElementById('cmdPaletteBackdrop');
    const input = document.getElementById('cmdSearchInput');
    backdrop.style.display = 'flex';
    input.value = '';
    filterCmdList();
    setTimeout(() => input.focus(), 50);
}

function closeCommandPalette() {
    const backdrop = document.getElementById('cmdPaletteBackdrop');
    backdrop.style.display = 'none';
}

function handleCmdBackdropClick(e) {
    if (e.target.id === 'cmdPaletteBackdrop') {
        closeCommandPalette();
    }
}

function filterCmdList() {
    const query = document.getElementById('cmdSearchInput').value.trim().toLowerCase();
    const resultsContainer = document.getElementById('cmdResultsList');

    if (!query) {
        filteredCmdItems = [...CMD_ITEMS];
    } else {
        filteredCmdItems = CMD_ITEMS.filter(item => {
            return item.title.toLowerCase().includes(query) ||
                   item.desc.toLowerCase().includes(query) ||
                   (item.keywords && item.keywords.toLowerCase().includes(query)) ||
                   item.group.toLowerCase().includes(query);
        });
    }

    selectedCmdIndex = 0;
    renderCmdResults();
}

function renderCmdResults() {
    const resultsContainer = document.getElementById('cmdResultsList');
    if (filteredCmdItems.length === 0) {
        resultsContainer.innerHTML = '<div style="padding:24px; text-align:center; color:var(--il-muted); font-size:13px;">No matching actions found. Try "routine", "questions", or "whiteboard".</div>';
        return;
    }

    // Group items
    const groups = {};
    filteredCmdItems.forEach((item, index) => {
        if (!groups[item.group]) groups[item.group] = [];
        groups[item.group].push({ item, index });
    });

    let html = '';
    for (const [groupName, items] of Object.entries(groups)) {
        html += `<div class="cmd-group-label">${groupName}</div>`;
        items.forEach(({ item, index }) => {
            const isSel = index === selectedCmdIndex ? ' selected' : '';
            html += `
            <div class="cmd-item${isSel}" onclick="executeCmdItem(${index})" onmouseenter="selectCmdIndex(${index})">
                <div class="cmd-item-left">
                    <span class="cmd-item-icon">${item.icon}</span>
                    <div>
                        <div style="font-weight:600; color:var(--il-text);">${item.title}</div>
                        <div style="font-size:11px; color:var(--il-muted);">${item.desc}</div>
                    </div>
                </div>
                <span style="font-size:11px; color:var(--il-muted);">↵</span>
            </div>`;
        });
    }

    resultsContainer.innerHTML = html;
}

function selectCmdIndex(idx) {
    if (!filteredCmdItems || filteredCmdItems.length === 0) return;
    if (idx < 0) idx = filteredCmdItems.length - 1;
    if (idx >= filteredCmdItems.length) idx = 0;
    selectedCmdIndex = idx;
    const items = document.querySelectorAll('.cmd-item');
    items.forEach((el, i) => {
        const isSel = (i === idx);
        el.classList.toggle('selected', isSel);
        if (isSel) {
            el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    });
}

function executeCmdItem(idx) {
    const item = filteredCmdItems[idx];
    if (!item) return;

    closeCommandPalette();
    if (item.action === 'toggleTheme') {
        const btn = document.getElementById('themeToggleBtn');
        if (btn) btn.click();
    } else if (item.url) {
        window.location.href = item.url;
    }
}

// Global Keydown Handler: Ctrl + K, Arrows, Escape
document.addEventListener('keydown', function(e) {
    // Check for Ctrl + K or Cmd + K
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        const backdrop = document.getElementById('cmdPaletteBackdrop');
        if (backdrop.style.display === 'flex') {
            closeCommandPalette();
        } else {
            openCommandPalette();
        }
        return;
    }

    const backdrop = document.getElementById('cmdPaletteBackdrop');
    if (backdrop && backdrop.style.display === 'flex') {
        if (e.key === 'Escape') {
            e.preventDefault();
            closeCommandPalette();
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectCmdIndex(selectedCmdIndex + 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectCmdIndex(selectedCmdIndex - 1);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            executeCmdItem(selectedCmdIndex);
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('themeToggleBtn');
    if (btn) {
        btn.addEventListener('click', function() {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('il-theme', next);
        });
    }
});
</script>
<main class="container">
<?php if ($msg = Session::flash('success')): ?>
    <div class="alert alert-success"><?= Helper::e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = Session::flash('error')): ?>
    <div class="alert alert-error"><?= Helper::e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = Session::flash('warning')): ?>
    <div class="alert alert-warning"><?= Helper::e($msg) ?></div>
<?php endif; ?>