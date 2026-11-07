<?php
/**
 * Infinity Learning - All Application Modals
 */
if (!defined('BPSC_PORTAL')) {
    exit('Direct access not permitted');
}
?>
    <!-- ========================================================= -->
    <!-- MODAL: TOPIC MASTERY QUIZ (FOCUSED RETENTION TEST)         -->
    <!-- ========================================================= -->
    <div id="topicMasteryQuizModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2.5 sm:p-4">
      <div class="bg-white max-w-2xl w-full rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-6 shadow-2xl space-y-4 max-h-[92vh] sm:max-h-[90vh] overflow-y-auto custom-scroll">
        
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-sans font-bold text-brand-orange uppercase bg-orange-100/70 text-orange-900 px-2.5 py-0.5 rounded">Practice Quiz</span>
              <span class="text-[10px] font-sans font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded font-bold border border-emerald-200">Passing Score: 75%</span>
            </div>
            <h3 class="font-extrabold text-base text-slate-900 mt-1" id="topicQuizModalTitle">Topic Practice Test</h3>
          </div>
          <button onclick="closeModal('topicMasteryQuizModal')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center transition cursor-pointer">✕</button>
        </div>

        <!-- STEP 1: TOPIC TEST INSTRUCTIONS -->
        <div id="topicQuizInstructionDeck" class="space-y-4 text-xs">
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
            <span class="text-[10px] font-sans font-bold uppercase tracking-wider text-brand-orange">Quiz Instructions</span>
            <h4 class="font-bold text-sm text-slate-900" id="topicQuizInstTopicTitle">Topic Practice Quiz</h4>
            <p class="text-xs text-slate-600 leading-relaxed font-medium">
              Test your understanding of this topic with this quick practice quiz. Read each question carefully and select the best answer.
            </p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 text-center">
              <span class="text-[10px] font-bold text-slate-500 uppercase block">Total Questions</span>
              <span class="font-serif text-xl font-black text-slate-900 block mt-0.5" id="topicQuizInstCount">4 Questions</span>
            </div>
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 text-center">
              <span class="text-[10px] font-bold text-slate-500 uppercase block">Passing Score</span>
              <span class="font-serif text-xl font-black text-emerald-600 block mt-0.5" id="topicQuizInstReq">3 of 4 Correct (75%)</span>
            </div>
          </div>

          <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-amber-950 space-y-1.5 text-xs leading-relaxed">
            <strong class="font-bold flex items-center gap-1.5 text-amber-900">
              <span>📋</span> Quiz Guidelines:
            </strong>
            <ul class="list-disc pl-5 space-y-1 text-slate-700 font-medium">
              <li>All questions carry equal marks.</li>
              <li>Scoring 75% or higher marks this topic as <strong>Mastered</strong> in your study progress.</li>
              <li>If you score below 75%, you can review the notes and attempt the quiz again anytime.</li>
            </ul>
          </div>

          <div class="pt-2 flex flex-col-reverse sm:flex-row justify-end gap-2 border-t border-slate-100">
            <button type="button" onclick="closeModal('topicMasteryQuizModal')" class="px-4 py-2 border border-slate-300 rounded-xl font-bold text-xs text-slate-700 hover:bg-slate-100 transition cursor-pointer">
              Cancel
            </button>
            <button type="button" onclick="beginTopicQuizQuestions()" class="px-6 py-2.5 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs shadow-md transition cursor-pointer flex items-center gap-1.5">
              <span>⚡</span> Start Practice Quiz →
            </button>
          </div>
        </div>

        <!-- STEP 2: QUIZ QUESTIONS (REVEALED AFTER CLICKING START) -->
        <form id="topicQuizForm" onsubmit="event.preventDefault(); evaluateTopicMasteryQuiz();" class="hidden space-y-4">
          <div id="topicQuizQuestionsMount" class="space-y-4">
            <!-- Injected via renderTopicQuizQuestions() -->
          </div>

          <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 text-xs text-slate-600 font-medium flex items-center justify-between">
            <span>Scoring ≥ 75% promotes topic to <strong>Mastered</strong> immediately.</span>
            <span class="font-sans font-bold text-brand-orange bg-orange-50 px-2 py-0.5 rounded border border-orange-200" id="topicQuizPassRequirement">3/4 correct to pass</span>
          </div>

          <div class="pt-2 flex flex-col-reverse sm:flex-row justify-end gap-2 border-t border-slate-100">
            <button type="button" onclick="closeModal('topicMasteryQuizModal')" class="px-4 py-2 border border-slate-300 rounded-xl font-bold text-xs text-slate-700 hover:bg-slate-100 transition cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-6 py-2.5 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs shadow-md transition cursor-pointer">
              Submit & Verify Mastery →
            </button>
          </div>
        </form>

      </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: MANAGE TOPIC NOTES & MASTER QUIZZES (TEACHER/ADMIN)-->
    <!-- ========================================================= -->
    <div id="topicNotesModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2.5 sm:p-4">
      <div class="bg-white max-w-3xl w-full rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-6 shadow-2xl space-y-4 max-h-[92vh] sm:max-h-[92vh] overflow-y-auto custom-scroll">
        
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
              <span>📝</span> Manage Topic Study Notes & Practice Questions
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Select a topic, upload study materials (PDF/Word), write summary notes, and add practice questions.</p>
          </div>
          <button onclick="closeModal('topicNotesModal')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center transition cursor-pointer">✕</button>
        </div>

        <form onsubmit="event.preventDefault(); saveTopicNotesAndQuiz();" class="space-y-4 text-xs">
          
          <!-- 1. Categorized Topic Picker -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Select Topic <span class="text-brand-orange">*</span></label>
              <select id="editorTopicSelect" onchange="loadTopicDataIntoEditor(this.value)" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange cursor-pointer">
                <!-- Grouped options populated via JS -->
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Topic Title / Header <span class="text-brand-orange">*</span></label>
              <input type="text" id="editorTopicTitle" required class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
            </div>
          </div>

          <!-- Notes Section -->
          <div class="space-y-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Topic Summary <span class="text-brand-orange">*</span></label>
              <input type="text" id="editorTopicSummary" required placeholder="Enter a clear, concise overview of what students will learn in this topic..." class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
            </div>

            <!-- 2. Direct File Upload for Detailed Study Notes -->
            <div class="p-4 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-300 hover:border-brand-orange transition space-y-2.5">
              <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-1.5">
                <div>
                  <span class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                    <span>📤</span> Upload Study Material (PDF / Word DOCX)
                  </span>
                  <p class="text-[10px] text-slate-500 font-medium mt-0.5">Upload lecture notes, summaries, or handouts for students to download.</p>
                </div>
                <span class="text-[10px] font-sans font-bold text-brand-orange bg-orange-100/70 px-2.5 py-1 rounded" id="editorTopicFileBadge">No file chosen</span>
              </div>

              <div class="flex items-center gap-2">
                <input type="file" id="editorTopicFileInput" accept=".pdf,.docx,.doc,.txt,.md" onchange="handleTopicNoteFileSelected(this)" class="w-full text-xs text-slate-700 cursor-pointer file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-orange file:text-white hover:file:bg-brand-orangeHover file:cursor-pointer">
                <button type="button" id="editorTopicRemoveFileBtn" onclick="removeTopicNoteAttachedFile()" class="hidden px-2.5 py-1.5 text-red-600 hover:bg-red-50 rounded-lg font-bold text-[10px] border border-red-200 transition shrink-0 cursor-pointer">
                  Remove File
                </button>
              </div>
            </div>

            <!-- WYSIWYG VISUAL FORMATTING EDITOR (NO RAW HTML TAGS VISIBLE) -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <label class="block font-bold text-slate-700 text-xs font-semibold">Detailed Study Notes</label>
                <span class="text-[10px] text-slate-500 font-medium">Formatted notes display cleanly for students</span>
              </div>

              <!-- Visual Toolbar -->
              <div class="flex items-center gap-1 p-2 bg-slate-100 border border-slate-300 rounded-t-xl border-b-0 flex-wrap">
                <button type="button" onclick="formatEditorText('bold')" class="px-2.5 py-1 text-xs font-bold bg-white border border-slate-300 rounded-lg hover:bg-orange-50 hover:border-brand-orange text-slate-800 transition cursor-pointer" title="Bold">B</button>
                <button type="button" onclick="formatEditorText('italic')" class="px-2.5 py-1 text-xs italic bg-white border border-slate-300 rounded-lg hover:bg-orange-50 hover:border-brand-orange text-slate-800 transition cursor-pointer" title="Italic">I</button>
                <button type="button" onclick="formatEditorText('formatBlock', '<h4>')" class="px-2.5 py-1 text-xs font-semibold bg-white border border-slate-300 rounded-lg hover:bg-orange-50 hover:border-brand-orange text-slate-800 transition cursor-pointer" title="Heading">H4</button>
                <div class="w-[1px] h-4 bg-slate-300 mx-1"></div>
                <button type="button" onclick="formatEditorText('insertUnorderedList')" class="px-2.5 py-1 text-xs bg-white border border-slate-300 rounded-lg hover:bg-orange-50 hover:border-brand-orange text-slate-800 transition cursor-pointer" title="Bullet List">• Bullet List</button>
                <button type="button" onclick="formatEditorText('insertOrderedList')" class="px-2.5 py-1 text-xs bg-white border border-slate-300 rounded-lg hover:bg-orange-50 hover:border-brand-orange text-slate-800 transition cursor-pointer" title="Numbered List">1. Numbered List</button>
                <button type="button" onclick="formatEditorText('removeFormat')" class="px-2.5 py-1 text-[10px] text-slate-500 bg-white border border-slate-300 rounded-lg hover:bg-slate-200 transition cursor-pointer" title="Clear Formatting">Clear</button>
              </div>

              <!-- Editable Visual Canvas -->
              <div id="topicNoteVisualEditor" 
                   contenteditable="true" 
                   class="w-full min-h-[140px] max-h-[220px] overflow-y-auto p-3.5 bg-white border border-slate-300 rounded-b-xl text-xs leading-relaxed text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-orange/40 custom-scroll font-normal">
              </div>
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Important Exam Tips & Common Mistakes</label>
              <input type="text" id="editorTopicTips" required placeholder="Important exam points, common student mistakes, frequent BPSC question trends..." class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
            </div>
          </div>

          <!-- 3. Topic Mastery Quiz Builder Section with Question Bank Picker & Diagrams -->
          <div class="pt-3 border-t border-slate-200 space-y-3">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
              <div>
                <span class="font-bold text-slate-900 block text-xs">Topic Practice Questions</span>
                <span class="text-[10px] text-slate-500 font-medium">Students need 75% or higher to mark this topic as complete.</span>
              </div>
              <div class="flex items-center gap-1.5">
                <button type="button" onclick="toggleTopicQuestionBankPicker()" class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-300 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                  <span>📥</span> Import from Question Bank
                </button>
                <button type="button" onclick="addBlankQuizQuestionToEditor()" class="px-3 py-1.5 bg-orange-50 hover:bg-orange-100 text-brand-orange border border-orange-200 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                  <span>+</span> Add Blank Q
                </button>
              </div>
            </div>

            <!-- Inline Question Bank Quick Picker Panel -->
            <div id="topicQbPickerPanel" class="hidden p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-2.5 shadow-xs">
              <div class="flex items-center justify-between">
                <span class="font-bold text-xs text-slate-900">Select from Previously Uploaded Questions:</span>
                <button type="button" onclick="toggleTopicQuestionBankPicker()" class="text-xs text-slate-500 hover:text-slate-800 font-bold cursor-pointer">✕ Close</button>
              </div>
              <input type="text" id="topicQbSearchInput" oninput="renderTopicQbPickerList()" placeholder="🔍 Search existing questions by keyword or topic..." class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
              <div id="topicQbListMount" class="max-h-44 overflow-y-auto space-y-2 custom-scroll p-1">
                <!-- Dynamically populated -->
              </div>
            </div>

            <!-- Question Cards with Diagram Uploads -->
            <div id="editorQuizQuestionsMount" class="space-y-3 max-h-64 overflow-y-auto custom-scroll p-1">
              <!-- Populated dynamically via JS -->
            </div>
          </div>

          <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
            <button type="button" onclick="closeModal('topicNotesModal')" class="px-4 py-2 border border-slate-300 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md transition cursor-pointer flex items-center gap-1.5">
              <span>💾</span> Save Notes & Mastery Test →
            </button>
          </div>

        </form>

      </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: PUBLISH TEST & QUESTION BANK CURATOR               -->
    <!-- ========================================================= -->
    <div id="publishCuratorModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2.5 sm:p-4">
      <div class="bg-white max-w-4xl w-full rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-6 shadow-2xl space-y-4 max-h-[92vh] sm:max-h-[92vh] overflow-y-auto custom-scroll">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
              <span>📝</span> Create & Publish Mock Test
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Create a new mock test or update an existing paper. Set exam duration, marks, and choose questions by topic.</p>
          </div>
          <button onclick="closeModal('publishCuratorModal')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center transition cursor-pointer">✕</button>
        </div>

        <div class="space-y-4 text-xs">
          <!-- Paper Target / Create New -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Select Test Paper <span class="text-brand-orange">*</span></label>
              <select id="curatorTargetQpSelect" onchange="handleCuratorPaperSelectChange()" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange cursor-pointer">
                <!-- Populated via JS -->
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Student Access</label>
              <select id="curatorAudienceSelect" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange cursor-pointer">
                <option value="all">Public (Available to all students)</option>
                <option value="candidate_only">Private (Restricted batch only)</option>
              </select>
            </div>
          </div>

          <!-- New Paper Title Input (Visible when __NEW__ is selected) -->
          <div id="curatorNewPaperWrapper" class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-200">
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Test Paper Name <span class="text-brand-orange">*</span></label>
              <input type="text" id="curatorNewPaperTitle" placeholder="e.g. General Science & GS Complete Mock Test" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs">Class / Teaching Level</label>
              <select id="curatorNewPaperCadre" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 cursor-pointer">
                <option value="Classes 1–5 (Primary)">Classes 1–5 (Primary)</option>
                <option value="Classes 6–8 (Middle)" selected>Classes 6–8 (Middle)</option>
                <option value="Classes 9–10 (Secondary)">Classes 9–10 (Secondary)</option>
                <option value="Classes 11–12 (Higher Sec)">Classes 11–12 (Higher Sec)</option>
                <option value="All Cadres Unified">All Cadres Unified</option>
              </select>
            </div>
          </div>

          <!-- DYNAMIC TEST SETTINGS: Time, Marks, and Negative Deduction -->
          <div class="p-4 bg-gradient-to-r from-orange-50/50 via-amber-50/40 to-slate-50 rounded-2xl border border-orange-200/80 space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-black uppercase text-brand-orange tracking-wider flex items-center gap-1.5">
                <span>⏱️</span> Test Timing & Marking Scheme
              </span>
              <span class="text-[10px] text-slate-500 font-medium">Custom settings for this test paper</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
              <div>
                <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Test Duration</label>
                <div class="flex items-center">
                  <input type="number" id="curatorDuration" value="120" min="5" max="360" class="w-full p-2 rounded-l-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
                  <span class="px-2.5 py-2 bg-slate-200 border border-l-0 border-slate-300 rounded-r-xl text-[10px] font-bold text-slate-600">Mins</span>
                </div>
              </div>

              <div>
                <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Marks Per Question</label>
                <input type="number" id="curatorMarksPerQ" value="1.0" min="0.25" max="50" step="0.25" oninput="updateCuratorMetrics()" class="w-full p-2 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
              </div>

              <div>
                <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Negative Penalty</label>
                <select id="curatorPenalty" onchange="updateCuratorMetrics()" class="w-full p-2 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange cursor-pointer">
                  <option value="0">0 (No Negative Marking)</option>
                  <option value="-0.25">-0.25 (1/4th Deduction)</option>
                  <option value="-0.33" selected>-0.33 (1/3rd Deduction - BPSC)</option>
                  <option value="-0.50">-0.50 (1/2 Deduction)</option>
                  <option value="-1.00">-1.00 (Full Mark Deduction)</option>
                </select>
              </div>

              <div>
                <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Total Calculated Marks</label>
                <input type="number" id="curatorTotalMarks" readonly class="w-full p-2 rounded-xl border border-slate-300 bg-slate-100 text-xs font-black text-brand-orange font-sans">
              </div>
            </div>
          </div>

          <!-- Question Picker Toolbar -->
          <div class="pt-2 border-t border-slate-200 space-y-2">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
              <div>
                <span class="font-extrabold text-xs text-slate-900 block">Select Questions by Topic</span>
                <span class="text-[10px] text-slate-500 font-medium">Browse questions categorized by topic. Select questions individually or select entire topics.</span>
              </div>
              <div class="flex items-center gap-2">
                <button type="button" onclick="selectAllCuratorQuestions(true)" class="px-2.5 py-1 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-100 text-[10px] font-bold cursor-pointer transition">
                  Select All
                </button>
                <button type="button" onclick="selectAllCuratorQuestions(false)" class="px-2.5 py-1 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-100 text-[10px] font-bold cursor-pointer transition">
                  Clear All
                </button>
                <span class="font-sans text-[10px] px-3 py-1 rounded-lg bg-brand-orange text-white font-black shadow-xs" id="curatorCountBadge">
                  0 Qs (0 Marks)
                </span>
              </div>
            </div>

            <!-- Search Filter -->
            <div class="relative">
              <input type="text" id="curatorSearchInput" oninput="renderCuratorQuestionList()" placeholder="🔍 Search questions by keyword or topic statement..." class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
            </div>
          </div>

          <!-- Question Items Mount (Organized Topic-Wise via JS) -->
          <div id="curatorQuestionsMount" class="max-h-72 overflow-y-auto space-y-3 custom-scroll p-1">
            <!-- Populated via renderCuratorQuestionList() -->
          </div>

          <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-slate-600 text-[11px] leading-relaxed font-medium flex items-center justify-between gap-3">
            <div>
              Publishing will update the test status to <strong>LIVE</strong> immediately with your custom duration, marks, and negative marking rules.
            </div>
            <div class="text-right shrink-0">
              <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">Live Synced to SQL</span>
            </div>
          </div>

          <div class="pt-2 flex flex-col-reverse sm:flex-row justify-end gap-2 border-t border-slate-100">
            <button type="button" onclick="closeModal('publishCuratorModal')" class="px-4 py-2 border border-slate-300 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
              Cancel
            </button>
            <button type="button" onclick="commitPublishTestCurator()" class="px-6 py-2.5 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs shadow-md transition cursor-pointer flex items-center gap-1.5">
              <span>🚀</span> Publish Test for Students →
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: BULK QUESTION UPLOAD VIA WORD (.DOCX / TEXT)       -->
    <!-- ========================================================= -->
    <div id="adminBulkDocxModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2.5 sm:p-4">
      <div class="bg-white max-w-2xl w-full rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-6 shadow-2xl space-y-4 max-h-[92vh] sm:max-h-[90vh] overflow-y-auto custom-scroll">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
              <span>📄</span> Import Questions from Word (.docx)
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Upload a Word file containing multiple choice questions. They will be added to your question bank automatically.</p>
          </div>
          <button onclick="closeModal('adminBulkDocxModal')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center transition cursor-pointer">✕</button>
        </div>

        <!-- Clean Download Template Banner -->
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
          <div>
            <h4 class="font-bold text-slate-800 text-xs">Need a Sample Word Question Template?</h4>
            <p class="text-[10px] text-slate-500 mt-0.5 font-medium">Standard question table format: Question, Options, Correct Answer, Solution, Marks.</p>
          </div>
          <button onclick="downloadOfficialWordTemplate()" class="px-3.5 py-1.5 bg-brand-orange hover:bg-brand-orangeHover text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0 cursor-pointer">
            <span>📥</span> Download Clean Template (.doc)
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
          <!-- Topic / Subject Domain Selector -->
          <div>
            <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Subject / Topic <span class="text-brand-orange">*</span></label>
            <select id="bulkDocxTopicSelect" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange cursor-pointer">
              <option value="General Studies & National Movement" selected>General Studies & National Movement</option>
              <option value="Bihar Special GK & History">Bihar Special GK & History</option>
              <option value="Science & Technology">Science & Technology</option>
              <option value="Mathematics & Quantitative Aptitude">Mathematics & Quantitative Aptitude</option>
              <option value="Language & Literature (Hindi & English)">Language & Literature (Hindi & English)</option>
              <option value="Geography & Environmental Studies">Geography & Environmental Studies</option>
            </select>
          </div>

          <!-- Target Question Paper -->
          <div>
            <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Save Questions To</label>
            <select id="bulkDocxTargetQp" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange cursor-pointer">
              <!-- Populated via JS -->
            </select>
          </div>
        </div>

        <!-- File Select Area -->
        <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center bg-slate-50 relative hover:border-brand-orange transition">
          <input type="file" id="bulkDocxFileInput" accept=".docx,.doc" onchange="handleDocxFileSelected(this)" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
          <div class="space-y-1.5">
            <span class="text-3xl block">📑</span>
            <p class="text-xs font-bold text-slate-800" id="bulkDocxFileName">Click to select .docx file or drag & drop here</p>
            <p class="text-[10px] text-slate-500 font-medium">Parses standard Word question tables with options, correct answer keys, and explanations.</p>
          </div>
        </div>

        <!-- Parsed Questions Preview Wrapper -->
        <div id="bulkParsedPreviewWrapper" class="hidden space-y-3">
          <div class="flex items-center justify-between border-b border-slate-200 pb-2">
            <span class="font-bold text-xs text-slate-900">Questions Preview (<span id="parsedQuestionsCount" class="text-brand-orange font-bold font-sans">0</span> questions detected)</span>
            <span class="text-[10px] text-slate-500 font-medium">Review before committing to Question Bank</span>
          </div>
          <div id="bulkParsedListMount" class="max-h-60 overflow-y-auto space-y-2.5 custom-scroll"></div>
        </div>

        <div class="pt-2 flex justify-end items-center gap-2 border-t border-slate-100">
          <button type="button" onclick="closeModal('adminBulkDocxModal')" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
            Cancel
          </button>
          <button type="button" onclick="processBulkDocxUpload()" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md transition cursor-pointer flex items-center gap-1.5">
            <span>💾</span> Import Questions Now →
          </button>
        </div>

      </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: ADD SINGLE QUESTION (TEACHER & ADMIN)              -->
    <!-- ========================================================= -->
    <div id="adminAddQuestionModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2.5 sm:p-4">
      <div class="bg-white max-w-xl w-full rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-6 shadow-2xl space-y-4 max-h-[92vh] sm:max-h-[90vh] overflow-y-auto custom-scroll">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
              <span>➕</span> Add a Question
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Add a question to your question bank so it can be included in future mock tests.</p>
          </div>
          <button onclick="closeModal('adminAddQuestionModal')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center transition cursor-pointer">✕</button>
        </div>

        <form onsubmit="event.preventDefault(); submitAdminAddQuestion();" class="space-y-3.5 text-xs">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Subject / Topic <span class="text-brand-orange">*</span></label>
              <select id="adminQSection" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange cursor-pointer">
                <option value="General Studies & National Movement" selected>General Studies & National Movement</option>
                <option value="Bihar Special GK & History">Bihar Special GK & History</option>
                <option value="Science & Technology">Science & Technology</option>
                <option value="Mathematics & Quantitative Aptitude">Mathematics & Quantitative Aptitude</option>
                <option value="Language & Literature (Hindi & English)">Language & Literature (Hindi & English)</option>
                <option value="Geography & Environmental Studies">Geography & Environmental Studies</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Attach to Test Paper (Optional)</label>
              <select id="adminQpSelectForQuestion" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 cursor-pointer"></select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Positive Marks</label>
              <input type="number" id="adminQPosMarks" value="1.0" step="0.25" min="0.25" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800">
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Negative Penalty</label>
              <select id="adminQNegMarks" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 cursor-pointer">
                <option value="0">0 (None)</option>
                <option value="-0.25">-0.25 (1/4th)</option>
                <option value="-0.33" selected>-0.33 (1/3rd BPSC)</option>
                <option value="-0.50">-0.50 (1/2)</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Question Statement <span class="text-brand-orange">*</span></label>
            <textarea id="adminQText" rows="2" required placeholder="Type the question statement clearly..." class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-orange"></textarea>
          </div>

          <!-- Question Diagram Upload -->
          <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
            <div class="flex items-center justify-between">
              <span class="font-bold text-[11px] text-slate-800 flex items-center gap-1.5">
                <span>🖼️</span> Attach Diagram / Geometry Figure / Map (Optional)
              </span>
              <span class="text-[10px] text-slate-500 font-sans" id="adminQDiagramFileName">No image selected</span>
            </div>
            <input type="file" id="adminQDiagramInput" accept="image/*" onchange="previewQuestionDiagram(this)" class="w-full text-xs text-slate-700 cursor-pointer">
            <div id="adminQDiagramPreviewWrapper" class="hidden mt-2 p-2 bg-white rounded-xl border border-slate-200 text-center">
              <img id="adminQDiagramPreview" src="" class="max-h-28 mx-auto object-contain rounded" alt="Preview">
            </div>
          </div>

          <!-- Options A - D -->
          <div class="space-y-2">
            <label class="block font-bold text-slate-700 text-xs font-semibold">Multiple Choice Options <span class="text-brand-orange">*</span></label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <div class="flex items-center gap-1.5">
                <span class="w-6 h-6 rounded-lg bg-slate-200 text-slate-700 font-sans font-bold text-xs flex items-center justify-center shrink-0">A</span>
                <input type="text" id="adminOptA" required placeholder="Option A text" class="w-full p-2 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-800">
              </div>
              <div class="flex items-center gap-1.5">
                <span class="w-6 h-6 rounded-lg bg-slate-200 text-slate-700 font-sans font-bold text-xs flex items-center justify-center shrink-0">B</span>
                <input type="text" id="adminOptB" required placeholder="Option B text" class="w-full p-2 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-800">
              </div>
              <div class="flex items-center gap-1.5">
                <span class="w-6 h-6 rounded-lg bg-slate-200 text-slate-700 font-sans font-bold text-xs flex items-center justify-center shrink-0">C</span>
                <input type="text" id="adminOptC" required placeholder="Option C text" class="w-full p-2 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-800">
              </div>
              <div class="flex items-center gap-1.5">
                <span class="w-6 h-6 rounded-lg bg-slate-200 text-slate-700 font-sans font-bold text-xs flex items-center justify-center shrink-0">D</span>
                <input type="text" id="adminOptD" required placeholder="Option D text" class="w-full p-2 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-800">
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Correct Answer Key <span class="text-brand-orange">*</span></label>
              <select id="adminQCorrectAns" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 cursor-pointer">
                <option value="0">Option A is Correct</option>
                <option value="1">Option B is Correct</option>
                <option value="2">Option C is Correct</option>
                <option value="3">Option D is Correct</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Explanation / Solution Note</label>
              <input type="text" id="adminQExplanation" placeholder="Verified curriculum reference..." class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-800">
            </div>
          </div>

          <button type="submit" class="w-full py-3 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs shadow-md transition cursor-pointer flex items-center justify-center gap-2">
            <span>💾</span> Save Question to Master Question Bank
          </button>
        </form>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: ADD BOOK (ADMIN / FACULTY)                         -->
    <!-- ========================================================= -->
    <div id="adminAddBookModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2.5 sm:p-4">
      <div class="bg-white max-w-lg w-full rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-6 shadow-2xl space-y-4 max-h-[92vh] sm:max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
              <span>📚</span> Upload Book or Study Material
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Publish textbooks, reference guides, and compendiums to the student digital library in protected read-only mode.</p>
          </div>
          <button onclick="closeModal('adminAddBookModal')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center transition cursor-pointer">✕</button>
        </div>

        <form onsubmit="event.preventDefault(); submitAdminAddBook();" class="space-y-4 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Book Title <span class="text-brand-orange">*</span></label>
            <input type="text" id="adminBookTitle" required placeholder="e.g. Bihar SCERT History (Class 6–8)" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Teaching Cadre <span class="text-brand-orange">*</span></label>
              <select id="adminBookCadre" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange cursor-pointer">
                <option value="Classes 1–5 (Primary)">Classes 1–5 (Primary)</option>
                <option value="Classes 6–8 (Middle)" selected>Classes 6–8 (Middle)</option>
                <option value="Classes 9–10 (Secondary)">Classes 9–10 (Secondary)</option>
                <option value="Classes 11–12 (Higher Sec)">Classes 11–12 (Higher Sec)</option>
                <option value="All Teaching Levels">All Teaching Levels</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Subject Domain <span class="text-brand-orange">*</span></label>
              <input type="text" id="adminBookSubject" required placeholder="e.g. History & INM" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Author / Publisher <span class="text-brand-orange">*</span></label>
              <input type="text" id="adminBookAuthor" required placeholder="e.g. SCERT Bihar / NCERT" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Access Tier</label>
              <select id="adminBookAccess" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange cursor-pointer">
                <option value="Free" selected>Free (All Candidates)</option>
                <option value="Premium">Premium Access Only</option>
              </select>
            </div>
          </div>
          <div class="border-2 border-dashed border-slate-300 hover:border-brand-orange rounded-2xl p-4 text-center bg-slate-50 hover:bg-orange-50/30 transition relative cursor-pointer group">
            <input type="file" id="adminBookFileInput" accept=".pdf,.doc,.docx" onchange="handleBookFileSelected(this)" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
            <div class="space-y-1">
              <span class="text-2xl group-hover:scale-110 transition-transform inline-block">📁</span>
              <p class="text-xs font-bold text-slate-800" id="adminBookFileName">Click to select Book PDF or drag & drop</p>
              <p class="text-[10px] text-slate-500" id="adminBookFileSize">PDF documents up to 50MB supported</p>
            </div>
          </div>

          <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-900 leading-relaxed">
            <strong>🔒 Student Security Policy:</strong> Published books are accessible to candidates exclusively in <em>In-Browser Protected Read-Only Mode</em>. Text selection, right-click, and browser downloads are disabled.
          </div>

          <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" onclick="closeModal('adminAddBookModal')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-50 transition cursor-pointer">
              Cancel
            </button>
            <button type="submit" id="btnSubmitAdminBook" class="px-5 py-2.5 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
              <span>Publish Book to Digital Library</span> <span>→</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: BOOKS & STUDY MATERIAL DECK                        -->
    <!-- ========================================================= -->
    <div id="booksModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2.5 sm:p-4">
      <div class="bg-white max-w-2xl w-full rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-6 shadow-2xl space-y-4 max-h-[92vh] sm:max-h-[85vh] overflow-y-auto custom-scroll">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
              <span>📚</span> SCERT & NCERT Reference Books
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Official curriculum textbooks and study books for BPSC TRE candidates</p>
          </div>
          <button onclick="closeModal('booksModal')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center transition cursor-pointer">✕</button>
        </div>
        <!-- Download Notice -->
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
          <span class="text-xl">📥</span>
          <div class="text-xs text-emerald-900 leading-snug">
            <span class="font-bold">Direct Document Downloads Enabled:</span>
            Download standard textbooks and faculty-uploaded books (.pdf / .doc) to your device for easy offline reading and revision.
          </div>
        </div>
        <div id="booksModalListMount" class="space-y-3"></div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: PREVIOUS YEAR PAPERS (PYQ)                         -->
    <!-- ========================================================= -->
    <div id="pyqModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2.5 sm:p-4">
      <div class="bg-white max-w-2xl w-full rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-6 shadow-2xl space-y-4 max-h-[92vh] sm:max-h-[85vh] overflow-y-auto custom-scroll">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
              <span>📜</span> Official BPSC TRE Previous Year Papers (PYQs)
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Original question papers with official answer keys for offline and online practice</p>
          </div>
          <button onclick="closeModal('pyqModal')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center transition cursor-pointer">✕</button>
        </div>
        <!-- Download Notice -->
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
          <span class="text-xl">📥</span>
          <div class="text-xs text-emerald-900 leading-snug">
            <span class="font-bold">Direct Document Downloads Enabled:</span>
            Download authentic question papers (.pdf / .doc) for offline practice, or start the online test directly on the portal.
          </div>
        </div>
        <div class="space-y-3" id="pyqModalListMount">
          <!-- Dynamically populated via renderPyqList() -->
        </div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: UPLOAD PYQ PAPER (TEACHER & ADMIN)                 -->
    <!-- ========================================================= -->
    <div id="uploadPyqModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2.5 sm:p-4">
      <div class="bg-white max-w-lg w-full rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-6 shadow-2xl space-y-4 max-h-[92vh] sm:max-h-[90vh] overflow-y-auto custom-scroll">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
              <span>📜</span> Upload Previous Year Paper (PYQ)
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Upload a previous year exam question paper (PDF) for students to download and practice.</p>
          </div>
          <button onclick="closeModal('uploadPyqModal')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center transition cursor-pointer">✕</button>
        </div>

        <form onsubmit="event.preventDefault(); submitUploadPyq();" class="space-y-3.5 text-xs">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Exam Edition <span class="text-brand-orange">*</span></label>
              <select id="pyqEditionInput" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange cursor-pointer">
                <option value="BPSC TRE 3.0">BPSC TRE 3.0 (March / July 2024)</option>
                <option value="BPSC TRE 2.0">BPSC TRE 2.0 (Dec 2023)</option>
                <option value="BPSC TRE 1.0">BPSC TRE 1.0 (August 2023)</option>
                <option value="BPSC Special Cadre">BPSC Head Teacher / Special Cadre</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Teaching Cadre <span class="text-brand-orange">*</span></label>
              <select id="pyqCadreInput" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange cursor-pointer">
                <option value="Classes 1–5 (Primary)">Classes 1–5 (Primary)</option>
                <option value="Classes 6–8 (Middle)">Classes 6–8 (Middle)</option>
                <option value="Classes 9–10 (Secondary)">Classes 9–10 (Secondary)</option>
                <option value="Classes 11–12 (Higher Sec)">Classes 11–12 (Higher Sec)</option>
                <option value="Common Qualifying (Part I)">Common Qualifying (Part I)</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Paper Title / Description <span class="text-brand-orange">*</span></label>
            <input type="text" id="pyqTitleInput" required placeholder="e.g. Mathematics & Science Official Question Paper Shift 1" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Subject Stream <span class="text-brand-orange">*</span></label>
              <input type="text" id="pyqSubjectInput" required placeholder="e.g. Social Science / Mathematics" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1 text-xs font-semibold">Shift / Exam Date <span class="text-brand-orange">*</span></label>
              <input type="text" id="pyqShiftDateInput" required placeholder="e.g. 15 March 2024, Shift 2" class="w-full p-2.5 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-orange">
            </div>
          </div>

          <!-- File Upload Zone -->
          <div class="border-2 border-dashed border-slate-300 rounded-2xl p-5 text-center bg-slate-50 relative hover:border-brand-orange transition">
            <input type="file" id="pyqFileInput" accept=".pdf,.docx,.doc" onchange="handlePyqFileSelected(this)" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
            <div class="space-y-1.5">
              <span class="text-3xl block">📑</span>
              <p class="text-xs font-bold text-slate-800" id="pyqFileName">Click to select official PDF or drag & drop</p>
              <p class="text-[10px] text-slate-500 font-sans" id="pyqFileSize">PDF documents up to 50MB supported</p>
            </div>
          </div>

          <div class="pt-2 flex flex-col-reverse sm:flex-row justify-end gap-2 border-t border-slate-100">
            <button type="button" onclick="closeModal('uploadPyqModal')" class="px-4 py-2 border border-slate-300 rounded-xl font-bold text-xs text-slate-700 hover:bg-slate-100 transition cursor-pointer">
              Cancel
            </button>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-md transition cursor-pointer flex items-center gap-1.5">
              <span>🚀</span> Upload & Publish Question Paper →
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: COURSES & PAYMENT GATEWAY CHECKOUT                 -->
    <!-- ========================================================= -->
    <div id="coursesModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2.5 sm:p-4">
      <div class="bg-white max-w-lg w-full rounded-2xl sm:rounded-3xl border border-brand-peachBorder p-4 sm:p-6 shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-brand-peachSoft pb-3">
          <h3 class="font-extrabold text-sm text-brand-slateText flex items-center gap-2">
            <span>💳</span> Available Courses & CBT Pass Packages
          </h3>
          <button onclick="closeModal('coursesModal')" class="text-brand-muted hover:text-brand-slateText font-bold text-xs cursor-pointer">✕</button>
        </div>
        <div class="space-y-3 text-xs">
          <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder flex justify-between items-center">
            <div>
              <span class="font-bold text-brand-slateText block text-sm">Classes 6–8 Middle Master Pass</span>
              <span class="text-[10px] text-brand-muted">Full Mock Tests + Video Solutions + PDF Notes</span>
              <div class="text-brand-orange font-bold font-serif text-base mt-1">₹1,499</div>
            </div>
            <button onclick="triggerRazorpayCheckout('Classes 6-8 Middle Master Pass', 1499)" class="px-4 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold transition cursor-pointer">
              Enroll Now
            </button>
          </div>
          <div class="p-4 rounded-2xl bg-brand-ivory border border-brand-peachBorder flex justify-between items-center">
            <div>
              <span class="font-bold text-brand-slateText block text-sm">BPSC TRE Complete Test Series</span>
              <span class="text-[10px] text-brand-muted">20 Full-Length Mocks with authentic -0.33 marking</span>
              <div class="text-brand-orange font-bold font-serif text-base mt-1">₹499</div>
            </div>
            <button onclick="triggerRazorpayCheckout('BPSC TRE Complete Test Series', 499)" class="px-4 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold transition cursor-pointer">
              Enroll Now
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL: PROCTORING BREACH AUTO-SUBMIT WARNING             -->
    <!-- ========================================================= -->
    <div id="breachAlertModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2.5 sm:p-4">
      <div class="bg-white max-w-sm w-full rounded-2xl sm:rounded-3xl border border-red-300 p-4 sm:p-6 shadow-2xl text-center space-y-3">
        <div class="w-12 h-12 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center text-2xl mx-auto">⚠️</div>
        <h3 class="font-bold text-brand-slateText text-sm">Exam Window Alert</h3>
        <p class="text-xs text-brand-muted leading-relaxed" id="breachModalMsg">
          You moved away from the active examination window. To ensure fair exam simulation, your test has been submitted automatically.
        </p>
        <button onclick="closeModal('breachAlertModal')" class="w-full py-2 bg-brand-orange text-white rounded-xl text-xs font-bold cursor-pointer">
          View Your Scorecard
        </button>
      </div>
    </div>

