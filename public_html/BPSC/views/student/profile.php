<?php
/**
 * Infinity Learning - Candidate Profile, Active Subscription & Achievements View
 */
if (!defined('BPSC_PORTAL')) {
    exit('Direct access not permitted');
}

$currentUser = Auth::user();
$studentName = $currentUser['name'] ?? 'Candidate Aspirant';
$studentLevelLabel = $currentUser['level_label'] ?? 'Classes 6–8 Middle School';
$studentDistrict = $currentUser['district'] ?? 'Patna';
$studentEmail = $currentUser['email'] ?? '';
$studentPhone = $currentUser['phone'] ?? '';
$studentId = $currentUser['id'] ?? $currentUser['user_id'] ?? 'USR_STU';

// Calculate initials safely
$parts = explode(' ', trim($studentName));
$firstInitial = !empty($parts[0]) ? mb_substr($parts[0], 0, 1) : 'C';
$secondInitial = !empty($parts[1]) ? mb_substr($parts[1], 0, 1) : (mb_strlen($parts[0] ?? '') > 1 ? mb_substr($parts[0], 1, 1) : 'A');
$studentInitials = strtoupper($firstInitial . $secondInitial);

$studentSubscription = $currentUser['subscription'] ?? [
    'plan_name' => 'BPSC TRE 4.0 Standard Pass',
    'status' => 'Active (Enrolled)',
    'badge' => 'Active Pass',
    'amount_paid' => 'Free Trial',
    'expiry' => 'December 31, 2026',
    'enrolled_cadre' => $studentLevelLabel
];

$studentAchievements = $currentUser['achievements'] ?? [
    'tests_taken' => 0,
    'best_score' => '--',
    'avg_accuracy' => '0%',
    'rank_district' => 'Pending First Mock',
    'earned_badges' => ['Registered Aspirant', 'Admit Ready', $studentDistrict . ' Aspirant']
];
?>
    <!-- ========================================================= -->
    <!-- VIEW 6: CANDIDATE PROFILE & SUBSCRIPTION TERMINAL         -->
    <!-- ========================================================= -->
    <main id="view-profile" class="hidden max-w-7xl mx-auto w-full px-3 sm:px-4 lg:px-8 py-4 sm:py-6 flex-1 space-y-4 sm:space-y-6">
      
      <!-- Top Breadcrumb & Return Bar -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder p-4 sm:p-6 shadow-sm">
        <div class="flex items-center gap-3">
          <button type="button" onclick="returnToDashboard(event)" class="px-3.5 py-2 sm:px-4 sm:py-2.5 rounded-2xl bg-brand-orange text-white hover:bg-brand-orangeHover text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-sm transform hover:-translate-x-0.5" title="Click to return to candidate dashboard">
            <span>←</span>
            <span>Dashboard</span>
          </button>
          <div>
            <h2 class="text-base sm:text-lg font-extrabold text-brand-slateText">Candidate Profile & Academic Details</h2>
            <p class="text-xs text-brand-muted hidden sm:block">Your verified account details, academic standing, active subscription package, and mock test achievements</p>
          </div>
        </div>
        <div class="flex items-center gap-2 self-end sm:self-auto">
          <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold font-mono flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Verified
          </span>
          <button type="button" onclick="launchProctoredCBT()" class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl bg-brand-orange hover:bg-brand-orangeHover text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
            <span>⚡</span> Mock CBT
          </button>
        </div>
      </div>

      <!-- CANDIDATE VERIFIED HERO RIBBON -->
      <section class="bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder p-4 sm:p-6 lg:p-7 shadow-sm">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 sm:gap-6">
          
          <!-- Candidate Identity & Level Cadre Badge -->
          <div class="space-y-2 w-full lg:w-auto">
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
              <span class="text-[10px] sm:text-xs px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-brand-yellowPill text-brand-yellowText font-bold font-mono">
                👋 Welcome Aspirant
              </span>
              <span class="text-[10px] sm:text-xs px-2 sm:px-2.5 py-0.5 rounded-full bg-slate-100 text-brand-slateText font-medium border border-slate-200">
                Advt. No. 15/2026
              </span>
              <span class="text-[10px] sm:text-xs px-2 sm:px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                30% Min Language Threshold Active
              </span>
            </div>
            <div class="flex items-center gap-3 sm:gap-4 pt-1">
              <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-brand-orange to-amber-500 text-white border-2 border-brand-peachBorder flex items-center justify-center font-serif text-xl sm:text-2xl font-bold shadow-sm shrink-0" id="profileAvatarBig">
                <?php echo htmlspecialchars($studentInitials ?: 'CA'); ?>
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <h1 class="text-xl sm:text-3xl font-extrabold text-brand-slateText tracking-tight truncate" id="profileFullName">
                    <?php echo htmlspecialchars($studentName); ?>
                  </h1>
                  <span class="text-[10px] font-sans font-bold bg-brand-peachSoft text-brand-orange px-2 py-0.5 rounded border border-brand-peachBorder" id="profileUserId">
                    <?php echo htmlspecialchars($studentId); ?>
                  </span>
                </div>
                <p class="text-xs sm:text-sm text-brand-muted font-medium truncate" id="profileCadreText">
                  <?php echo htmlspecialchars($studentLevelLabel); ?> · <span class="font-semibold text-brand-orange"><?php echo htmlspecialchars($studentDistrict); ?> District</span>
                </p>
                <div class="pt-0.5 flex flex-wrap items-center gap-1.5 text-[11px] font-mono text-brand-muted" id="profileContactPill">
                  <span class="truncate max-w-[200px] sm:max-w-none">✉ <span id="profileEmailText"><?php echo htmlspecialchars($studentEmail); ?></span></span>
                  <span>· 📱 <span id="profilePhoneText"><?php echo htmlspecialchars($studentPhone ?: 'Not Provided'); ?></span></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Dynamic HUD Cards: 2 cols on mobile, 4 cols on desktop -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 w-full lg:w-auto">
            <div class="bg-brand-ivory rounded-2xl border border-brand-peachBorder p-2.5 sm:p-3.5 text-center">
              <div class="font-serif text-xl sm:text-3xl font-bold text-brand-orange" id="profileHudCompleted">0</div>
              <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-brand-muted mt-0.5">Mastered</div>
            </div>
            <div class="bg-brand-ivory rounded-2xl border border-brand-peachBorder p-2.5 sm:p-3.5 text-center">
              <div class="font-serif text-xl sm:text-3xl font-bold text-brand-orange" id="profileHudProgress">0%</div>
              <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-brand-muted mt-0.5">Prepared</div>
            </div>
            <div class="bg-brand-ivory rounded-2xl border border-brand-peachBorder p-2.5 sm:p-3.5 text-center">
              <div class="font-serif text-xl sm:text-3xl font-bold text-brand-orange" id="profileHudAvailableTestsCount">0</div>
              <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-brand-muted mt-0.5">Live CBTs</div>
            </div>
            <div class="bg-brand-ivory rounded-2xl border border-brand-peachBorder p-2.5 sm:p-3.5 text-center">
              <div class="font-serif text-xl sm:text-3xl font-bold text-brand-orange">-0.33</div>
              <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-brand-muted mt-0.5">Negative</div>
            </div>
          </div>

        </div>

        <!-- Academic Standing Gauge Bar -->
        <div class="mt-6 pt-5 border-t border-brand-peachSoft">
          <div class="flex justify-between items-center text-xs font-bold text-brand-slateText mb-1.5">
            <span class="flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-brand-teal"></span>
              Academic Standing & Topic Progression
            </span>
            <span class="text-brand-teal font-mono text-xs" id="profileGaugePercentage">0.0% Verified</span>
          </div>
          <div class="w-full bg-brand-peachSoft h-2.5 rounded-full overflow-hidden">
            <div id="profileGaugeBar" class="bg-brand-teal h-full transition-all duration-500 ease-out" style="width: 0%"></div>
          </div>
        </div>
      </section>

      <!-- VERIFIED REGISTRATION DOSSIER CARD (COMPLETE CANDIDATE DETAILS) -->
      <section class="bg-white rounded-3xl border border-brand-peachBorder p-6 sm:p-7 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-brand-peachSoft pb-3">
          <div class="flex items-center gap-2.5">
            <span class="w-9 h-9 rounded-xl bg-brand-peachSoft text-brand-orange flex items-center justify-center font-bold text-base">📋</span>
            <div>
              <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">Candidate Registration Information</h3>
              <p class="text-[11px] text-brand-muted">Official particulars on record for BPSC Teacher Recruitment Examination (TRE 4.0)</p>
            </div>
          </div>
          <span class="text-[11px] font-sans font-bold bg-brand-ivory text-brand-slateText border border-brand-peachBorder px-3 py-1 rounded-full">
            Advt. No. 15/2026
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs pt-1">
          <div class="p-3.5 rounded-2xl bg-brand-ivory border border-brand-peachBorder space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-muted block">Full Name</span>
            <span class="font-extrabold text-sm text-brand-slateText block" id="profileDossierName"><?php echo htmlspecialchars($studentName); ?></span>
            <span class="text-[10px] text-emerald-700 font-medium">✓ Active Student</span>
          </div>

          <div class="p-3.5 rounded-2xl bg-brand-ivory border border-brand-peachBorder space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-muted block">Candidate User ID</span>
            <span class="font-sans font-bold text-sm text-brand-orange block" id="profileDossierId"><?php echo htmlspecialchars($studentId); ?></span>
            <span class="text-[10px] text-brand-muted font-medium">Permanent Aspirant Roll</span>
          </div>

          <div class="p-3.5 rounded-2xl bg-brand-ivory border border-brand-peachBorder space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-muted block">Registered Email</span>
            <span class="font-sans font-semibold text-xs text-brand-slateText block truncate" id="profileDossierEmail" title="<?php echo htmlspecialchars($studentEmail); ?>"><?php echo htmlspecialchars($studentEmail ?: 'Not Provided'); ?></span>
            <span class="text-[10px] text-brand-muted font-medium">Login & Notifications</span>
          </div>

          <div class="p-3.5 rounded-2xl bg-brand-ivory border border-brand-peachBorder space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-muted block">Mobile Number</span>
            <span class="font-sans font-semibold text-xs text-brand-slateText block" id="profileDossierPhone"><?php echo htmlspecialchars($studentPhone ?: 'Not Provided'); ?></span>
            <span class="text-[10px] text-brand-muted font-medium">SMS CBT Alerts</span>
          </div>

          <div class="p-3.5 rounded-2xl bg-brand-ivory border border-brand-peachBorder space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-muted block">Teaching Cadre Level</span>
            <span class="font-bold text-xs text-brand-orange block" id="profileDossierCadre"><?php echo htmlspecialchars($studentLevelLabel); ?></span>
            <span class="text-[10px] text-brand-muted font-medium">Target Exam Category</span>
          </div>

          <div class="p-3.5 rounded-2xl bg-brand-ivory border border-brand-peachBorder space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-muted block">Home District (Bihar)</span>
            <span class="font-bold text-xs text-brand-slateText block" id="profileDossierDistrict"><?php echo htmlspecialchars($studentDistrict); ?> District</span>
            <span class="text-[10px] text-brand-muted font-medium">38 Districts Merit Base</span>
          </div>

          <div class="p-3.5 rounded-2xl bg-brand-ivory border border-brand-peachBorder space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-muted block">Language Paper (Part I)</span>
            <span class="font-bold text-xs text-emerald-700 block">30 Questions (9 Qualifying Marks)</span>
            <span class="text-[10px] text-brand-muted font-medium">Mandatory Threshold Active</span>
          </div>

          <div class="p-3.5 rounded-2xl bg-brand-ivory border border-brand-peachBorder space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-muted block">Account Status</span>
            <span class="font-bold text-xs text-emerald-600 flex items-center gap-1.5 mt-0.5">
              <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Active Aspirant
            </span>
            <span class="text-[10px] text-brand-muted font-medium">Proctor Engine Enabled</span>
          </div>
        </div>
      </section>

      <!-- 2 PRIMARY CARDS: ACTIVE SUBSCRIPTION & ACHIEVEMENTS -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- 1. YOUR ACTIVE SUBSCRIPTION -->
        <section class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-5">
          <div class="flex items-center justify-between border-b border-brand-peachSoft pb-3.5">
            <div class="flex items-center gap-2.5">
              <span class="w-10 h-10 rounded-2xl bg-orange-100 text-brand-orange flex items-center justify-center font-bold text-lg">💳</span>
              <div>
                <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">Your Active Subscription</h3>
                <p class="text-[11px] text-brand-muted">Current course package, privileges & access validity</p>
              </div>
            </div>
            <span id="profileSubBadge" class="text-xs font-sans font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 px-3 py-1 rounded-full">
              <?php echo htmlspecialchars($studentSubscription['badge'] ?? 'Active Pass'); ?>
            </span>
          </div>

          <div class="space-y-3 text-xs">
            <div class="flex justify-between items-center py-2 border-b border-brand-peachSoft/50">
              <span class="text-brand-muted font-semibold">Subscribed Package:</span>
              <span class="font-black text-brand-slateText text-right text-sm" id="profileSubPlanName">
                <?php echo htmlspecialchars($studentSubscription['plan_name'] ?? 'BPSC TRE 4.0 Standard Pass'); ?>
              </span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-brand-peachSoft/50">
              <span class="text-brand-muted font-semibold">Subscription Status:</span>
              <span class="font-bold text-emerald-600 flex items-center gap-1.5" id="profileSubStatus">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <?php echo htmlspecialchars($studentSubscription['status'] ?? 'Active (Enrolled)'); ?>
              </span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-brand-peachSoft/50">
              <span class="text-brand-muted font-semibold">Fee / Investment:</span>
              <span class="font-extrabold text-brand-slateText font-mono" id="profileSubAmount">
                <?php echo htmlspecialchars($studentSubscription['amount_paid'] ?? 'Free Trial'); ?>
              </span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-brand-peachSoft/50">
              <span class="text-brand-muted font-semibold">Target Teaching Cadre:</span>
              <span class="font-bold text-brand-orange" id="profileSubCadre">
                <?php echo htmlspecialchars($studentLevelLabel); ?>
              </span>
            </div>
            <div class="flex justify-between items-center py-2">
              <span class="text-brand-muted font-semibold">Access Valid Until:</span>
              <span class="font-mono text-brand-slateText font-bold" id="profileSubExpiry">
                <?php echo htmlspecialchars($studentSubscription['expiry'] ?? 'December 31, 2026'); ?>
              </span>
            </div>
          </div>

          <!-- Feature Checklist -->
          <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder space-y-2 text-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-muted block">Included Privileges with Your Pass:</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] font-medium text-brand-slateText">
              <div class="flex items-center gap-1.5">
                <span class="text-emerald-600 font-bold">✓</span> Full Syllabus Practice Mock Tests
              </div>
              <div class="flex items-center gap-1.5">
                <span class="text-emerald-600 font-bold">✓</span> Anti-Cheat Tab Monitor
              </div>
              <div class="flex items-center gap-1.5">
                <span class="text-emerald-600 font-bold">✓</span> SCERT Reference Books & PYQs
              </div>
              <div class="flex items-center gap-1.5">
                <span class="text-emerald-600 font-bold">✓</span> Previous Years Question Papers
              </div>
              <div class="flex items-center gap-1.5">
                <span class="text-emerald-600 font-bold">✓</span> Topic Progress & Strength Analysis
              </div>
              <div class="flex items-center gap-1.5">
                <span class="text-emerald-600 font-bold">✓</span> 38 Districts Merit Ranking
              </div>
            </div>
          </div>

          <div class="pt-2 flex items-center gap-3">
            <button type="button" onclick="openModal('coursesModal')" class="flex-1 py-3 px-4 rounded-xl bg-brand-orange hover:bg-brand-orangeHover text-white text-xs font-bold transition text-center cursor-pointer shadow-sm">
              Upgrade or Switch Course Package →
            </button>
          </div>
        </section>

        <!-- 2. YOUR ACHIEVEMENTS & MOCK RECORD -->
        <section class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-5">
          <div class="flex items-center justify-between border-b border-brand-peachSoft pb-3.5">
            <div class="flex items-center gap-2.5">
              <span class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg">🏆</span>
              <div>
                <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">Your Achievements & Mock Record</h3>
                <p class="text-[11px] text-brand-muted">Individual academic standing & test metrics</p>
              </div>
            </div>
            <span id="profileAchievCountBadge" class="text-xs font-sans font-bold bg-amber-100 text-amber-900 border border-amber-300 px-3 py-1 rounded-full">
              <?php echo (int)($studentAchievements['tests_taken'] ?? 0); ?> Tests Taken
            </span>
          </div>

          <!-- 3 Metrics Cards -->
          <div class="grid grid-cols-3 gap-3 text-center text-xs">
            <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder">
              <span class="text-[10px] uppercase font-bold text-brand-muted block">Tests Taken</span>
              <span class="font-serif text-2xl font-black text-brand-orange mt-1 block" id="profileAchievTests">
                <?php echo (int)($studentAchievements['tests_taken'] ?? 0); ?>
              </span>
              <span class="text-[9px] text-brand-muted font-mono">Attempted</span>
            </div>
            <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder">
              <span class="text-[10px] uppercase font-bold text-brand-muted block">Best Score</span>
              <span class="font-serif text-2xl font-black text-emerald-600 mt-1 block" id="profileAchievBestScore">
                <?php echo htmlspecialchars($studentAchievements['best_score'] ?? '--'); ?>
              </span>
              <span class="text-[9px] text-brand-muted font-mono">Highest Marks</span>
            </div>
            <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder">
              <span class="text-[10px] uppercase font-bold text-brand-muted block">Accuracy</span>
              <span class="font-serif text-2xl font-black text-brand-slateText mt-1 block" id="profileAchievAccuracy">
                <?php echo htmlspecialchars($studentAchievements['avg_accuracy'] ?? '0%'); ?>
              </span>
              <span class="text-[9px] text-brand-muted font-mono">Avg Accuracy</span>
            </div>
          </div>

          <!-- Earned Merit Badges -->
          <div class="space-y-2 pt-1">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-bold text-brand-slateText uppercase">Your Earned Merit Badges:</span>
              <span class="text-[10px] font-sans font-semibold text-brand-orange font-bold">Verified Credentials</span>
            </div>
            <div class="flex flex-wrap gap-2" id="profileAchievBadgesMount">
              <?php foreach (($studentAchievements['earned_badges'] ?? ['Registered Aspirant', 'Admit Ready']) as $badge): ?>
              <span class="px-3 py-1.5 rounded-xl bg-brand-ivory border border-brand-peachBorder text-xs font-bold text-brand-slateText inline-flex items-center gap-1.5 shadow-2xs">
                <span>🎖️</span> <?php echo htmlspecialchars($badge); ?>
              </span>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Individual Recent CBT Attempt Log -->
          <div class="space-y-2 pt-2 border-t border-brand-peachSoft">
            <span class="text-[11px] font-bold text-brand-slateText uppercase">Recent Mock Test Log:</span>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs border-collapse">
                <thead>
                  <tr class="border-b border-brand-peachSoft text-brand-muted text-[10px] font-bold uppercase">
                    <th class="py-2">Test Assessment</th>
                    <th class="py-2">Date</th>
                    <th class="py-2 text-right">Score</th>
                  </tr>
                </thead>
                <tbody id="profileRecentTestTableBody" class="divide-y border-brand-peachSoft/50 font-medium text-brand-slateText">
                  <!-- Dynamically populated per user -->
                </tbody>
              </table>
            </div>
          </div>
        </section>

      </div>

      <!-- Bottom Return to Dashboard Bar -->
      <div class="bg-white rounded-3xl border border-brand-peachBorder p-5 flex flex-col sm:flex-row justify-between items-center gap-4 shadow-sm">
        <div class="flex items-center gap-3">
          <button type="button" onclick="returnToDashboard(event)" class="px-5 py-2.5 rounded-2xl bg-brand-orange text-white hover:bg-brand-orangeHover text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-sm transform hover:-translate-x-0.5">
            <span>←</span>
            <span>Back to Dashboard</span>
          </button>
          <span class="text-xs text-brand-muted">Return to syllabus matrix, live radar, and question banks</span>
        </div>
        <div class="text-xs text-brand-muted font-medium">
          Infinity Learning · BPSC TRE 4.0 Preparation Portal
        </div>
      </div>

    </main>
