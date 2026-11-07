<?php
/**
 * Infinity Learning - Admin AIVERSE Central Dashboard View
 * Includes Faculty Verification, Approvals, and Approved Teacher Directory Management
 */
if (!defined('BPSC_PORTAL')) {
    exit('Direct access not permitted');
}

$pendingTeachers = UserManager::getPendingTeachers();
$approvedTeachers = UserManager::getApprovedTeachers();
$currentUser = Auth::user();
$isAdminUser = ($currentUser && ($currentUser['role'] ?? '') === 'admin');
?>
    <!-- ========================================================= -->
    <!-- VIEW 3: ADMIN AIVERSE CENTRAL DASHBOARD                   -->
    <!-- ========================================================= -->
    <main id="view-admin" class="<?php echo $isAdminUser ? '' : 'hidden'; ?> max-w-7xl mx-auto w-full px-3 sm:px-4 lg:px-8 py-4 sm:py-6 flex-1 space-y-4 sm:space-y-6">
      <div class="bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder p-4 sm:p-6 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <span class="text-xs font-sans font-bold uppercase tracking-wider text-brand-orange">Admin Control Panel</span>
          <h2 class="text-lg sm:text-xl font-extrabold text-brand-slateText mt-1">Portal Management & Teacher Approvals</h2>
          <p class="text-xs text-brand-muted font-medium">Approve new teachers, manage test papers, study notes, and the question bank.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
          <button onclick="openTopicNotesEditor()" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5" title="Upload study notes or topic practice questions">
            <span>📤</span> Notes
          </button>
          <button onclick="openModal('uploadPyqModal')" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
            <span>📜</span> PYQ
          </button>
          <button onclick="openModal('adminAddBookModal')" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5" title="Publish Reference Book to Digital Library">
            <span>📚</span> Book
          </button>
          <button onclick="openModal('adminAddQuestionModal')" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft text-brand-slateText rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
            + Q
          </button>
          <button onclick="openModal('adminBulkDocxModal')" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
            Word
          </button>
          <button onclick="openPublishCuratorModal()" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
            🚀 Publish
          </button>
        </div>
      </div>

      <!-- Financial & Platform Metrics (2 cols on phone, 4 on desktop) -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="p-3.5 sm:p-5 bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder shadow-sm">
          <span class="text-[10px] sm:text-xs text-brand-muted font-bold uppercase">Total Fees Collected</span>
          <p class="text-xl sm:text-2xl font-black text-emerald-600 mt-0.5 sm:mt-1" id="adminRevenueDisplay">₹0</p>
          <span class="text-[9px] sm:text-[10px] text-brand-muted font-mono" id="adminSuccessTxnCount">0 Successful Orders</span>
        </div>
        <div class="p-3.5 sm:p-5 bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder shadow-sm">
          <span class="text-[10px] sm:text-xs text-brand-muted font-bold uppercase">Pending Verification</span>
          <p class="text-xl sm:text-2xl font-black text-amber-600 mt-0.5 sm:mt-1" id="adminPendingRevenueDisplay">₹0</p>
          <span class="text-[9px] sm:text-[10px] text-brand-muted font-mono" id="adminPendingTxnCount">0 Under Review</span>
        </div>
        <div class="p-3.5 sm:p-5 bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder shadow-sm">
          <span class="text-[10px] sm:text-xs text-brand-muted font-bold uppercase">Teacher Accounts</span>
          <p class="text-xl sm:text-2xl font-black text-brand-orange mt-0.5 sm:mt-1" id="adminFacultyTotalStat"><?php echo count($pendingTeachers) + count($approvedTeachers); ?></p>
          <span class="text-[9px] sm:text-[10px] text-brand-muted font-mono"><span id="adminPendingStatInline"><?php echo count($pendingTeachers); ?></span> Pending Review</span>
        </div>
        <div class="p-3.5 sm:p-5 bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder shadow-sm">
          <span class="text-[10px] sm:text-xs text-brand-muted font-bold uppercase">Test Window Alerts</span>
          <p class="text-xl sm:text-2xl font-black text-brand-orange mt-0.5 sm:mt-1" id="adminBreachCount">0</p>
          <span class="text-[9px] sm:text-[10px] text-emerald-600 font-mono font-bold">Exam Monitor Active</span>
        </div>
      </div>

      <!-- ========================================================= -->
      <!-- SECTION: FACULTY VERIFICATION & TEACHER DIRECTORY         -->
      <!-- ========================================================= -->
      <div class="space-y-6">
        
        <!-- 1. PENDING TEACHER APPROVAL REQUESTS -->
        <div class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-4">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-brand-peachSoft pb-3">
            <div class="flex items-center gap-2.5">
              <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">⏳</span>
              <div>
                <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText flex items-center gap-2">
                  <span>Teacher Approval Requests</span>
                  <span id="pendingTeacherBadgeCount" class="text-[10px] font-sans font-semibold font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full border border-amber-300">
                    <?php echo count($pendingTeachers); ?> Pending
                  </span>
                </h3>
                <p class="text-[11px] text-brand-muted">Review teacher credentials. Approved teachers are granted immediate login; declined requests are strictly barred from logging in.</p>
              </div>
            </div>
            <div class="flex items-center gap-2 self-end sm:self-auto">
              <button type="button" 
                      data-collapsible-target="adminPendingTeachersBody" 
                      onclick="toggleSectionCollapse('adminPendingTeachersBody', this, 'Teacher Requests')" 
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                      title="Minimize Requests"
                      aria-expanded="true">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                <span>Minimize</span>
              </button>
              <button onclick="fetchAdminTeacherData()" class="px-3 py-1.5 bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft text-brand-slateText rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
                <span>🔄</span> Refresh Requests
              </button>
            </div>
          </div>

          <div id="adminPendingTeachersBody" class="overflow-x-auto rounded-2xl border border-brand-peachBorder shadow-2xs">
            <table class="w-full text-left text-xs border-collapse min-w-[650px]">
              <thead>
                <tr class="border-b border-brand-peachSoft font-bold text-brand-slateText bg-brand-ivory/60 whitespace-nowrap">
                  <th class="py-2.5 px-3">Applicant Name</th>
                  <th class="py-2.5 px-3">Contact & District</th>
                  <th class="py-2.5 px-3">Cadre & Subject</th>
                  <th class="py-2.5 px-3">Qualification & Experience</th>
                  <th class="py-2.5 px-3">Application Date</th>
                  <th class="py-2.5 px-3 text-right">Approval Action</th>
                </tr>
              </thead>
              <tbody id="adminPendingTeachersTableBody" class="divide-y divide-brand-peachSoft font-medium text-brand-slateText whitespace-nowrap">
                <?php if (empty($pendingTeachers)): ?>
                <tr>
                  <td colspan="6" class="py-6 text-center text-brand-muted text-xs">
                    No pending teacher onboarding requests awaiting approval at this time.
                  </td>
                </tr>
                <?php else: ?>
                  <?php foreach ($pendingTeachers as $t): ?>
                  <tr class="hover:bg-amber-50/40 transition">
                    <td class="py-3 px-3">
                      <div class="font-bold text-brand-slateText"><?php echo htmlspecialchars($t['name'] ?? 'Faculty'); ?></div>
                      <div class="text-[10px] font-sans font-semibold text-brand-muted"><?php echo htmlspecialchars($t['id'] ?? ''); ?></div>
                    </td>
                    <td class="py-3 px-3">
                      <div><?php echo htmlspecialchars($t['email'] ?? ''); ?></div>
                      <div class="text-[10px] text-brand-muted"><?php echo htmlspecialchars($t['phone'] ?? ''); ?> · <span class="font-semibold text-brand-orange"><?php echo htmlspecialchars($t['district'] ?? 'Bihar'); ?></span></div>
                    </td>
                    <td class="py-3 px-3">
                      <div class="font-semibold text-brand-slateText"><?php echo htmlspecialchars($t['subject'] ?? 'General'); ?></div>
                      <div class="text-[10px] text-brand-muted"><?php echo htmlspecialchars($t['level_label'] ?? ''); ?></div>
                    </td>
                    <td class="py-3 px-3">
                      <div class="max-w-xs truncate" title="<?php echo htmlspecialchars($t['qualification'] ?? ''); ?>">
                        <?php echo htmlspecialchars($t['qualification'] ?? 'N/A'); ?>
                      </div>
                    </td>
                    <td class="py-3 px-3 font-mono text-[11px] text-brand-muted">
                      <?php echo htmlspecialchars(substr($t['created_at'] ?? '', 0, 10)); ?>
                    </td>
                    <td class="py-3 px-3 text-right whitespace-nowrap space-x-1.5">
                      <button onclick="adminApproveTeacher('<?php echo htmlspecialchars($t['id']); ?>')" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-[11px] transition shadow-xs cursor-pointer inline-flex items-center gap-1">
                        <span>✓</span> Approve
                      </button>
                      <button onclick="adminDeclineTeacher('<?php echo htmlspecialchars($t['id']); ?>')" class="px-2.5 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 border border-red-300 rounded-xl font-bold text-[11px] transition shadow-xs cursor-pointer inline-flex items-center gap-1">
                        <span>✕</span> Decline
                      </button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 2. APPROVED FACULTY DIRECTORY (WITH REMOVE ACTION) -->
        <div class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-4">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-brand-peachSoft pb-3">
            <div class="flex items-center gap-2.5">
              <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">🎓</span>
              <div>
                <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText flex items-center gap-2">
                  <span>Approved Faculty Directory</span>
                  <span id="approvedTeacherBadgeCount" class="text-[10px] font-sans font-semibold font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full border border-emerald-300">
                    <?php echo count($approvedTeachers); ?> Active
                  </span>
                </h3>
                <p class="text-[11px] text-brand-muted">Active verified teachers with full terminal privileges. Platform administrator can remove teachers anytime if any problem occurs.</p>
              </div>
            </div>
            <div class="flex items-center gap-2 self-end sm:self-auto">
              <button type="button" 
                      data-collapsible-target="adminApprovedTeachersBody" 
                      onclick="toggleSectionCollapse('adminApprovedTeachersBody', this, 'Approved Faculty')" 
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                      title="Minimize Faculty Directory"
                      aria-expanded="true">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                <span>Minimize</span>
              </button>
              <span class="text-[11px] font-mono text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-xl font-bold border border-emerald-200">
                ✓ Active Terminal Authorization
              </span>
            </div>
          </div>

          <div id="adminApprovedTeachersBody" class="overflow-x-auto rounded-2xl border border-brand-peachBorder shadow-2xs">
            <table class="w-full text-left text-xs border-collapse min-w-[650px]">
              <thead>
                <tr class="border-b border-brand-peachSoft font-bold text-brand-slateText bg-brand-ivory/60 whitespace-nowrap">
                  <th class="py-2.5 px-3">Faculty Name</th>
                  <th class="py-2.5 px-3">Email & Contact</th>
                  <th class="py-2.5 px-3">Subject & Cadre</th>
                  <th class="py-2.5 px-3">District</th>
                  <th class="py-2.5 px-3">Approved On</th>
                  <th class="py-2.5 px-3 text-right">Faculty Actions</th>
                </tr>
              </thead>
              <tbody id="adminApprovedTeachersTableBody" class="divide-y divide-brand-peachSoft font-medium text-brand-slateText whitespace-nowrap">
                <?php if (empty($approvedTeachers)): ?>
                <tr>
                  <td colspan="6" class="py-6 text-center text-brand-muted text-xs">
                    No approved faculty in the directory currently.
                  </td>
                </tr>
                <?php else: ?>
                  <?php foreach ($approvedTeachers as $t): ?>
                  <tr class="hover:bg-emerald-50/30 transition">
                    <td class="py-3 px-3">
                      <div class="font-bold text-brand-slateText"><?php echo htmlspecialchars($t['name'] ?? 'Faculty'); ?></div>
                      <div class="text-[10px] font-sans font-semibold text-brand-muted"><?php echo htmlspecialchars($t['id'] ?? ''); ?></div>
                    </td>
                    <td class="py-3 px-3">
                      <div><?php echo htmlspecialchars($t['email'] ?? ''); ?></div>
                      <div class="text-[10px] text-brand-muted"><?php echo htmlspecialchars($t['phone'] ?? 'N/A'); ?></div>
                    </td>
                    <td class="py-3 px-3">
                      <div class="font-semibold text-brand-slateText"><?php echo htmlspecialchars($t['subject'] ?? 'General'); ?></div>
                      <div class="text-[10px] text-brand-muted"><?php echo htmlspecialchars($t['level_label'] ?? ''); ?></div>
                    </td>
                    <td class="py-3 px-3 font-semibold text-brand-orange">
                      <?php echo htmlspecialchars($t['district'] ?? 'Bihar'); ?>
                    </td>
                    <td class="py-3 px-3 font-mono text-[11px] text-brand-muted">
                      <?php echo htmlspecialchars(substr($t['approved_at'] ?? $t['created_at'] ?? '', 0, 10)); ?>
                    </td>
                    <td class="py-3 px-3 text-right whitespace-nowrap">
                      <button onclick="adminRemoveTeacher('<?php echo htmlspecialchars($t['id']); ?>')" class="px-2.5 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold text-[11px] transition shadow-xs cursor-pointer inline-flex items-center gap-1">
                        <span>🗑️</span> Remove Teacher
                      </button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- Payment Ledger -->
      <div class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-brand-peachSoft pb-3">
          <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">Gateway Payment Transactions</h3>
          <div class="flex items-center gap-2 self-end sm:self-auto">
            <button type="button" 
                    data-collapsible-target="adminGatewayPaymentsBody" 
                    onclick="toggleSectionCollapse('adminGatewayPaymentsBody', this, 'Payments')" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                    title="Minimize Payments"
                    aria-expanded="true">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
              <span>Minimize</span>
            </button>
          </div>
        </div>
        <div id="adminGatewayPaymentsBody" class="overflow-x-auto rounded-2xl border border-brand-peachBorder shadow-2xs">
          <table class="w-full text-left text-xs border-collapse min-w-[650px]">
            <thead class="whitespace-nowrap bg-brand-ivory border-b border-brand-peachSoft">
              <tr class="border-b border-brand-peachSoft font-bold text-brand-slateText">
                <th class="py-2.5 px-3">Txn ID</th>
                <th class="py-2.5 px-3">Candidate</th>
                <th class="py-2.5 px-3">Course / Package</th>
                <th class="py-2.5 px-3">Gateway & Date</th>
                <th class="py-2.5 px-3">Amount</th>
                <th class="py-2.5 px-3">Status</th>
                <th class="py-2.5 px-3 text-right">Update Status</th>
              </tr>
            </thead>
            <tbody id="adminPaymentTable" class="divide-y divide-brand-peachSoft font-medium text-brand-slateText whitespace-nowrap"></tbody>
          </table>
        </div>
      </div>

      <!-- Admin Master Question Papers List -->
      <div class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-4">
        <div class="flex justify-between items-center border-b border-brand-peachSoft pb-3">
          <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">All Test Papers (Public & Private Batches)</h3>
          <button type="button" 
                  data-collapsible-target="adminMasterQpBody" 
                  onclick="toggleSectionCollapse('adminMasterQpBody', this, 'Master Papers')" 
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                  title="Minimize Master Papers"
                  aria-expanded="true">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
            <span>Minimize</span>
          </button>
        </div>
        <div id="adminMasterQpBody" class="space-y-2.5">
          <div class="space-y-2.5" id="adminQpMasterList"></div>
        </div>
      </div>

      <!-- Questions Database Viewer -->
      <div class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-4">
        <div class="flex justify-between items-center border-b border-brand-peachSoft pb-3">
          <div class="flex items-center gap-2">
            <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">Active Questions Bank Database</h3>
            <span class="text-[10px] font-sans font-semibold font-bold bg-brand-orangeLight text-brand-orange px-2 py-0.5 rounded border border-brand-peachBorder" id="dbTotalQuestionsCount">0 Questions</span>
          </div>
          <button type="button" 
                  data-collapsible-target="adminQuestionBankBody" 
                  onclick="toggleSectionCollapse('adminQuestionBankBody', this, 'Question Bank')" 
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                  title="Minimize Question Bank"
                  aria-expanded="true">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
            <span>Minimize</span>
          </button>
        </div>
        <div id="adminQuestionBankBody" class="overflow-x-auto rounded-2xl border border-brand-peachBorder shadow-2xs">
          <table class="w-full text-left text-xs border-collapse min-w-[650px]">
            <thead class="whitespace-nowrap bg-brand-ivory border-b border-brand-peachSoft">
              <tr class="border-b border-brand-peachSoft font-bold text-brand-slateText">
                <th class="py-2.5 px-3 w-12">#</th>
                <th class="py-2.5 px-3">Question & Section</th>
                <th class="py-2.5 px-3">Options & Key</th>
                <th class="py-2.5 px-3">Marks (+ / -)</th>
                <th class="py-2.5 px-3">Solution Note</th>
              </tr>
            </thead>
            <tbody id="dbQuestionsTableBody" class="divide-y divide-brand-peachSoft font-medium text-brand-slateText whitespace-nowrap"></tbody>
          </table>
        </div>
      </div>

      <!-- Admin Master Published Study Notes & Handouts -->
      <div class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-brand-peachSoft pb-3">
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">Faculty & Admin Published Study Notes Repository</h3>
              <span class="text-[10px] font-sans font-semibold bg-emerald-100 text-emerald-900 font-bold px-2 py-0.5 rounded-full border border-emerald-300">Live Terminal</span>
            </div>
            <p class="text-xs text-brand-muted">Uploaded study dossiers, formulas, and attached handouts visible to candidates. Only teachers and admins can upload.</p>
          </div>
          <div class="flex items-center gap-2 self-end sm:self-auto">
            <button type="button" 
                    data-collapsible-target="adminNotesRepoBody" 
                    onclick="toggleSectionCollapse('adminNotesRepoBody', this, 'Notes Repository')" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                    title="Minimize Notes Repository"
                    aria-expanded="true">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
              <span>Minimize</span>
            </button>
            <button onclick="openTopicNotesEditor()" class="px-3.5 py-1.5 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
              <span>📤</span> + Upload New Notes
            </button>
          </div>
        </div>
        <div id="adminNotesRepoBody" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="adminUploadedNotesMount">
            <!-- Populated dynamically via renderAdminUploadedNotes() -->
          </div>
        </div>
      </div>

      <!-- Admin Digital Textbooks & Curriculum Books Repository -->
      <div class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-brand-peachSoft pb-3">
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">Registered Curriculum Books & Digital Library</h3>
              <span class="text-[10px] font-sans font-semibold bg-purple-100 text-purple-900 font-bold px-2 py-0.5 rounded-full border border-purple-300">Live Library</span>
            </div>
            <p class="text-xs text-brand-muted">Curriculum textbooks, SCERT/NCERT compendiums, and uploaded PDF study books available to students in protected read-only mode.</p>
          </div>
          <div class="flex items-center gap-2 self-end sm:self-auto">
            <button type="button" 
                    data-collapsible-target="adminBooksRepoBody" 
                    onclick="toggleSectionCollapse('adminBooksRepoBody', this, 'Curriculum Books')" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                    title="Minimize Curriculum Books"
                    aria-expanded="true">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
              <span>Minimize</span>
            </button>
            <button onclick="openModal('adminAddBookModal')" class="px-3.5 py-1.5 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
              <span>📚</span> + Publish Book
            </button>
          </div>
        </div>
        <div id="adminBooksRepoBody" class="overflow-x-auto rounded-2xl border border-brand-peachBorder shadow-2xs">
          <table class="w-full text-left text-xs min-w-[620px]">
            <thead class="whitespace-nowrap bg-brand-ivory border-b border-brand-peachSoft">
              <tr class="border-b border-brand-peachSoft font-bold text-brand-slateText">
                <th class="py-2.5 px-3">ID</th>
                <th class="py-2.5 px-3">Title</th>
                <th class="py-2.5 px-3">Subject & Cadre</th>
                <th class="py-2.5 px-3">Author</th>
                <th class="py-2.5 px-3">Size</th>
                <th class="py-2.5 px-3">Tier</th>
                <th class="py-2.5 px-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody id="adminBooksTableBody" class="divide-y divide-brand-peachSoft font-medium text-brand-slateText whitespace-nowrap"></tbody>
          </table>
        </div>
      </div>

      <!-- Curriculum Preparation Guide & Topic Notes Hub (Admin Master View) -->
      <div class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-5" id="adminCurriculumManagerSection">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-brand-peachSoft pb-3">
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">Curriculum Preparation Guide & Topic Notes Hub</h3>
              <span class="text-[10px] font-sans font-semibold bg-purple-100 text-purple-900 font-bold px-2 py-0.5 rounded-full border border-purple-300">Admin Control</span>
            </div>
            <p class="text-xs text-brand-muted">Browse full BPSC TRE 4.0 syllabus across all 4 cadres and attach study dossiers, PDF handouts, or test quizzes to any topic.</p>
          </div>
          <div class="flex items-center gap-2 self-end sm:self-auto">
            <button type="button" 
                    data-collapsible-target="adminCurriculumHubBody" 
                    onclick="toggleSectionCollapse('adminCurriculumHubBody', this, 'Curriculum Hub')" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                    title="Minimize Curriculum Hub"
                    aria-expanded="true">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
              <span>Minimize</span>
            </button>
          </div>
        </div>

        <div id="adminCurriculumHubBody" class="space-y-4">
          <!-- Cadre & Subject Stream Filter Bar for Admins -->
          <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-brand-ivory rounded-2xl border border-brand-peachBorder">
            <div class="flex flex-wrap items-center gap-1.5">
              <span class="text-xs font-bold text-brand-slateText mr-2">Cadre:</span>
              <button type="button" onclick="switchTeacherCadre('prm')" id="aCadreBtn-prm" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer border border-brand-peachBorder bg-white hover:bg-brand-peachSoft text-brand-slateText">Primary 1–5</button>
              <button type="button" onclick="switchTeacherCadre('mid')" id="aCadreBtn-mid" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer border border-brand-peachBorder bg-white hover:bg-brand-peachSoft text-brand-slateText">Middle 6–8</button>
              <button type="button" onclick="switchTeacherCadre('sec')" id="aCadreBtn-sec" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer border border-brand-peachBorder bg-white hover:bg-brand-peachSoft text-brand-slateText">Secondary 9–10</button>
              <button type="button" onclick="switchTeacherCadre('hsec')" id="aCadreBtn-hsec" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer border border-brand-peachBorder bg-white hover:bg-brand-peachSoft text-brand-slateText">Higher Sec 11–12</button>
            </div>

            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-brand-slateText">Subject Stream:</span>
              <select id="adminSubjectStreamSelect" onchange="switchTeacherSubject(this.value)" class="py-1.5 px-3 rounded-xl border border-brand-peachBorder bg-white text-xs font-bold text-brand-slateText focus:outline-none focus:ring-2 focus:ring-brand-orange shadow-2xs cursor-pointer">
                <option value="">— Select Level First —</option>
              </select>
            </div>
          </div>

          <!-- Topic Grid with Direct Upload Buttons (Shown after selecting level) -->
          <div id="adminCurriculumGridMount" class="hidden space-y-4">
            <!-- Populated dynamically via renderTeacherCurriculumGrid() -->
          </div>
        </div>
      </div>
    </main>
