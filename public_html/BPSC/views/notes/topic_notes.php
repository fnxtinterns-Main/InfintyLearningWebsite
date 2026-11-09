<?php
/**
 * Infinity Learning - Topic Study Notes & Mastery Gateway View
 */
if (!defined('BPSC_PORTAL')) {
    exit('Direct access not permitted');
}
?>
    <!-- ========================================================= -->
    <!-- VIEW 5: TOPIC STUDY NOTES & MASTERY GATEWAY (STUDENT)     -->
    <!-- ========================================================= -->
    <main id="view-topic-notes" class="hidden max-w-5xl mx-auto w-full px-4 lg:px-8 py-6 flex-1 space-y-6">
      
      <!-- Top Navigation & Status Banner -->
      <div class="bg-white rounded-3xl border border-brand-peachBorder p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <button onclick="setActiveRole('student')" class="text-xs font-bold text-brand-orange hover:underline flex items-center gap-1 cursor-pointer">
              ← Return to Candidate Dashboard
            </button>
            <span class="text-brand-peachBorder">•</span>
            <span class="text-[10px] font-sans px-2 py-0.5 rounded bg-brand-peachSoft text-brand-orange font-bold uppercase" id="topicNotesCategoryBadge">PART II GS</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-black text-brand-slateText tracking-tight" id="topicNotesHeading">Topic Study Notes & Revision Guide</h1>
          <div class="text-xs text-brand-muted font-medium flex items-center gap-2 flex-wrap" id="topicNotesMeta">
            <span>BPSC TRE 4.0 Study Notes & Revision Guide</span>
            <span class="text-brand-peachBorder">•</span>
            <span class="text-brand-orange font-sans font-bold" id="topicNotesUploadMeta">📅 Uploaded: Recent</span>
          </div>
        </div>

        <div class="flex items-center gap-2.5">
          <span id="topicNotesCurrentStatusBadge" class="text-xs px-3 py-1.5 rounded-xl font-bold font-sans uppercase bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
            In Progress
          </span>
          <button id="topicNotesTeacherUploadBtn" onclick="openTopicNotesEditor(currentActiveStudyTopicId)" class="hidden px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer" title="Teacher / Admin: Upload or edit notes and attach PDF handout for this topic">
            <span>📤</span> Upload Notes & PDF
          </button>
          <button onclick="launchCurrentTopicQuiz()" class="px-4 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer">
            <span>⚡</span> Take Practice Test
          </button>
        </div>
      </div>

      <!-- Notes Content Deck -->
      <article class="bg-white rounded-3xl border border-brand-peachBorder p-6 sm:p-8 shadow-sm space-y-6 leading-relaxed">
        
        <!-- Summary Callout -->
        <div class="p-4 rounded-2xl bg-brand-ivory border-l-4 border-brand-orange space-y-1">
          <span class="text-[10px] font-sans font-bold uppercase tracking-wider text-brand-orange">Key Concept Overview</span>
          <p class="text-xs sm:text-sm font-semibold text-brand-slateText" id="topicNotesSummary">
            Detailed overview of concepts, core frameworks, and historical/scientific developments.
          </p>
        </div>

        <!-- Attached Document Card (when document is uploaded) -->
        <div id="topicNotesAttachmentCard" class="hidden p-4 rounded-2xl bg-brand-orangeLight border border-brand-peachBorder flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-brand-orange text-white flex items-center justify-center font-bold text-lg shrink-0">
              📄
            </div>
            <div>
              <span class="text-[10px] font-sans uppercase font-bold text-brand-orange" id="topicNotesAttachedBadge">Document Attached</span>
              <h4 class="font-bold text-xs sm:text-sm text-brand-slateText" id="topicNotesAttachedFileName">Document.pdf</h4>
              <p class="text-[10px] text-brand-muted font-mono" id="topicNotesAttachedMeta">Uploaded study notes document</p>
            </div>
          </div>
          <button type="button" onclick="downloadCurrentTopicAttachment()" class="px-4 py-2 bg-brand-orange hover:bg-brand-orangeHover text-white rounded-xl font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
            <span>📥</span> Download Document →
          </button>
        </div>

        <!-- Detailed Body -->
        <div class="space-y-4 text-xs sm:text-sm text-brand-slateText" id="topicNotesBody">
          <!-- Dynamic Content Loaded via loadTopicStudyDossier() -->
        </div>

        <!-- BPSC Past Year Exam Insights & Traps -->
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-2">
          <div class="flex items-center gap-2 text-emerald-900 font-bold text-xs uppercase tracking-wide">
            <span>🎯</span> BPSC Exam Trends & High-Yield Traps
          </div>
          <p class="text-xs text-emerald-950 font-medium leading-relaxed" id="topicNotesTips">
            Pay special attention to important dates, key terms, and common questions asked in previous BPSC exams.
          </p>
        </div>

      </article>

      <!-- Bottom Mastery Test Action Callout -->
      <section class="bg-gradient-to-r from-brand-orange via-orange-600 to-amber-500 rounded-3xl p-6 sm:p-7 text-white shadow-md flex flex-col sm:flex-row items-center justify-between gap-5">
        <div class="space-y-1 text-center sm:text-left">
          <span class="text-[10px] font-sans font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-white/20 text-amber-100">
            Topic Check (75% Target Score)
          </span>
          <h2 class="text-lg sm:text-xl font-black">Ready to test your preparation?</h2>
          <p class="text-xs text-orange-100 max-w-xl font-medium">
            Take this quick practice test to check your concepts. Scoring 75% or higher confirms your topic preparation and updates your progress report.
          </p>
        </div>

        <button onclick="launchCurrentTopicQuiz()" class="w-full sm:w-auto px-6 py-3 bg-white hover:bg-amber-50 text-brand-orange font-extrabold text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center justify-center gap-2 cursor-pointer shrink-0">
          <span>Start Practice Test Now</span>
          <span>⚡</span>
        </button>
      </section>

    </main>

