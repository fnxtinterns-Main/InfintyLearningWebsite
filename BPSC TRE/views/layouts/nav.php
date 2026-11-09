<?php
/**
 * Infinity Learning - Top Announcement & Shell Navigation
 */
if (!defined('BPSC_PORTAL')) {
    exit('Direct access not permitted');
}

$currentUser = Auth::user();
$userRole = $currentUser['role'] ?? 'student';
$isTeacher = ($userRole === 'teacher');
$isAdmin = ($userRole === 'admin');
$isStudent = (!$isTeacher && !$isAdmin);
?>
  <!-- ========================================================= -->
  <!-- 2. MAIN APPLICATION SHELL (AFTER AUTHENTICATION)          -->
  <!-- ========================================================= -->
  <div id="mainAppShell" class="<?php echo Auth::check() ? '' : 'hidden'; ?> min-h-screen flex flex-col bg-brand-cream pb-16 sm:pb-6">
    
    <!-- Announcement Bar -->
    <div class="bg-gradient-to-r from-brand-orange to-amber-600 text-white text-[11px] font-bold py-1.5 px-3 sm:px-4 overflow-hidden relative flex items-center shadow-xs">
      <span class="bg-black/25 px-2 py-0.5 rounded text-[10px] font-bold uppercase mr-2 shrink-0">Notice</span>
      <div class="whitespace-nowrap overflow-hidden flex-1">
        <div class="inline-block marquee-track font-medium text-amber-50">
          📢 BPSC TRE 4.0: Practice free mock tests, download official SCERT books, and prepare with previous year questions.</div>
      </div>
    </div>

    <!-- Navigation Header -->
    <header class="bg-white/95 backdrop-blur-md border-b border-brand-peachBorder sticky top-0 z-40 px-3 sm:px-4 lg:px-8 py-2">
      <div class="max-w-7xl mx-auto flex items-center justify-between gap-2 sm:gap-3">
        
        <!-- Left: Brand Scope -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
          <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-white border border-brand-peachBorder p-1 flex items-center justify-center shadow-sm cursor-pointer" onclick="returnToDashboard(event)" title="Infinity Learning Portal">
            <img src="/assets/img/logo.png" alt="Infinity Learning" class="w-full h-full object-contain">
          </div>
          <div>
            <div class="flex items-center gap-1.5 sm:gap-2">
              <span class="font-extrabold tracking-wider text-xs sm:text-sm uppercase text-brand-slateText cursor-pointer" onclick="returnToDashboard(event)">Infinity</span>
              <span class="text-[10px] sm:text-xs px-2 sm:px-2.5 py-0.5 rounded-full bg-brand-peachSoft text-brand-orange font-bold border border-brand-peachBorder cursor-pointer hover:bg-brand-peachBorder/40 transition truncate max-w-[120px] sm:max-w-none" id="currentRoleBadge" onclick="openProfilePage(event)" title="Click to view candidate profile">
                <?php echo htmlspecialchars($currentUser['badge'] ?? 'Candidate Portal'); ?>
              </span>
            </div>
            <p class="hidden sm:block text-[10px] text-brand-muted tracking-tight font-medium cursor-pointer hover:text-brand-orange transition truncate max-w-[180px] sm:max-w-none" id="userSubRoleLabel" onclick="openProfilePage(event)" title="Click to view candidate profile">
              <?php echo htmlspecialchars($currentUser['subLabel'] ?? 'Candidate Portal'); ?>
            </p>
          </div>
        </div>

        <!-- Right: Desktop Countdown & Role Toolbars -->
        <div class="hidden sm:flex items-center gap-2 sm:gap-3">
          
          <!-- Exam Countdown Pill -->
          <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-orangeLight border border-brand-peachBorder text-xs text-brand-orange font-sans font-bold whitespace-nowrap">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-[11px] font-bold text-brand-slateText">Exam in:</span>
            <span id="headerExamCountdown" class="tracking-tight whitespace-nowrap">--d --h --m --s</span>
          </div>

          <!-- 1. Student Action Toolbar (Desktop) -->
          <div id="studentNavActions" class="<?php echo $isStudent ? 'flex' : 'hidden'; ?> items-center gap-2">
            <button type="button" id="navDashboardBtn" onclick="returnToDashboard(event)" class="hidden px-3.5 py-1.5 rounded-xl border border-brand-orange bg-brand-orange hover:bg-brand-orangeHover text-xs font-bold text-white transition flex items-center gap-1.5 cursor-pointer shadow-xs" title="Return to Candidate Dashboard">
              <span>🏠</span> <span>Dashboard</span>
            </button>
            <button type="button" id="navProfileBtn" onclick="openProfilePage(event)" class="px-3.5 py-1.5 rounded-xl border border-brand-peachBorder bg-brand-orangeLight hover:bg-brand-peachSoft text-xs font-bold text-brand-orange transition flex items-center gap-1.5 cursor-pointer shadow-xs" title="Open Your Profile, Subscription & Achievements">
              <span>👤</span> <span>Profile</span>
            </button>
            <button type="button" onclick="launchProctoredCBT()" class="px-3 py-1.5 rounded-xl bg-brand-orange hover:bg-brand-orangeHover text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
              <span>⚡</span> <span>Mock Test</span> CBT
            </button>
            <button type="button" onclick="openModal('booksModal')" class="px-3 py-1.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold text-brand-slateText hover:bg-brand-peachSoft transition flex items-center gap-1 cursor-pointer">
              <span>📚</span> <span>Books</span>
            </button>
            <button type="button" onclick="openModal('pyqModal')" class="px-3 py-1.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold text-brand-slateText hover:bg-brand-peachSoft transition flex items-center gap-1 cursor-pointer">
              <span>📜</span> <span>PYQs</span>
            </button>
            <button type="button" onclick="openModal('coursesModal')" class="px-3 py-1.5 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold text-brand-slateText hover:bg-brand-peachSoft transition flex items-center gap-1 cursor-pointer">
              <span>💳</span> <span>Courses</span>
            </button>
          </div>

          <!-- 2. Teacher Action Toolbar (Desktop) -->
          <div id="teacherNavActions" class="<?php echo $isTeacher ? 'flex' : 'hidden'; ?> items-center gap-2">
            <button type="button" onclick="openTopicNotesEditor()" class="px-3 py-1.5 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1" title="Upload new study notes or topic handout">
              <span>📤</span> <span>Upload Notes</span>
            </button>
            <button type="button" onclick="openModal('uploadPyqModal')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1" title="Upload Official Previous Year Question Paper">
              <span>📜</span> <span>Upload PYQ</span>
            </button>
            <button type="button" onclick="openModal('adminBulkDocxModal')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1" title="Upload Bulk Word Docx Question Papers">
              <span>📄</span> <span>Import Qs</span>
            </button>
            <button type="button" onclick="openModal('adminAddBookModal')" class="px-3 py-1.5 bg-brand-ivory border border-brand-peachBorder text-brand-slateText hover:bg-brand-peachSoft rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1" title="Publish Book to Digital Library">
              <span>📚</span> <span>Add Book</span>
            </button>
            <button type="button" onclick="openPublishCuratorModal()" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1" title="Publish Mock Test Paper with Question Bank Curation">
              <span>🚀</span> <span>Publish Test</span>
            </button>
          </div>

          <!-- 3. Admin Action Toolbar (Desktop) -->
          <div id="adminNavActions" class="<?php echo $isAdmin ? 'flex' : 'hidden'; ?> items-center gap-2">
            <button type="button" onclick="fetchAdminTeacherData(); document.getElementById('adminPendingTeachersBody')?.scrollIntoView({behavior:'smooth'})" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1" title="Jump to Pending Faculty Approvals">
              <span>⏳</span> <span>Approvals</span>
            </button>
            <button type="button" onclick="document.getElementById('adminApprovedTeachersBody')?.scrollIntoView({behavior:'smooth'})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1" title="Jump to Approved Faculty Directory">
              <span>🎓</span> <span>Faculty</span>
            </button>
            <button type="button" onclick="document.getElementById('adminGatewayPaymentsBody')?.scrollIntoView({behavior:'smooth'})" class="px-3 py-1.5 bg-brand-ivory border border-brand-peachBorder text-brand-slateText hover:bg-brand-peachSoft rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1" title="Jump to Gateway Payments Ledger">
              <span>💳</span> <span>Ledger</span>
            </button>
            <button type="button" onclick="document.getElementById('adminQuestionBankBody')?.scrollIntoView({behavior:'smooth'})" class="px-3 py-1.5 bg-purple-700 hover:bg-purple-800 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1" title="Jump to Question Bank Database">
              <span>📑</span> <span>Q-Bank</span>
            </button>
          </div>

          <!-- Documentation (PDF) Download -->
          <a href="download-pdf.php" target="_blank" download="BPSC_TRE_PORTAL_DOCUMENTATION.pdf" title="View or Download Complete Portal Documentation as PDF" class="hidden md:flex px-3 py-1.5 rounded-xl border border-red-300 bg-red-50 hover:bg-red-100 text-xs font-bold text-red-800 transition items-center gap-1 shadow-xs cursor-pointer">
            <span>📕</span> <span>Docs (PDF)</span>
          </a>

          <!-- Main Website Portal Link -->
          <a href="<?php echo htmlspecialchars(AppConfig::MAIN_WEBSITE_URL); ?>" target="_blank" title="Open Main Infinity Learning Website" class="hidden xl:flex px-3 py-1.5 rounded-xl border border-brand-peachBorder bg-brand-ivory hover:bg-brand-peachSoft text-xs font-semibold text-brand-slateText transition items-center gap-1">
            <span>🌐</span> <span>Main Site</span>
          </a>

          <!-- Sign Out -->
          <button type="button" onclick="logOutAndShowGate()" class="px-3 py-1.5 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-xs font-bold text-red-700 transition flex items-center gap-1 cursor-pointer">
            <span>⎋</span> <span>Sign Out</span>
          </button>

        </div>

        <!-- Right: Mobile Quick Header Actions (< 640px) -->
        <div class="flex sm:hidden items-center gap-1.5">
          <!-- Student Quick Test Button -->
          <div id="mobileStudentQuickActions" class="<?php echo $isStudent ? 'flex' : 'hidden'; ?> items-center gap-1">
            <button type="button" onclick="launchProctoredCBT()" class="px-2.5 py-1.5 rounded-xl bg-brand-orange text-white text-[11px] font-bold shadow-xs flex items-center gap-1 cursor-pointer">
              <span>⚡</span> <span>CBT Test</span>
            </button>
          </div>

          <!-- Teacher Quick Button -->
          <div id="mobileTeacherQuickActions" class="<?php echo $isTeacher ? 'flex' : 'hidden'; ?> items-center gap-1">
            <button type="button" onclick="openTopicNotesEditor()" class="px-2.5 py-1.5 rounded-xl bg-brand-orange text-white text-[11px] font-bold shadow-xs flex items-center gap-1 cursor-pointer">
              <span>📤</span> <span>Upload</span>
            </button>
          </div>

          <!-- Admin Quick Button -->
          <div id="mobileAdminQuickActions" class="<?php echo $isAdmin ? 'flex' : 'hidden'; ?> items-center gap-1">
            <button type="button" onclick="fetchAdminTeacherData(); document.getElementById('adminPendingTeachersBody')?.scrollIntoView({behavior:'smooth'})" class="px-2.5 py-1.5 rounded-xl bg-amber-600 text-white text-[11px] font-bold shadow-xs flex items-center gap-1 cursor-pointer">
              <span>⏳</span> <span>Approvals</span>
            </button>
          </div>

          <!-- Mobile Hamburger Toggle Button -->
          <button type="button" id="mobileNavMenuToggle" onclick="toggleMobileNavMenu()" class="p-2 rounded-xl border border-brand-peachBorder bg-brand-ivory text-brand-slateText hover:bg-brand-peachSoft transition flex items-center justify-center cursor-pointer min-w-[38px] min-h-[38px]" aria-label="Toggle Navigation Menu">
            <svg id="mobileNavMenuIcon" class="w-5 h-5 text-brand-slateText" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
        </div>

      </div>

      <!-- ========================================================= -->
      <!-- MOBILE SLIDE-DOWN DRAWER MENU (< 640px)                   -->
      <!-- ========================================================= -->
      <div id="mobileNavDropdown" class="hidden sm:hidden pt-3 pb-2 border-t border-brand-peachSoft mt-2 space-y-2.5 transition-all">
        
        <!-- User Info Bar -->
        <div class="p-2.5 rounded-xl bg-brand-ivory border border-brand-peachBorder flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-brand-orange text-white font-bold flex items-center justify-center text-xs">
              👤
            </div>
            <div>
              <p class="font-bold text-xs text-brand-slateText" id="mobileUserName"><?php echo htmlspecialchars($currentUser['name'] ?? 'Candidate'); ?></p>
              <p class="text-[10px] text-brand-muted"><?php echo htmlspecialchars($currentUser['district'] ?? 'Bihar'); ?> District</p>
            </div>
          </div>
          <button type="button" onclick="openProfilePage(event); toggleMobileNavMenu(false);" class="text-[11px] font-bold text-brand-orange bg-brand-peachSoft px-2.5 py-1 rounded-lg border border-brand-peachBorder cursor-pointer">
            Profile →
          </button>
        </div>

        <!-- Student Menu Links -->
        <div id="mobileStudentLinks" class="<?php echo $isStudent ? 'grid' : 'hidden'; ?> grid-cols-2 gap-2 text-xs font-bold">
          <button type="button" onclick="returnToDashboard(event); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft text-brand-slateText flex items-center gap-2 text-left cursor-pointer">
            <span class="text-base">🏠</span>
            <span>Dashboard</span>
          </button>
          <button type="button" onclick="launchProctoredCBT(); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-brand-orange text-white shadow-xs flex items-center gap-2 text-left cursor-pointer">
            <span class="text-base">⚡</span>
            <span>Mock Test</span>
          </button>
          <button type="button" onclick="openModal('booksModal'); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft text-brand-slateText flex items-center gap-2 text-left cursor-pointer">
            <span class="text-base">📚</span>
            <span>SCERT Books</span>
          </button>
          <button type="button" onclick="openModal('pyqModal'); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft text-brand-slateText flex items-center gap-2 text-left cursor-pointer">
            <span class="text-base">📜</span>
            <span>Past Papers</span>
          </button>
          <button type="button" onclick="openModal('coursesModal'); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft text-brand-slateText flex items-center gap-2 text-left cursor-pointer">
            <span class="text-base">💳</span>
            <span>CBT Passes</span>
          </button>
          <button type="button" onclick="openProfilePage(event); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft text-brand-slateText flex items-center gap-2 text-left cursor-pointer">
            <span class="text-base">👤</span>
            <span>My Account</span>
          </button>
        </div>

        <!-- Teacher Menu Links -->
        <div id="mobileTeacherLinks" class="<?php echo $isTeacher ? 'grid' : 'hidden'; ?> grid-cols-2 gap-2 text-xs font-bold">
          <button type="button" onclick="openTopicNotesEditor(); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-brand-orange text-white shadow-xs flex items-center gap-2 text-left cursor-pointer">
            <span>📤</span> <span>Upload Notes</span>
          </button>
          <button type="button" onclick="openModal('uploadPyqModal'); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-blue-600 text-white shadow-xs flex items-center gap-2 text-left cursor-pointer">
            <span>📜</span> <span>Upload PYQ</span>
          </button>
          <button type="button" onclick="openModal('adminBulkDocxModal'); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-emerald-600 text-white shadow-xs flex items-center gap-2 text-left cursor-pointer">
            <span>📄</span> <span>Import Word</span>
          </button>
          <button type="button" onclick="openModal('adminAddBookModal'); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-purple-600 text-white shadow-xs flex items-center gap-2 text-left cursor-pointer">
            <span>📚</span> <span>Publish Book</span>
          </button>
          <button type="button" onclick="openPublishCuratorModal(); toggleMobileNavMenu(false);" class="col-span-2 p-2.5 rounded-xl bg-brand-ivory border border-brand-peachBorder text-brand-slateText flex items-center justify-center gap-2 cursor-pointer">
            <span>🚀</span> <span>Publish Test Paper</span>
          </button>
        </div>

        <!-- Admin Menu Links -->
        <div id="mobileAdminLinks" class="<?php echo $isAdmin ? 'grid' : 'hidden'; ?> grid-cols-2 gap-2 text-xs font-bold">
          <button type="button" onclick="fetchAdminTeacherData(); document.getElementById('adminPendingTeachersBody')?.scrollIntoView({behavior:'smooth'}); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-amber-600 text-white shadow-xs flex items-center gap-2 text-left cursor-pointer">
            <span>⏳</span> <span>Approvals</span>
          </button>
          <button type="button" onclick="document.getElementById('adminApprovedTeachersBody')?.scrollIntoView({behavior:'smooth'}); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-emerald-600 text-white shadow-xs flex items-center gap-2 text-left cursor-pointer">
            <span>🎓</span> <span>Faculty</span>
          </button>
          <button type="button" onclick="document.getElementById('adminGatewayPaymentsBody')?.scrollIntoView({behavior:'smooth'}); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-brand-ivory border border-brand-peachBorder text-brand-slateText flex items-center gap-2 text-left cursor-pointer">
            <span>💳</span> <span>Ledger</span>
          </button>
          <button type="button" onclick="document.getElementById('adminQuestionBankBody')?.scrollIntoView({behavior:'smooth'}); toggleMobileNavMenu(false);" class="p-2.5 rounded-xl bg-purple-700 text-white shadow-xs flex items-center gap-2 text-left cursor-pointer">
            <span>📑</span> <span>Q-Bank</span>
          </button>
        </div>

        <!-- Documentation Download Button (PDF) -->
        <a href="download-pdf.php" target="_blank" download="BPSC_TRE_PORTAL_DOCUMENTATION.pdf" class="p-2.5 rounded-xl border border-red-300 bg-red-50 text-red-800 text-xs font-bold flex items-center justify-center gap-2 cursor-pointer shadow-xs">
          <span>📕</span>
          <span>Download Documentation (PDF)</span>
        </a>

        <!-- External & Logout Bar -->
        <div class="pt-2 border-t border-brand-peachSoft flex items-center justify-between gap-2">
          <a href="<?php echo htmlspecialchars(AppConfig::MAIN_WEBSITE_URL); ?>" target="_blank" class="flex-1 py-2 px-3 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-bold text-brand-slateText flex items-center justify-center gap-1.5">
            <span>🌐</span> <span>Main Site</span>
          </a>
          <button type="button" onclick="logOutAndShowGate()" class="flex-1 py-2 px-3 rounded-xl border border-red-200 bg-red-50 text-xs font-bold text-red-700 flex items-center justify-center gap-1.5 cursor-pointer">
            <span>⎋</span> <span>Sign Out</span>
          </button>
        </div>

      </div>
    </header>

    <!-- ========================================================= -->
    <!-- MOBILE BOTTOM NAVIGATION DOCK (PHONES < 640px, STUDENT)   -->
    <!-- ========================================================= -->
    <nav id="mobileBottomNav" class="<?php echo $isStudent ? 'flex' : 'hidden'; ?> sm:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-brand-peachBorder py-1.5 px-3 justify-around items-center shadow-lg transition-transform duration-200" aria-label="Mobile Navigation Dock">
      <button type="button" onclick="returnToDashboard(event)" id="mobBottomHome" class="flex flex-col items-center justify-center text-brand-orange py-1 px-2 rounded-xl transition cursor-pointer min-w-[50px] min-h-[44px]">
        <span class="text-lg leading-none">🏠</span>
        <span class="text-[10px] font-bold mt-0.5">Home</span>
      </button>
      <button type="button" onclick="launchProctoredCBT()" id="mobBottomCbt" class="flex flex-col items-center justify-center text-brand-slateText hover:text-brand-orange py-1 px-2 rounded-xl transition cursor-pointer min-w-[50px] min-h-[44px]">
        <span class="text-lg leading-none">⚡</span>
        <span class="text-[10px] font-bold mt-0.5">Mock Test</span>
      </button>
      <button type="button" onclick="openModal('booksModal')" id="mobBottomBooks" class="flex flex-col items-center justify-center text-brand-slateText hover:text-brand-orange py-1 px-2 rounded-xl transition cursor-pointer min-w-[50px] min-h-[44px]">
        <span class="text-lg leading-none">📚</span>
        <span class="text-[10px] font-bold mt-0.5">Books</span>
      </button>
      <button type="button" onclick="openModal('pyqModal')" id="mobBottomPyqs" class="flex flex-col items-center justify-center text-brand-slateText hover:text-brand-orange py-1 px-2 rounded-xl transition cursor-pointer min-w-[50px] min-h-[44px]">
        <span class="text-lg leading-none">📜</span>
        <span class="text-[10px] font-bold mt-0.5">PYQs</span>
      </button>
      <button type="button" onclick="openProfilePage(event)" id="mobBottomProfile" class="flex flex-col items-center justify-center text-brand-slateText hover:text-brand-orange py-1 px-2 rounded-xl transition cursor-pointer min-w-[50px] min-h-[44px]">
        <span class="text-lg leading-none">👤</span>
        <span class="text-[10px] font-bold mt-0.5">Profile</span>
      </button>
    </nav>


