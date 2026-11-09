<?php
/**
 * Infinity Learning — BPSC TRE 4.0 Sub-Portal
 * Dedicated Administrator Gateway & Provisioning Controller
 * Route: https://infintylearning.net/bpsc/admin
 */

define('BPSC_PORTAL', true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/UserManager.php';
require_once __DIR__ . '/../src/Auth.php';

$message = '';
$messageType = '';

// Default requested credentials
$targetEmail = 'fnxt.admin@gmail.com';
$targetPassword = 'qweqweqwe1';
$targetName = 'Master Administrator';
$targetPhone = '+919876543211';

// Handle Form Submissions
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($requestMethod === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. ACTION: Create or Reset Master Admin Account
    if ($action === 'create_admin') {
        $adminName = trim($_POST['name'] ?? $targetName);
        $adminEmail = trim(strtolower($_POST['email'] ?? $targetEmail));
        $adminPhone = trim($_POST['phone'] ?? $targetPhone);
        $adminPassword = $_POST['password'] ?? $targetPassword;

        if (empty($adminEmail) || empty($adminPassword)) {
            $message = 'Email and password are required.';
            $messageType = 'error';
        } else {
            try {
                $existing = Database::fetchOne("SELECT id FROM users WHERE LOWER(email) = :email", [':email' => $adminEmail]);
                $hash = password_hash($adminPassword, PASSWORD_BCRYPT);
                $now = date('Y-m-d H:i:s');

                if ($existing) {
                    Database::execute(
                        "UPDATE users SET name = :name, phone = :phone, password_hash = :hash, role = 'admin', status = 'active', level_label = 'Platform Administration' WHERE id = :id",
                        [
                            ':name' => $adminName,
                            ':phone' => $adminPhone,
                            ':hash' => $hash,
                            ':id' => $existing['id']
                        ]
                    );
                    $message = "Admin account '$adminEmail' updated successfully with active administrator privileges.";
                } else {
                    $newId = 'USR_ADM_' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 6));
                    Database::execute(
                        "INSERT INTO users (id, name, email, phone, password_hash, role, level_label, district, status, plan_name, plan_status, plan_badge, plan_amount, plan_expiry, created_at)
                         VALUES (:id, :name, :email, :phone, :hash, 'admin', 'Platform Administration', 'Patna', 'active', 'BPSC TRE 4.0 Platform Administration Pass', 'Active (Enrolled)', 'Active Access', 'Trial Access', 'December 31, 2026', :created)",
                        [
                            ':id' => $newId,
                            ':name' => $adminName,
                            ':email' => $adminEmail,
                            ':phone' => $adminPhone,
                            ':hash' => $hash,
                            ':created' => $now
                        ]
                    );
                    $message = "Master Administrator account '$adminEmail' created successfully!";
                }
                $messageType = 'success';

                // Automatically log in the created/updated admin
                $loginAttempt = Auth::attempt($adminEmail, $adminPassword, ROLE_ADMIN);
                if ($loginAttempt['success']) {
                    header("Location: ../index.php");
                    exit;
                }
            } catch (Exception $e) {
                $message = 'Database error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }

    // 2. ACTION: Direct Login
    if ($action === 'login_admin') {
        $loginEmail = trim($_POST['email'] ?? '');
        $loginPassword = $_POST['password'] ?? '';

        $attempt = Auth::attempt($loginEmail, $loginPassword, ROLE_ADMIN);
        if ($attempt['success']) {
            header("Location: ../index.php");
            exit;
        } else {
            $message = $attempt['message'] ?? 'Login failed. Invalid administrator credentials.';
            $messageType = 'error';
        }
    }
}

// Fetch existing admin accounts
$adminUsers = [];
try {
    $adminUsers = Database::fetchAll("SELECT id, name, email, phone, status, created_at FROM users WHERE role = 'admin' ORDER BY created_at ASC");
} catch (Exception $e) {
    // Database connection fallback
}

$currentUser = Auth::user();
$isLoggedInAdmin = ($currentUser && ($currentUser['role'] ?? '') === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Gateway & Provisioning — Infinity Learning BPSC TRE 4.0</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/assets/img/logo.png">
  <link rel="shortcut icon" type="image/png" href="/assets/img/logo.png">
  <link rel="apple-touch-icon" href="/assets/img/logo.png">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              orange: '#FF6B00',
              orangeHover: '#E05E00',
              peachBg: '#FFF5F0',
              peachBorder: '#FED7AA',
              navy: '#0B132B',
              navySurface: '#1C2541',
              slateText: '#1E293B',
              muted: '#64748B',
              ivory: '#FAF9F6'
            }
          }
        }
      }
    };
  </script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-brand-navy to-slate-950 text-slate-100 min-h-screen flex flex-col justify-between antialiased font-sans">

  <!-- Header Banner -->
  <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur-md sticky top-0 z-50 px-6 py-4">
    <div class="max-w-6xl mx-auto flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-white border border-slate-700 p-1 flex items-center justify-center shadow-lg shadow-orange-500/10">
          <img src="/assets/img/logo.png" alt="Infinity Learning" class="w-full h-full object-contain">
        </div>
        <div>
          <h1 class="text-base font-black tracking-tight text-white">INFINITY LEARNING</h1>
          <p class="text-xs text-brand-orange font-bold uppercase tracking-wider">AIVERSE Admin Central Terminal</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <a href="../index.php" class="text-xs font-semibold text-slate-300 hover:text-white px-3 py-1.5 rounded-lg border border-slate-700 hover:border-slate-500 transition-colors">
          ← Back to Main Portal
        </a>
      </div>
    </div>
  </header>

  <!-- Main Container -->
  <main class="max-w-4xl mx-auto w-full px-4 py-8 flex-1 flex flex-col justify-center">

    <!-- Active Session Banner -->
    <?php if ($isLoggedInAdmin): ?>
    <div class="mb-6 p-4 rounded-2xl bg-emerald-950/70 border border-emerald-600/50 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
      <div class="flex items-center gap-3">
        <span class="text-2xl">🛡️</span>
        <div>
          <div class="text-sm font-bold text-emerald-300">Active Admin Session Detected</div>
          <div class="text-xs text-slate-300">
            Logged in as: <strong class="text-white"><?php echo htmlspecialchars($currentUser['name'] ?? 'Admin'); ?></strong> 
            (<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>)
          </div>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="../index.php" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all">
          Launch Admin Dashboard →
        </a>
        <a href="../logout.php" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition-all">
          Logout
        </a>
      </div>
    </div>
    <?php endif; ?>

    <!-- Status Alerts -->
    <?php if (!empty($message)): ?>
    <div class="mb-6 p-4 rounded-2xl <?php echo $messageType === 'success' ? 'bg-emerald-950/80 border-emerald-600/60 text-emerald-200' : 'bg-rose-950/80 border-rose-600/60 text-rose-200'; ?> border shadow-lg flex items-center gap-3 text-sm">
      <span class="text-lg"><?php echo $messageType === 'success' ? '✅' : '⚠️'; ?></span>
      <div class="font-medium"><?php echo htmlspecialchars($message); ?></div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

      <!-- CARD 1: Create or Provision Target Admin -->
      <div class="bg-slate-800/80 border border-slate-700/80 rounded-3xl p-6 shadow-2xl backdrop-blur-sm flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-4">
            <span class="px-3 py-1 rounded-full bg-brand-orange/20 text-brand-orange border border-brand-orange/30 text-xs font-black uppercase tracking-wider">
              Admin Provisioning
            </span>
            <span class="text-xs text-slate-400 font-mono">POST /admin</span>
          </div>

          <h2 class="text-lg font-black text-white mb-2">Create / Update Master Admin</h2>
          <p class="text-xs text-slate-400 mb-6 leading-relaxed">
            Instantly provision the administrator account with credentials configured for Infinity Learning.
          </p>

          <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="action" value="create_admin">

            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                Full Name
              </label>
              <input type="text" name="name" value="<?php echo htmlspecialchars($targetName); ?>" required
                     class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-xs font-semibold focus:outline-none focus:border-brand-orange focus:ring-1 focus:ring-brand-orange">
            </div>

            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                Login ID / Email <span class="text-brand-orange">*</span>
              </label>
              <input type="email" name="email" value="<?php echo htmlspecialchars($targetEmail); ?>" required
                     class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-xs font-semibold focus:outline-none focus:border-brand-orange focus:ring-1 focus:ring-brand-orange font-mono">
            </div>

            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                Phone Number
              </label>
              <input type="text" name="phone" value="<?php echo htmlspecialchars($targetPhone); ?>"
                     class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-xs font-semibold focus:outline-none focus:border-brand-orange focus:ring-1 focus:ring-brand-orange">
            </div>

            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                Password <span class="text-brand-orange">*</span>
              </label>
              <input type="text" name="password" value="<?php echo htmlspecialchars($targetPassword); ?>" required
                     class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-xs font-semibold focus:outline-none focus:border-brand-orange focus:ring-1 focus:ring-brand-orange font-mono">
            </div>

            <button type="submit"
                    class="w-full mt-2 py-3 rounded-xl bg-gradient-to-r from-brand-orange to-amber-600 hover:from-brand-orangeHover hover:to-amber-500 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-orange-500/25 transition-all flex items-center justify-center gap-2">
              <span>⚡</span> Provision & Login as Admin
            </button>
          </form>
        </div>
      </div>

      <!-- CARD 2: Direct Admin Login -->
      <div class="bg-slate-800/80 border border-slate-700/80 rounded-3xl p-6 shadow-2xl backdrop-blur-sm flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-4">
            <span class="px-3 py-1 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 text-xs font-black uppercase tracking-wider">
              Direct Sign In
            </span>
            <span class="text-xs text-slate-400 font-mono">ROLE: ADMIN</span>
          </div>

          <h2 class="text-lg font-black text-white mb-2">Admin Sign In</h2>
          <p class="text-xs text-slate-400 mb-6 leading-relaxed">
            Enter with administrator access to enter the AIVERSE Control Deck.
          </p>

          <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="action" value="login_admin">

            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                Admin Email
              </label>
              <input type="email" name="email" value="<?php echo htmlspecialchars($targetEmail); ?>" required
                     class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-xs font-semibold focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 font-mono">
            </div>

            <div>
              <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                Password
              </label>
              <input type="password" name="password" value="<?php echo htmlspecialchars($targetPassword); ?>" required
                     class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white text-xs font-semibold focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 font-mono">
            </div>

            <button type="submit"
                    class="w-full mt-6 py-3 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs uppercase tracking-wider border border-slate-600 shadow-md transition-all flex items-center justify-center gap-2">
              <span>🔐</span> Sign In to Admin Central
            </button>
          </form>

          <!-- Registered Admins List -->
          <div class="mt-6 pt-5 border-t border-slate-700/60">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2.5 flex items-center justify-between">
              <span>Active Admin Directory</span>
              <span class="px-2 py-0.5 rounded bg-slate-700 text-slate-300 text-[10px]"><?php echo count($adminUsers); ?> registered</span>
            </div>
            <div class="space-y-2">
              <?php foreach ($adminUsers as $adm): ?>
              <div class="p-2.5 rounded-xl bg-slate-900/70 border border-slate-800 text-xs flex items-center justify-between">
                <div>
                  <div class="font-bold text-slate-200"><?php echo htmlspecialchars($adm['name']); ?></div>
                  <div class="text-[10px] text-slate-400 font-mono"><?php echo htmlspecialchars($adm['email']); ?></div>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?php echo ($adm['status'] ?? '') === 'active' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-slate-800 text-slate-400'; ?>">
                  <?php echo htmlspecialchars($adm['status'] ?? 'active'); ?>
                </span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Security Information Footer -->
    <div class="mt-8 text-center text-xs text-slate-500">
      Infinity Learning BPSC TRE 4.0 Sub-Portal · Admin URL: <code class="text-slate-400 bg-slate-900 px-2 py-0.5 rounded border border-slate-800 font-mono">https://infintylearning.net/bpsc/admin</code>
    </div>
  </main>

  <footer class="py-4 border-t border-slate-800 text-center text-xs text-slate-500">
    &copy; <?php echo date('Y'); ?> Infinity Learning. All rights reserved.
  </footer>

</body>
</html>
