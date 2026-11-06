# Infinity Learning Platform (`infintylearning.net`)
## Comprehensive Master System Architecture, Module Manual & Operations Guide

> **Document Classification:** Official Master Technical Specification & Operations Runbook  
> **Platform Version:** 3.0 Enterprise Multi-Tier  
> **Target Production Host:** AWS EC2 Cloud (`13.205.112.255`), Hostinger DNS, Upstash Redis Mumbai  
> **Live Web Services:**  
> - Public Educational LMS: [https://infintylearning.net](https://infintylearning.net)  
> - FutureNxt Corporate Portal: [https://infintylearning.net/futurenxt](https://infintylearning.net/futurenxt)

---

## Table of Contents
1. [Platform Vision & High-Level Architecture](#1-platform-vision--high-level-architecture)
2. [Architectural Isolation: How Subsystems Never Affect Each Other](#2-architectural-isolation-how-subsystems-never-affect-each-other)
3. [Complete Database Architecture & Schemas](#3-complete-database-architecture--schemas)
4. [Educational Platform: Role-by-Role Deep Dive](#4-educational-platform-role-by-role-deep-dive)
   - [Super Admin Portal (`/admin/*`)](#a-super-admin-portal-admin)
   - [School Administrator Portal (`/school/*`)](#b-school-administrator-portal-school)
   - [Teacher Portal (`/teacher/*`)](#c-teacher-portal-teacher)
   - [Student Portal (`/student/*`)](#d-student-portal-student)
   - [Parent Access & Public Features](#e-parent-access--public-features)
5. [Core Technical Engines](#5-core-technical-engines)
   - [How Attendance is Marked, Stored & Computed](#a-how-attendance-is-marked-stored--computed)
   - [How Official Certificates are Generated (PDF Engine)](#b-how-official-certificates-are-generated-pdf-engine)
   - [Rising Rooks Chess Academy Engine](#c-rising-rooks-chess-academy-engine)
   - [AIverse Digital Course & Bundle Marketplace](#d-aiverse-digital-course--bundle-marketplace)
   - [Academic Assessment & AI Question Bank Engine](#e-academic-assessment--ai-question-bank-engine)
   - [Live Interactive Whiteboard](#f-live-interactive-whiteboard)
6. [FutureNxt Enterprise Operations Subsystem](#6-futurenxt-enterprise-operations-subsystem)
7. [Cloud Infrastructure, Server Setup & Network Topology](#7-cloud-infrastructure-server-setup--network-topology)
8. [Master Connection & Operations Runbook](#8-master-connection--operations-runbook)
9. [Performance Benchmarks & Scaling Proof](#9-performance-benchmarks--scaling-proof)
10. [Backup, Disaster Recovery & Security Runbook](#10-backup-disaster-recovery--security-runbook)

---

## 1. Platform Vision & High-Level Architecture

`infintylearning.net` is a unified, cloud-native web ecosystem partitioned into two major application domains operating on the same AWS infrastructure:

```
                                  [ https://infintylearning.net ]
                                                 │
                                        [ Nginx 1.30.6 ]
                                                 │
                                   [ Route Dispatcher: index.php ]
                                                 │
                   ┌─────────────────────────────┴─────────────────────────────┐
                   ▼                                                           ▼
     [ DOMAIN A: INFINITY LEARNING ]                             [ DOMAIN B: FUTURENXT ENTERPRISE ]
     Namespace: App\*                                            Namespace: App\FutureNxt\*
     Database: u999011396_inflearning                            Database: u999011396_futurenxt
     Sessions: il_session                                        Sessions: fnx_user / Auth
     ─────────────────────────────────                           ─────────────────────────────────
     • Super Admin Command Center                                • Executive Portal (CEO / CTO / Admin)
     • School Management System (SMS)                            • Hierarchical Team Codes (FNX-M-001-E-001)
     • Teacher Workspace & AI Bank                               • Strict 3-Task Concurrent Workload
     • Student Learning Experience                               • Mandatory Daily Standup Updates
     • Rising Rooks Chess Academy                                • Conflict-Aware Leave & WFH Engine
     • Timetable & Attendance Engine                             • PII & KYC Masking (Aadhaar/PAN/Bank)
     • Dynamic PDF Certificate Generator                         • Company Notice & Holiday Broadcasts
     • AIverse Course Catalog & Bundles                          • 6-Hour Task Deadline Warning Engine
```

---

## 2. Architectural Isolation: How Subsystems Never Affect Each Other

A cornerstone requirement of the platform is **complete fault tolerance and modular independence**: an issue, error, migration, or heavy load in one part of the platform **never degrades or breaks any other part**.

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                               ARCHITECTURAL ISOLATION                                  │
├────────────────────────────────┬───────────────────────────────────────────────────────┤
│ Isolation Layer                │ Implementation & Safeguard Mechanism                  │
├────────────────────────────────┼───────────────────────────────────────────────────────┤
│ 1. Database Boundary           │ Physical separation into two distinct schemas:        │
│                                │ • `u999011396_inflearning` (56 educational tables)    │
│                                │ • `u999011396_futurenxt` (8 enterprise tables)        │
│                                │ Different database users, distinct credentials, and   │
│                                │ separate PDO connection pools. FutureNxt cannot lock   │
│                                │ LMS tables, and LMS queries never touch HRMS tables.  │
├────────────────────────────────┼───────────────────────────────────────────────────────┤
│ 2. PHP Namespace Isolation     │ Strict PSR-4 namespace boundaries:                    │
│                                │ • `App\*` for LMS, School, Teacher, Student           │
│                                │ • `App\FutureNxt\*` for Corporate Operations          │
│                                │ Zero class collisions, separate autoloading paths.    │
├────────────────────────────────┼───────────────────────────────────────────────────────┤
│ 3. Strict Role-Based Routing   │ Central router (`public_html/index.php`) maps paths to│
│                                │ discrete view scripts. Each section enforces session  │
│                                │ checks before executing any view logic:               │
│                                │ • `/admin/*` requires `Auth::requireRole('admin')`    │
│                                │ • `/school/*` requires `Auth::requireRole('school')`  │
│                                │ • `/teacher/*` requires `Auth::requireRole('teacher')`│
│                                │ • `/student/*` requires `Auth::requireRole('student')`│
│                                │ • `/futurenxt/*` requires `FutureNxt\Auth` session    │
├────────────────────────────────┼───────────────────────────────────────────────────────┤
│ 4. Independent Upload Paths    │ Uploads are strictly separated into dedicated silos:  │
│                                │ • `/uploads/certificates/` (PDF certificates)         │
│                                │ • `/uploads/lessons/` (LMS documents & video files)   │
│                                │ • `/uploads/futurenxt/tasks/` (Task completion PDFs)  │
│                                │ • `/uploads/futurenxt/photos/` (Staff profile photos) │
│                                │ File permissions are isolated to prevent cross-reads. │
├────────────────────────────────┼───────────────────────────────────────────────────────┤
│ 5. Session State Autonomy      │ LMS user sessions (`$_SESSION['user_id']`,            │
│                                │ `$_SESSION['role']`) run in parallel with FutureNxt   │
│                                │ employee authentication (`$_SESSION['fnx_user']`).    │
│                                │ Logging out of FutureNxt does not log out an LMS user │
│                                │ and vice versa.                                       │
└────────────────────────────────┴───────────────────────────────────────────────────────┘
```

---

## 3. Complete Database Architecture & Schemas

### Database 1: `u999011396_inflearning` (Educational LMS)
Contains 56 tables modeling the complete academic and operational lifecycle of schools, teachers, students, parents, and automated curriculum:

```
                    ┌─────────────────────────┐
                    │         schools         │
                    └────────────┬────────────┘
                                 │ 1:N
        ┌────────────────────────┼────────────────────────┐
        ▼                        ▼                        ▼
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│   teachers   │         │   classes    │         │ student_fees │
└───────┬──────┘         └───────┬──────┘         └──────────────┘
        │ 1:N                    │ 1:N
        │                ┌───────┴──────┐
        ▼                ▼              ▼
┌──────────────┐   ┌───────────┐  ┌───────────┐
│ teacher_notes│   │  batches  │  │ subjects  │
└──────────────┘   └─────┬─────┘  └─────┬─────┘
                         │ 1:N          │ 1:N
        ┌────────────────┼──────────────┼────────────────┐
        ▼                ▼              ▼                ▼
┌──────────────┐  ┌──────────────┐┌──────────────┐┌──────────────┐
│batch_students│  │  attendance  ││   lessons    ││question_bank │
└───────┬──────┘  └──────────────┘└──────┬───────┘└──────────────┘
        │ N:1                            │ 1:N
        ▼                                ▼
┌──────────────┐                  ┌──────────────┐
│   students   │                  │lesson_doubts │
└───────┬──────┘                  └──────────────┘
        │ 1:N
        ▼
┌──────────────┐
│ certificates │
└──────────────┘
```

#### Core Table Definitions:
1. **`users`**: Central authentication table for all LMS roles (`super_admin`, `school_admin`, `teacher`, `student`, `parent`). Stores password hash (Bcrypt), phone, email, OTP status, active state, and timestamps.
2. **`schools`**: Organization profile, domain/subdomain, registration number, address, official logo, school crest, contact information, subscription status.
3. **`teachers`**: Linked to `users` (`user_id`) and `schools` (`school_id`). Designation, qualifications, employee ID, bio, assigned subjects.
4. **`students`**: Linked to `users` (`user_id`) and `schools` (`school_id`). Roll number, admission date, class/grade, section, gender, date of birth, guardian name.
5. **`batches`**: Represents a distinct academic group (e.g. "Class 10 - Section A - Batch 2026"). Linked to `schools`, `teachers`, and academic year.
6. **`batch_students`**: Junction table mapping students to batches (`batch_id`, `student_user_id`, `enrolled_at`, `status`).
7. **`attendance`**: Daily roll-call records. Primary composite key on `(batch_id, student_user_id, attendance_date)`. Status ENUM (`present`, `absent`, `late`, `excused`, `leave`), `marked_by`, `remarks`, `created_at`.
8. **`certificates`**: Digital credentials issued for competitions and courses. `uuid`, `student_user_id`, `title`, `type` (`winner`, `participation`, `completion`), `file_path`, `metadata` (JSON containing rank, event, school name, verification hash).
9. **`lessons`**: Curriculum repository. Subject, chapter, topic, title, video link, document attachments (`.pdf`, `.docx`, `.pptx`), duration, sort order.
10. **`lesson_doubts` & `lesson_doubt_replies`**: Peer and teacher doubt resolution forum. Supports image attachment, reply threads, and `resolved` boolean flag.
11. **`question_bank`**: Categorized test items. Subject, class, topic, question type (MCQ, Short Answer, True/False), difficulty level, marks, options (JSON), correct answer, Bloom's taxonomy category.
12. **`academic_assessments` & `assessment_submissions`**: Timed online examinations. Start time, end time, max marks, passing percentage, questions list, student answers, scored marks, evaluation status.
13. **`student_fees`**: Financial tracking per student. Total fee, discounts, installments, paid amount, payment mode, receipt number, payment date.
14. **`aiverse_courses` & `aiverse_bundles`**: Digital modular courses, syllabus outline, pricing, bundle mappings, discount banners.
15. **`counseling_requests` & `counseling_logs`**: Student mental wellness and career counseling request queue, meeting schedule, confidential counselor notes.
16. **`chess_games`, `chess_puzzles`, `chess_ratings`**: Rising Rooks Chess Academy game history, move notations (PGN/FEN), Elo ratings, puzzle attempts.

---

### Database 2: `u999011396_futurenxt` (Enterprise HRMS)
Dedicated corporate database managing executive command, team hierarchy, and personnel accountability:

1. **`fnx_employees`**: Corporate staff records. `emp_code`, `email`, `password_hash`, `full_name`, `role` (`ceo`, `cto`, `admin`, `office_manager`, `manager`, `employee`, `intern`), `manager_id` (foreign key to reporting manager), `designation`, `department`, `phone`, `current_address`, `permanent_address`, `photo_path`, `aadhar_number`, `pan_number`, `bank_name`, `bank_account_no`, `bank_ifsc`, `bank_account_name`, `upi_id`, `status` (`active`, `pip`, `suspended`, `resigned`, `terminated`).
2. **`fnx_tasks`**: Operational assignments. `task_code` (`TSK-0001`), `title`, `description`, `priority` (`low`, `medium`, `high`, `urgent`), `due_date`, `due_time` (default `18:00:00`), `status` (`pending`, `in_progress`, `submitted`, `approved`, `rejected`), `doc_path` (mandatory documentation PDF), `reminder_sent_at`, `created_by`, `approved_by`.
3. **`fnx_task_assignees`**: Multi-assignee task distribution. `task_id`, `employee_id`, `assigned_by`, `assigned_at`, `is_active`, `reassigned_to`, `reassign_reason`.
4. **`fnx_daily_updates`**: Mandatory daily standup updates. `employee_id`, `update_date`, `tasks_summary`, `work_done` (point-by-point deliverables), `blockers`, `plan_tomorrow`, `hours_spent`. Unique key on `(employee_id, update_date)`.
5. **`fnx_leaves`**: Employee leave ledger. `employee_id`, `leave_type`, `start_date`, `end_date`, `total_days`, `reason`, `status` (`pending`, `approved`, `rejected`), `reviewed_by`, `reviewer_remarks`.
6. **`fnx_wfh_requests`**: Work From Home requests. `employee_id`, `start_date`, `end_date`, `reason`, `status`.
7. **`fnx_notices`**: Company-wide broadcasts. `title`, `category` (`holiday`, `event`, `notice`, `urgent`, `policy`), `target_audience`, `content`, `notice_date`, `is_pinned`, `status`, `created_by`.
8. **`fnx_audit_logs`**: Immutable security audit trail recording user ID, action, entity type, entity ID, and details.

---

## 4. Educational Platform: Role-by-Role Deep Dive

### A. Super Admin Portal (`/admin/*`)
The central command authority for the entire educational SaaS platform.
- **Access Route:** `https://infintylearning.net/admin`
- **Dashboard (`/admin/dashboard`):** Real-time aggregation of total registered schools, active teachers, enrolled students, lessons published, certificates generated, and financial metrics.
- **School Management (`/admin/schools`):** Onboard new schools, approve school licensing, configure subdomains, toggle active/suspended status, and audit school administrator accounts.
- **Global Teacher Directory (`/admin/teachers`):** Cross-school oversight of all faculty members with subject specializations, qualifications, and performance logs.
- **Curriculum & Lesson Vault (`/admin/lessons`):** Master repository of video lectures, curriculum syllabi, and downloadable worksheets available to all participating institutions.
- **Global Certificates (`/admin/certificates`):** Platform-wide certificate generation and revocation controls.
- **Question Paper Generator (`/admin/question-paper`):** Automated compiler assembling customized examination question papers from the central question bank with customizable layout, difficulty distribution, and answer keys.

---

### B. School Administrator Portal (`/school/*`)
Provides institutional administrative tools for principals, academic coordinators, and school administrators.
- **Access Route:** `https://infintylearning.net/school`
- **Institutional Dashboard (`/school/dashboard`):** Daily attendance percentage across all grades, fee collection summary, active batches, and faculty presence.
- **Faculty Management (`/school/teachers`):** Onboard teachers, set subject allocations, configure timetable slots, and audit teacher notes.
- **Student Admissions & Directory (`/school/students`):** Enroll students, assign student roll numbers, map students to classes and sections, generate student identity cards.
- **Batches & Timetables (`/school/batches`, `/school/timetable`):** Create academic divisions, configure weekly lecture schedules, assign room numbers, and handle substitute teacher assignments.
- **Fee Management (`/school/fees`):** Configure tuition fee slabs, record offline/online fee receipts, generate print-ready PDF fee receipts, and track pending dues per student.
- **AIverse Institutional Courses (`/school/courses`, `/school/bundles`):** Select digital enrichment courses, create bundled course packages, and enroll entire batches into specialized tracks.
- **School Branding & Custom Crests (`/school/branding`):** Upload official institutional crest/logo, header banners, and customized signatures used in automated certificate and report card generation.
- **Official Term Report Cards (`/school/report-card`):** Calculate cumulative marks, letter grades, percentage, teacher remarks, and export print-ready A4 grade sheets.

---

### C. Teacher Portal (`/teacher/*`)
The primary instructional workspace for educators.
- **Access Route:** `https://infintylearning.net/teacher`
- **Teacher Dashboard (`/teacher/dashboard`):** Today's scheduled lectures, quick-attendance shortcut, unread student doubts, and pending assignment reviews.
- **Batch Attendance Grid (`/teacher/attendance`):** Interactive attendance sheet listing all enrolled students with 1-click toggles for `Present`, `Absent`, `Late`, and `Excused`. Features bulk mark-all-present and instant save.
- **AI-Powered Question Bank (`/teacher/questions`):**
  - Search and filter questions by subject, class, and difficulty.
  - **AI Question Generation:** Integration with AI agents to automatically generate fresh multiple-choice and short-answer questions tailored to specific chapter objectives and Bloom's taxonomy levels.
- **Assessments & Online Quizzes (`/teacher/assessments`):** Create timed examinations, select questions from the bank, specify start/end windows, and evaluate subjective/objective submissions with grade books.
- **Student Doubt Desk (`/teacher/doubts`):** Real-time inbox of student doubts attached to specific video lessons. Teachers provide explanations, attach reference images, and mark questions as resolved.
- **Teacher Class Notes (`/teacher/notes`):** Upload revision guides, handwritten notes, and supplementary PDFs directly accessible by students of that batch.
- **Student Counseling Desk (`/teacher/counseling`):** Review confidential counseling requests, record student behavioral observations, and schedule one-on-one counseling sessions.

---

### D. Student Portal (`/student/*`)
The learner experience portal designed for engagement and academic success.
- **Access Route:** `https://infintylearning.net/student/dashboard`
- **Student Dashboard:** Daily timetable, active lesson playlist, attendance percentage gauge, upcoming tests, and announcements.
- **Video Lessons & Digital Classroom (`/lessons`, `/lesson?id=...`):** Stream structured syllabus lessons, view chapter breakdowns, track lesson completion percentage, and ask doubts directly below the video player.
- **Live Interactive Whiteboard (`/whiteboard`):** Real-time collaborative HTML5 canvas with freehand drawing, geometric tools, text notes, color palettes, and eraser for live tutoring sessions.
- **Rising Rooks Chess Academy (`/chess`):** Full chess academy featuring AI bot matches (Beginner to Grandmaster), daily tactics puzzles, match history, and Elo ranking.
- **Assessments & Exams (`/student/assessments`):** Take timed tests with anti-cheat timers, instant auto-grading for objective questions, and detailed performance breakdown.
- **Certificate Wall (`/my-certificates`):** Instant access to view and download all earned certificates (competition winner, academic excellence, participation) in high-resolution PDF format.
- **Fees & Ledger (`/student/fees`):** Review fee payment history, view installment schedules, and download official fee receipts.

---

### E. Parent Access & Public Features
- **Parent Pass (`/parent-pass?token=...`):** Secure tokenized link enabling parents to monitor their child's attendance rate, exam scorecards, fee status, and teacher remarks without needing to log in with student credentials.
- **Public Portals:**
  - Landing Page (`/`): Platform showcase, course offerings, admissions.
  - Counseling Desk (`/counseling`, `/counseling/apply`): Public booking for career and student counseling.
  - Policy Pages (`/terms`, `/privacy`, `/refund-policy`): Full legal and regulatory disclosures.

---

## 5. Core Technical Engines

### A. How Attendance is Marked, Stored & Computed

```
[ Teacher / Batch View ] ──▶ Selects Date & Batch ──▶ Queries Enrolled Students
                                                             │
                                                             ▼
                                                [ Attendance Matrix Grid ]
                                                Student A: [Present]
                                                Student B: [Absent]
                                                Student C: [Late]
                                                             │
                                                             ▼ Click "Save Attendance"
                                           [ POST /teacher/attendance-save-handler ]
                                                             │
                                                             ▼
                                     [ App\Attendance::mark() ]
                                     INSERT INTO attendance (batch_id, student_user_id,
                                                             attendance_date, status, marked_by)
                                     ON DUPLICATE KEY UPDATE status=VALUES(status)...
                                                             │
                                    ┌────────────────────────┴────────────────────────┐
                                    ▼                                                 ▼
                        [ Student Portal / Parent Pass ]                   [ School Analytics ]
                        Calculates % Present:                              Computes Batch %:
                        (Present / Total Days) * 100                       Visual attendance charts
```

- **Database Table:** `attendance`
- **Method:** `App\Attendance::mark(int $batchId, int $studentUserId, string $date, string $status, int $byUserId, string $remarks = '')`
- **Idempotency:** Uses MySQL `ON DUPLICATE KEY UPDATE` on the unique composite key `(batch_id, student_user_id, attendance_date)`. Rerunning or editing attendance updates existing records cleanly without duplicate rows.
- **Computation Engine:**
  - `summaryForStudent($userId)`: Counts days by status (`present`, `absent`, `late`, `excused`).
  - `summaryForBatch($batchId, $from, $to)`: Generates batch-level attendance percentages for management reports.

---

### B. How Official Certificates are Generated (PDF Engine)

Certificates are dynamically synthesized into **print-ready, publication-grade vector PDF documents** using `App\Certificate` and `App\InfinityPDF` (FPDF/FPDI):

```
[ Trigger Event ] (e.g. Competition Winner / Course Completion)
         │
         ▼
[ App\Certificate::generate() ]
         ├── Dimensions: A4 Landscape (297mm x 210mm)
         ├── Layer 1: Warm Background Fill (#FFFEFA)
         ├── Layer 2: Deep Navy Outer Border (RGB: 18, 42, 90)
         ├── Layer 3: Metallic Gold Inner Pin-Stripe (RGB: 190, 148, 32)
         ├── Layer 4: High-Resolution Logos (School Crest + Infinity Learning)
         ├── Layer 5: Dynamic Typography (Student Name, Event Title, Rank, Date)
         ├── Layer 6: Unique Verification UUID (Cryptographic Verification Hash)
         └── Layer 7: Output to `/uploads/certificates/cert_[type]_[id]_[timestamp].pdf`
         │
         ▼
[ INSERT INTO certificates ] (UUID, Student ID, File Path, Metadata JSON)
         │
         ▼
[ Student Dashboard & Public Verification URL: /my-certificates ]
```

- **File Output:** Saved to `/var/www/infintylearning/public_html/uploads/certificates/`.
- **Tamper-Proof Verification:** Each certificate record stores a unique cryptographic UUID (`certificates.uuid`). Anyone verifying the certificate can validate authenticity against the database record.

---

### C. Rising Rooks Chess Academy Engine
A full-featured chess training and playing platform integrated into Infinity Learning:
- **Play vs AI Bots:** Multiple skill tiers powered by calibrated engine profiles (Beginner, Intermediate, Advanced, Master).
- **Daily Puzzles:** Tactically curated positions requiring students to find the best move sequences.
- **Match History & PGN Viewer:** Records all played moves in Portable Game Notation (PGN) for post-game coach analysis.
- **Elo Rating Engine:** Calculates real-time student rating changes based on game outcomes using standard Elo formulas.

---

### D. AIverse Digital Course & Bundle Marketplace
- **Modular Curriculum:** Enables schools and individual students to purchase or unlock specialized subject tracks (e.g. Robotics, AI Fundamentals, Coding, Advanced Mathematics).
- **Bundle System:** Groups multiple digital courses into discounted bundles with custom banners and promotional pricing.
- **Enrollment Handler:** Validates payment, provisions access in `aiverse_enrollments`, and makes chapters instantly accessible in the student course player.

---

### E. Academic Assessment & AI Question Bank Engine
- **Question Hierarchy:** Class $\rightarrow$ Subject $\rightarrow$ Chapter $\rightarrow$ Topic.
- **Question Types:** Multiple Choice Questions (MCQ), Multi-Select, True/False, Short Subjective Answer.
- **AI Question Synthesis:** Calls generative AI endpoints to synthesize fresh questions based on syllabus topics, saving teachers hours of manual authoring.
- **Online Examination Room:** Secure test-taking interface with timer countdown, auto-save on answer selection, and automated score computation.

---

### F. Live Interactive Whiteboard
- **Technology:** HTML5 Canvas + JavaScript vector drawing engine.
- **Tools:** Freehand pencil, brush thickness slider, geometric shapes (rectangles, circles, straight lines, coordinate grids), color palette picker, eraser, and complete canvas wipe.
- **Use Case:** Interactive online tutoring sessions and doubt resolution during live classes.

---

## 6. FutureNxt Enterprise Operations Subsystem

FutureNxt (`/futurenxt`) is the executive management and human resources command center of the company:

### A. Role Hierarchy & Authority Matrix
1. **CEO (`ceo`)**: Chief Executive Officer. Executive oversight. **Exempt from mandatory daily updates.** Full employee onboarding, profile modification, notice broadcasting, and leave approval authority.
2. **CTO (`cto`)**: Chief Technology Officer. Technical and engineering oversight. Full administrative authority.
3. **Admin (`admin`)**: Operations and platform administrator.
4. **Office Manager (`office_manager`)**: Operations supervisor.
5. **Manager (`manager`)**: Team and department manager. Onboards reportees and manages sub-teams.
6. **Employee (`employee`)**: Full-time corporate staff.
7. **Intern (`intern`)**: Trainees and interns.

### B. Manager Hierarchical Code Auto-Generation
- When an employee is assigned under a manager whose code is `FNX-M-001`, their code is system-generated:
  - Employee: `FNX-M-001-E-001`, `FNX-M-001-E-002`, `FNX-M-001-E-003`...
  - Intern: `FNX-M-001-I-001`, `FNX-M-001-I-002`...
- If assigned directly under CEO, CTO, or Admin, standard corporate codes are generated (`FNX-E-001`, `FNX-I-001`).

### C. Strict 3-Task Concurrent Capacity Safeguard
- Company policy enforces a strict maximum of **3 active tasks** (`pending`, `in_progress`, `submitted`) per person.
- If a manager attempts to assign a 4th task, the system rejects the request.
- Tasks require an uploaded documentation PDF before they can be submitted for review.
- Any team member can click **👁️ View Task** to review full requirements and deliverables.

### D. Daily Work Updates & Standup
- All personnel (except CEO) must log point-by-point deliverables, hours spent, blockers, and tomorrow's plans before midnight.
- Active tasks can be checked off to auto-fill deliverables.
- Standup dashboard displays real-time team completion rates and highlights pending employees.

### E. Conflict-Aware Leaves & WFH
- When an employee requests leave, the system cross-references all active tasks assigned to them during the requested dates and alerts management.
- Approvals and rejections trigger immediate automated email notifications to the employee via Hostinger Business Mail SMTP.

### F. Company Notice Board & Holiday Calendar
- Admin, CEO, and CTO can broadcast official notices with categories (`holiday`, `event`, `notice`, `urgent`, `policy`), target specific groups, and send instant email blasts.
- Pinned notices and holiday schedules appear in the dashboard notice widget and header bell notification.

### G. Automated 6-Hour Task Deadline Warning Engine
- Background hourly cron job on AWS EC2 (`cronie`):
  ```bash
  0 * * * * php /var/www/infintylearning/infinitylearning/cron/task_reminders.php >> /tmp/fnx_cron.log 2>&1
  ```
- Scans tasks due in $\le 6$ hours and sends urgent email warnings to all assigned personnel.

### H. PII Masking & Privacy Controls
- Aadhaar (`•••• •••• 1234`), PAN (`••••••5678`), bank account numbers, and passwords remain masked by default. Clicking the 👁️ icon reveals details securely.

---

## 7. Cloud Infrastructure, Server Setup & Network Topology

```
[ Client Web Browser ]
         │
         │ DNS Resolution (infintylearning.net ──▶ 13.205.112.255)
         ▼
[ Hostinger DNS Zone Management ]
         │
         │ Port 80/443 (TCP)
         ▼
[ AWS EC2 Instance (Mumbai ap-south-1) · 13.205.112.255 ]
         │
         ├── Nginx Web Server (Port 443 TLS 1.3 / Port 80 Redirect)
         │       │
         │       ├── Static Assets Cache (/assets/*, /uploads/*)
         │       └── FastCGI Unix Socket (/run/php-fpm/www.sock)
         │
         ├── PHP 8.2-FPM Engine
         │       │
         │       ├── Upstash Redis Cluster (tls://holy-panther-217313.upstash.io:6379)
         │       │   (Object Cache & 30-Day Stateless Session Storage)
         │       │
         │       ├── Local MariaDB / MySQL Server (localhost:3306)
         │       │   ├── Schema A: u999011396_inflearning (LMS Platform)
         │       │   └── Schema B: u999011396_futurenxt (Enterprise Operations)
         │       │
         │       └── Hostinger SMTP Relay (smtp.hostinger.com:587 STARTTLS)
         │           (Transaction Mails, Leave Decisions, 6-Hour Task Warnings)
         │
         └── Automated Cron Service (crond / cronie)
             └── Hourly Task Deadline Scanner (cron/task_reminders.php)
```

---

## 8. Master Connection & Operations Runbook

### A. How to Connect to AWS EC2 via SSH

#### From Windows (PowerShell):
```powershell
# Step 1: Open PowerShell and locate your key file:
cd "C:\Users\MY PC\Downloads"

# Step 2: Establish SSH connection:
ssh -i "inflea.pem" ec2-user@13.205.112.255
```

#### From macOS / Linux Terminal:
```bash
# Step 1: Secure private key permissions:
chmod 400 ~/Downloads/inflea.pem

# Step 2: Connect:
ssh -i ~/Downloads/inflea.pem ec2-user@13.205.112.255
```

---

### B. Service Management Cheatsheet
Run these commands on the server to monitor and maintain system components:

```bash
# Check service health:
sudo systemctl status nginx
sudo systemctl status php-fpm
sudo systemctl status mariadb
sudo systemctl status crond

# Graceful reloads (zero downtime for users):
sudo systemctl reload nginx
sudo systemctl reload php-fpm

# Full service restarts:
sudo systemctl restart php-fpm
sudo systemctl restart nginx
sudo systemctl restart mariadb
```

---

### C. Direct MySQL Database Access via CLI

```bash
# 1. Connect to Educational Platform Database:
mysql -u u999011396_Inflearning -p'Infinitylearning@123' u999011396_inflearning

# 2. Connect to FutureNxt Operations Database:
mysql -u u999011396_fnxt_portal -p'Inflearning@99' u999011396_futurenxt

# Useful MySQL commands:
SHOW TABLES;
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM fnx_employees;
exit;
```

---

### D. Real-Time Application & Server Log Monitoring

```bash
# Monitor Nginx web requests and errors in real-time:
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log

# Monitor PHP runtime errors:
sudo tail -f /var/log/php-fpm/error.log

# Monitor automated 6-hour task deadline reminder cron:
tail -f /tmp/fnx_cron.log
```

---

### E. Hostinger DNS & Business Mail Setup
1. **Domain Pointing:**
   - Go to **Hostinger hPanel** $\rightarrow$ **Domains** $\rightarrow$ `infintylearning.net` $\rightarrow$ **DNS / Nameservers**.
   - Ensure the `@` A Record points to `13.205.112.255`.
   - Ensure the `www` record points to `@` or `13.205.112.255`.
2. **Business Mail SMTP:**
   - Host: `smtp.hostinger.com`
   - Port: `587` (STARTTLS)
   - User: `info@infintylearning.net`
   - Password: Defined securely in `/var/www/infintylearning/infinitylearning/.env`.

---

## 9. Performance Benchmarks & Scaling Proof

Independent load tests were executed directly on the live AWS EC2 production server using **ApacheBench (`ab`)**:

| Benchmark Parameter | Test Run 1 (50 Concurrency) | Test Run 2 (100 Concurrency) |
| :--- | :--- | :--- |
| **Total Completed Requests** | 1,000 requests | 2,000 requests |
| **Concurrency Level** | **50 concurrent workers** | **100 concurrent workers** |
| **Total Test Time** | 1.501 seconds | 2.860 seconds |
| **Throughput (Requests / Sec)** | **666.38 req/sec** | **699.39 req/sec** (~700 req/sec) |
| **Data Transfer Rate** | 5.87 MB / second | 6.16 MB / second |
| **Failed Requests** | **0 (0.00%)** | **0 (0.00%)** |
| **Mean Latency (Average)** | **75.0 ms** | **142.9 ms** |
| **50% of Requests Completed In** | $\le 72$ ms | $\le 140$ ms |
| **95% of Requests Completed In** | $\le 78$ ms | $\le 173$ ms |
| **99% of Requests Completed In** | $\le 101$ ms | $\le 196$ ms |

> **Conclusion:** The system architecture easily absorbs **hundreds of simultaneous active users**, delivering instant sub-175ms responses with zero dropped requests.

---

## 10. Backup, Disaster Recovery & Security Runbook

### Daily Automated Database Backup Script:
```bash
# Dump both production databases into a timestamped, gzip-compressed archive:
sudo mysqldump -u u999011396_Inflearning -p'Infinitylearning@123' u999011396_inflearning | gzip > /var/backups/inflearning_$(date +%F_%H%M%S).sql.gz
sudo mysqldump -u u999011396_fnxt_portal -p'Inflearning@99' u999011396_futurenxt | gzip > /var/backups/futurenxt_$(date +%F_%H%M%S).sql.gz
```

### Full Database Restore Command:
```bash
gunzip < /var/backups/inflearning_YYYY-MM-DD.sql.gz | mysql -u u999011396_Inflearning -p'Infinitylearning@123' u999011396_inflearning
gunzip < /var/backups/futurenxt_YYYY-MM-DD.sql.gz | mysql -u u999011396_fnxt_portal -p'Inflearning@99' u999011396_futurenxt
```

### File Uploads Backup:
```bash
# Compress all user documents, certificates, and media:
sudo tar -czvf /var/backups/uploads_backup_$(date +%F).tar.gz /var/www/infintylearning/public_html/uploads/
```
