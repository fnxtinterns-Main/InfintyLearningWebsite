<?php
declare(strict_types=1);

use App\Auth;
use App\Helper;
use App\AcademicAssessment;
use App\QuestionBank;

Auth::requireLogin();
$user = Auth::user();
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;
$userRole = $user['role'] ?? '';
$isAdmin = in_array($userRole, ['admin', 'super_admin', 'school_admin'], true);

// School branding
$sDb = \App\Database::connect();
$school = null;
if ($schoolId) {
    $sStmt = $sDb->prepare("SELECT * FROM schools WHERE id = ? LIMIT 1");
    $sStmt->execute([$schoolId]);
    $school = $sStmt->fetch() ?: null;
}
$subjects = $sDb->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();

$teachersList = [];
if ($isAdmin) {
    if ($schoolId) {
        $stmtT = $sDb->prepare("SELECT u.id, u.email, t.full_name AS name, t.expert_field FROM users u LEFT JOIN teachers t ON t.user_id = u.id WHERE u.school_id = ? AND u.role = 'teacher' ORDER BY t.full_name ASC");
        $stmtT->execute([$schoolId]);
        $teachersList = $stmtT->fetchAll();
    } else {
        $teachersList = $sDb->query("SELECT u.id, u.email, t.full_name AS name, t.expert_field FROM users u LEFT JOIN teachers t ON t.user_id = u.id WHERE u.role = 'teacher' ORDER BY t.full_name ASC")->fetchAll();
    }
}

// Optional assessment ID
$assessmentId = (int)($_GET['id'] ?? 0);
$assessment = null;
$questions = [];

if ($assessmentId) {
    $assessment = AcademicAssessment::find($assessmentId);
    if ($assessment && !empty($assessment['questions_json'])) {
        $questions = json_decode($assessment['questions_json'], true) ?: [];
    }
}

// If no assessment loaded, populate standard questions from Question Bank or default exam template
if (empty($questions)) {
    $qbList = QuestionBank::list([
        'school_id' => $schoolId,
        'limit' => 12
    ]);
    if (!empty($qbList)) {
        $questions = $qbList;
    }
}

$pageTitle = 'Examination Question Paper Generator — Infinity Learning';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= Helper::e($pageTitle) ?></title>
<link rel="icon" href="/assets/img/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Merriweather:wght@400;700;900&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    background: #E8E5DF;
    font-family: 'Merriweather', Georgia, serif;
    color: #111;
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* TOP TOOLBAR */
.toolbar-wrap {
    max-width: 860px;
    width: 100%;
    margin-bottom: 16px;
    background: #fff;
    padding: 12px 18px;
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    border: none;
    transition: all 0.15s ease;
}
.btn-primary { background: #FF7A18; color: #fff; }
.btn-primary:hover { background: #E66508; }
.btn-secondary { background: #F3EFEA; color: #2B1B12; border: 1px solid #D5C7B8; }
.btn-secondary:hover { background: #EAE3D9; }
.btn-success { background: #16A34A; color: #fff; }

/* THE A4 QUESTION PAPER */
.paper-sheet {
    max-width: 860px;
    width: 100%;
    background: #fff;
    padding: 50px 58px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid #D5C7B8;
    line-height: 1.6;
}

.school-header {
    text-align: center;
    margin-bottom: 20px;
}
.school-logo {
    max-height: 54px;
    object-fit: contain;
    margin-bottom: 6px;
}
.school-name {
    font-size: 24px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #000;
}
.school-address {
    font-size: 12px;
    font-style: italic;
    color: #444;
    margin-top: 2px;
}
.exam-title {
    font-size: 17px;
    font-weight: 800;
    text-transform: uppercase;
    margin-top: 8px;
    text-decoration: underline;
    letter-spacing: 0.05em;
}

/* META BOX */
.meta-box {
    border: 1.5px solid #000;
    padding: 10px 16px;
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    font-size: 13.5px;
    font-weight: 700;
}
.meta-left, .meta-right { display: flex; flex-direction: column; gap: 4px; }

/* INSTRUCTIONS */
.instructions-box {
    border-bottom: 1px solid #000;
    padding-bottom: 12px;
    margin-bottom: 24px;
    font-size: 12.5px;
}
.instructions-box strong { font-size: 13px; text-transform: uppercase; }
.instructions-box ol { margin-left: 20px; margin-top: 4px; }
.instructions-box li { margin-bottom: 2px; }

/* SECTION HEADINGS */
.section-heading {
    text-align: center;
    font-size: 14.5px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin: 24px 0 14px;
    border-bottom: 1px dashed #666;
    padding-bottom: 4px;
}

/* QUESTION ITEMS */
.q-item {
    margin-bottom: 18px;
    font-size: 14px;
    page-break-inside: avoid;
}
.q-title-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    font-weight: 700;
}
.q-num { margin-right: 6px; }
.q-marks {
    font-family: 'JetBrains Mono', monospace;
    font-weight: 700;
    font-size: 12px;
    white-space: nowrap;
    margin-left: 12px;
}

.mcq-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 16px;
    margin-top: 6px;
    padding-left: 24px;
    font-size: 13.5px;
}

.editable-active {
    outline: 2px dashed #FF7A18 !important;
    background: #FFFDF5;
    padding: 2px 4px;
}

/* QUESTION PAPER IMAGE & DIAGRAM STYLES */
.btn-attach-img {
    background: #F3EFEA;
    border: 1px dashed #B8A89A;
    color: #6B5B52;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 5px;
    transition: all 0.15s ease;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.btn-attach-img:hover {
    background: #E8E0D5;
    border-color: #8C786A;
    color: #111;
}
.paper-q-img-wrap {
    margin: 8px 0;
}
.paper-q-img-container {
    position: relative;
    display: inline-block;
    max-width: 100%;
}
.paper-q-img {
    max-height: 220px;
    max-width: 100%;
    object-fit: contain;
    border: 1px solid #D5C7B8;
    border-radius: 6px;
    display: block;
}
.remove-q-img-btn {
    position: absolute;
    top: 4px;
    right: 4px;
    background: rgba(220, 38, 38, 0.9);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 11px;
    font-weight: 800;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.remove-q-img-btn:hover {
    background: #DC2626;
}

@media print {
    body { background: #fff !important; padding: 0 !important; }
    .toolbar-wrap, .q-paper-tools, .btn-attach-img, .remove-q-img-btn { display: none !important; }
    .paper-sheet {
        border: none !important;
        box-shadow: none !important;
        padding: 24px 30px !important;
        max-width: 100% !important;
        width: 100% !important;
    }
    .paper-q-img { max-height: 200px !important; border: 1px solid #888 !important; }
}
</style>
</head>
<body>

<!-- TOOLBAR -->
<div class="toolbar-wrap">
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <a href="javascript:history.back()" class="btn btn-secondary">← Back</a>
        
        <!-- 1-CLICK CLASS SWITCHER -->
        <div style="display:flex; align-items:center; gap:6px;">
            <label style="font-weight:700; font-size:12.5px;">Switch Class:</label>
            <select id="classSwitcher" onchange="changeClass(this.value)" class="form-control" style="font-family:'Plus Jakarta Sans',sans-serif; padding:5px 10px; border-radius:6px; border:1px solid #D5C7B8; font-weight:700; cursor:pointer;">
                <option value="CLASS V">Class V</option>
                <option value="CLASS VI" selected>Class VI</option>
                <option value="CLASS VII">Class VII</option>
                <option value="CLASS VIII">Class VIII</option>
                <option value="CLASS IX">Class IX</option>
                <option value="CLASS X">Class X</option>
                <option value="CLASS XI">Class XI</option>
                <option value="CLASS XII">Class XII</option>
            </select>
        </div>
    </div>

    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <button type="button" onclick="openAiPaperModal()" class="btn" style="background:linear-gradient(135deg, #6366f1, #8b5cf6); color:#fff; font-weight:700; box-shadow:0 3px 10px rgba(99,102,241,0.25);" title="Generate 10 questions including Equations & Diagrams using AI">
            ✨ AI Generate Paper (10 Qs)
        </button>
        <button type="button" onclick="document.getElementById('schoolLogoFileInput').click()" class="btn btn-secondary" title="Upload custom school crest / logo for question paper header">
            📷 Upload School Logo
        </button>
        <button type="button" id="editToggleBtn" onclick="toggleEditMode()" class="btn btn-secondary" title="Click to edit any text directly on the question paper">
            ✏️ Edit Paper Text
        </button>
        <button type="button" onclick="saveTemplate()" class="btn btn-secondary" title="Remember this header and instruction format">
            💾 Save Template
        </button>
        <button type="button" onclick="window.print()" class="btn btn-primary">
            🖨️ Print / Download PDF
        </button>

        <input type="file" id="schoolLogoFileInput" accept="image/*" style="display:none;" onchange="handleSchoolLogoUpload(this)">
        <input type="file" id="questionImageFileInput" accept="image/*" style="display:none;" onchange="handleQuestionImageSelected(this)">
    </div>
</div>

<!-- AI PAPER GENERATOR MODAL -->
<div id="aiPaperModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#fff; width:100%; max-width:680px; max-height:92vh; overflow-y:auto; border-radius:16px; padding:24px; box-shadow:0 25px 60px rgba(0,0,0,0.3); font-family:'Plus Jakarta Sans',sans-serif;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid #eee; padding-bottom:10px;">
            <div>
                <h3 style="margin:0; font-size:18px; font-weight:800; color:#111;">✨ AI Question Paper Generator</h3>
                <p style="margin:3px 0 0; font-size:12px; color:#666;">Generate 10 balanced questions with equations, diagrams, Bloom's cognitive levels, and custom instructions.</p>
            </div>
            <button type="button" onclick="closeAiPaperModal()" style="background:none; border:none; font-size:22px; cursor:pointer; color:#888;">✕</button>
        </div>

        <form id="aiPaperForm" onsubmit="handleAiPaperGeneration(event)">
            <?php if ($isAdmin && !empty($teachersList)): ?>
            <div style="margin-bottom:12px; background:#FFF8EE; border:1px solid #FFE4B5; border-radius:8px; padding:10px 12px;">
                <label style="display:block; font-size:12px; font-weight:700; color:#B45309; margin-bottom:4px;">
                    👑 Admin Mode: Generate on behalf of Teacher
                </label>
                <select id="aiPaperTeacherId" class="form-control" style="width:100%; padding:7px 10px; border-radius:6px; border:1px solid #F59E0B; font-weight:600; font-size:12.5px;">
                    <option value="">Myself (Admin / School Template)</option>
                    <?php foreach ($teachersList as $t): ?>
                        <option value="<?= (int)$t['id'] ?>">
                            <?= htmlspecialchars($t['name'] ?: 'Teacher #' . $t['id']) ?> <?= !empty($t['expert_field']) ? '— ' . htmlspecialchars($t['expert_field']) : '' ?> (<?= htmlspecialchars($t['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Subject *</label>
                    <select id="aiPaperSubject" class="form-control" required style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid #ccc; font-weight:600;">
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= (int)$s['id'] ?>" data-name="<?= htmlspecialchars($s['name']) ?>" <?= stripos($s['name'], 'Math') !== false ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Target Class *</label>
                    <select id="aiPaperClass" class="form-control" required style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid #ccc; font-weight:600;">
                        <option value="CLASS V">Class V</option>
                        <option value="CLASS VI" selected>Class VI</option>
                        <option value="CLASS VII">Class VII</option>
                        <option value="CLASS VIII">Class VIII</option>
                        <option value="CLASS IX">Class IX</option>
                        <option value="CLASS X">Class X</option>
                        <option value="CLASS XI">Class XI</option>
                        <option value="CLASS XII">Class XII</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Topic / Chapter Name *</label>
                <input type="text" id="aiPaperTopic" placeholder="e.g. Quadratic Equations & Triangles, Motion & Force, Algebra" required style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #ccc; font-size:13.5px;" value="Algebra, Equations & Geometry">
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; display:flex; justify-content:space-between;">
                    <span>💡 Teacher Custom Instructions & Premise (Optional)</span>
                    <span style="font-size:11px; font-weight:500; color:#888;">Guides AI pedagogy</span>
                </label>
                <textarea id="aiPaperInstructions" rows="2" placeholder="e.g. Include real-life word problems, avoid fractional exponents, provide 2-step proofs for geometry, focus on board exam high-frequency questions..." style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #ccc; font-size:12.5px; font-family:'Plus Jakarta Sans',sans-serif; resize:vertical;"></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Question Paper Style</label>
                    <select id="aiPaperType" class="form-control" style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid #ccc; font-weight:600; font-size:12.5px;">
                        <option value="mixed_paper" selected>🎯 Balanced Exam (MCQs + Equations + Diagrams)</option>
                        <option value="equation_problem">➗ Solve Equations & Numericals (All 10)</option>
                        <option value="diagram_based">📐 Diagram & Figure-Based (All 10)</option>
                        <option value="mcq">📝 Objective MCQs (All 10)</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Question Quantity</label>
                    <select id="aiPaperCount" class="form-control" style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid #ccc; font-weight:600; font-size:12.5px;">
                        <option value="10" selected>10 Questions (Complete Paper)</option>
                        <option value="8">8 Questions</option>
                        <option value="5">5 Questions</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Cognitive Level (Bloom's)</label>
                    <select id="aiPaperCognitive" class="form-control" style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid #ccc; font-weight:600; font-size:12.5px;">
                        <option value="balanced" selected>🎯 Balanced (Recall + Application + HOTS)</option>
                        <option value="recall">🧠 Remembering & Conceptual Knowledge</option>
                        <option value="application">📐 Numerical Calculation & Problem Solving</option>
                        <option value="analytical">🔬 Analytical & Higher Order Thinking (HOTS)</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Exam Board Pattern</label>
                    <select id="aiPaperPattern" class="form-control" style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid #ccc; font-weight:600; font-size:12.5px;">
                        <option value="cbse" selected>🏛️ CBSE Pattern (Competency Based)</option>
                        <option value="icse">📜 ICSE / ISC Analytical Pattern</option>
                        <option value="state">🏫 State Board Standard</option>
                        <option value="competitive">⚡ JEE / NEET Foundation</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px;">Language & Medium</label>
                    <select id="aiPaperLanguage" class="form-control" style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid #ccc; font-weight:600; font-size:12.5px;">
                        <option value="en" selected>English (Standard)</option>
                        <option value="bilingual">Bilingual (English + Hindi Support)</option>
                    </select>
                </div>
                <div style="display:flex; align-items:center; padding-top:20px;">
                    <label style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; cursor:pointer;">
                        <input type="checkbox" id="aiPaperMarkingScheme" checked style="width:16px; height:16px; accent-color:#6366f1;">
                        <span>Include Step-by-Step Marking Scheme</span>
                    </label>
                </div>
            </div>

            <div id="aiPaperStatus" style="margin-bottom:12px; font-size:12.5px; display:none;"></div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeAiPaperModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" id="btnGenPaperSubmit" class="btn" style="background:linear-gradient(135deg, #6366f1, #8b5cf6); color:#fff; font-weight:700; padding:10px 20px;">
                    🚀 Generate & Populate Paper
                </button>
            </div>
        </form>
    </div>
</div>

<!-- PRINTABLE QUESTION PAPER -->
<div class="paper-sheet" id="paperSheet">
    <!-- SCHOOL HEADER -->
    <div class="school-header">
        <div id="schoolLogoWrap" style="<?= empty($school['crest_logo']) ? 'display:none;' : '' ?>">
            <img id="schoolHeaderLogo" src="<?= Helper::e($school['crest_logo'] ?? '') ?>" class="school-logo" alt="School Crest">
        </div>
        <div class="school-name editable-field" id="schoolNameField"><?= Helper::e($school['name'] ?: 'INFINITY PUBLIC SCHOOL') ?></div>
        <div class="school-address editable-field" id="schoolAddressField"><?= Helper::e($school['address'] ?: 'Academic District, Affiliated to Central Board of Secondary Education') ?></div>
        <div class="exam-title editable-field" id="examTitleField">MID-TERM SUMMATIVE ASSESSMENT · 2026–2027</div>
    </div>

    <!-- META BOX -->
    <div class="meta-box">
        <div class="meta-left">
            <div><span style="font-family:'Plus Jakarta Sans'; font-size:12px; color:#555;">CLASS:</span> <span id="classDisplayField" style="font-size:14px; text-decoration:underline;">CLASS VI</span></div>
            <div><span style="font-family:'Plus Jakarta Sans'; font-size:12px; color:#555;">SUBJECT:</span> <span class="editable-field" id="subjectField">MATHEMATICS</span></div>
        </div>
        <div class="meta-right" style="text-align:right;">
            <div><span style="font-family:'Plus Jakarta Sans'; font-size:12px; color:#555;">TIME ALLOWED:</span> <span class="editable-field" id="timeField">2 HOURS 30 MINS</span></div>
            <div><span style="font-family:'Plus Jakarta Sans'; font-size:12px; color:#555;">MAXIMUM MARKS:</span> <span class="editable-field" id="marksField">80 MARKS</span></div>
        </div>
    </div>

    <!-- GENERAL INSTRUCTIONS -->
    <div class="instructions-box editable-field" id="instructionsField">
        <strong>General Instructions:</strong>
        <ol>
            <li>This question paper contains <strong>Three Sections</strong>: Section A, Section B, and Section C. All questions are compulsory.</li>
            <li><strong>Section A</strong> comprises 6 Multiple Choice Questions carrying <strong>1 mark</strong> each.</li>
            <li><strong>Section B</strong> comprises 4 Short Answer Questions carrying <strong>3 marks</strong> each.</li>
            <li><strong>Section C</strong> comprises 2 Long Answer Questions carrying <strong>5 marks</strong> each with internal choices.</li>
            <li>Use of calculators or electronic devices is strictly prohibited. Rough work must be done on the designated side margin.</li>
        </ol>
    </div>

    <!-- DYNAMIC PAPER SECTIONS CONTAINER -->
    <div id="paperSectionsContainer">
        <!-- SECTION A: MCQs -->
        <div class="section-heading">SECTION A — OBJECTIVE & MULTIPLE CHOICE (1 Mark Each)</div>
        
        <div class="q-item">
            <div class="q-title-row">
                <div><span class="q-num">1.</span> <span class="editable-field">What is the additive inverse of the integer -15?</span></div>
                <div class="q-marks">[1 Mark]</div>
            </div>
            <div class="mcq-grid">
                <div>(a) <span class="editable-field">15</span></div>
                <div>(b) <span class="editable-field">-15</span></div>
                <div>(c) <span class="editable-field">0</span></div>
                <div>(d) <span class="editable-field">1</span></div>
            </div>
            <div class="q-paper-tools">
                <button type="button" class="btn-attach-img" onclick="triggerQuestionImageUpload(this.closest('.q-item'))">📷 Attach / Change Diagram</button>
            </div>
        </div>

        <div class="q-item">
            <div class="q-title-row">
                <div><span class="q-num">2.</span> <span class="editable-field">The sum of all interior angles of a quadrilateral is equal to:</span></div>
                <div class="q-marks">[1 Mark]</div>
            </div>
            <div class="mcq-grid">
                <div>(a) <span class="editable-field">180°</span></div>
                <div>(b) <span class="editable-field">270°</span></div>
                <div>(c) <span class="editable-field">360°</span></div>
                <div>(d) <span class="editable-field">540°</span></div>
            </div>
            <div class="q-paper-tools">
                <button type="button" class="btn-attach-img" onclick="triggerQuestionImageUpload(this.closest('.q-item'))">📷 Attach / Change Diagram</button>
            </div>
        </div>

        <div class="q-item">
            <div class="q-title-row">
                <div><span class="q-num">3.</span> <span class="editable-field">Which of the following numbers is divisible by both 3 and 5?</span></div>
                <div class="q-marks">[1 Mark]</div>
            </div>
            <div class="mcq-grid">
                <div>(a) <span class="editable-field">25</span></div>
                <div>(b) <span class="editable-field">35</span></div>
                <div>(c) <span class="editable-field">45</span></div>
                <div>(d) <span class="editable-field">55</span></div>
            </div>
            <div class="q-paper-tools">
                <button type="button" class="btn-attach-img" onclick="triggerQuestionImageUpload(this.closest('.q-item'))">📷 Attach / Change Diagram</button>
            </div>
        </div>

        <div class="q-item">
            <div class="q-title-row">
                <div><span class="q-num">4.</span> <span class="editable-field">The decimal equivalent of the fraction 7/20 is:</span></div>
                <div class="q-marks">[1 Mark]</div>
            </div>
            <div class="mcq-grid">
                <div>(a) <span class="editable-field">0.35</span></div>
                <div>(b) <span class="editable-field">0.035</span></div>
                <div>(c) <span class="editable-field">0.70</span></div>
                <div>(d) <span class="editable-field">0.14</span></div>
            </div>
            <div class="q-paper-tools">
                <button type="button" class="btn-attach-img" onclick="triggerQuestionImageUpload(this.closest('.q-item'))">📷 Attach / Change Diagram</button>
            </div>
        </div>

        <!-- SECTION B: SHORT ANSWER -->
        <div class="section-heading">SECTION B — SHORT ANSWER & SOLVE THE EQUATIONS (3 Marks Each)</div>

        <div class="q-item">
            <div class="q-title-row">
                <div><span class="q-num">5.</span> <span class="editable-field">Find the Least Common Multiple (LCM) and Highest Common Factor (HCF) of 24 and 36 using prime factorization.</span></div>
                <div class="q-marks">[3 Marks]</div>
            </div>
            <div class="q-paper-tools">
                <button type="button" class="btn-attach-img" onclick="triggerQuestionImageUpload(this.closest('.q-item'))">📷 Attach / Change Diagram</button>
            </div>
        </div>

        <div class="q-item">
            <div class="q-title-row">
                <div><span class="q-num">6.</span> <span class="editable-field">Solve the algebraic equation for x and check your result: 4(2x - 3) + 3(x + 5) - 2x = 29.</span></div>
                <div class="q-marks">[3 Marks]</div>
            </div>
            <div class="q-paper-tools">
                <button type="button" class="btn-attach-img" onclick="triggerQuestionImageUpload(this.closest('.q-item'))">📷 Attach / Change Diagram</button>
            </div>
        </div>

        <div class="q-item">
            <div class="q-title-row">
                <div><span class="q-num">7.</span> <span class="editable-field">A rectangular playground is 45 meters long and 30 meters wide. Calculate the total cost of fencing it at the rate of ₹25 per meter.</span></div>
                <div class="q-marks">[3 Marks]</div>
            </div>
            <div class="q-paper-tools">
                <button type="button" class="btn-attach-img" onclick="triggerQuestionImageUpload(this.closest('.q-item'))">📷 Attach / Change Diagram</button>
            </div>
        </div>

        <div class="q-item">
            <div class="q-title-row">
                <div><span class="q-num">8.</span> <span class="editable-field">Solve the simultaneous system of linear equations: 3x + 2y = 12 and x - y = 1.</span></div>
                <div class="q-marks">[3 Marks]</div>
            </div>
            <div class="q-paper-tools">
                <button type="button" class="btn-attach-img" onclick="triggerQuestionImageUpload(this.closest('.q-item'))">📷 Attach / Change Diagram</button>
            </div>
        </div>

        <!-- SECTION C: LONG ANSWER -->
        <div class="section-heading">SECTION C — DIAGRAM & LONG ANSWER QUESTIONS (5 Marks Each)</div>

        <div class="q-item">
            <div class="q-title-row">
                <div><span class="q-num">9.</span> <span class="editable-field">[Diagram: Right-angled Triangle △ABC, ∠B = 90°, AB = 8 cm, BC = 6 cm. AC is the hypotenuse.]<br><br>In the given figure, △ABC is right-angled at B. Calculate: (a) Length of hypotenuse AC using Pythagoras theorem, (b) Trigonometric ratios sin A and cos C, (c) Area of △ABC.</span></div>
                <div class="q-marks">[5 Marks]</div>
            </div>
            <div class="q-paper-tools">
                <button type="button" class="btn-attach-img" onclick="triggerQuestionImageUpload(this.closest('.q-item'))">📷 Attach / Change Diagram</button>
            </div>
        </div>

        <div class="q-item">
            <div class="q-title-row">
                <div><span class="q-num">10.</span> <span class="editable-field">Construct a frequency distribution table with class intervals for the following marks obtained by 25 students in an assessment: 12, 18, 25, 29, 34, 40, 15, 22, 28, 30, 45, 16, 21, 33, 38, 42, 19, 24, 27, 31, 36, 44, 20, 26, 35. Represent the data with a neat histogram.</span></div>
                <div class="q-marks">[5 Marks]</div>
            </div>
            <div style="text-align:center; font-weight:800; margin:10px 0; font-size:12px;">[ OR ]</div>
            <div class="q-title-row">
                <div><span class="q-num"></span> <span class="editable-field">In a school library, 40% of the books are in English, 35% in Hindi, and the remaining 300 books are in Regional Languages. Find the total number of books in the library and how many are in English.</span></div>
                <div class="q-marks">[5 Marks]</div>
            </div>
            <div class="q-paper-tools">
                <button type="button" class="btn-attach-img" onclick="triggerQuestionImageUpload(this.closest('.q-item'))">📷 Attach / Change Diagram</button>
            </div>
        </div>
    </div>

    <div style="text-align:center; font-size:12px; font-weight:700; margin-top:36px; border-top:1px solid #000; padding-top:12px; letter-spacing:0.1em;">
        *** END OF QUESTION PAPER ***
    </div>
</div>

<script>
// 1-Click Class Switcher
function changeClass(val) {
    document.getElementById('classDisplayField').textContent = val;
}

// Live Inline Edit Mode Toggle
let isEditing = false;
function toggleEditMode() {
    isEditing = !isEditing;
    const fields = document.querySelectorAll('.editable-field');
    const btn = document.getElementById('editToggleBtn');

    fields.forEach(f => {
        f.contentEditable = isEditing ? 'true' : 'false';
        if (isEditing) {
            f.classList.add('editable-active');
        } else {
            f.classList.remove('editable-active');
        }
    });

    if (isEditing) {
        btn.textContent = '✔️ Done Editing';
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-success');
    } else {
        btn.textContent = '✏️ Edit Paper Text';
        btn.classList.remove('btn-success');
        btn.classList.add('btn-secondary');
    }
}

// Save Custom Template to LocalStorage
function saveTemplate() {
    const data = {
        schoolName: document.getElementById('schoolNameField').innerText,
        schoolAddress: document.getElementById('schoolAddressField').innerText,
        examTitle: document.getElementById('examTitleField').innerText,
        instructions: document.getElementById('instructionsField').innerHTML,
        time: document.getElementById('timeField').innerText,
        marks: document.getElementById('marksField').innerText
    };
    localStorage.setItem('il_exam_template', JSON.stringify(data));
    alert('✅ Exam format template saved! It will load automatically for all future papers.');
}

// Auto load template if saved
window.addEventListener('DOMContentLoaded', () => {
    try {
        const saved = localStorage.getItem('il_exam_template');
        if (saved) {
            const data = JSON.parse(saved);
            if (data.schoolName) document.getElementById('schoolNameField').innerText = data.schoolName;
            if (data.schoolAddress) document.getElementById('schoolAddressField').innerText = data.schoolAddress;
            if (data.examTitle) document.getElementById('examTitleField').innerText = data.examTitle;
            if (data.instructions) document.getElementById('instructionsField').innerHTML = data.instructions;
            if (data.time) document.getElementById('timeField').innerText = data.time;
            if (data.marks) document.getElementById('marksField').innerText = data.marks;
        }
    } catch(e) {}
});

// AI Paper Generation Modal Controls
function openAiPaperModal() {
    document.getElementById('aiPaperModal').style.display = 'flex';
    const curClass = document.getElementById('classDisplayField').innerText.trim();
    if (curClass) document.getElementById('aiPaperClass').value = curClass;
}
function closeAiPaperModal() {
    document.getElementById('aiPaperModal').style.display = 'none';
}

// Global target question element for image attachments
let currentQuestionTarget = null;

// School Logo Upload Handler
function handleSchoolLogoUpload(input) {
    const file = input.files[0];
    if (!file) return;

    // Immediate preview using FileReader
    const reader = new FileReader();
    reader.onload = function(e) {
        const logoImg = document.getElementById('schoolHeaderLogo');
        if (logoImg) logoImg.src = e.target.result;
        const logoWrap = document.getElementById('schoolLogoWrap');
        if (logoWrap) logoWrap.style.display = 'block';
    };
    reader.readAsDataURL(file);

    // Asynchronously save to server
    const formData = new FormData();
    formData.append('image', file);
    fetch('/teacher/extract-question-from-image.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res && res.image_url) {
                const logoImg = document.getElementById('schoolHeaderLogo');
                if (logoImg) logoImg.src = res.image_url;
            }
        })
        .catch(err => console.warn('School logo stored locally', err));
}

// Trigger image upload for an individual question item
function triggerQuestionImageUpload(targetElem) {
    currentQuestionTarget = targetElem;
    const fileInput = document.getElementById('questionImageFileInput');
    if (fileInput) {
        fileInput.value = '';
        fileInput.click();
    }
}

// Handle question image file selection
function handleQuestionImageSelected(input) {
    const file = input.files[0];
    if (!file || !currentQuestionTarget) return;

    // Immediate preview
    const reader = new FileReader();
    reader.onload = function(e) {
        renderQuestionImage(currentQuestionTarget, e.target.result);
    };
    reader.readAsDataURL(file);

    // Upload to server and store permanently
    const formData = new FormData();
    formData.append('image', file);
    fetch('/teacher/extract-question-from-image.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res && res.image_url) {
                renderQuestionImage(currentQuestionTarget, res.image_url);
            }
        })
        .catch(err => console.warn('Question image preview loaded', err));
}

function renderQuestionImage(targetElem, src) {
    if (!targetElem) return;
    let wrap = targetElem.querySelector('.paper-q-img-wrap');
    if (!wrap) {
        wrap = document.createElement('div');
        wrap.className = 'paper-q-img-wrap';
        const titleRow = targetElem.querySelector('.q-title-row');
        if (titleRow && titleRow.nextSibling) {
            targetElem.insertBefore(wrap, titleRow.nextSibling);
        } else {
            targetElem.appendChild(wrap);
        }
    }
    wrap.innerHTML = `
        <div class="paper-q-img-container">
            <img src="${escapeHtml(src)}" class="paper-q-img" alt="Question Diagram">
            <button type="button" class="remove-q-img-btn" onclick="this.closest('.paper-q-img-wrap').remove()" title="Remove Diagram">✕</button>
        </div>
    `;
}

async function handleAiPaperGeneration(e) {
    e.preventDefault();
    const btn = document.getElementById('btnGenPaperSubmit');
    const statusDiv = document.getElementById('aiPaperStatus');

    const subjectSelect = document.getElementById('aiPaperSubject');
    const subjectId = subjectSelect.value;
    const subjectName = subjectSelect.options[subjectSelect.selectedIndex].getAttribute('data-name') || 'MATHEMATICS';
    const targetClass = document.getElementById('aiPaperClass').value;
    const topic = document.getElementById('aiPaperTopic').value;
    const style = document.getElementById('aiPaperType').value;
    const count = parseInt(document.getElementById('aiPaperCount').value, 10) || 10;

    const teacherSelect = document.getElementById('aiPaperTeacherId');
    const teacherId = teacherSelect ? teacherSelect.value : '';
    const instructions = document.getElementById('aiPaperInstructions') ? document.getElementById('aiPaperInstructions').value : '';
    const cognitive = document.getElementById('aiPaperCognitive') ? document.getElementById('aiPaperCognitive').value : 'balanced';
    const pattern = document.getElementById('aiPaperPattern') ? document.getElementById('aiPaperPattern').value : 'cbse';
    const lang = document.getElementById('aiPaperLanguage') ? document.getElementById('aiPaperLanguage').value : 'en';
    const marking = document.getElementById('aiPaperMarkingScheme') && document.getElementById('aiPaperMarkingScheme').checked ? 1 : 0;

    btn.disabled = true;
    btn.innerHTML = '⏳ Generating ' + count + ' Questions with Equations & Diagrams...';
    statusDiv.style.display = 'block';
    statusDiv.innerHTML = '<div style="color:#6366f1; font-weight:600;">Consulting pedagogical standards, custom instructions & crafting equations/diagrams...</div>';

    try {
        const resp = await fetch('/teacher/ai-generate-questions-handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                subject_id: subjectId,
                class_level: targetClass,
                topic: topic,
                question_type: style,
                count: count,
                difficulty: 'medium',
                custom_instructions: instructions,
                cognitive_level: cognitive,
                exam_pattern: pattern,
                language_tone: lang,
                include_marking_scheme: marking,
                on_behalf_teacher_id: teacherId,
                auto_save: 0
            })
        });

        const data = await resp.json();
        if (!data.success) {
            throw new Error(data.error || 'Failed to generate paper questions');
        }

        const questions = data.questions || [];
        if (questions.length === 0) {
            throw new Error('No questions received.');
        }

        // 1. Update Header & Meta Info
        document.getElementById('subjectField').innerText = subjectName.toUpperCase();
        document.getElementById('classDisplayField').innerText = targetClass;
        document.getElementById('classSwitcher').value = targetClass;

        // 2. Separate into Sections:
        // Section A: MCQs
        // Section B: Equation Problems & Short Answers
        // Section C: Diagrams & Long Descriptive Questions
        const secA = [];
        const secB = [];
        const secC = [];

        questions.forEach(q => {
            const t = q.question_type;
            const text = q.question_text || '';
            if (t === 'mcq' && q.options && q.options.length) {
                secA.push(q);
            } else if (t === 'diagram_based' || text.includes('[Diagram:') || q.marks >= 4.0) {
                secC.push(q);
            } else {
                secB.push(q);
            }
        });

        // Ensure at least some questions in each section if mixed paper
        if (style === 'mixed_paper') {
            if (secA.length === 0 && questions.length >= 3) {
                secA.push(...questions.splice(0, 3));
            }
            if (secC.length === 0 && secB.length >= 2) {
                secC.push(secB.pop());
            }
        }

        function renderQuestionImgHtml(imgUrl) {
            if (!imgUrl) return '';
            return `
            <div class="paper-q-img-wrap">
                <div class="paper-q-img-container">
                    <img src="${escapeHtml(imgUrl)}" class="paper-q-img" alt="Question Diagram">
                    <button type="button" class="remove-q-img-btn" onclick="this.closest('.paper-q-img-wrap').remove()" title="Remove Diagram">✕</button>
                </div>
            </div>`;
        }

        // 3. Render HTML
        let qNum = 1;
        let html = '';

        // SECTION A
        if (secA.length > 0) {
            html += `<div class="section-heading">SECTION A — OBJECTIVE & MULTIPLE CHOICE (${secA[0].marks || 1} Mark Each)</div>`;
            secA.forEach(q => {
                let optsHtml = '';
                if (q.options && q.options.length) {
                    optsHtml = `<div class="mcq-grid">${q.options.map((opt, oi) => {
                        const letter = String.fromCharCode(97 + oi);
                        const optClean = opt.replace(/^\([a-d]\)\s*/i, '');
                        return `<div>(${letter}) <span class="editable-field">${escapeHtml(optClean)}</span></div>`;
                    }).join('')}</div>`;
                }
                html += `
                <div class="q-item">
                    <div class="q-title-row">
                        <div><span class="q-num">${qNum}.</span> <span class="editable-field">${formatQuestionText(q.question_text)}</span></div>
                        <div class="q-marks">[${q.marks || 1} Mark]</div>
                    </div>
                    ${renderQuestionImgHtml(q.image_url)}
                    ${optsHtml}
                    <div class="q-paper-tools">
                        <button type="button" class="btn-attach-img" onclick="triggerQuestionImageUpload(this.closest('.q-item'))">📷 Attach / Change Diagram</button>
                    </div>
                </div>`;
                qNum++;
            });
        }

        // SECTION B
        if (secB.length > 0) {
            html += `<div class="section-heading">SECTION B — SHORT ANSWER & SOLVE THE EQUATIONS (${secB[0].marks || 3} Marks Each)</div>`;
            secB.forEach(q => {
                html += `
                <div class="q-item">
                    <div class="q-title-row">
                        <div><span class="q-num">${qNum}.</span> <span class="editable-field">${formatQuestionText(q.question_text)}</span></div>
                        <div class="q-marks">[${q.marks || 3} Marks]</div>
                    </div>
                    ${renderQuestionImgHtml(q.image_url)}
                    <div class="q-paper-tools">
                        <button type="button" class="btn-attach-img" onclick="triggerQuestionImageUpload(this.closest('.q-item'))">📷 Attach / Change Diagram</button>
                    </div>
                </div>`;
                qNum++;
            });
        }

        // SECTION C
        if (secC.length > 0) {
            html += `<div class="section-heading">SECTION C — DIAGRAM & LONG ANSWER QUESTIONS (${secC[0].marks || 5} Marks Each)</div>`;
            secC.forEach((q, cIdx) => {
                html += `
                <div class="q-item">
                    <div class="q-title-row">
                        <div><span class="q-num">${qNum}.</span> <span class="editable-field">${formatQuestionText(q.question_text)}</span></div>
                        <div class="q-marks">[${q.marks || 5} Marks]</div>
                    </div>
                    ${renderQuestionImgHtml(q.image_url)}
                    <div class="q-paper-tools">
                        <button type="button" class="btn-attach-img" onclick="triggerQuestionImageUpload(this.closest('.q-item'))">📷 Attach / Change Diagram</button>
                    </div>
                </div>`;
                qNum++;
            });
        }

        // Update container
        document.getElementById('paperSectionsContainer').innerHTML = html;

        // If edit mode was already turned on, activate editable on new items
        if (isEditing) {
            document.querySelectorAll('#paperSectionsContainer .editable-field').forEach(f => {
                f.contentEditable = 'true';
                f.classList.add('editable-active');
            });
        }

        // Update General Instructions Box
        const totalMarksCalc = secA.length * 1 + secB.length * 3 + secC.length * 5;
        document.getElementById('marksField').innerText = totalMarksCalc + ' MARKS';
        document.getElementById('instructionsField').innerHTML = `
            <strong>General Instructions:</strong>
            <ol>
                <li>This question paper contains <strong>Three Sections</strong>: Section A, Section B, and Section C with a total of <strong>${questions.length} Questions</strong>. All questions are compulsory.</li>
                <li><strong>Section A</strong> comprises ${secA.length} Multiple Choice Questions carrying <strong>1 mark</strong> each.</li>
                <li><strong>Section B</strong> comprises ${secB.length} Equation Solving & Numerical Questions carrying <strong>3 marks</strong> each.</li>
                <li><strong>Section C</strong> comprises ${secC.length} Diagram & Long Answer Questions carrying <strong>5 marks</strong> each.</li>
                <li>Use of calculators or electronic devices is strictly prohibited. Rough work must be done on the designated side margin.</li>
            </ol>
        `;

        closeAiPaperModal();
        alert('🎉 Examination Paper successfully generated with ' + questions.length + ' questions! You can attach diagrams to any question, edit text inline, or print.');

    } catch (err) {
        statusDiv.innerHTML = `<div style="color:#ef4444; font-weight:600;">Error: ${err.message}</div>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '🚀 Generate & Populate Paper';
    }
}

function formatQuestionText(text) {
    if (!text) return '';
    let escaped = escapeHtml(text);
    // Render [Diagram: ...] as a distinct diagram box on paper
    escaped = escaped.replace(/\[Diagram:\s*([\s\S]*?)\]/gi, function(match, diagContent) {
        return `<div style="border:1.5px dashed #111; background:#FAFAFA; border-radius:6px; padding:10px 14px; margin:8px 0; font-family:'JetBrains Mono', monospace; font-size:12.5px;">
            <strong style="text-transform:uppercase; letter-spacing:0.05em; display:flex; align-items:center; gap:6px;">
                <span>📐 DIAGRAM / FIGURE REFERENCE:</span>
            </strong>
            <div style="margin-top:4px; font-weight:normal; white-space:pre-wrap;">${diagContent.trim()}</div>
        </div>`;
    });
    return escaped.replace(/\n/g, '<br>');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

</body>
</html>
