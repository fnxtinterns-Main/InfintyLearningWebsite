<?php
/**
 * Infinity Learning - Candidate / Aspirant Dashboard View
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

$studentLevel = UserManager::getCadreCode($studentLevelLabel);

// Calculate initials
$parts = explode(' ', trim($studentName));
$studentInitials = strtoupper(substr($parts[0] ?? 'C', 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : substr($parts[0] ?? 'A', 1, 1)));

$studentSubscription = $currentUser['subscription'] ?? [
    'plan_name' => 'BPSC TRE 4.0 Standard Pass',
    'status' => 'Active (Enrolled)',
    'badge' => 'Active Access',
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

require_once __DIR__ . '/../../src/LeaderboardManager.php';
$biharDistricts = UserManager::getBiharDistricts();
$initialLeaderboard = LeaderboardManager::getLeaderboard('district', $studentDistrict, $currentUser['id'] ?? null);
$userStanding = $initialLeaderboard['current_user_standing'] ?? null;

require_once __DIR__ . '/../../src/Database.php';
$qpRows = Database::fetchAll("SELECT * FROM question_papers WHERE status = 'published' ORDER BY published_at DESC");
$publishedQuestionPapers = array_map(function($p) {
    if (!empty($p['questions']) && is_string($p['questions'])) {
        $p['questions'] = json_decode($p['questions'], true) ?: [];
    }
    return $p;
}, $qpRows);
?>
    <!-- ========================================================= -->
    <!-- VIEW 1: STUDENT / CANDIDATE DASHBOARD                     -->
    <!-- ========================================================= -->
    <main id="view-student" class="<?php echo ($currentUser && in_array($currentUser['role'] ?? '', ['teacher', 'admin'])) ? 'hidden' : ''; ?> max-w-7xl mx-auto w-full px-3 sm:px-4 lg:px-8 py-4 sm:py-6 flex-1 space-y-4 sm:space-y-6">

      <!-- HERO RIBBON: Dossier & HUD Metrics -->
      <section class="bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder p-4 sm:p-6 lg:p-8 shadow-sm relative overflow-hidden">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 sm:gap-6 relative z-10">
          
          <div class="space-y-2.5 sm:space-y-3 w-full lg:w-auto">
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2.5">
              <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-brand-yellowPill text-brand-yellowText font-bold text-[10px] sm:text-xs">
                👋 WELCOME ASPIRANT
              </span>
              <span class="text-[10px] sm:text-xs px-2 sm:px-2.5 py-0.5 rounded-full bg-brand-ivory text-brand-slateText border border-brand-peachBorder font-semibold">
                Advt. No. 15/2026
              </span>
              <span class="text-[10px] sm:text-xs px-2 sm:px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold">
                30% Min Language Threshold Active
              </span>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
              <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-brand-ivory border-2 border-brand-peachBorder flex items-center justify-center font-serif text-xl sm:text-2xl font-bold text-brand-orange shadow-sm cursor-pointer hover:border-brand-orange transition shrink-0" id="studentHeroAvatar" onclick="openProfilePage(event)" title="Click to view candidate profile">
                <?php echo htmlspecialchars($studentInitials ?: 'CA'); ?>
              </div>
              <div class="min-w-0">
                <h1 class="text-xl sm:text-3xl font-extrabold text-brand-slateText tracking-tight cursor-pointer hover:text-brand-orange transition truncate" id="studentHeroName" onclick="openProfilePage(event)" title="Click to view candidate profile">
                  <?php echo htmlspecialchars($studentName); ?>
                </h1>
                <p class="text-xs sm:text-sm text-brand-muted font-medium truncate" id="studentHeroCadreLabel">
                  <?php echo htmlspecialchars($studentLevelLabel); ?> · <span class="font-semibold text-brand-orange"><?php echo htmlspecialchars($studentDistrict); ?> District</span>
                </p>
                <div class="pt-0.5 flex flex-wrap items-center gap-1.5 text-[11px] font-sans text-brand-muted font-medium" id="studentHeroContactPill">
                  <span class="truncate max-w-[200px] sm:max-w-none">✉ <?php echo htmlspecialchars($studentEmail); ?></span>
                  <?php if (!empty($studentPhone)): ?>
                  <span>· 📱 <?php echo htmlspecialchars($studentPhone); ?></span>
                  <?php endif; ?>
                </div>
                <div class="pt-1.5">
                  <button type="button" onclick="openProfilePage(event)" class="inline-flex items-center gap-1.5 px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-xl bg-brand-orangeLight hover:bg-brand-peachSoft border border-brand-peachBorder text-[11px] sm:text-xs font-bold text-brand-orange transition cursor-pointer shadow-xs">
                    <span>👤</span> <span>View Profile & Subscriptions →</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Dynamic HUD Cards: 2 cols on mobile, 4 cols on desktop -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 w-full lg:w-auto">
            <div class="bg-brand-ivory rounded-2xl border border-brand-peachBorder p-2.5 sm:p-3.5 text-center">
              <div class="font-serif text-xl sm:text-3xl font-bold text-brand-orange" id="hudCompleted">0</div>
              <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-brand-muted mt-0.5">Mastered</div>
            </div>
            <div class="bg-brand-ivory rounded-2xl border border-brand-peachBorder p-2.5 sm:p-3.5 text-center">
              <div class="font-serif text-xl sm:text-3xl font-bold text-brand-orange" id="hudProgress">0%</div>
              <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-brand-muted mt-0.5">Prepared</div>
            </div>
            <div class="bg-brand-ivory rounded-2xl border border-brand-peachBorder p-2.5 sm:p-3.5 text-center">
              <div class="font-serif text-xl sm:text-3xl font-bold text-brand-orange" id="hudAvailableTestsCount">0</div>
              <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-brand-muted mt-0.5">Live CBTs</div>
            </div>
            <div class="bg-brand-ivory rounded-2xl border border-brand-peachBorder p-2.5 sm:p-3.5 text-center">
              <div class="font-serif text-xl sm:text-3xl font-bold text-brand-orange">-0.33</div>
              <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-brand-muted mt-0.5">Negative</div>
            </div>
          </div>

        </div>

        <!-- Academic Standing Gauge Bar -->
        <div class="mt-4 sm:mt-6 pt-3 sm:pt-5 border-t border-brand-peachSoft">
          <div class="flex justify-between items-center text-xs font-bold text-brand-slateText mb-1.5">
            <span class="flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-brand-teal"></span>
              Academic Standing & Topic Progression
            </span>
            <span class="text-brand-teal font-mono text-xs" id="gaugePercentage">0% Syllabus Covered</span>
          </div>
          <div class="w-full bg-brand-peachSoft h-2 sm:h-2.5 rounded-full overflow-hidden">
            <div id="gaugeBar" class="bg-brand-teal h-full transition-all duration-500 ease-out" style="width: 0%"></div>
          </div>
        </div>
      </section>

      <!-- PROMINENT TARGET EXAM COUNTDOWN HERO BANNER -->
      <section class="bg-gradient-to-r from-brand-orange via-orange-600 to-amber-500 rounded-2xl sm:rounded-3xl p-4 sm:p-7 text-white shadow-md relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-4 sm:gap-6">
        <div class="space-y-1.5 text-center md:text-left z-10 w-full md:w-auto">
          <div class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-[10px] sm:text-[11px] font-sans font-bold uppercase tracking-wider text-amber-100">
            <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-400 animate-ping"></span>
            Official Target Exam Countdown
          </div>
          <h2 class="text-lg sm:text-2xl font-black tracking-tight text-white">BPSC TRE 4.0 Exam Preparation</h2>
          <p class="text-xs text-orange-100 max-w-xl leading-relaxed">
            Target Examination Date: <span class="font-bold underline decoration-amber-300"><?php echo date('F d, Y', strtotime(AppConfig::TARGET_EXAM_DATE)); ?></span> · Part I Language Qualifying (30Q) & GS Merit Framework Active.
          </p>
        </div>

        <!-- 4 Prominent Digit Cards -->
        <div class="flex items-center justify-center gap-2 sm:gap-3.5 z-10 w-full md:w-auto">
          <div class="bg-black/25 backdrop-blur-md border border-white/20 rounded-2xl p-2 sm:p-3.5 flex-1 sm:flex-none sm:min-w-[76px] text-center shadow-inner">
            <div class="font-mono text-xl sm:text-3xl font-black text-white" id="examDays">--</div>
            <div class="text-[9px] sm:text-[10px] uppercase font-bold tracking-wider text-orange-100 mt-0.5">Days</div>
          </div>
          <div class="bg-black/25 backdrop-blur-md border border-white/20 rounded-2xl p-2 sm:p-3.5 flex-1 sm:flex-none sm:min-w-[76px] text-center shadow-inner">
            <div class="font-mono text-xl sm:text-3xl font-black text-white" id="examHours">--</div>
            <div class="text-[9px] sm:text-[10px] uppercase font-bold tracking-wider text-orange-100 mt-0.5">Hours</div>
          </div>
          <div class="bg-black/25 backdrop-blur-md border border-white/20 rounded-2xl p-2 sm:p-3.5 flex-1 sm:flex-none sm:min-w-[76px] text-center shadow-inner">
            <div class="font-mono text-xl sm:text-3xl font-black text-white" id="examMins">--</div>
            <div class="text-[9px] sm:text-[10px] uppercase font-bold tracking-wider text-orange-100 mt-0.5">Mins</div>
          </div>
          <div class="bg-black/25 backdrop-blur-md border border-white/20 rounded-2xl p-2 sm:p-3.5 flex-1 sm:flex-none sm:min-w-[76px] text-center shadow-inner">
            <div class="font-mono text-xl sm:text-3xl font-black text-amber-300" id="examSecs">--</div>
            <div class="text-[9px] sm:text-[10px] uppercase font-bold tracking-wider text-orange-100 mt-0.5">Secs</div>
          </div>
        </div>
      </section>

      <!-- CANDIDATE PUBLISHED CBT TESTS CAROUSEL -->
      <section class="bg-white rounded-3xl border border-brand-peachBorder p-5 sm:p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-brand-peachSoft pb-3">
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">Practice Mock Tests</h2>
              <span class="text-[10px] font-mono bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">Live Tests</span>
            </div>
            <p class="text-[11px] text-brand-muted">Full-length practice tests matching official exam timing, syllabus pattern, and negative marking.</p>
          </div>
          <div class="flex items-center gap-2 self-end sm:self-auto">
            <button type="button" 
                    data-collapsible-target="studentCbtTestsBody" 
                    onclick="toggleSectionCollapse('studentCbtTestsBody', this, 'CBT Tests')" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                    title="Minimize CBT Tests"
                    aria-expanded="true">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
              <span>Minimize</span>
            </button>
            <button onclick="launchProctoredCBT()" class="px-3.5 py-1.5 rounded-xl bg-brand-orange hover:bg-brand-orangeHover text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
              <span>⚡</span> Start Mock Test
            </button>
          </div>
        </div>

        <div id="studentCbtTestsBody" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3" id="candidatePublishedTestsMount">
            <?php if (empty($publishedQuestionPapers)): ?>
              <div class="col-span-3 p-8 text-center bg-brand-ivory rounded-2xl border border-brand-peachBorder space-y-2">
                <span class="text-3xl block">📋</span>
                <h4 class="font-bold text-sm text-brand-slateText">No Mock Tests Available Yet</h4>
                <p class="text-xs text-brand-muted max-w-md mx-auto">
                  No mock tests are available right now. New mock tests will appear here as soon as your teachers publish them.
                </p>
              </div>
            <?php else: ?>
              <?php foreach ($publishedQuestionPapers as $qp): 
                $qpId = $qp['id'] ?? '';
                $qpTitle = $qp['title'] ?? 'Mock Test';
                $qpCadre = $qp['cadre'] ?? 'All Cadres';
                $qpDuration = $qp['duration'] ?? 120;
                $qpQuestionsCount = count($qp['questions'] ?? []);
                $qpPenalty = $qp['penalty'] ?? '-0.33';
                $qpVis = $qp['visibility'] ?? 'all';
                $qpAuthor = $qp['author'] ?? 'Faculty';
              ?>
                <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder flex flex-col justify-between gap-3 shadow-xs hover:border-brand-orange transition group">
                  <div>
                    <div class="flex items-center justify-between gap-1 mb-1.5 flex-wrap">
                      <span class="text-[10px] font-sans font-bold text-brand-orange bg-brand-peachSoft px-2 py-0.5 rounded"><?php echo htmlspecialchars($qpId); ?></span>
                      <?php if ($qpVis === 'candidate_only'): ?>
                        <span class="text-[10px] font-sans font-semibold px-2 py-0.5 rounded font-bold uppercase bg-purple-100 text-purple-800 border border-purple-200">Private Batch</span>
                      <?php else: ?>
                        <span class="text-[10px] font-sans font-semibold px-2 py-0.5 rounded font-bold uppercase bg-emerald-100 text-emerald-800">Public</span>
                      <?php endif; ?>
                    </div>
                    <h3 class="font-bold text-brand-slateText text-xs group-hover:text-brand-orange transition leading-snug"><?php echo htmlspecialchars($qpTitle); ?></h3>
                    <p class="text-[10px] text-brand-muted mt-1 font-mono"><?php echo htmlspecialchars($qpCadre); ?> · <?php echo (int)$qpDuration; ?> Mins · <?php echo (int)$qpQuestionsCount; ?> Questions · Rule <?php echo htmlspecialchars($qpPenalty); ?></p>
                    <p class="text-[9px] text-brand-muted/80 font-mono mt-0.5">Prepared by <?php echo htmlspecialchars($qpAuthor); ?></p>
                  </div>
                  <button type="button" onclick="launchProctoredCBT('<?php echo htmlspecialchars($qpId); ?>')" class="w-full py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>⚡</span> Start CBT Examination
                  </button>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- DIGITAL TEXTBOOKS & REFERENCE LIBRARY SECTION (READ-ONLY ONLINE) -->
      <section class="bg-white rounded-3xl border border-brand-peachBorder p-5 sm:p-6 shadow-sm space-y-4" id="candidateDigitalBooksSection">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-brand-peachSoft pb-3">
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-blue-100 border border-blue-200 text-blue-900 text-[11px] font-bold font-mono">
                📚 Official Academic Bookshelf
              </span>
              <span class="text-[10px] font-mono bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full border border-emerald-200 flex items-center gap-1">
                📥 Direct Document Downloads Enabled
              </span>
            </div>
            <h2 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText mt-1">SCERT & NCERT Textbooks</h2>
            <p class="text-[11px] text-brand-muted">Download official school textbooks and study books directly to your phone or computer for offline reading.</p>
          </div>
          <div class="flex items-center gap-2 self-end sm:self-auto">
            <button type="button" 
                    data-collapsible-target="studentBooksShelfBody" 
                    onclick="toggleSectionCollapse('studentBooksShelfBody', this, 'Books Library')" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                    title="Minimize Books Library"
                    aria-expanded="true">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
              <span>Minimize</span>
            </button>
            <button type="button" onclick="openModal('booksModal')" class="px-3.5 py-1.5 rounded-xl bg-brand-orangeLight hover:bg-brand-peachSoft text-brand-orange border border-brand-peachBorder text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
              <span>📚</span> View All 12 SCERT Books →
            </button>
          </div>
        </div>

        <div id="studentBooksShelfBody" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="candidateBooksCardsMount">
            <!-- Book 1: SCERT History -->
            <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder hover:border-brand-orange transition shadow-xs flex flex-col justify-between gap-4 group">
              <div class="space-y-2">
                <div class="flex items-center justify-between gap-1 flex-wrap">
                  <span class="text-[10px] font-sans font-bold text-emerald-800 bg-emerald-100 border border-emerald-200 px-2 py-0.5 rounded">FREE ACCESS</span>
                  <span class="text-[11px] font-sans text-brand-muted font-medium font-bold">Classes 6–8 (Middle)</span>
                </div>
                <div class="flex items-start gap-3">
                  <div class="w-12 h-14 rounded-xl bg-orange-100 border border-orange-200 text-brand-orange flex items-center justify-center text-2xl shadow-inner shrink-0 group-hover:scale-105 transition-transform">
                    📜
                  </div>
                  <div>
                    <h3 class="font-bold text-brand-slateText text-xs group-hover:text-brand-orange transition leading-snug">Bihar SCERT History (Class 6–8)</h3>
                    <p class="text-[10px] text-brand-muted mt-0.5 font-mono">History & INM · SCERT Bihar</p>
                    <span class="inline-block mt-1 text-[10px] font-sans font-semibold bg-white border border-brand-peachBorder text-slate-600 px-1.5 py-0.5 rounded">5 Key Chapters Included</span>
                  </div>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed line-clamp-2">
                  Covers ancient Bihar history, Mauryan Empire, Nalanda, 1857 revolt, and Champaran Satyagraha.
                </p>
              </div>
              <div class="pt-2 border-t border-brand-peachSoft/80 flex items-center justify-between gap-2">
                <span class="text-[10px] font-mono text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 font-bold">📥 Downloadable</span>
                <button type="button" onclick="downloadBook('BK_01')" class="px-4 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                  <span>📥</span> Download Book (.doc) →
                </button>
              </div>
            </div>

            <!-- Book 2: NCERT Mathematics -->
            <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder hover:border-brand-orange transition shadow-xs flex flex-col justify-between gap-4 group">
              <div class="space-y-2">
                <div class="flex items-center justify-between gap-1 flex-wrap">
                  <span class="text-[10px] font-sans font-bold text-emerald-800 bg-emerald-100 border border-emerald-200 px-2 py-0.5 rounded">FREE ACCESS</span>
                  <span class="text-[11px] font-sans text-brand-muted font-medium font-bold">All Teaching Levels</span>
                </div>
                <div class="flex items-start gap-3">
                  <div class="w-12 h-14 rounded-xl bg-blue-100 border border-blue-200 text-blue-700 flex items-center justify-center text-2xl shadow-inner shrink-0 group-hover:scale-105 transition-transform">
                    📐
                  </div>
                  <div>
                    <h3 class="font-bold text-brand-slateText text-xs group-hover:text-brand-orange transition leading-snug">NCERT Elementary Mathematics</h3>
                    <p class="text-[10px] text-brand-muted mt-0.5 font-mono">Elementary Math · NCERT</p>
                    <span class="inline-block mt-1 text-[10px] font-sans font-semibold bg-white border border-brand-peachBorder text-slate-600 px-1.5 py-0.5 rounded">5 Core Units</span>
                  </div>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed line-clamp-2">
                  Key formulas and shortcut methods for Number System, Percentage, Profit & Loss, and Mensuration.
                </p>
              </div>
              <div class="pt-2 border-t border-brand-peachSoft/80 flex items-center justify-between gap-2">
                <span class="text-[10px] font-mono text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 font-bold">📥 Downloadable</span>
                <button type="button" onclick="downloadBook('BK_02')" class="px-4 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                  <span>📥</span> Download Book (.doc) →
                </button>
              </div>
            </div>

            <!-- Book 3: General Science -->
            <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder hover:border-brand-orange transition shadow-xs flex flex-col justify-between gap-4 group">
              <div class="space-y-2">
                <div class="flex items-center justify-between gap-1 flex-wrap">
                  <span class="text-[10px] font-sans font-bold text-brand-orange bg-brand-peachSoft border border-brand-peachBorder px-2 py-0.5 rounded">PREMIUM VAULT</span>
                  <span class="text-[11px] font-sans text-brand-muted font-medium font-bold">Classes 9–10 (Secondary)</span>
                </div>
                <div class="flex items-start gap-3">
                  <div class="w-12 h-14 rounded-xl bg-purple-100 border border-purple-200 text-purple-700 flex items-center justify-center text-2xl shadow-inner shrink-0 group-hover:scale-105 transition-transform">
                    🔬
                  </div>
                  <div>
                    <h3 class="font-bold text-brand-slateText text-xs group-hover:text-brand-orange transition leading-snug">General Science (Physics, Chemistry & Biology)</h3>
                    <p class="text-[10px] text-brand-muted mt-0.5 font-mono">General Science · School Curriculum</p>
                    <span class="inline-block mt-1 text-[10px] font-sans font-semibold bg-white border border-brand-peachBorder text-slate-600 px-1.5 py-0.5 rounded">5 Complete Core Units</span>
                  </div>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed line-clamp-2">
                  NCERT-based revision notes on Physics, Chemistry, and Biology concepts.
                </p>
              </div>
              <div class="pt-2 border-t border-brand-peachSoft/80 flex items-center justify-between gap-2">
                <span class="text-[10px] font-mono text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 font-bold">📥 Downloadable</span>
                <button type="button" onclick="downloadBook('BK_03')" class="px-4 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                  <span>📥</span> Download Book (.doc) →
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- OFFICIAL BPSC TRE PREVIOUS YEAR PAPERS (PYQ) SECTION (DOWNLOADABLE) -->
      <section class="bg-white rounded-3xl border border-brand-peachBorder p-5 sm:p-6 shadow-sm space-y-4" id="candidatePyqSection">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-brand-peachSoft pb-3">
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-purple-100 border border-purple-200 text-purple-900 text-[11px] font-bold font-mono">
                📜 Previous Year Papers (PYQs)
              </span>
              <span class="text-[10px] font-mono bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full border border-emerald-200 flex items-center gap-1">
                📥 Direct Document Downloads Enabled
              </span>
            </div>
            <h2 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText mt-1">Previous Year Question Papers (PYQs)</h2>
            <p class="text-[11px] text-brand-muted">Download real question papers from previous BPSC teacher exams to understand the exam pattern and question style.</p>
          </div>
          <div class="flex items-center gap-2 self-end sm:self-auto">
            <button type="button" 
                    data-collapsible-target="studentPyqShelfBody" 
                    onclick="toggleSectionCollapse('studentPyqShelfBody', this, 'PYQ Vault')" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                    title="Minimize PYQ Vault"
                    aria-expanded="true">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
              <span>Minimize</span>
            </button>
            <button type="button" onclick="openModal('pyqModal')" class="px-3.5 py-1.5 rounded-xl bg-brand-orangeLight hover:bg-brand-peachSoft text-brand-orange border border-brand-peachBorder text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
              <span>📜</span> View All Past Papers →
            </button>
          </div>
        </div>

        <div id="studentPyqShelfBody" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="candidatePyqCardsMount">
            <!-- Paper 1 -->
            <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder hover:border-brand-orange transition shadow-xs flex flex-col justify-between gap-3 group">
              <div class="space-y-1.5">
                <div class="flex items-center justify-between gap-1 flex-wrap">
                  <span class="text-[10px] font-sans font-semibold font-bold px-2 py-0.5 rounded uppercase bg-blue-100 text-blue-800 border border-blue-200">BPSC TRE 3.0</span>
                  <span class="text-[11px] font-sans text-brand-muted font-medium font-bold">Classes 6–8 (Middle)</span>
                </div>
                <h3 class="font-bold text-brand-slateText text-xs group-hover:text-brand-orange transition leading-snug">BPSC TRE 3.0 Official Question Paper (Re-Exam)</h3>
                <p class="text-[10px] text-brand-muted font-mono">Mathematics & Science · July 2024 Shift 1</p>
                <div class="flex items-center gap-2 pt-1 text-[10px] text-slate-500 font-mono">
                  <span>150 Questions</span> · <span>-0.33 Marking</span> · <span>Bilingual</span>
                </div>
              </div>
              <div class="pt-2 border-t border-brand-peachSoft/80 flex items-center justify-between gap-2">
                <span class="text-[10px] font-mono text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 font-bold">📥 Downloadable</span>
                <button type="button" onclick="downloadPyq('PYQ_01')" class="px-4 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                  <span>📥</span> Download Paper (.doc) →
                </button>
              </div>
            </div>

            <!-- Paper 2 -->
            <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder hover:border-brand-orange transition shadow-xs flex flex-col justify-between gap-3 group">
              <div class="space-y-1.5">
                <div class="flex items-center justify-between gap-1 flex-wrap">
                  <span class="text-[10px] font-sans font-semibold font-bold px-2 py-0.5 rounded uppercase bg-blue-100 text-blue-800 border border-blue-200">BPSC TRE 3.0</span>
                  <span class="text-[11px] font-sans text-brand-muted font-medium font-bold">Classes 6–8 (Middle)</span>
                </div>
                <h3 class="font-bold text-brand-slateText text-xs group-hover:text-brand-orange transition leading-snug">BPSC TRE 3.0 Social Science Solved Paper</h3>
                <p class="text-[10px] text-brand-muted font-mono">Social Science · March 2024 Shift 2</p>
                <div class="flex items-center gap-2 pt-1 text-[10px] text-slate-500 font-mono">
                  <span>150 Questions</span> · <span>Official Answer Key</span>
                </div>
              </div>
              <div class="pt-2 border-t border-brand-peachSoft/80 flex items-center justify-between gap-2">
                <span class="text-[10px] font-mono text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 font-bold">📥 Downloadable</span>
                <button type="button" onclick="downloadPyq('PYQ_02')" class="px-4 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                  <span>📥</span> Download Paper (.doc) →
                </button>
              </div>
            </div>

            <!-- Paper 3 -->
            <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder hover:border-brand-orange transition shadow-xs flex flex-col justify-between gap-3 group">
              <div class="space-y-1.5">
                <div class="flex items-center justify-between gap-1 flex-wrap">
                  <span class="text-[10px] font-sans font-semibold font-bold px-2 py-0.5 rounded uppercase bg-blue-100 text-blue-800 border border-blue-200">BPSC TRE 2.0</span>
                  <span class="text-[11px] font-sans text-brand-muted font-medium font-bold">Classes 9–10 (Secondary)</span>
                </div>
                <h3 class="font-bold text-brand-slateText text-xs group-hover:text-brand-orange transition leading-snug">BPSC TRE 2.0 Official Question Paper with Answer Key</h3>
                <p class="text-[10px] text-brand-muted font-mono">Secondary Science & GS · Dec 2023</p>
                <div class="flex items-center gap-2 pt-1 text-[10px] text-slate-500 font-mono">
                  <span>150 Questions</span> · <span>Complete Key</span>
                </div>
              </div>
              <div class="pt-2 border-t border-brand-peachSoft/80 flex items-center justify-between gap-2">
                <span class="text-[10px] font-mono text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 font-bold">📥 Downloadable</span>
                <button type="button" onclick="downloadPyq('PYQ_03')" class="px-4 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                  <span>📥</span> Download Paper (.doc) →
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- CANDIDATE VIEW: BPSC TRE 4.0 ASPIRANT MERIT LEADERBOARD -->
      <section class="bg-white rounded-3xl border border-brand-peachBorder p-5 sm:p-7 shadow-sm space-y-5" id="candidateLeaderboardSection">
        
        <!-- Header & Ranking Scope Switcher -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 border-b border-brand-peachSoft pb-4">
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-amber-100 border border-amber-300 text-amber-900 text-[11px] font-bold font-mono">
                🏆 BPSC TRE 4.0 Official Merit Rankings
              </span>
              <span class="text-[10px] font-mono bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full border border-emerald-300 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live Database Standings
              </span>
            </div>
            <h2 class="text-base sm:text-lg font-black text-brand-slateText mt-1.5 tracking-tight">Student Leaderboard & Rankings</h2>
            <p class="text-xs text-brand-muted">Live candidate rankings based on mock test scores and exam accuracy across Bihar.</p>
          </div>

          <!-- Controls: District vs State Toggle + District Selector -->
          <div class="flex flex-wrap items-center gap-2 sm:gap-2.5 w-full lg:w-auto">
            <div class="inline-flex p-1 bg-brand-ivory rounded-2xl border border-brand-peachBorder text-xs font-bold shadow-2xs w-full sm:w-auto">
              <button type="button" id="btnLeaderboardDistrict" onclick="switchLeaderboardScope('district')" class="flex-1 sm:flex-none justify-center px-3.5 py-2 sm:py-1.5 rounded-xl transition text-xs font-bold bg-brand-orange text-white shadow-xs cursor-pointer flex items-center gap-1.5">
                <span>📍</span> District Ranking
              </button>
              <button type="button" id="btnLeaderboardState" onclick="switchLeaderboardScope('state')" class="flex-1 sm:flex-none justify-center px-3.5 py-2 sm:py-1.5 rounded-xl transition text-xs font-bold text-brand-slateText hover:bg-brand-peachSoft cursor-pointer flex items-center gap-1.5">
                <span>🏛️</span> State Ranking (All Bihar)
              </button>
            </div>

            <!-- District Dropdown Selector (Active for District Scope) -->
            <div id="leaderboardDistrictSelectWrapper" class="relative w-full sm:w-auto">
              <select id="leaderboardDistrictSelect" onchange="changeLeaderboardDistrict(this.value)" class="w-full sm:w-auto py-2 px-3 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-bold text-brand-slateText focus:outline-none focus:ring-2 focus:ring-brand-orange shadow-2xs cursor-pointer">
                <?php foreach ($biharDistricts as $dist): ?>
                  <option value="<?php echo htmlspecialchars($dist); ?>" <?php echo (strcasecmp($dist, $studentDistrict) === 0) ? 'selected' : ''; ?>>
                    📍 <?php echo htmlspecialchars($dist); ?> District
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Minimize / Expand Leaderboard Button -->
            <button type="button" 
                    data-collapsible-target="studentLeaderboardBody" 
                    onclick="toggleSectionCollapse('studentLeaderboardBody', this, 'Leaderboard')" 
                    class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs w-full sm:w-auto" 
                    title="Minimize Leaderboard"
                    aria-expanded="true">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
              <span>Minimize</span>
            </button>
          </div>
        </div>

        <div id="studentLeaderboardBody" class="space-y-5">
        <!-- Current Student Standing Highlight Banner -->
        <?php if ($userStanding): ?>
        <div id="leaderboardUserStandingCard" class="bg-gradient-to-r from-amber-50 via-orange-50/70 to-amber-100/60 border border-brand-orange/40 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row items-center justify-between gap-4 shadow-2xs">
          <div class="flex items-center gap-3.5 w-full md:w-auto">
            <div class="w-12 h-12 rounded-2xl bg-brand-orange text-white font-serif font-black text-xl flex items-center justify-center shadow-sm shrink-0">
              #<?php echo (int)($userStanding['district_rank'] ?? 1); ?>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="font-bold text-sm text-brand-slateText"><?php echo htmlspecialchars($userStanding['full_name']); ?></span>
                <span class="px-2 py-0.5 rounded-md bg-brand-orange text-white text-[9px] font-bold font-mono uppercase tracking-wider">You</span>
                <span class="text-[11px] font-sans text-brand-muted font-medium">ID: <?php echo htmlspecialchars($userStanding['id']); ?></span>
              </div>
              <p class="text-xs text-brand-slateText mt-0.5">
                <span class="font-bold text-brand-orange">District Rank #<?php echo (int)$userStanding['district_rank']; ?> in <?php echo htmlspecialchars($userStanding['district']); ?></span> · 
                <span class="text-brand-muted">State Rank #<?php echo (int)$userStanding['state_rank']; ?> in Bihar</span>
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3 sm:gap-6 w-full md:w-auto justify-between md:justify-end border-t md:border-t-0 pt-3 md:pt-0 border-amber-200/60 text-center">
            <div>
              <div class="font-mono text-base font-black text-brand-orange" id="standingCardScore">
                <?php echo number_format($userStanding['overall_score'], 2); ?> <span class="text-[10px] text-brand-muted font-normal">/ 150</span>
              </div>
              <div class="text-[9px] uppercase font-bold text-brand-muted">Overall Score</div>
            </div>
            <div class="h-7 w-px bg-amber-200"></div>
            <div>
              <div class="font-mono text-base font-black text-emerald-700" id="standingCardAcc">
                <?php echo $userStanding['avg_accuracy']; ?>%
              </div>
              <div class="text-[9px] uppercase font-bold text-brand-muted">Avg. Accuracy</div>
            </div>
            <div class="h-7 w-px bg-amber-200"></div>
            <div>
              <div class="font-mono text-base font-black text-brand-slateText" id="standingCardTests">
                <?php echo $userStanding['tests_taken']; ?>
              </div>
              <div class="text-[9px] uppercase font-bold text-brand-muted">Mocks Attempted</div>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Top 3 Podium Showcase -->
        <div id="leaderboardPodiumMount" class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
          <?php 
          $top3 = array_slice($initialLeaderboard['rankings'] ?? [], 0, 3);
          $podiumStyles = [
            0 => ['badge' => '🥇 Rank 1 (Topper)', 'border' => 'border-amber-400 bg-gradient-to-b from-amber-50 to-amber-100/40 text-amber-900', 'tag' => 'bg-amber-400 text-amber-950', 'icon' => '👑'],
            1 => ['badge' => '🥈 Rank 2 (Elite)', 'border' => 'border-slate-300 bg-gradient-to-b from-slate-50 to-slate-100/50 text-slate-800', 'tag' => 'bg-slate-300 text-slate-800', 'icon' => '🥈'],
            2 => ['badge' => '🥉 Rank 3 (Honors)', 'border' => 'border-orange-300 bg-gradient-to-b from-orange-50 to-orange-100/40 text-orange-900', 'tag' => 'bg-orange-300 text-orange-950', 'icon' => '🥉']
          ];
          foreach ($top3 as $idx => $podiumItem):
            $style = $podiumStyles[$idx] ?? $podiumStyles[2];
          ?>
            <div class="rounded-2xl border-2 <?php echo $style['border']; ?> p-4 shadow-xs flex flex-col justify-between gap-3 relative overflow-hidden">
              <div class="flex items-start justify-between gap-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono <?php echo $style['tag']; ?>">
                  <span><?php echo $style['icon']; ?></span> <?php echo $style['badge']; ?>
                </span>
                <span class="font-mono text-xs font-bold text-brand-muted">#<?php echo $podiumItem['display_rank']; ?></span>
              </div>

              <div>
                <h4 class="font-extrabold text-sm text-brand-slateText truncate" title="<?php echo htmlspecialchars($podiumItem['full_name']); ?>">
                  <?php echo htmlspecialchars($podiumItem['full_name']); ?>
                  <?php if (!empty($podiumItem['is_current_user'])): ?>
                    <span class="text-[10px] font-sans font-semibold px-1.5 py-0.2 rounded bg-brand-orange text-white uppercase ml-1">You</span>
                  <?php endif; ?>
                </h4>
                <p class="text-[11px] text-brand-muted mt-0.5 truncate">
                  <?php echo htmlspecialchars($podiumItem['district']); ?> · <?php echo htmlspecialchars($podiumItem['cadre_label']); ?>
                </p>
              </div>

              <div class="pt-2.5 border-t border-black/5 flex items-center justify-between text-xs">
                <div>
                  <span class="text-[9px] uppercase font-bold text-brand-muted block">Score</span>
                  <span class="font-sans font-bold text-brand-orange text-sm"><?php echo number_format($podiumItem['overall_score'], 2); ?></span>
                  <span class="text-[10px] text-brand-muted">/150</span>
                </div>
                <div class="text-right">
                  <span class="text-[9px] uppercase font-bold text-brand-muted block">Accuracy</span>
                  <span class="font-sans font-bold text-emerald-700"><?php echo $podiumItem['avg_accuracy']; ?>%</span>
                </div>
                <div class="text-right">
                  <span class="text-[9px] uppercase font-bold text-brand-muted block">Mocks</span>
                  <span class="font-sans font-bold text-brand-slateText"><?php echo $podiumItem['tests_taken']; ?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($top3)): ?>
            <div class="col-span-3 p-6 text-center text-xs text-brand-muted bg-brand-ivory rounded-2xl border border-brand-peachBorder">
              No aspirants ranked in this district yet. Launch an assessment below to claim the #1 spot!
            </div>
          <?php endif; ?>
        </div>

        <!-- Full Leaderboard Table with Live Search Filter -->
        <div class="space-y-3 pt-2">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="flex items-center gap-2">
              <h3 class="text-xs font-bold uppercase tracking-wider text-brand-slateText" id="leaderboardTableTitle">
                Aspirants Ranking (<?php echo htmlspecialchars($initialLeaderboard['selected_district'] ?? $studentDistrict); ?> District)
              </h3>
              <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-brand-peachSoft text-brand-orange font-bold border border-brand-peachBorder" id="leaderboardCountBadge">
                <?php echo count($initialLeaderboard['rankings'] ?? []); ?> Candidates Listed
              </span>
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full sm:w-72">
              <input type="text" id="leaderboardSearchInput" oninput="filterLeaderboardTable(this.value)" placeholder="Search aspirant full name or district..." class="w-full py-1.5 pl-8 pr-3 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-orange text-brand-slateText shadow-2xs">
              <span class="absolute left-2.5 top-2 text-brand-muted text-xs">🔍</span>
            </div>
          </div>

          <!-- Responsive Table -->
          <div class="overflow-x-auto rounded-2xl border border-brand-peachBorder shadow-2xs">
            <table class="w-full text-left text-xs border-collapse min-w-[650px]">
              <thead>
                <tr class="bg-brand-ivory border-b border-brand-peachBorder font-bold text-brand-slateText whitespace-nowrap">
                  <th class="p-3.5 text-center w-14">Rank</th>
                  <th class="p-3.5">Aspirant Full Name</th>
                  <th class="p-3.5">Teaching Cadre</th>
                  <th class="p-3.5">District</th>
                  <th class="p-3.5 text-center">Mocks Attempted</th>
                  <th class="p-3.5 text-right">Overall Merit Score</th>
                  <th class="p-3.5 text-right">Avg. Accuracy</th>
                  <th class="p-3.5 text-center">Merit Standing</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-brand-peachSoft font-medium text-brand-slateText whitespace-nowrap" id="leaderboardTableBody">
                <?php if (empty($initialLeaderboard['rankings'])): ?>
                  <tr>
                    <td colspan="8" class="p-6 text-center text-brand-muted text-xs">
                      No candidate test records found for this scope yet.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($initialLeaderboard['rankings'] as $stu): 
                    $isYou = !empty($stu['is_current_user']);
                    $rowBg = $isYou ? 'bg-amber-50/80 font-bold border-l-4 border-l-brand-orange' : 'hover:bg-brand-ivory/50 transition';
                    $rankVal = $stu['display_rank'] ?? 1;
                    $rankMedal = $rankVal == 1 ? '🥇 1' : ($rankVal == 2 ? '🥈 2' : ($rankVal == 3 ? '🥉 3' : '#' . $rankVal));
                  ?>
                    <tr class="leaderboard-row <?php echo $rowBg; ?>" data-fullname="<?php echo strtolower(htmlspecialchars($stu['full_name'])); ?>" data-district="<?php echo strtolower(htmlspecialchars($stu['district'])); ?>">
                      <td class="p-3.5 text-center font-sans font-bold <?php echo $rankVal <= 3 ? 'text-brand-orange text-sm' : 'text-brand-muted'; ?>">
                        <?php echo $rankMedal; ?>
                      </td>
                      <td class="p-3.5">
                        <div class="flex items-center gap-1.5 flex-wrap">
                          <span class="font-bold text-brand-slateText"><?php echo htmlspecialchars($stu['full_name']); ?></span>
                          <?php if ($isYou): ?>
                            <span class="px-2 py-0.5 rounded-md bg-brand-orange text-white text-[9px] font-bold font-mono uppercase tracking-wider">You</span>
                          <?php endif; ?>
                        </div>
                      </td>
                      <td class="p-3.5 text-brand-muted text-[11px]">
                        <?php echo htmlspecialchars($stu['cadre_label']); ?>
                      </td>
                      <td class="p-3.5 font-semibold text-brand-slateText">
                        <?php echo htmlspecialchars($stu['district']); ?>
                      </td>
                      <td class="p-3.5 text-center font-sans font-bold text-brand-slateText">
                        <?php echo (int)$stu['tests_taken']; ?>
                      </td>
                      <td class="p-3.5 text-right font-mono font-extrabold text-brand-orange text-sm">
                        <?php echo number_format($stu['overall_score'], 2); ?> <span class="text-[10px] text-brand-muted font-normal">/ 150</span>
                      </td>
                      <td class="p-3.5 text-right font-sans font-bold text-emerald-700">
                        <?php echo $stu['avg_accuracy']; ?>%
                      </td>
                      <td class="p-3.5 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono <?php echo $rankVal <= 3 ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-brand-ivory text-brand-muted border border-brand-peachBorder'; ?>">
                          <?php echo htmlspecialchars($stu['badge']); ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        </div>

      </section>



      <!-- COCKPIT WORKBENCH (DUAL COLUMNS) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT: SCOPE FILTER & 6-AXIS RADAR (4 COLS) -->
        <aside class="lg:col-span-4 space-y-5 lg:sticky lg:top-20">
          
          <!-- Scope Filter -->
          <div class="bg-white rounded-3xl border border-brand-peachBorder p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-brand-peachSoft pb-2.5">
              <div class="flex items-center gap-2">
                <h2 class="text-xs font-bold uppercase tracking-wider text-brand-slateText">Choose Your Subject & Level</h2>
                <span class="text-[10px] font-mono bg-brand-orangeLight text-brand-orange px-2 py-0.5 rounded font-bold border border-brand-peachBorder">Advt. 15/2026</span>
              </div>
              <button type="button" 
                      data-collapsible-target="studentSyllabusFilterBody" 
                      onclick="toggleSectionCollapse('studentSyllabusFilterBody', this, 'Scope Filter')" 
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[11px] font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                      title="Minimize Scope Filter">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                <span>Minimize</span>
              </button>
            </div>

            <div id="studentSyllabusFilterBody" class="space-y-4">
            <!-- Teaching Cadre Tabs -->
            <div>
              <label class="block text-[10px] font-bold text-brand-muted mb-1.5 uppercase">Teaching Cadre Level</label>
              <div class="grid grid-cols-2 gap-1.5 bg-brand-ivory p-1.5 rounded-2xl border border-brand-peachBorder text-xs font-bold" id="cadreTabContainer">
                <button type="button" onclick="handleCadreChange('prm')" id="cadreTab-prm" class="py-2.5 px-2 rounded-xl text-center transition text-[11px] font-bold min-h-[42px] flex items-center justify-center <?php echo $studentLevel === 'prm' ? 'bg-brand-orange text-white shadow-xs' : 'text-brand-slateText hover:bg-brand-peachSoft'; ?> cursor-pointer">
                  Primary 1–5
                </button>
                <button type="button" onclick="handleCadreChange('mid')" id="cadreTab-mid" class="py-2.5 px-2 rounded-xl text-center transition text-[11px] font-bold min-h-[42px] flex items-center justify-center <?php echo $studentLevel === 'mid' ? 'bg-brand-orange text-white shadow-xs' : 'text-brand-slateText hover:bg-brand-peachSoft'; ?> cursor-pointer">
                  Middle 6–8
                </button>
                <button type="button" onclick="handleCadreChange('sec')" id="cadreTab-sec" class="py-2.5 px-2 rounded-xl text-center transition text-[11px] font-bold min-h-[42px] flex items-center justify-center <?php echo $studentLevel === 'sec' ? 'bg-brand-orange text-white shadow-xs' : 'text-brand-slateText hover:bg-brand-peachSoft'; ?> cursor-pointer">
                  Secondary 9–10
                </button>
                <button type="button" onclick="handleCadreChange('hsec')" id="cadreTab-hsec" class="py-2.5 px-2 rounded-xl text-center transition text-[11px] font-bold min-h-[42px] flex items-center justify-center <?php echo $studentLevel === 'hsec' ? 'bg-brand-orange text-white shadow-xs' : 'text-brand-slateText hover:bg-brand-peachSoft'; ?> cursor-pointer">
                  Higher Sec 11–12
                </button>
              </div>
            </div>

            <!-- Custom Scrollable & Searchable Subject Stream Selector with Scroll Controls -->
            <div id="disciplineWrapper" class="relative">
              <div class="flex items-center justify-between mb-1">
                <label class="block text-[10px] font-bold text-brand-muted uppercase">Part III Subject Stream</label>
                <span class="text-[10px] font-sans font-semibold text-brand-orange font-bold" id="subjectCountBadge">0 Subjects</span>
              </div>
              
              <!-- Trigger Button -->
              <button type="button" id="customSubjectDropdownBtn" onclick="toggleSubjectDropdown()" class="w-full text-xs font-bold bg-brand-ivory hover:bg-brand-peachSoft/50 border-2 border-brand-orange/60 rounded-xl p-3 text-brand-slateText flex items-center justify-between transition shadow-xs cursor-pointer min-h-[44px]">
                <span id="selectedSubjectDisplay" class="truncate text-brand-slateText">Mathematics & Science</span>
                <span id="subjectDropdownArrow" class="text-brand-orange transition-transform duration-200 text-xs ml-2">▼</span>
              </button>

              <!-- Hidden Native Input for Logic Sync -->
              <input type="hidden" id="disciplineSelector" value="Mathematics & Science">

              <!-- Dropdown Panel with Smooth Scroll & Instant Search -->
              <div id="customSubjectDropdownMenu" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white border-2 border-brand-peachBorder rounded-2xl shadow-xl z-50 p-2 space-y-2">
                <!-- Search Input Box -->
                <div class="relative">
                  <input type="text" id="subjectSearchInput" oninput="filterSubjectOptions(this.value)" placeholder="Search subjects (e.g. Math, Biology)..." class="w-full p-2 pl-7 rounded-xl border border-brand-peachBorder bg-brand-ivory text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-orange text-brand-slateText">
                  <span class="absolute left-2.5 top-2.5 text-brand-muted text-xs">🔍</span>
                </div>

                <!-- Scrollable Subject Options List Container -->
                <div id="subjectOptionsScrollList" class="max-h-60 overflow-y-auto space-y-1 custom-scroll p-1 divide-y divide-brand-peachSoft/40">
                  <!-- Populated dynamically via JS -->
                </div>
              </div>
            </div>

            <div class="pt-2 border-t border-brand-peachSoft flex items-center justify-between gap-2">
              <button onclick="filterCheckpoints('all')" id="btnFilterAll" class="flex-1 py-2 px-2 rounded-xl text-xs font-bold bg-brand-orange text-white transition cursor-pointer min-h-[38px]">All Topics</button>
              <button onclick="filterCheckpoints('hy')" id="btnFilterHY" class="flex-1 py-2 px-2 rounded-xl text-xs font-bold bg-brand-ivory text-brand-slateText hover:bg-brand-peachSoft transition cursor-pointer min-h-[38px]">High-Yield</button>
              <button onclick="filterCheckpoints('inc')" id="btnFilterInc" class="flex-1 py-2 px-2 rounded-xl text-xs font-bold bg-brand-ivory text-brand-slateText hover:bg-brand-peachSoft transition cursor-pointer min-h-[38px]">Unchecked</button>
            </div>
            </div>
          </div>

          <!-- Dynamic 6-Axis Knowledge Radar SVG Visualizer -->
          <div class="bg-white rounded-3xl border border-brand-peachBorder p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-brand-peachSoft pb-2.5">
              <div class="flex items-center gap-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-slateText">Syllabus Strengths & Topic Radar</h3>
                <span class="text-[10px] text-brand-orange font-bold font-mono">Topic Radar</span>
              </div>
              <button type="button" 
                      data-collapsible-target="studentKnowledgeConstellationBody" 
                      onclick="toggleSectionCollapse('studentKnowledgeConstellationBody', this, 'Radar')" 
                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[11px] font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                      title="Minimize Radar">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                <span>Minimize</span>
              </button>
            </div>

            <div id="studentKnowledgeConstellationBody" class="space-y-3">
            <div class="relative flex items-center justify-center p-2 bg-brand-ivory rounded-2xl border border-brand-peachBorder">
              <svg id="radarSvg" class="w-full max-w-[260px] h-[220px] overflow-visible" viewBox="-120 -115 240 230">
                <!-- Guide Rings -->
                <circle cx="0" cy="0" r="25" fill="none" stroke="#FED7AA" stroke-dasharray="2,2"/>
                <circle cx="0" cy="0" r="50" fill="none" stroke="#FED7AA" stroke-dasharray="2,2"/>
                <circle cx="0" cy="0" r="80" fill="none" stroke="#FED7AA" stroke-width="1.2"/>
                
                <!-- 6 Radial Axes (60 deg separation) -->
                <line x1="0" y1="0" x2="0" y2="-80" stroke="#FED7AA" stroke-width="1"/>
                <line x1="0" y1="0" x2="69.28" y2="-40" stroke="#FED7AA" stroke-width="1"/>
                <line x1="0" y1="0" x2="69.28" y2="40" stroke="#FED7AA" stroke-width="1"/>
                <line x1="0" y1="0" x2="0" y2="80" stroke="#FED7AA" stroke-width="1"/>
                <line x1="0" y1="0" x2="-69.28" y2="40" stroke="#FED7AA" stroke-width="1"/>
                <line x1="0" y1="0" x2="-69.28" y2="-40" stroke="#FED7AA" stroke-width="1"/>

                <!-- Domain Labels -->
                <text x="0" y="-87" text-anchor="middle" font-size="7.5" font-weight="700" fill="#EA580C">LANGUAGE</text>
                <text x="76" y="-42" text-anchor="start" font-size="7.5" font-weight="700" fill="#854D0E">E-MATH</text>
                <text x="76" y="44" text-anchor="start" font-size="7.5" font-weight="700" fill="#854D0E">SCIENCE</text>
                <text x="0" y="93" text-anchor="middle" font-size="7.5" font-weight="700" fill="#EA580C">INM & BIHAR</text>
                <text x="-76" y="44" text-anchor="end" font-size="7.5" font-weight="700" fill="#854D0E">GEOGRAPHY</text>
                <text x="-76" y="-42" text-anchor="end" font-size="7.5" font-weight="700" fill="#EA580C">PART III</text>

                <!-- Dynamic Polygon Overlay -->
                <polygon id="radarPolygon" points="0,-16 16,-8 16,8 0,16 -16,8 -16,-8" 
                         fill="rgba(234, 88, 12, 0.24)" stroke="#EA580C" stroke-width="2.2" stroke-linejoin="round" class="transition-all duration-700 ease-out"/>
              </svg>
            </div>

            <!-- Real-time Numeric Domain Breakdown Grid -->
            <div class="grid grid-cols-3 gap-2 pt-1 text-[10px] text-center font-mono">
              <div class="p-1.5 rounded-xl bg-brand-ivory border border-brand-peachBorder">
                <span class="text-brand-muted block text-[9px]">Lang</span>
                <span class="font-bold text-brand-orange" id="axisScore-0">0%</span>
              </div>
              <div class="p-1.5 rounded-xl bg-brand-ivory border border-brand-peachBorder">
                <span class="text-brand-muted block text-[9px]">Math</span>
                <span class="font-bold text-brand-orange" id="axisScore-1">0%</span>
              </div>
              <div class="p-1.5 rounded-xl bg-brand-ivory border border-brand-peachBorder">
                <span class="text-brand-muted block text-[9px]">Sci</span>
                <span class="font-bold text-brand-orange" id="axisScore-2">0%</span>
              </div>
              <div class="p-1.5 rounded-xl bg-brand-ivory border border-brand-peachBorder">
                <span class="text-brand-muted block text-[9px]">INM</span>
                <span class="font-bold text-brand-orange" id="axisScore-3">0%</span>
              </div>
              <div class="p-1.5 rounded-xl bg-brand-ivory border border-brand-peachBorder">
                <span class="text-brand-muted block text-[9px]">Geo</span>
                <span class="font-bold text-brand-orange" id="axisScore-4">0%</span>
              </div>
              <div class="p-1.5 rounded-xl bg-brand-ivory border border-brand-peachBorder">
                <span class="text-brand-muted block text-[9px]">Part III</span>
                <span class="font-bold text-brand-orange" id="axisScore-5">0%</span>
              </div>
            </div>
            </div>
          </div>

        </aside>

        <!-- RIGHT: MASTER SYLLABUS ENGINE (8 COLS) -->
        <div class="lg:col-span-8 space-y-4 sm:space-y-6">
          <div class="bg-white p-1 sm:p-1.5 rounded-2xl border border-brand-peachBorder shadow-sm flex items-center gap-1 sm:gap-1.5 text-xs font-bold text-brand-muted overflow-x-auto no-scrollbar">
            <button onclick="switchView('matrix')" id="viewBtn-matrix" class="flex-1 py-2 sm:py-2.5 px-2.5 sm:px-3 rounded-xl bg-brand-orange text-white shadow-sm transition whitespace-nowrap cursor-pointer text-center text-xs">
              1. Syllabus & Topics
            </button>
            <button onclick="switchView('structure')" id="viewBtn-structure" class="flex-1 py-2 sm:py-2.5 px-2.5 sm:px-3 rounded-xl hover:text-brand-orange hover:bg-brand-orangeLight transition whitespace-nowrap cursor-pointer text-center text-xs">
              2. Exam Pattern
            </button>
            <button onclick="switchView('bihar')" id="viewBtn-bihar" class="flex-1 py-2 sm:py-2.5 px-2.5 sm:px-3 rounded-xl hover:text-brand-orange hover:bg-brand-orangeLight transition whitespace-nowrap cursor-pointer text-center text-xs">
              3. Bihar Special GK
            </button>
          </div>

          <!-- SECTION 1: SYLLABUS CHECKLIST -->
          <section id="view-matrix" class="space-y-4">
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder p-4 sm:p-6 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs font-sans font-bold uppercase tracking-wider text-brand-orange" id="cadreBadge">Classes 6–8</span>
                  <span class="w-1.5 h-1.5 rounded-full bg-brand-peachBorder"></span>
                  <span class="text-xs font-semibold text-brand-muted">Advt. No. 15/2026 Checklist</span>
                </div>
                <h2 class="text-base sm:text-lg font-extrabold text-brand-slateText" id="cadreTitle">Middle School Teacher Preparation Grid</h2>
                <p class="text-xs text-brand-muted mt-0.5 font-medium" id="cadreSub">Part I (30Q) + Part II GS (40Q) + Part III Subject (80Q)</p>
              </div>
              
              <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="button" onclick="toggleAllSyllabusParts()" id="btnToggleAllParts" class="flex-1 sm:flex-none text-xs font-bold px-3 py-2 sm:py-1.5 rounded-xl bg-brand-ivory text-brand-slateText border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs" title="Collapse or Expand all syllabus parts">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                  <span>Collapse All</span>
                </button>
                <button onclick="markCurrentSet(0)" class="flex-1 sm:flex-none text-xs font-bold px-3 py-2 sm:py-1.5 rounded-xl bg-brand-ivory text-brand-slateText border border-brand-peachBorder hover:bg-brand-peachSoft transition cursor-pointer">
                  Reset Visible
                </button>
              </div>
            </div>

            <!-- Dynamic Matrix Accordion Mount -->
            <div id="matrixMountPoint" class="space-y-4"></div>
          </section>

          <!-- SECTION 2: EXAM STRUCTURE MATRIX -->
          <section id="view-structure" class="hidden space-y-4">
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder p-4 sm:p-8 shadow-sm space-y-4 sm:space-y-6">
              <div>
                <h2 class="text-lg sm:text-xl font-extrabold text-brand-slateText">BPSC TRE 4.0 Exam Pattern</h2>
                <p class="text-xs text-brand-muted mt-1">Official Exam Pattern & Marks Breakdown</p>
              </div>

              <div class="overflow-x-auto rounded-2xl border border-brand-peachBorder shadow-2xs">
                <table class="w-full text-left text-xs border-collapse min-w-[550px]">
                  <thead>
                    <tr class="bg-brand-ivory border-b border-brand-peachBorder font-bold text-brand-slateText whitespace-nowrap">
                      <th class="p-3.5">Teaching Level</th>
                      <th class="p-3.5">Part I: Language (Pass 30%)</th>
                      <th class="p-3.5">Part II: General Studies</th>
                      <th class="p-3.5">Part III: Concerned Subject</th>
                      <th class="p-3.5 text-brand-orange">Total Questions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-brand-peachSoft font-medium text-brand-slateText whitespace-nowrap">
                    <tr class="hover:bg-brand-ivory/50">
                      <td class="p-3.5 font-bold">Classes 1–5 (Primary)</td>
                      <td class="p-3.5">30 questions (Qualifying)</td>
                      <td class="p-3.5 font-bold text-brand-orange">120 questions (Merit)</td>
                      <td class="p-3.5 text-brand-muted">Unified with GS</td>
                      <td class="p-3.5 font-serif font-bold text-lg text-brand-orange">150</td>
                    </tr>
                    <tr class="hover:bg-brand-ivory/50">
                      <td class="p-3.5 font-bold">Classes 6–8 (Middle)</td>
                      <td class="p-3.5">30 questions (Qualifying)</td>
                      <td class="p-3.5">40 questions</td>
                      <td class="p-3.5 font-bold text-brand-orange">80 questions</td>
                      <td class="p-3.5 font-serif font-bold text-lg text-brand-orange">150</td>
                    </tr>
                    <tr class="hover:bg-brand-ivory/50">
                      <td class="p-3.5 font-bold">Classes 9–10 (Secondary)</td>
                      <td class="p-3.5">30 questions (Qualifying)</td>
                      <td class="p-3.5">40 questions</td>
                      <td class="p-3.5 font-bold text-brand-orange">80 questions</td>
                      <td class="p-3.5 font-serif font-bold text-lg text-brand-orange">150</td>
                    </tr>
                    <tr class="hover:bg-brand-ivory/50">
                      <td class="p-3.5 font-bold">Classes 11–12 (Higher Sec)</td>
                      <td class="p-3.5">30 questions (Qualifying)</td>
                      <td class="p-3.5">40 questions</td>
                      <td class="p-3.5 font-bold text-brand-orange">80 questions</td>
                      <td class="p-3.5 font-serif font-bold text-lg text-brand-orange">150</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </section>

          <!-- SECTION 3: BIHAR HIGH-YIELD FLASH VAULT -->
          <section id="view-bihar" class="hidden space-y-4">
            <div class="bg-white rounded-3xl border border-brand-peachBorder p-6 sm:p-8 shadow-sm space-y-6">
              <div class="flex justify-between items-center">
                <div>
                  <h2 class="text-xl font-extrabold text-brand-slateText">Important Bihar General Knowledge (GK)</h2>
                  <p class="text-xs text-brand-muted mt-0.5">Important Bihar GK facts frequently asked in previous BPSC exams.</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-brand-yellowPill text-brand-yellowText font-bold text-xs">
                  State Specific
                </span>
              </div>

              <!-- 4 Interactive 3D Flip Cards -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Card 1 -->
                <div class="card-3d-wrap min-h-[220px] sm:h-52 cursor-pointer" onclick="this.querySelector('.card-3d-inner').classList.toggle('card-3d-flipped')">
                  <div class="card-3d-inner w-full h-full relative">
                    <div class="card-face absolute inset-0 bg-brand-ivory border border-brand-peachBorder rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
                      <div>
                        <span class="text-[10px] font-sans font-bold uppercase text-brand-orange bg-brand-peachSoft px-2 py-0.5 rounded">History</span>
                        <h3 class="font-bold text-brand-slateText mt-2 text-sm">1857 Revolt in Bihar</h3>
                        <p class="text-xs text-brand-muted mt-1 leading-relaxed">Who spearheaded the rebellion in Bihar and what was his primary fortress?</p>
                      </div>
                      <span class="text-[10px] text-brand-orange font-bold">Tap to flip card ↻</span>
                    </div>
                    <div class="card-face card-back absolute inset-0 bg-brand-orange text-white rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-md">
                      <div>
                        <span class="text-[10px] font-sans font-bold uppercase tracking-wider text-amber-200">Key Facts</span>
                        <h4 class="font-bold text-sm mt-1">Babu Veer Kunwar Singh</h4>
                        <p class="text-xs leading-relaxed text-amber-50 mt-1">
                          Zamindar of Jagdishpur (Bhojpur). Organized sepoy rebellion in Arrah and commanded guerrilla operations until 1858.
                        </p>
                      </div>
                      <span class="text-[10px] text-amber-200 font-bold">Tap to flip back ↻</span>
                    </div>
                  </div>
                </div>

                <!-- Card 2 -->
                <div class="card-3d-wrap min-h-[220px] sm:h-52 cursor-pointer" onclick="this.querySelector('.card-3d-inner').classList.toggle('card-3d-flipped')">
                  <div class="card-3d-inner w-full h-full relative">
                    <div class="card-face absolute inset-0 bg-brand-ivory border border-brand-peachBorder rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
                      <div>
                        <span class="text-[10px] font-sans font-bold uppercase text-brand-orange bg-brand-peachSoft px-2 py-0.5 rounded">Freedom Struggle</span>
                        <h3 class="font-bold text-brand-slateText mt-2 text-sm">Champaran Satyagraha (1917)</h3>
                        <p class="text-xs text-brand-muted mt-1 leading-relaxed">Who invited Gandhiji to Bihar and what agrarian policy was challenged?</p>
                      </div>
                      <span class="text-[10px] text-brand-orange font-bold">Tap to flip card ↻</span>
                    </div>
                    <div class="card-face card-back absolute inset-0 bg-brand-orange text-white rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-md">
                      <div>
                        <span class="text-[10px] font-sans font-bold uppercase tracking-wider text-amber-200">Key Facts</span>
                        <h4 class="font-bold text-sm mt-1">Raj Kumar Shukla & Tinkathia</h4>
                        <p class="text-xs leading-relaxed text-amber-50 mt-1">
                          Raj Kumar Shukla persuaded Gandhi at the Lucknow Congress. Led to his first Satyagraha against mandatory 3/20th indigo farming.
                        </p>
                      </div>
                      <span class="text-[10px] text-amber-200 font-bold">Tap to flip back ↻</span>
                    </div>
                  </div>
                </div>

                <!-- Card 3 -->
                <div class="card-3d-wrap min-h-[220px] sm:h-52 cursor-pointer" onclick="this.querySelector('.card-3d-inner').classList.toggle('card-3d-flipped')">
                  <div class="card-3d-inner w-full h-full relative">
                    <div class="card-face absolute inset-0 bg-brand-ivory border border-brand-peachBorder rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
                      <div>
                        <span class="text-[10px] font-sans font-bold uppercase text-brand-orange bg-brand-peachSoft px-2 py-0.5 rounded">Geography</span>
                        <h3 class="font-bold text-brand-slateText mt-2 text-sm">Rivers & Geography of Bihar</h3>
                        <p class="text-xs text-brand-muted mt-1 leading-relaxed">How does the Ganga traverse Bihar and what are its major tributaries?</p>
                      </div>
                      <span class="text-[10px] text-brand-orange font-bold">Tap to flip card ↻</span>
                    </div>
                    <div class="card-face card-back absolute inset-0 bg-brand-orange text-white rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-md">
                      <div>
                        <span class="text-[10px] font-sans font-bold uppercase tracking-wider text-amber-200">Key Facts</span>
                        <h4 class="font-bold text-sm mt-1">Ganga & North/South Divide</h4>
                        <p class="text-xs leading-relaxed text-amber-50 mt-1">
                          Ganga enters at Chausa (Buxar). North: Gandak, Bagmati, Kosi ("Sorrow of Bihar"). South: Karmanasa, Son, Punpun, Falgu.
                        </p>
                      </div>
                      <span class="text-[10px] text-amber-200 font-bold">Tap to flip back ↻</span>
                    </div>
                  </div>
                </div>

                <!-- Card 4 -->
                <div class="card-3d-wrap min-h-[220px] sm:h-52 cursor-pointer" onclick="this.querySelector('.card-3d-inner').classList.toggle('card-3d-flipped')">
                  <div class="card-3d-inner w-full h-full relative">
                    <div class="card-face absolute inset-0 bg-brand-ivory border border-brand-peachBorder rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
                      <div>
                        <span class="text-[10px] font-sans font-bold uppercase text-brand-orange bg-brand-peachSoft px-2 py-0.5 rounded">Ancient Republics</span>
                        <h3 class="font-bold text-brand-slateText mt-2 text-sm">Ancient Bihar: Vaishali & Nalanda</h3>
                        <p class="text-xs text-brand-muted mt-1 leading-relaxed">Which ancient assembly represents the world's first republic?</p>
                      </div>
                      <span class="text-[10px] text-brand-orange font-bold">Tap to flip card ↻</span>
                    </div>
                    <div class="card-face card-back absolute inset-0 bg-brand-orange text-white rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-md">
                      <div>
                        <span class="text-[10px] font-sans font-bold uppercase tracking-wider text-amber-200">Key Facts</span>
                        <h4 class="font-bold text-sm mt-1">Vaishali & Nalanda Mahavihara</h4>
                        <p class="text-xs leading-relaxed text-amber-50 mt-1">
                          Vaishali (Licchavis) is recognized as the world's first democratic republic. Nalanda and Vikramshila flourished as international monastic seats.
                        </p>
                      </div>
                      <span class="text-[10px] text-amber-200 font-bold">Tap to flip back ↻</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

        </div>

      </div>
    </main>

