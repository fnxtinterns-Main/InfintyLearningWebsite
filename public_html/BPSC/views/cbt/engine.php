<?php
/**
 * Infinity Learning - Proctored CBT Split-Screen Engine View
 */
if (!defined('BPSC_PORTAL')) {
    exit('Direct access not permitted');
}
?>
    <!-- ========================================================= -->
    <!-- VIEW 4: PROCTORED CBT SPLIT-SCREEN ENGINE                 -->
    <!-- ========================================================= -->
    <main id="view-cbt" class="hidden max-w-7xl mx-auto w-full px-4 lg:px-8 py-6 flex-1 space-y-6">
      
      <!-- ========================================================= -->
      <!-- CBT STEP 1: PRE-EXAM INSTRUCTIONS & BRIEFING SCREEN       -->
      <!-- ========================================================= -->
      <section id="cbtInstructionsDeck" class="max-w-4xl mx-auto bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        
        <!-- Header & Breadcrumb -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-100 pb-5">
          <div>
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-sans font-bold uppercase tracking-wider text-brand-orange bg-orange-100/70 text-orange-900 px-3 py-1 rounded-full border border-orange-200">
                Online Mock Test
              </span>
              <span class="text-[10px] font-sans px-2.5 font-semibold py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-300 font-bold">
                Official Pattern
              </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1.5">
              Mock Test Instructions
            </h2>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">
              Please read these simple instructions before starting your test:
            </p>
          </div>
          <button type="button" onclick="cancelCbtInstructions()" class="px-4 py-2 rounded-xl bg-slate-100 border border-slate-300 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs self-end sm:self-auto" title="Cancel and return to dashboard">
            <span>←</span> <span>Cancel & Return</span>
          </button>
        </div>

        <!-- Examination Particulars Matrix (4 Dynamic Cards) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-center">
            <span class="text-xs font-semibold text-slate-500 block">Test Paper</span>
            <span class="font-bold text-slate-900 text-sm mt-1 block truncate" id="cbtInstructPaperTitle">Diagnostic Paper</span>
            <span class="text-[10px] font-sans font-medium text-brand-orange font-bold" id="cbtInstructCadre">General Studies</span>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-center">
            <span class="text-[10px] uppercase font-bold text-slate-500 block">Duration</span>
            <span class="font-serif text-2xl font-black text-brand-orange mt-0.5 block" id="cbtInstructDuration">120 Mins</span>
            <span class="text-[10px] text-slate-500 font-sans font-medium">Exam Duration</span>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-center">
            <span class="text-[10px] uppercase font-bold text-slate-500 block">Total Questions</span>
            <span class="font-serif text-2xl font-black text-slate-900 mt-0.5 block" id="cbtInstructQuestionCount">0 Qs</span>
            <span class="text-[10px] text-slate-500 font-sans font-medium" id="cbtInstructTotalMarks">0.00 Marks Max</span>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-center">
            <span class="text-[10px] uppercase font-bold text-slate-500 block">Negative Marking</span>
            <span class="font-serif text-2xl font-black text-red-600 mt-0.5 block" id="cbtInstructPenalty">-0.33</span>
            <span class="text-[10px] text-red-700 font-sans font-semibold" id="cbtInstructPenaltySub">1/3rd per incorrect</span>
          </div>
        </div>

        <!-- Crucial Notice: Tab-Switching & Proctoring -->
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl space-y-2">
          <div class="flex items-center gap-2 text-amber-900 font-bold text-xs">
            <span class="text-base">📌</span>
            <span>Important Test Guidelines:</span>
          </div>
          <ul class="text-xs text-amber-950 space-y-1 pl-6 list-disc list-outside leading-relaxed font-medium">
            <li><strong>Stay on this tab:</strong> Switching tabs or opening other apps will automatically submit your test.</li>
            <li><strong>Do not refresh or press back:</strong> Please do not reload the page or click back buttons during the exam.</li>
            <li><strong>Timer runs continuously:</strong> The test timer starts as soon as you click 'Start Test' and cannot be paused.</li>
          </ul>
        </div>

        <!-- Detailed Protocol Sections -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
          
          <!-- Section 1: Question Answering Protocol -->
          <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 space-y-2.5">
            <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
              <span>📝</span> How to Answer & Navigate Questions
            </h4>
            <ul class="space-y-1.5 text-slate-600 pl-4 list-disc list-outside leading-relaxed font-medium">
              <li>Click on any option (radio button or card) to select your answer.</li>
              <li>You can freely change your response to any question at any time before submitting.</li>
              <li>Use the <strong>Question Numbers</strong> to jump directly to any question.</li>
              <li>Your answers are saved instantaneously on your device as you select them.</li>
              <li>Part I Language Qualifying Paper requires <strong>at least 30% marks (9 marks out of 30)</strong> to qualify.</li>
            </ul>
          </div>

          <!-- Section 2: Palette Legend & Scoring -->
          <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 space-y-2.5">
            <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wide flex items-center gap-1.5">
              <span>🎯</span> Scoring & Palette Status Legend
            </h4>
            <div class="space-y-2 text-slate-600">
              <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white font-mono font-bold text-xs flex items-center justify-center">1</span>
                <span><strong>Green button</strong>: Question has been answered.</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-white border border-slate-300 text-slate-800 font-mono font-bold text-xs flex items-center justify-center">2</span>
                <span><strong>Light button</strong>: Question is unattempted.</span>
              </div>
              <div class="pt-1.5 border-t border-slate-200 text-[11px] leading-relaxed">
                <div>• Correct Answer: <strong class="text-emerald-700 font-bold" id="cbtInstructPosMarksText">+1.00 Marks</strong></div>
                <div>• Incorrect Answer: <strong class="text-red-600 font-bold" id="cbtInstructNegMarksText">-0.33 Marks</strong></div>
                <div>• Unattempted / Unattempted: <strong>0.00 Marks</strong> (No Penalty)</div>
              </div>
            </div>
          </div>

        </div>

        <!-- Candidate Undertaking Agreement -->
        <div class="pt-1">
          <label class="flex items-start gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-200 cursor-pointer hover:bg-amber-100/60 transition">
            <input type="checkbox" id="cbtUndertakingCheckbox" checked class="mt-0.5 h-4 w-4 rounded text-brand-orange focus:ring-brand-orange cursor-pointer">
            <span class="text-xs text-amber-950 font-medium leading-relaxed" id="cbtUndertakingText">
              <strong>Candidate Declaration:</strong> I have read, understood, and agreed to all the examination rules, marking scheme, and strict tab-monitoring proctoring terms above. I am ready to begin my proctored CBT examination.
            </span>
          </label>
        </div>

        <!-- Launch Action Bar -->
        <div class="pt-2 flex flex-col sm:flex-row justify-between items-center gap-3 border-t border-slate-100">
          <button type="button" onclick="cancelCbtInstructions()" class="w-full sm:w-auto px-5 py-3 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 transition text-center cursor-pointer">
            ← Cancel & Return to Dashboard
          </button>
          <button type="button" onclick="beginActiveCbtSession()" class="w-full sm:w-auto px-8 py-3.5 bg-brand-orange hover:bg-brand-orangeHover text-white font-extrabold text-xs rounded-xl shadow-md hover:shadow-lg transition cursor-pointer flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
            <span>⚡</span>
            <span>I am Ready, Start Test Now</span>
            <span>→</span>
          </button>
        </div>

      </section>

      <!-- ========================================================= -->
      <!-- ========================================================= -->
      <!-- CBT STEP 2: ACTIVE SESSION DECK (SHOWN AFTER START TEST)  -->
      <!-- ========================================================= -->
      <div id="cbtActiveDeck" class="hidden grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start">
        
        <!-- Mobile Sticky Live HUD (< lg) -->
        <div class="lg:hidden sticky top-12 z-30 bg-white/95 backdrop-blur-md rounded-2xl border border-brand-peachBorder p-2.5 shadow-sm flex items-center justify-between gap-2 col-span-1">
          <div class="min-w-0">
            <span class="text-[9px] uppercase font-bold text-brand-orange block">BPSC Mock CBT</span>
            <h4 class="font-extrabold text-xs text-brand-slateText truncate max-w-[130px]" id="cbtMobilePaperTitle">Exam Paper</h4>
          </div>
          <div class="flex items-center gap-1.5">
            <div class="flex items-center gap-1 px-2.5 py-1 rounded-xl bg-orange-50 border border-orange-200 text-brand-orange font-mono font-bold text-xs shrink-0">
              <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-ping"></span>
              <span id="cbtMobileTimer">120:00</span>
            </div>
            <button type="button" onclick="toggleMobileCbtPalette()" class="px-2.5 py-1 bg-brand-ivory border border-brand-peachBorder hover:bg-brand-peachSoft text-brand-slateText rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer shrink-0">
              <span>📋</span> <span id="cbtMobileAnsweredBadge">0/0</span>
            </button>
          </div>
        </div>

        <!-- Left Panel: Guidelines & Question Palette (4 cols) -->
        <aside class="lg:col-span-4 space-y-4 lg:sticky lg:top-20">
          <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-5 shadow-sm space-y-3 sm:space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <div>
                <span class="text-[10px] font-sans font-bold text-brand-orange uppercase">Live Test</span>
                <h3 class="font-bold text-slate-900 text-xs truncate max-w-[170px]" id="cbtActivePaperTitle">Paper #</h3>
              </div>
              <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-orange-50 border border-orange-200 text-brand-orange font-mono font-bold text-xs">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                <span id="cbtCountdownTimer">120:00</span>
              </div>
            </div>

            <!-- Anti-Cheat Proctored Alert Card -->
            <div class="p-3 bg-red-50 border border-red-200 rounded-2xl text-[11px] text-red-800 space-y-1">
              <span class="font-bold flex items-center gap-1.5 text-xs text-red-900">
                <span>⚠️</span> Anti-Cheat Monitor Active
              </span>
              <p class="leading-relaxed font-medium">
                Switching tabs or minimizing window will automatically submit your exam.
              </p>
            </div>

            <div class="space-y-1 text-xs text-slate-800 border-t border-slate-100 pt-2.5">
              <div class="flex justify-between items-center text-slate-500">
                <span>Progress:</span>
                <span class="font-mono font-bold text-brand-orange" id="cbtAnsweredTracker">0 / 0 Answered</span>
              </div>
            </div>

            <!-- Question Navigation Palette -->
            <div class="pt-2 border-t border-slate-100 space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Question Palette</span>
                <button type="button" onclick="toggleMobileCbtPalette()" class="lg:hidden text-[11px] font-bold text-brand-orange flex items-center gap-1 cursor-pointer">
                  <span id="cbtPaletteToggleLabel">Hide Grid ▲</span>
                </button>
              </div>
              <div id="cbtQuestionPaletteWrapper" class="transition-all">
                <div class="flex flex-wrap gap-1.5 max-h-48 sm:max-h-64 lg:max-h-none overflow-y-auto custom-scroll p-1" id="cbtQuestionPaletteMount"></div>
              </div>
            </div>

            <button type="button" onclick="confirmManualCbtSubmit()" class="w-full py-2.5 bg-brand-orange hover:bg-brand-orangeHover text-white font-bold text-xs rounded-xl shadow-md transition cursor-pointer">
              Submit Test →
            </button>
          </div>
        </aside>

        <!-- Right Panel: Questions Deck (8 cols) -->
        <section class="lg:col-span-8 space-y-4">
          <div id="cbtQuestionsMount" class="space-y-4">
            <!-- Populated via renderCbtQuestions() -->
          </div>

          <!-- Bottom Sticky Submit Bar -->
          <div class="mt-4 sm:mt-6 p-3 sm:p-5 bg-white rounded-2xl sm:rounded-3xl border border-slate-200 shadow-lg flex items-center justify-between gap-3 sticky bottom-3 z-40 bg-white/95 backdrop-blur-md">
            <span class="text-xs text-slate-500 font-medium hidden sm:inline">Check your answers before submitting. Answers cannot be changed after submitting.</span>
            <span class="text-xs font-bold text-brand-slateText sm:hidden" id="cbtMobileSubmitCounter">0 Answered</span>
            <button type="button" onclick="confirmManualCbtSubmit()" class="w-full sm:w-auto px-6 sm:px-8 py-2.5 sm:py-3 bg-brand-orange hover:bg-brand-orangeHover text-white font-bold text-xs sm:text-sm rounded-xl shadow-md hover:shadow-lg transition cursor-pointer flex items-center justify-center gap-2">
              <span>Submit Test</span>
              <span>→</span>
            </button>
          </div>
        </section>
      </div>

      <!-- ========================================================= -->
      <!-- CBT STEP 3: SCORECARD & SOLUTION REVIEW SECTION           -->
      <!-- ========================================================= -->
      <section id="cbtResultSection" class="hidden max-w-4xl mx-auto bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-100 pb-6">
          <div>
            <div class="flex items-center gap-2">
              <span class="text-xs font-sans font-bold uppercase tracking-wider text-brand-orange">Your Test Result</span>
              <span id="cbtViolationTag" class="hidden text-[10px] px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 font-bold border border-red-200">
                ⚠️ Test Submitted: Window Changed
              </span>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Score Summary</h2>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Calculated based on your test time, marks, and negative marking.</p>
          </div>
          <div class="text-center bg-slate-50 p-4 rounded-2xl border border-slate-200 min-w-[130px]">
            <div class="font-serif text-3xl font-extrabold text-brand-orange" id="cbtScoreDisplay">0.00</div>
            <div class="text-[10px] font-bold uppercase text-slate-500 tracking-wider mt-1">Your Final Score</div>
          </div>
        </div>

        <div class="grid grid-cols-3 gap-3 text-center">
          <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-200">
            <span class="text-xs font-bold text-emerald-800 uppercase">Correct</span>
            <p class="font-serif text-xl font-black text-emerald-700 mt-1" id="cbtCorrectCount">0</p>
          </div>
          <div class="p-3 bg-red-50 rounded-2xl border border-red-200">
            <span class="text-xs font-bold text-red-800 uppercase">Wrong</span>
            <p class="font-serif text-xl font-black text-red-700 mt-1" id="cbtWrongCount">0</p>
          </div>
          <div class="p-3 bg-amber-50 rounded-2xl border border-amber-200">
            <span class="text-xs font-bold text-amber-800 uppercase">Blank</span>
            <p class="font-serif text-xl font-black text-amber-700 mt-1" id="cbtBlankCount">0</p>
          </div>
        </div>

        <div class="space-y-3 pt-2">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Question-by-Question Review & Answers</h3>
          <div class="space-y-3" id="cbtBreakdownMount"></div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-between items-center">
          <button type="button" onclick="setActiveRole('student')" class="px-5 py-2.5 bg-brand-orange hover:bg-brand-orangeHover text-white font-bold text-xs rounded-xl shadow-md transition cursor-pointer">
            ← Return to Dashboard
          </button>
        </div>
      </section>

    </main>
