<?php
declare(strict_types=1);

use App\Auth;
use App\ReportCard;
use App\Helper;

Auth::requireLogin();
$user = Auth::user();
$studentId = (int)($_GET['student_id'] ?? ($user['role'] === 'student' ? $user['id'] : 0));

if (!$studentId) {
    Helper::redirect('/dashboard');
}

$data = ReportCard::generateForStudent($studentId);
if (!$data) {
    http_response_code(404);
    exit('Student report card data not found.');
}

$student = $data['student'];
$subjects = $data['subjects'];
$att = $data['attendance'];
$overall = $data['overall'];

$pageTitle = 'Academic Report Card — ' . ($student['full_name'] ?: 'Student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= Helper::e($pageTitle) ?></title>
<link rel="icon" href="/assets/img/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@600;700;800&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    background: #EFECE6;
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #1A130E;
    padding: 30px 16px;
    display: flex;
    justify-content: center;
}
.no-print-bar {
    max-width: 900px;
    width: 100%;
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    border: none;
    transition: all 0.15s ease;
}
.btn-primary { background: #FF7A18; color: #fff; }
.btn-primary:hover { background: #E66508; }
.btn-secondary { background: #fff; color: #333; border: 1px solid #D5C7B8; }

/* The Official Report Card Certificate Frame */
.report-frame {
    max-width: 900px;
    width: 100%;
    background: #fff;
    border-radius: 18px;
    padding: 44px 52px;
    box-shadow: 0 12px 35px rgba(0,0,0,0.07);
    border: 3px double #D4AF37; /* Elegant subtle gold double border */
    position: relative;
}

/* Header */
.rc-header {
    text-align: center;
    border-bottom: 2px solid #2B1B12;
    padding-bottom: 20px;
    margin-bottom: 22px;
    position: relative;
}
.school-crest {
    width: 76px;
    height: 76px;
    object-fit: contain;
    border-radius: 12px;
    margin-bottom: 8px;
}
.school-crest-fallback {
    width: 76px;
    height: 76px;
    margin: 0 auto 8px;
    border-radius: 12px;
    background: linear-gradient(135deg, #7C2D5C, #A0386B);
    color: #fff;
    font-size: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.school-name {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 26px;
    font-weight: 800;
    color: #1F140E;
    letter-spacing: -0.02em;
    text-transform: uppercase;
}
.school-sub { font-size: 13px; color: #6F5E52; margin-top: 3px; }
.rc-badge {
    display: inline-block;
    margin-top: 10px;
    padding: 5px 16px;
    background: #FAF5EB;
    border: 1px solid #E6D8BE;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #8C6212;
}

/* Student Bio Grid */
.bio-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    background: #FAF8F5;
    border: 1px solid #EAE3D5;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
}
.bio-item label { display: block; font-size: 10.5px; text-transform: uppercase; font-weight: 800; color: #8A7768; letter-spacing: 0.05em; }
.bio-item value { display: block; font-size: 14px; font-weight: 700; color: #231913; margin-top: 2px; }

/* Marks Table */
table.marks-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 24px;
    font-size: 13px;
}
table.marks-table th {
    background: #2B1B12;
    color: #FFF9F4;
    padding: 10px 12px;
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 1px solid #2B1B12;
}
table.marks-table td {
    padding: 11px 12px;
    border: 1px solid #E6DDD0;
    color: #231913;
}
table.marks-table tr:nth-child(even) td { background: #FAF9F6; }
table.marks-table td.tac, table.marks-table th.tac { text-align: center; }
table.marks-table td.tar, table.marks-table th.tar { text-align: right; }
.grade-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 800;
    font-size: 12px;
    background: #FEF3C7;
    color: #92400E;
}
.grade-A-plus, .grade-A { background: #DCFCE7; color: #15803D; }

/* Summary & Non-Scholastic Box */
.summary-grid {
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}
.summary-card {
    background: #FAF8F5;
    border: 1px solid #EAE3D5;
    border-radius: 12px;
    padding: 18px 20px;
}
.summary-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #735C4A; margin-bottom: 12px; border-bottom: 1px solid #E6DDD0; padding-bottom: 6px; }
.summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13.5px; }

/* Result Banner */
.result-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #122E1F 0%, #1B452E 100%);
    color: #fff;
    border-radius: 12px;
    padding: 16px 24px;
    margin-bottom: 36px;
}
.result-status { font-size: 20px; font-weight: 800; letter-spacing: 0.05em; }
.result-pct { font-family: 'JetBrains Mono', monospace; font-size: 24px; font-weight: 800; color: #6EE7A8; }

/* Signatures */
.sig-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    padding-top: 30px;
    border-top: 1px dashed #D5C7B8;
    text-align: center;
}
.sig-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    height: 70px;
}
.sig-line {
    width: 170px;
    border-top: 1.5px solid #2B1B12;
    padding-top: 6px;
    font-size: 12px;
    font-weight: 700;
    color: #4A3428;
}
.school-seal {
    width: 58px;
    height: 58px;
    border: 2px dashed #B45309;
    color: #B45309;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    font-weight: 800;
    text-align: center;
    text-transform: uppercase;
    margin-bottom: 4px;
}

@media print {
    body { background: #fff !important; padding: 0 !important; }
    .no-print-bar { display: none !important; }
    .report-frame {
        border: 2px solid #888 !important;
        box-shadow: none !important;
        padding: 24px 30px !important;
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>
</head>
<body>

<div style="width:100%; max-width:900px;">
    <div class="no-print-bar">
        <a href="javascript:history.back()" class="btn btn-secondary">← Back</a>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print / Download Report Card PDF</button>
    </div>

    <div class="report-frame">
        <!-- HEADER -->
        <div class="rc-header">
            <?php if (!empty($student['crest_logo'])): ?>
                <img src="<?= Helper::e($student['crest_logo']) ?>" class="school-crest" alt="">
            <?php else: ?>
                <div class="school-crest-fallback">🎓</div>
            <?php endif; ?>

            <div class="school-name"><?= Helper::e($student['school_name'] ?: 'Infinity Learning Partner School') ?></div>
            <div class="school-sub">
                <?= Helper::e($student['school_address'] ?: 'Academic Campus · Department of Primary & Secondary Education') ?><br>
                Affiliated & Certified Institution · Academic Year 2026–2027
            </div>
            <div class="rc-badge">Cumulative Scholastic Assessment & Progress Report</div>
        </div>

        <!-- BIO -->
        <div class="bio-grid">
            <div class="bio-item">
                <label>Student Name</label>
                <value><?= Helper::e($student['full_name']) ?></value>
            </div>
            <div class="bio-item">
                <label>Class & Section</label>
                <value><?= Helper::e($student['current_class'] ?: 'Grade 8') ?></value>
            </div>
            <div class="bio-item">
                <label>Roll / Admission No.</label>
                <value><?= Helper::e($student['admission_number'] ?: 'ADM-2026-' . $student['user_id']) ?></value>
            </div>
            <div class="bio-item">
                <label>Attendance Record</label>
                <value><?= $att['percentage'] ?>% (<?= $att['present_days'] ?>/<?= $att['total_days'] ?> Days)</value>
            </div>
        </div>

        <!-- MARKS TABLE -->
        <table class="marks-table">
            <thead>
                <tr>
                    <th style="width:28%;">Subject / Discipline</th>
                    <th class="tac">Unit Test (20)</th>
                    <th class="tac">Term Exam (80)</th>
                    <th class="tac">Total Marks (100)</th>
                    <th class="tac">Grade</th>
                    <th>Teacher Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $s): ?>
                    <tr>
                        <td><strong><?= Helper::e($s['subject']) ?></strong></td>
                        <td class="tac"><?= number_format((float)$s['unit_test_obtained'], 1) ?></td>
                        <td class="tac"><?= number_format((float)$s['term_exam_obtained'], 1) ?></td>
                        <td class="tac" style="font-weight:700;"><?= number_format((float)$s['total_obtained'], 1) ?></td>
                        <td class="tac">
                            <span class="grade-badge grade-<?= str_replace('+', '-plus', $s['grade']) ?>">
                                <?= Helper::e($s['grade']) ?>
                            </span>
                        </td>
                        <td style="font-size:12px; color:#5B4739;"><?= Helper::e($s['remarks']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- SUMMARY & NON-SCHOLASTIC -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-title">Scholastic Grading Scale Reference</div>
                <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:6px; font-size:11.5px; text-align:center;">
                    <div style="background:#fff; padding:6px; border-radius:6px; border:1px solid #EAE3D5;"><strong>A+</strong>: 90–100%</div>
                    <div style="background:#fff; padding:6px; border-radius:6px; border:1px solid #EAE3D5;"><strong>A</strong>: 80–89%</div>
                    <div style="background:#fff; padding:6px; border-radius:6px; border:1px solid #EAE3D5;"><strong>B+</strong>: 70–79%</div>
                    <div style="background:#fff; padding:6px; border-radius:6px; border:1px solid #EAE3D5;"><strong>B</strong>: 60–69%</div>
                </div>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:6px; font-size:11.5px; text-align:center; margin-top:6px;">
                    <div style="background:#fff; padding:6px; border-radius:6px; border:1px solid #EAE3D5;"><strong>C</strong>: 50–59%</div>
                    <div style="background:#fff; padding:6px; border-radius:6px; border:1px solid #EAE3D5;"><strong>D</strong>: 40–49%</div>
                    <div style="background:#fff; padding:6px; border-radius:6px; border:1px solid #EAE3D5; color:#DC2626;"><strong>F</strong>: Below 40%</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-title">Co-Scholastic & Behavioral Record</div>
                <div class="summary-row"><span>Discipline & Conduct:</span><strong>Exemplary</strong></div>
                <div class="summary-row"><span>Chess Academy & Logic:</span><strong>Active Competitor</strong></div>
                <div class="summary-row"><span>Class Participation:</span><strong>Consistent</strong></div>
            </div>
        </div>

        <!-- RESULT BANNER -->
        <div class="result-banner">
            <div>
                <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; opacity:0.85;">Cumulative Final Assessment</div>
                <div class="result-status">RESULT: <?= Helper::e($overall['result']) ?></div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.08em; opacity:0.85;">Overall Percentage</div>
                <div class="result-pct"><?= $overall['percentage'] ?>% (Grade <?= $overall['grade'] ?>)</div>
            </div>
        </div>

        <!-- SIGNATURES -->
        <div class="sig-container">
            <div class="sig-box">
                <div class="sig-line">Class Teacher Signature</div>
            </div>
            <div class="sig-box">
                <div class="school-seal">Official<br>Seal</div>
                <div class="sig-line">Controller of Examinations</div>
            </div>
            <div class="sig-box">
                <div class="sig-line">Principal / Headmaster</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
