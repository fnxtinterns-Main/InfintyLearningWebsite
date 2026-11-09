<?php
/**
 * Infinity Learning - Teacher / Faculty Dashboard View
 */
if (!defined('BPSC_PORTAL')) {
    exit('Direct access not permitted');
}

$currentUser = Auth::user();
$teacherRole = $currentUser['role'] ?? 'teacher';
$isTeacherUser = ($teacherRole === 'teacher');
$teacherName = $isTeacherUser ? ($currentUser['name'] ?? 'Faculty Member') : 'Faculty Member';
$teacherSubject = $isTeacherUser ? ($currentUser['subject'] ?? 'Mathematics & Science') : 'Mathematics & Science';
$teacherLevelLabel = $isTeacherUser ? ($currentUser['level_label'] ?? 'Classes 6–8 Middle School') : 'Classes 6–8 Middle School';
$teacherDistrict = $isTeacherUser ? ($currentUser['district'] ?? 'Bihar') : 'Bihar';
$teacherQualification = $isTeacherUser ? ($currentUser['qualification'] ?? 'B.Ed, STET Qualified Educator') : 'B.Ed, STET Qualified Educator';
?>
    <!-- ========================================================= -->
    <!-- VIEW 2: TEACHER / FACULTY DASHBOARD                       -->
    <!-- ========================================================= -->
    <main id="view-teacher" class="<?php echo $isTeacherUser ? '' : 'hidden'; ?> max-w-7xl mx-auto w-full px-3 sm:px-4 lg:px-8 py-4 sm:py-6 flex-1 space-y-4 sm:space-y-6">
      
      <!-- TEACHER IDENTITY & WORKSPACE BANNER -->
      <div class="bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder p-4 sm:p-6 lg:p-8 shadow-sm flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 sm:gap-6 relative overflow-hidden">
        <div class="space-y-2 sm:space-y-3 w-full lg:w-auto">
          <div class="flex flex-wrap items-center gap-1.5 sm:gap-2.5">
            <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-amber-100 text-amber-900 border border-amber-300 font-bold text-xs font-mono">
              👨‍🏫 Teacher Portal
            </span>
            <span class="text-xs px-2 sm:px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold border border-emerald-300 flex items-center gap-1">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
              Verified Teacher
            </span>
          </div>

          <div>
            <h1 class="text-xl sm:text-3xl font-extrabold text-brand-slateText tracking-tight">
              Welcome, <?php echo htmlspecialchars($teacherName); ?>
            </h1>
            <p class="text-xs sm:text-sm text-brand-muted font-medium mt-1">
              <?php echo htmlspecialchars($teacherSubject); ?> · <?php echo htmlspecialchars($teacherLevelLabel); ?> · <?php echo htmlspecialchars($teacherDistrict); ?> District
            </p>
            <p class="text-[11px] text-brand-muted/80 font-mono mt-0.5">
              Qualification: <?php echo htmlspecialchars($teacherQualification); ?>
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
          <button onclick="openModal('adminAddQuestionModal')" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft text-brand-slateText rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
            <span>➕</span> Add Q
          </button>
          <button onclick="openModal('adminBulkDocxModal')" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
            <span>📄</span> Import Word
          </button>
          <button onclick="openPublishCuratorModal()" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
            <span>🚀</span> Publish Test
          </button>
          <button onclick="openTopicNotesEditor()" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5" title="Upload study notes or topic practice questions">
            <span>📤</span> Upload Notes
          </button>
          <button onclick="openModal('uploadPyqModal')" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5" title="Upload Official Previous Year Question Paper">
            <span>📜</span> Upload PYQ
          </button>
          <button onclick="openModal('adminAddBookModal')" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5" title="Publish Reference Book to Digital Library">
            <span>📚</span> Add Book
          </button>
        </div>
      </div>

      <!-- TEACHER KPI METRICS ROW -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="p-3.5 sm:p-5 bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder shadow-sm space-y-1">
          <div class="text-[10px] font-sans font-semibold text-brand-muted uppercase font-bold">Mock Tests Published</div>
          <div class="text-xl sm:text-2xl font-black text-brand-orange font-mono" id="teacherHudQpCount">--</div>
          <div class="text-[10px] sm:text-[11px] text-brand-muted">Total tests created</div>
        </div>
        <div class="p-3.5 sm:p-5 bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder shadow-sm space-y-1">
          <div class="text-[10px] font-sans font-semibold text-brand-muted uppercase font-bold">Study Notes</div>
          <div class="text-xl sm:text-2xl font-black text-amber-700 font-mono" id="teacherHudNotesCount">--</div>
          <div class="text-[10px] sm:text-[11px] text-brand-muted">Uploaded topic notes</div>
        </div>
        <div class="p-3.5 sm:p-5 bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder shadow-sm space-y-1">
          <div class="text-[10px] font-sans font-semibold text-brand-muted uppercase font-bold">Books Uploaded</div>
          <div class="text-xl sm:text-2xl font-black text-purple-700 font-mono" id="teacherHudBooksCount">--</div>
          <div class="text-[10px] sm:text-[11px] text-brand-muted">Reference books added</div>
        </div>
        <div class="p-3.5 sm:p-5 bg-white rounded-2xl sm:rounded-3xl border border-brand-peachBorder shadow-sm space-y-1">
          <div class="text-[10px] font-sans font-semibold text-brand-muted uppercase font-bold">Account Status</div>
          <div class="text-sm sm:text-base font-extrabold text-emerald-700 flex items-center gap-1 pt-1">
            <span>✓ Verified</span>
          </div>
          <div class="text-[10px] sm:text-[11px] text-brand-muted font-mono">BPSC Teaching Authority</div>
        </div>
      </div>

      <!-- Published Study Notes & Handouts (Teacher View) -->
      <div class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-brand-peachSoft pb-3">
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">Published Study Notes & Lecture Handouts</h3>
              <span class="text-[10px] font-sans font-semibold bg-amber-100 text-amber-900 font-bold px-2 py-0.5 rounded-full border border-amber-300">Live for Students</span>
            </div>
            <p class="text-xs text-brand-muted">Faculty notes are instantly visible on student dashboards with verified upload timestamps. Students can read notes and take mastery tests.</p>
          </div>
          <div class="flex items-center gap-2 self-end sm:self-auto">
            <button type="button" 
                    data-collapsible-target="teacherNotesVaultBody" 
                    onclick="toggleSectionCollapse('teacherNotesVaultBody', this, 'Study Notes')" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                    title="Minimize Study Notes"
                    aria-expanded="true">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
              <span>Minimize</span>
            </button>
            <button onclick="openTopicNotesEditor()" class="px-3.5 py-1.5 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
              <span>📤</span> + Upload New Notes
            </button>
          </div>
        </div>
        <div id="teacherNotesVaultBody" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="teacherUploadedNotesMount">
            <!-- Populated dynamically via renderTeacherUploadedNotes() -->
          </div>
        </div>
      </div>

      <!-- Curriculum Preparation Guide & Topic Notes Hub (Teacher View) -->
      <div class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-5" id="teacherCurriculumManagerSection">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-brand-peachSoft pb-3">
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">Curriculum Preparation Guide & Topic Notes Hub</h3>
              <span class="text-[10px] font-sans font-semibold bg-purple-100 text-purple-900 font-bold px-2 py-0.5 rounded-full border border-purple-300">Topic-by-Topic Uploader</span>
            </div>
            <p class="text-xs text-brand-muted">Select any teaching cadre and subject stream to attach lecture notes, PDF handouts, and quiz questions directly to syllabus topics.</p>
          </div>
          <div class="flex items-center gap-2 self-end sm:self-auto">
            <button type="button" 
                    data-collapsible-target="teacherCurriculumHubBody" 
                    onclick="toggleSectionCollapse('teacherCurriculumHubBody', this, 'Curriculum Guide')" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                    title="Minimize Curriculum Hub"
                    aria-expanded="true">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
              <span>Minimize</span>
            </button>
          </div>
        </div>

        <div id="teacherCurriculumHubBody" class="space-y-4">
          <!-- Cadre & Subject Stream Filter Bar for Teachers -->
          <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-brand-ivory rounded-2xl border border-brand-peachBorder">
            <div class="flex flex-wrap items-center gap-1.5">
              <span class="text-xs font-bold text-brand-slateText mr-2">Cadre:</span>
              <button type="button" onclick="switchTeacherCadre('prm')" id="tCadreBtn-prm" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer border border-brand-peachBorder bg-white hover:bg-brand-peachSoft text-brand-slateText">Primary 1–5</button>
              <button type="button" onclick="switchTeacherCadre('mid')" id="tCadreBtn-mid" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer border border-brand-peachBorder bg-white hover:bg-brand-peachSoft text-brand-slateText">Middle 6–8</button>
              <button type="button" onclick="switchTeacherCadre('sec')" id="tCadreBtn-sec" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer border border-brand-peachBorder bg-white hover:bg-brand-peachSoft text-brand-slateText">Secondary 9–10</button>
              <button type="button" onclick="switchTeacherCadre('hsec')" id="tCadreBtn-hsec" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer border border-brand-peachBorder bg-white hover:bg-brand-peachSoft text-brand-slateText">Higher Sec 11–12</button>
            </div>

            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-brand-slateText">Subject Stream:</span>
              <select id="teacherSubjectStreamSelect" onchange="switchTeacherSubject(this.value)" class="py-1.5 px-3 rounded-xl border border-brand-peachBorder bg-white text-xs font-bold text-brand-slateText focus:outline-none focus:ring-2 focus:ring-brand-orange shadow-2xs cursor-pointer">
                <option value="">— Select Level First —</option>
              </select>
            </div>
          </div>

          <!-- Topic Grid with Direct Upload Buttons (Shown after selecting level) -->
          <div id="teacherCurriculumGridMount" class="hidden space-y-4">
            <!-- Populated dynamically via renderTeacherCurriculumGrid() -->
          </div>
        </div>
      </div>

      <!-- Managed Question Papers -->
      <div class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-4">
        <div class="flex justify-between items-center border-b border-brand-peachSoft pb-3">
          <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">Managed Question Papers</h3>
          <button type="button" 
                  data-collapsible-target="teacherQpBody" 
                  onclick="toggleSectionCollapse('teacherQpBody', this, 'Question Papers')" 
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                  title="Minimize Question Papers"
                  aria-expanded="true">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
            <span>Minimize</span>
          </button>
        </div>
        <div id="teacherQpBody" class="space-y-2.5">
          <div class="space-y-2.5" id="qpListContainer"></div>
        </div>
      </div>

      <!-- Registered Curriculum Books -->
      <div class="bg-white rounded-3xl border border-brand-peachBorder p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-brand-peachSoft pb-3">
          <div class="flex items-center gap-2">
            <h3 class="text-sm font-extrabold uppercase tracking-wide text-brand-slateText">Registered Curriculum Books</h3>
            <span class="text-[10px] font-sans font-semibold bg-purple-100 text-purple-900 font-bold px-2 py-0.5 rounded-full border border-purple-300">Digital Library</span>
          </div>
          <div class="flex items-center gap-2 self-end sm:self-auto">
            <button type="button" 
                    data-collapsible-target="teacherBooksBody" 
                    onclick="toggleSectionCollapse('teacherBooksBody', this, 'Curriculum Books')" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-slateText bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft hover:text-brand-orange transition cursor-pointer shadow-2xs" 
                    title="Minimize Curriculum Books"
                    aria-expanded="true">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
              <span>Minimize</span>
            </button>
            <button type="button" onclick="openModal('adminAddBookModal')" class="px-3.5 py-1.5 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
              <span>📚</span> + Publish Book
            </button>
          </div>
        </div>
        <div id="teacherBooksBody" class="overflow-x-auto rounded-2xl border border-brand-peachBorder shadow-2xs">
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
            <tbody id="teacherBooksTableBody" class="divide-y divide-brand-peachSoft font-medium text-brand-slateText whitespace-nowrap"></tbody>
          </table>
        </div>
      </div>
    </main>

