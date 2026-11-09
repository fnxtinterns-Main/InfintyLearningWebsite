<?php
/**
 * Infinity Learning — BPSC TRE 4.0 Ecosystem & Proctored CBT Engine
 * Central PHP Application Entrypoint
 */

// Define access constant
define('BPSC_PORTAL', true);

// Load configurations and core services
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/UserManager.php';

// 1. Header Layout (HTML, Head, Fonts, Tailwind, CSS)
require_once __DIR__ . '/views/layouts/header.php';

// 2. Multi-Role Authentication Gate Screen (Candidate, Teacher, Admin)
require_once __DIR__ . '/views/auth/gate.php';

// 3. Application Shell Navigation (Announcement Bar, Sticky Header, Live Countdown)
require_once __DIR__ . '/views/layouts/nav.php';

// 4. View 1: Candidate / Student Dashboard (Aspirant Radar, HUD, Syllabus Engine, Bihar Vault)
require_once __DIR__ . '/views/student/dashboard.php';

// 5. View 2: Teacher / Faculty Dashboard (Question Papers, Books Library)
require_once __DIR__ . '/views/teacher/dashboard.php';

// 6. View 3: Admin AIVERSE Central Dashboard (Platform Metrics, Payment Ledger, Q-Bank)
require_once __DIR__ . '/views/admin/dashboard.php';

// 7. View 4: Proctored CBT Split-Screen Engine (Proctor Guard, Timer, Palette, Scorecard)
require_once __DIR__ . '/views/cbt/engine.php';

// 8. View 5: Topic Study Notes & 75% Mastery Gate (Lecture Notes, Documents, Retention Quiz)
require_once __DIR__ . '/views/notes/topic_notes.php';

// 9. View 6: Candidate Profile, Subscription & Achievements Terminal
require_once __DIR__ . '/views/student/profile.php';

// 10. All Modals (Curator, Word Bulk Upload, Diagrams, Books, PYQs, Checkout, Breach Alert)
require_once __DIR__ . '/views/modals/modals.php';

// 11. Footer Layout & Complete Core JavaScript Engine
require_once __DIR__ . '/views/layouts/footer.php';

