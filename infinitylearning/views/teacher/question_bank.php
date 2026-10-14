<?php
use App\Auth;
use App\Helper;
use App\QuestionBank;
use App\Database;

Auth::requireRole('teacher', 'admin');
$user = Auth::user();
$isAdmin = in_array($user['role'] ?? '', ['admin'], true);
$teacherId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

$db = Database::connect();
$subjects = $db->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();

$teachersList = [];
if ($isAdmin) {
    if ($schoolId) {
        $stmtT = $db->prepare("SELECT u.id, u.email, t.full_name AS name, t.expert_field FROM users u LEFT JOIN teachers t ON t.user_id = u.id WHERE u.school_id = ? AND u.role = 'teacher' ORDER BY t.full_name ASC");
        $stmtT->execute([$schoolId]);
        $teachersList = $stmtT->fetchAll();
    } else {
        $teachersList = $db->query("SELECT u.id, u.email, t.full_name AS name, t.expert_field FROM users u LEFT JOIN teachers t ON t.user_id = u.id WHERE u.role = 'teacher' ORDER BY t.full_name ASC")->fetchAll();
    }
}

$selectedTeacherId = !empty($_GET['teacher_id']) ? (int)$_GET['teacher_id'] : 0;
$selectedSubjectId = !empty($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$selectedDifficulty = $_GET['difficulty'] ?? '';
$selectedType = $_GET['type'] ?? '';
$search = trim($_GET['search'] ?? '');

$filters = [
    'subject_id' => $selectedSubjectId ?: null,
    'difficulty' => $selectedDifficulty ?: null,
    'question_type' => $selectedType ?: null,
    'search' => $search ?: null,
];

if ($isAdmin) {
    if ($selectedTeacherId > 0) {
        $filters['exact_teacher_id'] = $selectedTeacherId;
    } elseif ($schoolId) {
        $filters['school_id'] = $schoolId;
    }
} else {
    $filters['teacher_user_id'] = $teacherId;
    $filters['school_id'] = $schoolId;
}

$questions = QuestionBank::list($filters);

$pageTitle = 'Question Bank — Academic Vault';
require VIEW_PATH . '/layouts/header.php';
?>
<style>
/* High-Contrast Light Mode Button & Vault Styling */
html[data-theme="light"] .btn-secondary,
:root:not([data-theme="dark"]) .btn-secondary {
    background: #F3EFEA !important;
    color: #2B1B12 !important;
    border: 1.5px solid #D5C7B8 !important;
    font-weight: 700 !important;
    box-shadow: 0 1px 3px rgba(43,27,18,0.06);
}
html[data-theme="light"] .btn-secondary:hover,
:root:not([data-theme="dark"]) .btn-secondary:hover {
    background: #E8E0D5 !important;
    color: #111 !important;
    border-color: #B8A89A !important;
}

html[data-theme="light"] .btn-outline-primary,
:root:not([data-theme="dark"]) .btn-outline-primary {
    background: #FFF7F0 !important;
    color: #C2410C !important;
    border: 1.5px solid #FF7A18 !important;
    font-weight: 700 !important;
    box-shadow: 0 1px 3px rgba(255,122,24,0.08);
}
html[data-theme="light"] .btn-outline-primary:hover,
:root:not([data-theme="dark"]) .btn-outline-primary:hover {
    background: #FF7A18 !important;
    color: #fff !important;
    border-color: #FF7A18 !important;
}

html[data-theme="light"] .btn-outline-secondary,
:root:not([data-theme="dark"]) .btn-outline-secondary {
    background: #FFFFFF !important;
    color: #2B1B12 !important;
    border: 1.5px solid #D5C7B8 !important;
    font-weight: 700 !important;
    box-shadow: 0 1px 2px rgba(43,27,18,0.05);
}
html[data-theme="light"] .btn-outline-secondary:hover,
:root:not([data-theme="dark"]) .btn-outline-secondary:hover {
    background: #F3EFEA !important;
    color: #111 !important;
    border-color: #A89484 !important;
}

html[data-theme="light"] .kicker,
:root:not([data-theme="dark"]) .kicker {
    background: #FEF3C7 !important;
    color: #92400E !important;
    border: 1px solid #FDE68A !important;
    font-weight: 800 !important;
}

.question-row-item {
    transition: background 0.15s ease;
}
.question-row-item:hover {
    background: var(--surface-2);
}
</style>
<div class="admin-shell">
    <?php if ($isAdmin): ?>
        <?php 
        $adminSidebar = $schoolId ? VIEW_PATH . '/school/_sidebar.php' : VIEW_PATH . '/admin/_sidebar.php';
        if (file_exists($adminSidebar)) {
            require $adminSidebar;
        } else {
            require VIEW_PATH . '/teacher/_sidebar.php';
        }
        ?>
    <?php else: ?>
        <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <?php endif; ?>

    <div>
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker">📚 ACADEMIC VAULT</span>
                <h1 style="margin:2px 0 0;">
                    <?= $isAdmin ? 'School & Teacher Question Bank' : 'Teacher Question Bank' ?>
                </h1>
                <p class="muted">
                    <?= $isAdmin 
                        ? 'Administer school curriculum, upload questions on behalf of teachers, and build standardized exams.' 
                        : 'Build subject-wise question banks, attach diagrams, and generate comprehensive exam papers with AI.' ?>
                </p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <button type="button" class="btn" onclick="openAiQuestionModal()" style="background:linear-gradient(135deg, #6366f1, #8b5cf6); color:#fff; border:none; font-weight:700; box-shadow:0 4px 12px rgba(99,102,241,0.3);">✨ AI Question Generator</button>
                <button type="button" class="btn btn-primary" onclick="openNewQuestionModal()">+ Add New Question</button>
                <a href="/teacher/question-paper" class="btn btn-secondary">📄 Question Paper Builder</a>
                <button type="button" class="btn btn-outline-secondary" onclick="cleanDuplicateQuestions()" title="Remove duplicate questions if any exist">🧹 Clean Duplicates</button>
                <a href="/teacher/assessments" class="btn btn-outline-primary">Create Exam / Homework →</a>
            </div>
        </div>

        <?php if ($msg = \App\Session::flash('success')): ?>
            <div class="alert alert-success"><?= Helper::e($msg) ?></div>
        <?php endif; ?>
        <?php if ($err = \App\Session::flash('error')): ?>
            <div class="alert alert-danger"><?= Helper::e($err) ?></div>
        <?php endif; ?>

        <!-- FILTER BAR -->
        <div class="card" style="padding:16px 20px; margin-bottom:20px;">
            <form method="get" action="/teacher/questions" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <div style="flex:1; min-width:200px;">
                    <input type="text" name="search" class="form-control" placeholder="Search questions, equations, or explanations..." value="<?= Helper::e($search) ?>">
                </div>

                <?php if ($isAdmin && !empty($teachersList)): ?>
                    <div style="width:200px;">
                        <select name="teacher_id" class="form-control">
                            <option value="">All Teachers (School)</option>
                            <?php foreach ($teachersList as $t): ?>
                                <option value="<?= (int)$t['id'] ?>" <?= $selectedTeacherId === (int)$t['id'] ? 'selected' : '' ?>>
                                    👤 <?= Helper::e($t['name'] ?: $t['email']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div style="width:170px;">
                    <select name="subject_id" class="form-control">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= (int)$s['id'] ?>" <?= $selectedSubjectId === (int)$s['id'] ? 'selected' : '' ?>>
                                <?= Helper::e($s['icon'] ?? '📚') ?> <?= Helper::e($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="width:130px;">
                    <select name="difficulty" class="form-control">
                        <option value="">All Difficulties</option>
                        <option value="easy" <?= $selectedDifficulty === 'easy' ? 'selected' : '' ?>>Easy</option>
                        <option value="medium" <?= $selectedDifficulty === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="hard" <?= $selectedDifficulty === 'hard' ? 'selected' : '' ?>>Hard</option>
                    </select>
                </div>
                <div style="width:140px;">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="mcq" <?= $selectedType === 'mcq' ? 'selected' : '' ?>>MCQ</option>
                        <option value="short_answer" <?= $selectedType === 'short_answer' ? 'selected' : '' ?>>Short Answer</option>
                        <option value="descriptive" <?= $selectedType === 'descriptive' ? 'selected' : '' ?>>Descriptive</option>
                        <option value="numerical" <?= $selectedType === 'numerical' ? 'selected' : '' ?>>Numerical / Eq</option>
                        <option value="true_false" <?= $selectedType === 'true_false' ? 'selected' : '' ?>>True / False</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="padding:8px 22px; font-weight:700;">Filter</button>
                <?php if ($selectedSubjectId || $selectedDifficulty || $selectedType || $selectedTeacherId || $search): ?>
                    <a href="/teacher/questions" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- QUESTIONS LIST -->
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="padding:16px 20px; border-bottom:1px solid var(--il-border); background:var(--surface-0); display:flex; justify-content:space-between; align-items:center;">
                <strong>Questions Vault (<?= count($questions) ?>)</strong>
                <span class="muted" style="font-size:12px;">Stored in curriculum hierarchy with images & formulas</span>
            </div>

            <?php if (empty($questions)): ?>
                <div style="padding:48px 24px; text-align:center; color:var(--il-muted);">
                    <div style="font-size:36px; margin-bottom:8px;">❓</div>
                    <h4>No questions found in this view</h4>
                    <p style="margin:4px 0 16px;">Create your first question or adjust your filters above.</p>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openNewQuestionModal()">+ Create Question Now</button>
                </div>
            <?php else: ?>
                <div style="padding:8px 0;">
                    <?php foreach ($questions as $q): ?>
                        <div class="question-row-item" style="padding:16px 20px; border-bottom:1px solid var(--il-border); display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
                            <div style="flex:1;">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap;">
                                    <span class="badge" style="background:#F1F5F9; color:#475569; font-weight:700;">
                                        <?= Helper::e($q['subject_name'] ?? 'General') ?>
                                    </span>
                                    <?php if (!empty($q['class_level'])): ?>
                                        <span class="badge" style="background:#F3E8FF; color:#7E22CE; font-weight:700;">
                                            <?= Helper::e($q['class_level']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($q['chapter_name'])): ?>
                                        <span class="badge" style="background:#EFF6FF; color:#1E40AF;">
                                            Ch: <?= Helper::e($q['chapter_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge badge-<?= $q['difficulty'] === 'easy' ? 'success' : ($q['difficulty'] === 'hard' ? 'danger' : 'primary') ?>">
                                        <?= ucfirst(Helper::e($q['difficulty'])) ?>
                                    </span>
                                    <span class="badge" style="background:#FEF3C7; color:#92400E; font-weight:700;">
                                        <?= (float)$q['marks'] ?> Mark<?= (float)$q['marks'] > 1 ? 's' : '' ?>
                                    </span>
                                    <span class="muted" style="font-size:11px; text-transform:uppercase;">
                                        Type: <?= Helper::e($q['question_type']) ?>
                                    </span>
                                    <?php if ($isAdmin && !empty($q['teacher_name'])): ?>
                                        <span class="badge" style="background:#EDE9FE; color:#6D28D9; font-weight:700;">
                                            👤 <?= Helper::e($q['teacher_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- QUESTION TEXT WITH DIAGRAM HIGHLIGHTING -->
                                <div class="question-text-content" style="font-size:15px; font-weight:600; color:var(--il-text); margin-bottom:8px; line-height:1.5;">
                                    <?php 
                                    $rawText = $q['question_text'];
                                    // Highlight [Diagram: ...] block
                                    $formattedText = preg_replace_callback('/\[Diagram:\s*([\s\S]*?)\]/i', function($m) {
                                        return '<div class="diagram-ref-box" style="background:var(--surface-2); border:1.5px dashed #6366f1; border-radius:10px; padding:12px 16px; margin:10px 0; font-family:\'JetBrains Mono\', monospace; font-size:12.5px; color:var(--il-text);">'
                                             . '<div style="font-weight:700; color:#6366f1; margin-bottom:4px; display:flex; align-items:center; gap:6px;"><span>📐 DIAGRAM / FIGURE REFERENCE:</span></div>'
                                             . '<div>' . nl2br(Helper::e(trim($m[1]))) . '</div>'
                                             . '</div>';
                                    }, Helper::e($rawText));
                                    echo nl2br($formattedText);
                                    ?>
                                </div>

                                <!-- ATTACHED IMAGE (IF UPLOADED) -->
                                <?php if (!empty($q['image_url'])): ?>
                                    <div style="margin:10px 0;">
                                        <a href="<?= Helper::e($q['image_url']) ?>" target="_blank" title="Click to view full image">
                                            <img src="<?= Helper::e($q['image_url']) ?>" alt="Question Diagram" style="max-height:220px; max-width:100%; border-radius:10px; border:1px solid var(--il-border); background:#fff; padding:4px; box-shadow:0 3px 10px rgba(0,0,0,0.06); object-fit:contain;">
                                        </a>
                                        <div style="font-size:11px; color:var(--il-muted); margin-top:2px;">📷 Attached Diagram / Figure</div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($q['options_json'])): ?>
                                    <?php $opts = json_decode($q['options_json'], true) ?: []; ?>
                                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:6px; margin-bottom:8px;">
                                        <?php foreach ($opts as $oi => $opt): ?>
                                            <div style="background:var(--surface-0); padding:6px 10px; border-radius:6px; font-size:13px; border:1px solid var(--il-border); <?= strcasecmp((string)$opt, (string)$q['correct_answer']) === 0 ? 'border-color:#16A34A; background:#DCFCE7; font-weight:600;' : '' ?>">
                                                <strong style="color:var(--il-muted);"><?= chr(65 + $oi) ?>.</strong> <?= Helper::e($opt) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($q['correct_answer'])): ?>
                                    <div style="font-size:13px; margin-bottom:4px; color:var(--il-text);">
                                        <strong style="color:var(--success);">✓ Correct Answer:</strong> <?= Helper::e($q['correct_answer']) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($q['explanation'])): ?>
                                    <div style="font-size:12px; color:var(--il-muted); background:var(--surface-1); padding:8px 12px; border-radius:8px; border:1px solid var(--il-border); margin-top:6px;">
                                        <strong>💡 Explanation / Working:</strong> <?= Helper::e($q['explanation']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <form method="post" action="/teacher/question-save-handler.php" onsubmit="return deleteQuestionAjax(event, this);">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
                                <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                <button type="submit" class="btn btn-ghost btn-xs" style="color:#DC2626;" title="Delete">🗑️</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL TO ADD NEW QUESTION (WITH IMAGE UPLOAD & AI OCR EXTRACTOR) -->
<div id="newQuestionModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(6px);">
    <div class="card" style="background:var(--surface-1); color:var(--il-text); border:1px solid var(--il-border); border-radius:18px; max-width:740px; width:100%; max-height:92vh; overflow-y:auto; padding:26px 30px; box-shadow:0 24px 60px rgba(0,0,0,0.4);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid var(--il-border); padding-bottom:14px;">
            <div>
                <h3 style="margin:0; font-size:20px; color:var(--il-text);">➕ Add Question to Bank</h3>
                <p class="muted" style="margin:2px 0 0; font-size:13px;">Upload diagrams, attach photos, or type equations for curriculum bank.</p>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeNewQuestionModal()" style="font-size:20px; line-height:1;">&times;</button>
        </div>

        <form method="post" action="/teacher/question-save-handler.php" enctype="multipart/form-data" id="manualQuestionForm">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
            <input type="hidden" name="image_url" id="manualImageUrl">

            <?php if ($isAdmin && !empty($teachersList)): ?>
                <div style="margin-bottom:14px; background:var(--surface-2); padding:10px 14px; border-radius:10px; border:1px solid var(--il-border);">
                    <label class="form-label" style="font-weight:700; font-size:12.5px; display:block; margin-bottom:4px; color:var(--il-text);">
                        👤 Upload on Behalf of Teacher:
                    </label>
                    <select name="on_behalf_teacher_id" class="form-control" style="width:100%; font-size:13px;">
                        <option value="">-- Myself (Admin) / General School Pool --</option>
                        <?php foreach ($teachersList as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"><?= Helper::e($t['name'] ?: $t['email']) ?><?= !empty($t['expert_field']) ? ' (' . Helper::e($t['expert_field']) . ')' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <!-- IMAGE / DIAGRAM UPLOADER & AI SCANNER -->
            <div style="margin-bottom:18px; background:var(--surface-2); border:1.5px dashed var(--il-border); border-radius:12px; padding:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; flex-wrap:wrap; gap:8px;">
                    <label style="font-weight:700; font-size:13px; color:var(--il-text); display:flex; align-items:center; gap:6px;">
                        <span>📷 Upload Question Image / Textbook Diagram</span>
                    </label>
                    <button type="button" id="btnScanAi" onclick="scanQuestionImageWithAi()" class="btn btn-xs" style="background:linear-gradient(135deg, #6366f1, #8b5cf6); color:#fff; border:none; font-weight:700; padding:6px 14px; border-radius:6px; display:none;">
                        ✨ Scan & Autofill Form with AI
                    </button>
                </div>
                <input type="file" id="questionImageInput" name="question_image" accept="image/*" class="form-control" onchange="handleImageSelected(this)" style="width:100%; font-size:12.5px;">
                <div style="font-size:11.5px; color:var(--il-muted); margin-top:4px;">Upload textbook photos, geometry figures, circuit diagrams, or handwritten problems (PNG, JPG, WebP).</div>
                
                <div id="imagePreviewContainer" style="display:none; margin-top:12px; text-align:center; position:relative; background:#fff; border-radius:10px; padding:10px; border:1px solid var(--il-border);">
                    <img id="imagePreviewImg" src="" style="max-height:180px; max-width:100%; border-radius:6px; object-fit:contain;">
                    <button type="button" onclick="removeImagePreview()" style="position:absolute; top:6px; right:8px; background:#ef4444; color:#fff; border:none; border-radius:50%; width:24px; height:24px; font-weight:700; cursor:pointer;" title="Remove image">&times;</button>
                    <div id="scanStatusMsg" style="font-size:12px; margin-top:6px; font-weight:600;"></div>
                </div>
            </div>

            <div class="grid grid-2" style="margin-bottom:14px; gap:14px;">
                <div>
                    <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Subject *</label>
                    <select name="subject_id" class="form-control" required id="modalSubjectSelect" style="width:100%;">
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= (int)$s['id'] ?>"><?= Helper::e($s['icon'] ?? '📚') ?> <?= Helper::e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Target Class / Grade</label>
                    <input type="text" name="class_level" id="modalClassLevel" class="form-control" placeholder="e.g. Class X, Grade 8" style="width:100%;">
                </div>
            </div>

            <div class="grid grid-3" style="margin-bottom:14px; gap:12px;">
                <div>
                    <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Question Type</label>
                    <select name="question_type" class="form-control" id="modalQuestionType" onchange="toggleOptionFields(this.value)" style="width:100%;">
                        <option value="mcq">MCQ (Multiple Choice)</option>
                        <option value="true_false">True / False</option>
                        <option value="short_answer">Short Answer</option>
                        <option value="descriptive">Descriptive / Diagram</option>
                        <option value="numerical">Numerical / Equation</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Difficulty</label>
                    <select name="difficulty" id="modalDifficulty" class="form-control" style="width:100%;">
                        <option value="easy">Easy</option>
                        <option value="medium" selected>Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Marks</label>
                    <input type="number" step="0.5" name="marks" id="modalMarks" class="form-control" value="1.0" min="0.5" style="width:100%;">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Question Text *</label>
                <textarea name="question_text" id="modalQuestionText" class="form-control" rows="3" required placeholder="Type the question clearly. For diagrams, you can write [Diagram: description] or attach an image above..." style="width:100%; min-height:80px;"></textarea>
            </div>

            <!-- MCQ OPTIONS CONTAINER -->
            <div id="mcqOptionsGroup" style="margin-bottom:16px; background:var(--surface-2); padding:16px; border-radius:12px; border:1px solid var(--il-border);">
                <label class="form-label" style="font-weight:700; font-size:13px; margin-bottom:8px; display:block; color:var(--il-text);">
                    Options for Multiple Choice (A, B, C, D)
                </label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:10px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span class="badge badge-primary" style="font-size:11px; padding:4px 8px;">A</span>
                        <input type="text" name="options[]" id="optA" class="form-control" placeholder="Option A" style="flex:1;">
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span class="badge badge-primary" style="font-size:11px; padding:4px 8px;">B</span>
                        <input type="text" name="options[]" id="optB" class="form-control" placeholder="Option B" style="flex:1;">
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span class="badge badge-primary" style="font-size:11px; padding:4px 8px;">C</span>
                        <input type="text" name="options[]" id="optC" class="form-control" placeholder="Option C" style="flex:1;">
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span class="badge badge-primary" style="font-size:11px; padding:4px 8px;">D</span>
                        <input type="text" name="options[]" id="optD" class="form-control" placeholder="Option D" style="flex:1;">
                    </div>
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Correct Answer *</label>
                <input type="text" name="correct_answer" id="modalCorrectAnswer" class="form-control" placeholder="Correct answer text or option key" required style="width:100%;">
            </div>

            <div style="margin-bottom:20px;">
                <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Explanation / Solution Steps</label>
                <textarea name="explanation" id="modalExplanation" class="form-control" rows="2" placeholder="Explain the step-by-step solution..." style="width:100%; min-height:60px;"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="closeNewQuestionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:10px 22px; font-weight:700;">Save to Question Bank</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TO GENERATE QUESTIONS WITH AI (ENHANCED PREMISE & COGNITIVE CONTROLS) -->
<div id="aiQuestionModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:16px; backdrop-filter:blur(6px);">
    <div class="card" style="width:100%; max-width:780px; max-height:92vh; overflow-y:auto; padding:26px 30px; border-radius:18px; box-shadow:0 24px 60px rgba(0,0,0,0.4); border:1px solid var(--il-border);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid var(--il-border); padding-bottom:12px;">
            <div>
                <h3 style="margin:0; font-size:19px; display:flex; align-items:center; gap:8px;">
                    <span style="font-size:22px;">✨</span> AI Question & Exam Generator
                </h3>
                <p class="muted" style="font-size:12.5px; margin:2px 0 0;">Provide detailed premises, board instructions, equations, and diagrams.</p>
            </div>
            <button type="button" style="background:none; border:none; font-size:24px; cursor:pointer; color:var(--il-muted);" onclick="closeAiQuestionModal()">&times;</button>
        </div>

        <form id="aiGenForm" onsubmit="handleAiGeneration(event)">
            <?php if ($isAdmin && !empty($teachersList)): ?>
                <div style="margin-bottom:14px; background:var(--surface-2); padding:10px 14px; border-radius:10px; border:1px solid var(--il-border);">
                    <label class="form-label" style="font-weight:700; font-size:12.5px; display:block; margin-bottom:4px; color:var(--il-text);">
                        👤 Save on Behalf of Teacher:
                    </label>
                    <select id="aiOnBehalfTeacher" class="form-control" style="width:100%; font-size:12.5px;">
                        <option value="">-- Myself (Admin) / School Pool --</option>
                        <?php foreach ($teachersList as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"><?= Helper::e($t['name'] ?: $t['email']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
                <div>
                    <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Subject *</label>
                    <select id="aiSubject" class="form-control" required style="width:100%;">
                        <option value="">-- Choose Subject --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= (int)$s['id'] ?>" <?= $selectedSubjectId === (int)$s['id'] ? 'selected' : '' ?>>
                                <?= Helper::e($s['icon'] ?? '📚') ?> <?= Helper::e($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Target Class / Grade *</label>
                    <select id="aiClassLevel" class="form-control" required style="width:100%;">
                        <?php 
                        $classes = ['Class I', 'Class II', 'Class III', 'Class IV', 'Class V', 'Class VI', 'Class VII', 'Class VIII', 'Class IX', 'Class X', 'Class XI', 'Class XII'];
                        foreach ($classes as $c): ?>
                            <option value="<?= $c ?>" <?= $c === 'Class X' ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Chapter / Topic / Specific Concept *</label>
                <input type="text" id="aiTopic" class="form-control" placeholder="e.g. Plant Biology, Quadratic Equations, Newton's Third Law, Photosynthesis" required style="width:100%;">
            </div>

            <!-- TEACHER'S CUSTOM INSTRUCTIONS & PREMISE -->
            <div style="margin-bottom:14px;">
                <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">
                    ✍️ Teacher's Custom Instructions & Premise (Optional)
                </label>
                <textarea id="aiCustomInstructions" class="form-control" rows="2" placeholder="e.g. Focus strictly on NCERT Chapter 6, include step-by-step proofs for theorems, include real-world case study of electric vehicles, ensure questions follow CBSE 2024 board pattern..." style="width:100%; font-size:13px; min-height:55px;"></textarea>
            </div>

            <div class="grid grid-3" style="gap:12px; margin-bottom:14px;">
                <div>
                    <label class="form-label" style="font-weight:700; font-size:12.5px; display:block; margin-bottom:4px; color:var(--il-text);">
                        🧠 Cognitive / Bloom's Level
                    </label>
                    <select id="aiCognitiveLevel" class="form-control" style="width:100%; font-size:12.5px;">
                        <option value="balanced" selected>🎯 Balanced (All Levels)</option>
                        <option value="remember_understand">📖 Recall & Understanding</option>
                        <option value="apply_calculate">➗ Application & Calculations</option>
                        <option value="analyze_evaluate">🔬 Critical Analysis & Diagram</option>
                        <option value="hots_olympiad">🏆 Advanced HOTS / Olympiad</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:12.5px; display:block; margin-bottom:4px; color:var(--il-text);">
                        📋 Exam Board Pattern
                    </label>
                    <select id="aiExamPattern" class="form-control" style="width:100%; font-size:12.5px;">
                        <option value="standard" selected>Standard Curriculum</option>
                        <option value="cbse">CBSE Board Exam Style</option>
                        <option value="icse">ICSE / ISC Exam Style</option>
                        <option value="state_board">State Board Standard</option>
                        <option value="jee_neet_foundation">JEE / NEET Foundation</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:12.5px; display:block; margin-bottom:4px; color:var(--il-text);">
                        🌐 Language Format
                    </label>
                    <select id="aiLanguageTone" class="form-control" style="width:100%; font-size:12.5px;">
                        <option value="english" selected>English (Standard)</option>
                        <option value="bilingual">Bilingual (English + Hindi hints)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-3" style="gap:12px; margin-bottom:16px;">
                <div>
                    <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Question Type</label>
                    <select id="aiType" class="form-control" style="width:100%;">
                        <option value="mixed_paper" selected>🎯 Complete Mixed Exam (MCQs + Equations + Diagrams)</option>
                        <option value="equation_problem">➗ Solve Equations & Numerical Calculations</option>
                        <option value="diagram_based">📐 Diagram & Figure / Geometry Based</option>
                        <option value="mcq">MCQ (Multiple Choice)</option>
                        <option value="short_answer">Short Answer</option>
                        <option value="descriptive">Descriptive (Long / Proofs)</option>
                        <option value="true_false">True / False</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Difficulty</label>
                    <select id="aiDifficulty" class="form-control" style="width:100%;">
                        <option value="easy">Easy</option>
                        <option value="medium" selected>Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-weight:700; font-size:13px; display:block; margin-bottom:4px; color:var(--il-text);">Quantity</label>
                    <select id="aiCount" class="form-control" style="width:100%;">
                        <option value="1">1 Question</option>
                        <option value="3">3 Questions</option>
                        <option value="5">5 Questions</option>
                        <option value="8">8 Questions</option>
                        <option value="10" selected>10 Questions (Full Paper)</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer; color:var(--il-text);">
                        <input type="checkbox" id="aiAutoSave" checked> Automatically save directly to Question Bank
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer; color:var(--il-text);">
                        <input type="checkbox" id="aiIncludeMarking"> Include Step-by-Step Marking Breakdown in explanation
                    </label>
                </div>
                <button type="submit" id="btnRunAi" class="btn" style="background:linear-gradient(135deg, #6366f1, #8b5cf6); color:#fff; border:none; font-weight:700; padding:10px 22px;">
                    🚀 Generate Questions
                </button>
            </div>
        </form>

        <!-- PREVIEW CONTAINER -->
        <div id="aiPreviewBox" style="display:none; margin-top:16px; border-top:1px solid var(--il-border); padding-top:16px;">
            <div id="aiStatusMsg" style="margin-bottom:12px;"></div>
            <div id="aiCardsContainer" style="display:flex; flex-direction:column; gap:14px;"></div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                <button type="button" class="btn btn-secondary" onclick="closeAiQuestionModal()">Done / Close</button>
                <button type="button" id="btnSaveSelected" class="btn btn-primary" onclick="saveSelectedAiQuestions()" style="display:none;">
                    💾 Save Selected to Bank
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openNewQuestionModal() {
    document.getElementById('newQuestionModal').style.display = 'flex';
}
function closeNewQuestionModal() {
    document.getElementById('newQuestionModal').style.display = 'none';
}
function toggleOptionFields(type) {
    const group = document.getElementById('mcqOptionsGroup');
    if (type === 'mcq') {
        group.style.display = 'block';
    } else {
        group.style.display = 'none';
    }
}

function openAiQuestionModal() {
    document.getElementById('aiQuestionModal').style.display = 'flex';
}
function closeAiQuestionModal() {
    document.getElementById('aiQuestionModal').style.display = 'none';
}

function handleImageSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreviewImg').src = e.target.result;
            document.getElementById('imagePreviewContainer').style.display = 'block';
            document.getElementById('btnScanAi').style.display = 'inline-block';
            document.getElementById('scanStatusMsg').innerHTML = '<span style="color:#6366f1;">Image selected! Click "Scan & Autofill with AI" to automatically transcribe text & options.</span>';
        };
        reader.readAsDataURL(file);
    }
}

function removeImagePreview() {
    document.getElementById('questionImageInput').value = '';
    document.getElementById('manualImageUrl').value = '';
    document.getElementById('imagePreviewContainer').style.display = 'none';
    document.getElementById('btnScanAi').style.display = 'none';
}

async function scanQuestionImageWithAi() {
    const input = document.getElementById('questionImageInput');
    if (!input.files || !input.files[0]) return;

    const btn = document.getElementById('btnScanAi');
    const statusMsg = document.getElementById('scanStatusMsg');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '⏳ Scanning Image with AI...';
    statusMsg.innerHTML = '<span style="color:#d97706;">Uploading image and performing AI pedagogical transcription...</span>';

    const formData = new FormData();
    formData.append('image', input.files[0]);
    formData.append('csrf', '<?= Helper::e(\App\Session::csrfToken()) ?>');

    try {
        const res = await fetch('/teacher/extract-question-from-image.php', {
            method: 'POST',
            body: formData
        });
        const json = await res.json();
        if (json.success && json.data) {
            const d = json.data;
            if (d.image_url) {
                document.getElementById('manualImageUrl').value = d.image_url;
            }
            if (d.question_text) {
                document.getElementById('modalQuestionText').value = d.question_text;
            }
            if (d.question_type) {
                const typeSelect = document.getElementById('modalQuestionType');
                if (typeSelect) {
                    typeSelect.value = (d.question_type === 'equation_problem' || d.question_type === 'numerical') ? 'numerical' : ((d.question_type === 'mcq') ? 'mcq' : 'descriptive');
                    toggleOptionFields(typeSelect.value);
                }
            }
            if (d.options && Array.isArray(d.options) && d.options.length >= 2) {
                if (document.getElementById('optA')) document.getElementById('optA').value = d.options[0] || '';
                if (document.getElementById('optB')) document.getElementById('optB').value = d.options[1] || '';
                if (document.getElementById('optC')) document.getElementById('optC').value = d.options[2] || '';
                if (document.getElementById('optD')) document.getElementById('optD').value = d.options[3] || '';
            }
            if (d.correct_answer) {
                document.getElementById('modalCorrectAnswer').value = d.correct_answer;
            }
            if (d.explanation) {
                document.getElementById('modalExplanation').value = d.explanation;
            }
            if (d.marks) {
                document.getElementById('modalMarks').value = d.marks;
            }
            if (d.difficulty) {
                document.getElementById('modalDifficulty').value = d.difficulty;
            }

            statusMsg.innerHTML = '<span style="color:#10b981; font-weight:700;">✓ Form fields auto-filled successfully! Review and hit Save below.</span>';
        } else {
            statusMsg.innerHTML = '<span style="color:#ef4444;">Error scanning: ' + (json.error || 'Failed') + '</span>';
        }
    } catch (e) {
        statusMsg.innerHTML = '<span style="color:#ef4444;">Error: ' + e.message + '</span>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

let lastGeneratedQuestions = [];

async function handleAiGeneration(e) {
    e.preventDefault();
    const btn = document.getElementById('btnRunAi');
    const previewBox = document.getElementById('aiPreviewBox');
    const statusMsg = document.getElementById('aiStatusMsg');
    const cardsContainer = document.getElementById('aiCardsContainer');
    const btnSaveSelected = document.getElementById('btnSaveSelected');

    const subjectId = document.getElementById('aiSubject').value;
    const classLevel = document.getElementById('aiClassLevel').value;
    const topic = document.getElementById('aiTopic').value;
    const type = document.getElementById('aiType').value;
    const difficulty = document.getElementById('aiDifficulty').value;
    const count = document.getElementById('aiCount').value;
    const autoSave = document.getElementById('aiAutoSave').checked ? 1 : 0;
    const customInstructions = document.getElementById('aiCustomInstructions') ? document.getElementById('aiCustomInstructions').value : '';
    const cognitiveLevel = document.getElementById('aiCognitiveLevel') ? document.getElementById('aiCognitiveLevel').value : 'balanced';
    const examPattern = document.getElementById('aiExamPattern') ? document.getElementById('aiExamPattern').value : 'standard';
    const languageTone = document.getElementById('aiLanguageTone') ? document.getElementById('aiLanguageTone').value : 'english';
    const includeMarking = document.getElementById('aiIncludeMarking') && document.getElementById('aiIncludeMarking').checked ? 1 : 0;
    const onBehalfTeacher = document.getElementById('aiOnBehalfTeacher') ? document.getElementById('aiOnBehalfTeacher').value : '';

    btn.disabled = true;
    btn.innerHTML = '⏳ Generating with AI...';
    previewBox.style.display = 'block';
    statusMsg.innerHTML = '<div class="alert alert-info" style="margin:0;">Analyzing curriculum and generating structured questions...</div>';
    cardsContainer.innerHTML = '';
    btnSaveSelected.style.display = 'none';

    try {
        const resp = await fetch('/teacher/ai-generate-questions-handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                subject_id: subjectId,
                class_level: classLevel,
                topic: topic,
                question_type: type,
                difficulty: difficulty,
                count: count,
                auto_save: autoSave,
                custom_instructions: customInstructions,
                cognitive_level: cognitiveLevel,
                exam_pattern: examPattern,
                language_tone: languageTone,
                include_marking_scheme: includeMarking,
                on_behalf_teacher_id: onBehalfTeacher
            })
        });

        const data = await resp.json();
        if (!data.success) {
            throw new Error(data.error || 'Failed to generate questions');
        }

        lastGeneratedQuestions = data.questions || [];
        statusMsg.innerHTML = `<div class="alert alert-success" style="margin:0;">${data.message}</div>`;

        if (lastGeneratedQuestions.length === 0) {
            cardsContainer.innerHTML = '<p class="muted">No questions could be produced. Please try another topic.</p>';
            return;
        }

        let html = '';
        lastGeneratedQuestions.forEach((q, idx) => {
            let safeText = escapeHtml(q.question_text || q.q || '');
            
            // Format [Diagram: ...] blocks into visual highlights
            safeText = safeText.replace(/\[Diagram:\s*([\s\S]*?)\]/gi, function(m, p1) {
                return `<div style="background:var(--surface-2); border:1.5px dashed #6366f1; border-radius:8px; padding:12px 14px; margin:10px 0; font-family:'JetBrains Mono', monospace; font-size:12px; color:var(--il-text);">
                    <div style="font-weight:700; color:#6366f1; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                        <span>📐 DIAGRAM / FIGURE REFERENCE:</span>
                    </div>
                    <div style="white-space:pre-wrap;">${p1.trim()}</div>
                </div>`;
            });

            let qType = q.question_type || q.type || 'mcq';
            let typeBadge = qType.toUpperCase();
            if (qType === 'equation_problem') typeBadge = '➗ EQUATION TO SOLVE';
            if (qType === 'diagram_based') typeBadge = '📐 DIAGRAM PROBLEM';

            let opts = q.options || q.opts || null;

            html += `
            <div class="card" style="padding:16px; border:1px solid var(--il-border); background:var(--surface-1); border-radius:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; flex-wrap:wrap; gap:6px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        ${!autoSave ? `<input type="checkbox" class="ai-q-chk" data-idx="${idx}" checked>` : '<span style="color:#10b981; font-weight:700;">✓ Saved</span>'}
                        <span class="badge badge-primary" style="font-size:11px;">${typeBadge}</span>
                        <span class="badge badge-info" style="font-size:11px;">${q.difficulty}</span>
                    </div>
                    <span style="font-size:12px; font-weight:700; color:var(--il-muted);">${q.marks} Marks</span>
                </div>
                <div style="font-weight:600; margin-bottom:8px; font-size:14px; color:var(--il-text); white-space:pre-wrap; line-height:1.5;">Q${idx+1}. ${safeText}</div>
                ${opts && opts.length ? `
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-bottom:8px; font-size:12px;">
                        ${opts.map(o => `<div style="padding:5px 8px; background:var(--surface-2); border-radius:6px; border:1px solid var(--il-border);">${escapeHtml(o)}</div>`).join('')}
                    </div>
                ` : ''}
                <div style="font-size:12.5px; margin-bottom:4px; white-space:pre-wrap;">
                    <strong style="color:var(--success);">Correct Answer / Solution:</strong> ${escapeHtml(q.correct_answer || q.ans || '')}
                </div>
                ${(q.explanation || q.exp) ? `
                    <div style="font-size:11.5px; color:var(--il-muted); font-style:italic; white-space:pre-wrap; margin-top:4px; padding:6px 10px; background:var(--surface-0); border-radius:6px; border:1px solid var(--il-border);">
                        <strong>Explanation / Steps:</strong> ${escapeHtml(q.explanation || q.exp || '')}
                    </div>
                ` : ''}
            </div>`;
        });

        cardsContainer.innerHTML = html;

        if (!autoSave) {
            btnSaveSelected.style.display = 'inline-block';
        } else {
            statusMsg.innerHTML += `<div style="margin-top:10px;"><a href="/teacher/questions" class="btn btn-sm btn-outline-primary">🔄 Reload Question List to View</a></div>`;
        }

    } catch (err) {
        statusMsg.innerHTML = `<div class="alert alert-danger" style="margin:0;">${err.message}</div>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '🚀 Generate Questions';
    }
}

async function saveSelectedAiQuestions() {
    const checkboxes = document.querySelectorAll('.ai-q-chk:checked');
    if (!checkboxes.length) {
        alert('Please select at least one question to save.');
        return;
    }

    const selectedList = [];
    checkboxes.forEach(chk => {
        const idx = parseInt(chk.dataset.idx, 10);
        if (lastGeneratedQuestions[idx]) {
            selectedList.push(lastGeneratedQuestions[idx]);
        }
    });

    if (!selectedList.length) return;

    const btn = document.getElementById('btnSaveSelected');
    btn.disabled = true;
    btn.innerText = 'Saving...';

    const onBehalfTeacher = document.getElementById('aiOnBehalfTeacher') ? document.getElementById('aiOnBehalfTeacher').value : '';

    try {
        const resp = await fetch('/teacher/ai-generate-questions-handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'batch_save',
                questions: selectedList,
                on_behalf_teacher_id: onBehalfTeacher
            })
        });
        const res = await resp.json();
        if (res.success) {
            alert(res.message || 'Selected questions saved!');
            window.location.reload();
        } else {
            alert('Error saving questions: ' + (res.error || 'Failed'));
        }
    } catch (e) {
        alert('Error: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerText = '💾 Save Selected to Bank';
    }
}

async function cleanDuplicateQuestions() {
    if (!confirm('Scan and delete duplicate identical questions from your Question Bank?')) return;
    try {
        const resp = await fetch('/teacher/ai-generate-questions-handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_duplicates' })
        });
        const res = await resp.json();
        if (res.success) {
            alert(res.message);
            window.location.reload();
        } else {
            alert(res.error || 'Failed to clean duplicates');
        }
    } catch(e) {
        alert(e.message);
    }
}

async function deleteQuestionAjax(e, form) {
    e.preventDefault();
    if (!confirm('Delete this question from your bank?')) return false;

    const row = form.closest('.question-row-item');
    const btn = form.querySelector('button[type="submit"]');
    const originalContent = btn ? btn.innerHTML : '🗑️';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '⏳';
    }

    try {
        const formData = new FormData(form);
        const res = await fetch('/teacher/question-save-handler.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const data = await res.json();
        if (data && data.success) {
            if (row) {
                row.style.transition = 'all 0.25s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    row.remove();
                    const countEl = document.querySelector('.card strong');
                    if (countEl && countEl.textContent.includes('Questions Vault')) {
                        const remaining = document.querySelectorAll('.question-row-item').length;
                        countEl.textContent = `Questions Vault (${remaining})`;
                    }
                }, 250);
            } else {
                window.location.reload();
            }
        } else {
            alert('Error deleting question: ' + (data.error || 'Server error occurred'));
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }
    } catch (err) {
        form.onsubmit = null;
        form.submit();
    }
    return false;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
