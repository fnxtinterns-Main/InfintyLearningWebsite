<?php
/**
 * Infinity Learning - Multi-Role Authentication & Registration Gate View
 * Connects seamlessly with main website (infinitylearning.net)
 */
if (!defined('BPSC_PORTAL')) {
    exit('Direct access not permitted');
}

$biharDistricts = UserManager::getBiharDistricts();
$cadreLevels = UserManager::getCadreLevels();
?>
  <!-- ========================================================= -->
  <!-- 1. MULTI-ROLE AUTHENTICATION & REGISTRATION GATE SCREEN   -->
  <!-- ========================================================= -->
  <div id="authScreenGate" class="<?php echo Auth::check() ? 'hidden' : ''; ?> min-h-screen flex flex-col items-center justify-center p-3 sm:p-4 bg-brand-cream">
    <div class="w-full max-w-xl bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder p-4 sm:p-8 shadow-xl space-y-4 sm:space-y-6">
      
      <!-- Brand Crest & Main Portal Connection Badge -->
      <div class="text-center space-y-2">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-white border border-brand-peachBorder p-2 flex items-center justify-center shadow-md">
          <img src="/assets/img/logo.png" alt="Infinity Learning" class="w-full h-full object-contain">
        </div>
        <div>
          <h1 class="text-2xl font-black text-brand-slateText tracking-tight">Infinity Learning</h1>
          <p class="text-xs text-brand-muted">BPSC TRE 4.0 शिक्षक भर्ती परीक्षा · Online Learning & Mock Test Portal</p>
        </div>
        <div class="pt-1">
          <a href="<?php echo htmlspecialchars(AppConfig::MAIN_WEBSITE_URL); ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-orangeLight border border-brand-peachBorder text-[10px] font-bold text-brand-orange hover:bg-brand-peachSoft transition">
            <span>🌐</span> Connected to Main Portal (<?php echo parse_url(AppConfig::MAIN_WEBSITE_URL, PHP_URL_HOST); ?>)
          </a>
        </div>
      </div>

      <!-- Server & Client Alerts Container -->
      <div id="authAlertBox" class="<?php echo (!empty($_GET['error']) || !empty($_GET['reg_success'])) ? '' : 'hidden'; ?> transition-all">
        <?php if (!empty($_GET['error'])): ?>
        <div class="p-3.5 bg-red-50 border border-red-200 rounded-2xl text-xs text-red-800 flex items-center gap-2.5">
          <span class="text-base font-bold text-red-600">⚠️</span>
          <span class="font-medium" id="authAlertText"><?php echo htmlspecialchars($_GET['error']); ?></span>
        </div>
        <?php elseif (!empty($_GET['reg_success'])): ?>
        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800 flex items-center gap-2.5">
          <span class="text-base font-bold text-emerald-600">✓</span>
          <span class="font-medium" id="authAlertText"><?php echo htmlspecialchars($_GET['reg_success']); ?></span>
        </div>
        <?php else: ?>
        <div class="p-3.5 bg-red-50 border border-red-200 rounded-2xl text-xs text-red-800 flex items-center gap-2.5" id="authAlertDynamicInner">
          <span class="text-base font-bold text-red-600" id="authAlertIcon">⚠️</span>
          <span class="font-medium" id="authAlertText">Notification</span>
        </div>
        <?php endif; ?>
      </div>

      <!-- ======================================================= -->
      <!-- VIEW A: SIGN IN SCREEN (SHOWN BY DEFAULT)               -->
      <!-- ======================================================= -->
      <div id="authViewLogin" class="space-y-4">
        <!-- Persona Quick-Selector Sub-Tabs -->
        <div class="bg-brand-ivory p-1.5 rounded-2xl border border-brand-peachBorder flex items-center gap-1.5 text-xs font-bold">
          <button type="button" onclick="selectLoginTab('student')" id="tabBtn-student" class="flex-1 py-2 rounded-xl bg-brand-orange text-white shadow-xs transition cursor-pointer text-xs font-bold">
            Student
          </button>
          <button type="button" onclick="selectLoginTab('teacher')" id="tabBtn-teacher" class="flex-1 py-2 rounded-xl text-brand-muted hover:text-brand-slateText transition cursor-pointer text-xs font-bold">
            Teacher
          </button>
          <button type="button" onclick="selectLoginTab('admin')" id="tabBtn-admin" class="flex-1 py-2 rounded-xl text-brand-muted hover:text-brand-slateText transition cursor-pointer text-xs font-bold">
            Admin
          </button>
        </div>

        <!-- Persona Description Card -->
        <div class="p-3.5 rounded-2xl bg-brand-ivory border border-brand-peachBorder text-xs space-y-1">
          <div class="flex items-center justify-between">
            <span class="font-bold text-brand-slateText uppercase text-[11px]" id="personaTitle">Candidate / Aspirant Terminal</span>
            <span id="personaBadge" class="text-[10px] font-sans text-brand-orange bg-brand-peachSoft px-2 py-0.5 rounded font-bold border border-brand-peachBorder">Candidate Terminal</span>
          </div>
          <p class="text-[11px] text-brand-muted leading-relaxed" id="personaDesc">
            Access authentic BPSC TRE mock tests, official SCERT reference books, previous year papers, and topic-wise preparation.
          </p>
        </div>

        <!-- Login Form (Accepts Email, Mobile Phone, or Identifier & Password) -->
        <form id="portalLoginForm" onsubmit="event.preventDefault(); executeRoleLogin();" method="POST" action="login-handler.php" class="space-y-4 text-xs">
          <div>
            <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Email or Mobile Number</label>
            <input type="text" name="username" id="loginUsernameInput" required value="" autocomplete="username" placeholder="Enter your registered email or phone number" class="w-full p-3 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
          </div>
          <div>
            <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Password</label>
            <input type="password" name="password" id="loginPasswordInput" required value="" autocomplete="current-password" placeholder="Enter your password" class="w-full p-3 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
          </div>

          <input type="hidden" name="role" id="loginRoleInput" value="student">

          <button type="submit" id="loginSubmitBtn" class="w-full py-3 bg-brand-orange hover:bg-brand-orangeHover text-white font-bold text-xs rounded-xl shadow-sm transition cursor-pointer flex items-center justify-center gap-2">
            <span>Log In</span>
            <span>→</span>
          </button>
        </form>

        <!-- Prominent Registration Trigger Option -->
        <div class="pt-4 border-t border-brand-peachSoft text-center space-y-2">
          <p class="text-xs text-brand-muted font-medium">Don't have an account yet?</p>
          <button type="button" onclick="openRegisterPage('candidate')" class="w-full py-3 px-4 bg-brand-orangeLight hover:bg-brand-peachSoft border border-brand-peachBorder text-brand-orange font-bold text-xs rounded-xl transition flex items-center justify-center gap-2 cursor-pointer shadow-xs">
            <span>📝</span>
            <span>New Candidate or Teacher? Register Here</span>
            <span>→</span>
          </button>
        </div>
      </div>

      <!-- ======================================================= -->
      <!-- VIEW B: REGISTRATION SCREEN (OPENS WHEN USER PRESSES REGISTER) -->
      <!-- ======================================================= -->
      <div id="authViewRegister" class="hidden space-y-5">
        <!-- Back to Login Header Bar -->
        <div class="flex items-center justify-between border-b border-brand-peachSoft pb-3">
          <button type="button" onclick="openLoginPage()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-ivory border border-brand-peachBorder text-xs font-bold text-brand-slateText hover:bg-brand-peachSoft transition cursor-pointer">
            <span>←</span>
            <span>Back to Sign In</span>
          </button>
          <span class="text-xs font-sans font-bold uppercase tracking-wider text-brand-orange">Portal Registration</span>
        </div>

        <!-- Registration Role Selector (Candidate vs Teacher) -->
        <div class="bg-brand-ivory p-1.5 rounded-2xl border border-brand-peachBorder grid grid-cols-2 gap-1.5 text-xs font-bold">
          <button type="button" onclick="selectRegisterTab('candidate')" id="regTabBtn-candidate" class="py-2.5 rounded-xl bg-brand-orange text-white shadow-xs transition cursor-pointer text-center font-bold">
            📝 Student Registration
          </button>
          <button type="button" onclick="selectRegisterTab('teacher')" id="regTabBtn-teacher" class="py-2.5 rounded-xl text-brand-muted hover:text-brand-slateText transition cursor-pointer text-center font-bold">
            🎓 Teacher Registration
          </button>
        </div>

        <!-- 1. CANDIDATE REGISTRATION FORM -->
        <div id="regBox-candidate" class="space-y-4">
          <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/80 text-xs space-y-1">
            <div class="flex items-center justify-between">
              <span class="font-bold text-amber-900 uppercase text-[11px]">Student Account Creation</span>
              <span class="text-[10px] font-sans text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded font-bold">Instant Login</span>
            </div>
            <p class="text-[11px] text-amber-800/90 leading-relaxed">
              Create your student account to access full BPSC mock tests, official SCERT textbooks, previous year papers, and topic study notes.
            </p>
          </div>

          <form id="candidateRegForm" onsubmit="event.preventDefault(); executeCandidateRegister();" method="POST" action="register-handler.php" class="space-y-3.5 text-xs">
            <input type="hidden" name="reg_type" value="student">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="candRegName" required placeholder="e.g. Priya Kumari" class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
              </div>

              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Mobile / Phone Number <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" id="candRegPhone" required placeholder="e.g. +91 98765 43210" class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
              </div>
            </div>

            <div>
              <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Email Address <span class="text-red-500">*</span></label>
              <input type="email" name="email" id="candRegEmail" required placeholder="e.g. priya.aspirant@gmail.com" class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Select Level <span class="text-red-500">*</span></label>
                <select name="level" id="candRegLevel" required class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
                  <option value="prm">1. Primary (Classes 1–5)</option>
                  <option value="mid" selected>2. Middle (Classes 6–8)</option>
                  <option value="sec">3. Secondary (Classes 9–10)</option>
                  <option value="hsec">4. Higher Sec (Classes 11–12)</option>
                </select>
              </div>

              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Location (Bihar District) <span class="text-red-500">*</span></label>
                <select name="district" id="candRegDistrict" required class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
                  <?php foreach ($biharDistricts as $dist): ?>
                  <option value="<?php echo htmlspecialchars($dist); ?>" <?php echo $dist === 'Patna' ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($dist); ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" id="candRegPassword" required placeholder="Min 6 characters" class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
              </div>

              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Confirm Password <span class="text-red-500">*</span></label>
                <input type="password" name="confirm_password" id="candRegConfirmPassword" required placeholder="Re-enter password" class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
              </div>
            </div>

            <button type="submit" id="candRegSubmitBtn" class="w-full py-3 bg-brand-orange hover:bg-brand-orangeHover text-white font-bold text-xs rounded-xl shadow-sm transition cursor-pointer flex items-center justify-center gap-2">
              <span>Create Student Account</span>
              <span>→</span>
            </button>
          </form>

          <div class="pt-2 text-center text-xs text-brand-muted">
            <span>Already have an account?</span>
            <button type="button" onclick="openLoginPage()" class="text-brand-orange font-bold hover:underline ml-1 cursor-pointer">Sign In to Portal</button>
          </div>
        </div>

        <!-- 2. TEACHER REGISTRATION FORM -->
        <div id="regBox-teacher" class="hidden space-y-4">
          <!-- Approval Required Notice -->
          <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-300 text-xs space-y-1">
            <div class="flex items-center justify-between">
              <span class="font-bold text-amber-900 uppercase text-[11px]">Teacher Account Registration</span>
              <span class="text-[10px] font-sans text-amber-800 bg-amber-200/80 px-2 py-0.5 rounded font-bold">Admin Approval Required</span>
            </div>
            <p class="text-[11px] text-amber-800 leading-relaxed">
              Teacher accounts are reviewed by the portal administrator. Once approved, you will be able to log in, upload study notes, and publish mock tests.
            </p>
          </div>

          <form id="teacherRegForm" onsubmit="event.preventDefault(); executeTeacherRegister();" method="POST" action="register-handler.php" class="space-y-3 text-xs">
            <input type="hidden" name="reg_type" value="teacher">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="teachRegName" required placeholder="e.g. Dr. Amitesh Kumar" class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
              </div>

              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="teachRegEmail" required placeholder="e.g. amitesh.bihar@gmail.com" class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Phone Number <span class="text-red-500">*</span></label>
                <input type="tel" name="phone" id="teachRegPhone" required placeholder="e.g. +91 98765 43210" class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
              </div>

              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Subject Specialization <span class="text-red-500">*</span></label>
                <input type="text" name="subject" id="teachRegSubject" required placeholder="e.g. Mathematics & Science" class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
              </div>
            </div>

            <div>
              <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Highest Qualification & Teaching Experience <span class="text-red-500">*</span></label>
              <input type="text" name="qualification" id="teachRegQual" required placeholder="e.g. M.Sc (Maths), B.Ed, STET Qualified (6 Yrs Teaching Experience)" class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Target Cadre Level <span class="text-red-500">*</span></label>
                <select name="level" id="teachRegLevel" required class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
                  <option value="prm">1. Primary (Classes 1–5)</option>
                  <option value="mid" selected>2. Middle (Classes 6–8)</option>
                  <option value="sec">3. Secondary (Classes 9–10)</option>
                  <option value="hsec">4. Higher Sec (Classes 11–12)</option>
                </select>
              </div>

              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">District of Bihar <span class="text-red-500">*</span></label>
                <select name="district" id="teachRegDistrict" required class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
                  <?php foreach ($biharDistricts as $dist): ?>
                  <option value="<?php echo htmlspecialchars($dist); ?>" <?php echo $dist === 'Patna' ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($dist); ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" id="teachRegPassword" required placeholder="Min 6 characters" class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
              </div>

              <div>
                <label class="block font-bold text-brand-muted mb-1 text-xs font-semibold">Confirm Password <span class="text-red-500">*</span></label>
                <input type="password" name="confirm_password" id="teachRegConfirmPassword" required placeholder="Re-enter password" class="w-full p-2.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange">
              </div>
            </div>

            <button type="submit" id="teachRegSubmitBtn" class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition cursor-pointer flex items-center justify-center gap-2">
              <span>Submit Teacher Application for Admin Approval</span>
              <span>→</span>
            </button>
          </form>

          <div class="pt-2 text-center text-xs text-brand-muted">
            <span>Already registered or approved?</span>
            <button type="button" onclick="openLoginPage()" class="text-brand-orange font-bold hover:underline ml-1 cursor-pointer">Sign In to Portal</button>
          </div>
        </div>

      </div>

      <!-- Statutory DPDP & Main Website Links -->
      <div class="pt-2 text-center border-t border-brand-peachSoft/60 flex flex-wrap items-center justify-center gap-x-2.5 gap-y-1 text-[11px] text-brand-muted">
        <a href="<?php echo htmlspecialchars(AppConfig::MAIN_WEBSITE_URL); ?>" class="font-bold hover:text-brand-orange transition inline-flex items-center gap-0.5">
          <span>← Main Site</span>
        </a>
        <span>·</span>
        <a href="/privacy" target="_blank" class="hover:text-brand-orange transition font-medium">Privacy (DPDP 2023)</a>
        <span>·</span>
        <a href="/terms" target="_blank" class="hover:text-brand-orange transition font-medium">Terms</a>
        <span>·</span>
        <a href="/data-request" target="_blank" class="hover:text-brand-orange transition font-medium">Data Rights (Nomination)</a>
      </div>

    </div>
  </div>
