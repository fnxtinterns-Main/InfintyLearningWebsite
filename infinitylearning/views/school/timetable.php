<?php
use App\Auth;
use App\Helper;
use App\Timetable;
use App\School;
use App\Database;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);

$db = Database::connect();
$subjects = $db->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();
$teachers = $db->query("
    SELECT u.id, t.full_name, t.expert_field
    FROM users u
    JOIN teachers t ON t.user_id = u.id
    WHERE u.school_id = {$schoolId} AND u.status = 'active'
    ORDER BY t.full_name ASC
")->fetchAll();

$classes = Timetable::getClassesForSchool($schoolId);
$sectionsList = Timetable::getSectionsForSchool($schoolId);
$periods = Timetable::getPeriodsForSchool($schoolId);

$selectedClass = trim((string)($_GET['class'] ?? $_SESSION['last_timetable_class'] ?? ($classes[0] ?? 'Grade 1')));
$selectedSection = strtoupper(trim((string)($_GET['section'] ?? $_SESSION['last_timetable_section'] ?? ($sectionsList[0] ?? 'A'))));

if ($selectedClass !== '' && !in_array($selectedClass, $classes, true)) {
    $classes[] = $selectedClass;
}
if ($selectedSection !== '' && !in_array($selectedSection, $sectionsList, true)) {
    $sectionsList[] = $selectedSection;
}

$_SESSION['last_timetable_class'] = $selectedClass;
$_SESSION['last_timetable_section'] = $selectedSection;

$grid = Timetable::getGridForClass($schoolId, $selectedClass, $selectedSection);

$pageTitle = 'Class Routine & Timetable — ' . $school['name'];
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <main class="admin-main">
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker">🗓️ CLASS ROUTINE & BELL SCHEDULE</span>
                <h1 style="margin:2px 0 0; font-size:24px;">School Timetable Planner</h1>
                <p class="muted" style="font-size:14px; margin-top:4px;">Construct weekly period timetables for each class and section with 12-hour bell schedules and teacher conflict prevention.</p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button type="button" class="btn btn-secondary" onclick="window.print()">🖨️ Print Timetable</button>
                <button type="button" class="btn btn-secondary" onclick="openSubModal()">🔄 Substitution Finder</button>
                <button type="button" class="btn btn-secondary" onclick="openBellModal()">⏰ Bell Schedule & Timings</button>
                <button type="button" class="btn btn-primary" onclick="openSlotModal()">+ Assign Period Slot</button>
            </div>
        </div>

        <!-- PRINT ONLY BULLETIN HEADER -->
        <div class="only-print-header" style="display:none; margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #222; padding-bottom:10px; margin-bottom:12px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <?php if (!empty($school['crest_logo'])): ?>
                        <img src="<?= Helper::e($school['crest_logo']) ?>" style="height:44px; border-radius:6px;" alt="">
                    <?php endif; ?>
                    <div>
                        <h2 style="margin:0; font-size:18px; color:#000;"><?= Helper::e($school['name']) ?></h2>
                        <div style="font-size:12px; color:#444;">Official Weekly Timetable · Academic Year 2026–2027</div>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:16px; font-weight:800; color:#000;">Class: <?= Helper::e($selectedClass) ?> (Section <?= Helper::e($selectedSection) ?>)</div>
                    <div style="font-size:11px; color:#666;">Printed on <?= date('d M Y') ?></div>
                </div>
            </div>
        </div>

        <?php if ($msg = \App\Session::flash('success')): ?>
            <div class="alert alert-success" style="margin-bottom:16px;"><?= Helper::e($msg) ?></div>
        <?php endif; ?>
        <?php if ($err = \App\Session::flash('error')): ?>
            <div class="alert alert-danger" style="margin-bottom:16px;"><?= Helper::e($err) ?></div>
        <?php endif; ?>

        <!-- CLASS / SECTION TABS -->
        <div class="card" style="padding:16px 20px; margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;">
                <form method="get" action="/school/timetable" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                    <label style="font-weight:700; font-size:14px; margin:0; color:var(--il-text);">Select Class & Section:</label>
                    <select name="class" class="form-control" style="width:170px;" onchange="this.form.submit()">
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= Helper::e($c) ?>" <?= $selectedClass === $c ? 'selected' : '' ?>><?= Helper::e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="section" class="form-control" style="width:140px;" onchange="this.form.submit()">
                        <?php foreach ($sectionsList as $sec): ?>
                            <option value="<?= Helper::e($sec) ?>" <?= $selectedSection === $sec ? 'selected' : '' ?>>Section <?= Helper::e($sec) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="openAddClassModal()">
                        ➕ Add Custom Class
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="openAddSectionModal()">
                        ➕ Add Section
                    </button>
                </div>
            </div>
        </div>

        <!-- TIMETABLE VISUAL GRID -->
        <div class="card" style="padding:0; overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:center; font-size:13px; min-width:920px;">
                <thead>
                    <tr style="background:var(--surface-0); border-bottom:2px solid var(--il-border); font-size:11px; text-transform:uppercase; color:var(--il-muted);">
                        <th style="padding:14px 16px; width:110px; text-align:left;">Day</th>
                        <?php foreach ($periods as $pInfo): 
                            $isBreak = !empty($pInfo['is_break']);
                            $pNum = (int)$pInfo['period_number'];
                        ?>
                            <th style="padding:12px 10px; border-left:1px solid var(--il-border); <?= $isBreak ? 'background:rgba(245,158,11,0.08);' : '' ?>">
                                <div style="font-weight:700; color:<?= $isBreak ? '#b45309' : 'var(--il-text)' ?>;">
                                    <?= Helper::e($pInfo['label']) ?>
                                </div>
                                <div style="font-size:10px; color:var(--il-muted); font-weight:normal; margin-top:2px;">
                                    <?= Timetable::formatTime12($pInfo['start']) ?> - <?= Timetable::formatTime12($pInfo['end']) ?>
                                </div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Timetable::DAYS as $day): ?>
                        <tr style="border-bottom:1px solid var(--il-border);">
                            <td style="padding:14px 16px; font-weight:700; text-align:left; background:var(--surface-0); text-transform:capitalize; color:var(--il-text);">
                                <?= $day ?>
                            </td>
                            <?php foreach ($periods as $pInfo): 
                                $isBreak = !empty($pInfo['is_break']);
                                $pNum = (int)$pInfo['period_number'];
                                $slot = $grid[$day][$pNum] ?? null;
                            ?>
                                <td style="padding:8px 6px; border-left:1px solid var(--il-border); vertical-align:top; min-width:100px; <?= $isBreak ? 'background:rgba(245,158,11,0.04);' : '' ?>">
                                    <?php if ($isBreak): ?>
                                        <div style="padding:14px 4px; font-size:11px; color:#d97706; font-weight:600; font-style:italic;">
                                            ☕ <?= Helper::e($pInfo['label']) ?>
                                        </div>
                                    <?php elseif ($slot): ?>
                                        <div style="background:var(--surface-2); border:1px solid var(--il-primary); border-radius:8px; padding:8px 6px; position:relative; text-align:left; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                                            <div style="font-weight:700; color:var(--il-primary); font-size:12px;">
                                                <?= Helper::e($slot['subject_name']) ?>
                                            </div>
                                            <div style="font-size:11px; color:var(--il-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px;">
                                                <?= Helper::e($slot['teacher_name']) ?>
                                            </div>
                                            <?php if (!empty($slot['room_number'])): ?>
                                                <div style="font-size:10px; color:var(--il-muted); margin-top:1px;">📍 <?= Helper::e($slot['room_number']) ?></div>
                                            <?php endif; ?>
                                            <form method="post" action="/school/timetable-save-handler.php" style="margin-top:6px;" onsubmit="return confirm('Remove this slot?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="slot_id" value="<?= (int)$slot['id'] ?>">
                                                <input type="hidden" name="class" value="<?= Helper::e($selectedClass) ?>">
                                                <input type="hidden" name="section" value="<?= Helper::e($selectedSection) ?>">
                                                <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">
                                                <button type="submit" style="background:none; border:none; color:var(--il-danger); cursor:pointer; font-size:10px; padding:0; font-weight:600;">× Clear</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-ghost btn-xs" style="color:var(--il-muted); font-size:11px; border:1px dashed var(--il-border); width:100%; height:52px; border-radius:6px;" onclick="openSlotModal('<?= $day ?>', <?= $pNum ?>)">
                                            + Add
                                        </button>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- 1. MODAL TO ASSIGN PERIOD SLOT -->
<div id="slotModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(5px);">
    <div class="card" style="background:var(--surface-1); color:var(--il-text); border:1px solid var(--il-border); border-radius:18px; max-width:520px; width:100%; padding:26px; box-shadow:0 24px 60px rgba(0,0,0,0.35);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <div>
                <h3 style="margin:0; font-size:19px;">Assign Routine Period</h3>
                <div style="font-size:12.5px; color:var(--il-primary); font-weight:700; margin-top:2px;">
                    Class: <?= Helper::e($selectedClass) ?> · Section <?= Helper::e($selectedSection) ?>
                </div>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeSlotModal()">&times;</button>
        </div>

        <form method="post" action="/school/timetable-save-handler.php">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">

            <div class="grid grid-2" style="margin-bottom:14px; gap:12px;">
                <div>
                    <label class="form-label" style="font-weight:600; display:block; margin-bottom:4px;">Class / Grade *</label>
                    <select name="class_level" id="modalClassSelect" class="form-control" required style="width:100%;">
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= Helper::e($c) ?>" <?= strcasecmp($selectedClass, $c) === 0 ? 'selected' : '' ?>><?= Helper::e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-weight:600; display:block; margin-bottom:4px;">Section *</label>
                    <select name="section" id="modalSectionSelect" class="form-control" required style="width:100%;">
                        <?php foreach ($sectionsList as $sec): ?>
                            <option value="<?= Helper::e($sec) ?>" <?= strcasecmp($selectedSection, $sec) === 0 ? 'selected' : '' ?>>Section <?= Helper::e($sec) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-2" style="margin-bottom:14px; gap:12px;">
                <div>
                    <label class="form-label" style="font-weight:600; display:block; margin-bottom:4px;">Day of Week *</label>
                    <select name="day_of_week" id="modalDaySelect" class="form-control" required style="width:100%;">
                        <?php foreach (Timetable::DAYS as $d): ?>
                            <option value="<?= $d ?>"><?= ucfirst($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-weight:600; display:block; margin-bottom:4px;">Period Slot *</label>
                    <select name="period_number" id="modalPeriodSelect" class="form-control" required style="width:100%;">
                        <?php foreach ($periods as $pInfo): 
                            if (!empty($pInfo['is_break'])) continue;
                            $pNum = (int)$pInfo['period_number'];
                        ?>
                            <option value="<?= $pNum ?>">
                                <?= Helper::e($pInfo['label']) ?> (<?= Timetable::formatTime12($pInfo['start']) ?> - <?= Timetable::formatTime12($pInfo['end']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label class="form-label" style="font-weight:600; display:block; margin-bottom:4px;">Subject *</label>
                <select name="subject_id" class="form-control" required style="width:100%;">
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= (int)$s['id'] ?>"><?= Helper::e($s['icon'] ?? '📚') ?> <?= Helper::e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label class="form-label" style="font-weight:600; display:block; margin-bottom:4px;">Assign Teacher *</label>
                <select name="teacher_user_id" class="form-control" required style="width:100%;">
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= (int)$t['id'] ?>"><?= Helper::e($t['full_name']) ?> (<?= Helper::e($t['expert_field'] ?? 'Teacher') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:18px;">
                <label class="form-label" style="font-weight:600; display:block; margin-bottom:4px;">Room Number / Lab</label>
                <input type="text" name="room_number" class="form-control" placeholder="e.g. Room 204 or Physics Lab" style="width:100%;">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="closeSlotModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save to Timetable</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. MODAL TO ADD CUSTOM CLASS -->
<div id="addClassModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(5px);">
    <div class="card" style="background:var(--surface-1); color:var(--il-text); border:1px solid var(--il-border); border-radius:18px; max-width:440px; width:100%; padding:24px; box-shadow:0 24px 60px rgba(0,0,0,0.35);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <h3 style="margin:0; font-size:18px;">➕ Add New School Class</h3>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeAddClassModal()">&times;</button>
        </div>
        <p class="muted" style="font-size:13px; margin:0 0 16px;">Specify a grade or division name (e.g. Grade 1, Grade 5, Nursery, Class 4, Pre-Medical).</p>

        <form method="post" action="/school/timetable-save-handler.php">
            <input type="hidden" name="action" value="add_class">
            <input type="hidden" name="section" value="<?= Helper::e($selectedSection) ?>">
            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">

            <div class="form-group" style="margin-bottom:18px;">
                <label class="form-label" style="font-weight:600; display:block; margin-bottom:4px;">Class Name *</label>
                <input type="text" name="class_name" class="form-control" placeholder="e.g. Grade 4 or Nursery" required style="width:100%;">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="closeAddClassModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Class</button>
            </div>
        </form>
    </div>
</div>

<!-- 2b. MODAL TO ADD CUSTOM SECTION -->
<div id="addSectionModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(4px);">
    <div class="card" style="background:var(--surface-1); color:var(--il-text); border:1px solid var(--il-border); border-radius:16px; max-width:440px; width:100%; padding:24px; box-shadow:0 20px 50px rgba(0,0,0,0.3);">
        <h3 style="margin-top:0; font-size:18px;">➕ Add Section</h3>
        <p class="muted" style="font-size:13px; margin-bottom:16px;">Create a new section (e.g. Section F, Section G, Rose, Lotus, Science Sec) for your school timetable.</p>
        <form method="post" action="/school/timetable-save-handler.php">
            <input type="hidden" name="action" value="add_section">
            <input type="hidden" name="class" value="<?= Helper::e($selectedClass) ?>">
            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">

            <div class="form-group" style="margin-bottom:18px;">
                <label class="form-label" style="font-weight:600; display:block; margin-bottom:4px;">Section Name / Identifier *</label>
                <input type="text" name="section_name" class="form-control" placeholder="e.g. F or Rose or Section 1" required style="width:100%;">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="closeAddSectionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Section</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. MODAL TO CONFIGURE BELL SCHEDULE & TIMINGS -->
<div id="bellModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(5px);">
    <div class="card" style="background:var(--surface-1); color:var(--il-text); border:1px solid var(--il-border); border-radius:18px; max-width:760px; width:100%; max-height:92vh; overflow-y:auto; padding:26px; box-shadow:0 24px 60px rgba(0,0,0,0.35);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <div>
                <h3 style="margin:0; font-size:20px;">⏰ Bell Schedule & Period Timings</h3>
                <p class="muted" style="margin:2px 0 0; font-size:13px;">Configure 12-hour school period durations (30 min, 45 min, 1 hr) and add Recess / Lunch breaks.</p>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeBellModal()">&times;</button>
        </div>

        <!-- Quick Presets -->
        <div style="display:flex; gap:8px; margin-bottom:18px; flex-wrap:wrap;">
            <button type="button" class="btn btn-ghost btn-sm" onclick="applyPreset('standard_45')" style="border:1px solid var(--il-border); font-size:12px;">
                ⚡ 45-min Standard (8 slots + Recess)
            </button>
            <button type="button" class="btn btn-ghost btn-sm" onclick="applyPreset('hour_60')" style="border:1px solid var(--il-border); font-size:12px;">
                ⚡ 1-Hour Classes (6 slots + Lunch)
            </button>
            <button type="button" class="btn btn-ghost btn-sm" onclick="applyPreset('junior_30')" style="border:1px solid var(--il-border); font-size:12px;">
                ⚡ 30-min Junior (8 slots + Break)
            </button>
        </div>

        <form method="post" action="/school/timetable-save-handler.php">
            <input type="hidden" name="action" value="save_bell_schedule">
            <input type="hidden" name="class" value="<?= Helper::e($selectedClass) ?>">
            <input type="hidden" name="section" value="<?= Helper::e($selectedSection) ?>">
            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">

            <div id="bellSlotsContainer" style="display:flex; flex-direction:column; gap:10px; margin-bottom:18px;">
                <?php foreach ($periods as $pIdx => $pInfo): 
                    $pNum = (int)$pInfo['period_number'];
                    $isBreak = !empty($pInfo['is_break']);
                ?>
                    <div class="bell-slot-row card p-2" data-slot-index="<?= $pNum ?>" style="background:var(--surface-2); border:1px solid var(--il-border); border-radius:10px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <span style="font-weight:700; font-size:12px; width:24px; text-align:center; color:var(--il-muted);"><?= $pNum ?></span>
                        <input type="text" name="periods[<?= $pNum ?>][label]" class="form-control" value="<?= Helper::e($pInfo['label']) ?>" placeholder="Slot Label (e.g. Period 1, Recess)" style="flex:1; min-width:140px; font-size:13px;" required>
                        
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span class="muted" style="font-size:12px;">Start:</span>
                            <input type="time" name="periods[<?= $pNum ?>][start]" class="form-control" value="<?= substr($pInfo['start'], 0, 5) ?>" style="width:115px; font-size:13px;" required>
                        </div>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span class="muted" style="font-size:12px;">End:</span>
                            <input type="time" name="periods[<?= $pNum ?>][end]" class="form-control" value="<?= substr($pInfo['end'], 0, 5) ?>" style="width:115px; font-size:13px;" required>
                        </div>

                        <label style="display:flex; align-items:center; gap:4px; font-size:12px; cursor:pointer; color:var(--il-text);">
                            <input type="checkbox" name="periods[<?= $pNum ?>][is_break]" value="1" <?= $isBreak ? 'checked' : '' ?> style="accent-color:var(--il-warning);">
                            Recess / Break?
                        </label>

                        <button type="button" class="btn btn-ghost btn-xs" style="color:var(--il-danger); font-size:14px;" onclick="removeBellRow(this)" title="Delete Slot">🗑️</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; border-top:1px solid var(--il-border); padding-top:16px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="addBellRow()">+ Add Period Slot</button>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeBellModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Bell Schedule</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openSlotModal(day, period) {
    if (day) document.getElementById('modalDaySelect').value = day;
    if (period) document.getElementById('modalPeriodSelect').value = period;
    const modalCls = document.getElementById('modalClassSelect');
    if (modalCls) modalCls.value = <?= json_encode($selectedClass) ?>;
    const modalSec = document.getElementById('modalSectionSelect');
    if (modalSec) modalSec.value = <?= json_encode($selectedSection) ?>;
    document.getElementById('slotModal').style.display = 'flex';
}
function closeSlotModal() { document.getElementById('slotModal').style.display = 'none'; }

function openAddClassModal() { document.getElementById('addClassModal').style.display = 'flex'; }
function closeAddClassModal() { document.getElementById('addClassModal').style.display = 'none'; }

function openAddSectionModal() { document.getElementById('addSectionModal').style.display = 'flex'; }
function closeAddSectionModal() { document.getElementById('addSectionModal').style.display = 'none'; }

function openBellModal() { document.getElementById('bellModal').style.display = 'flex'; }
function closeBellModal() { document.getElementById('bellModal').style.display = 'none'; }

function removeBellRow(btn) {
    const row = btn.closest('.bell-slot-row');
    const container = document.getElementById('bellSlotsContainer');
    if (container.children.length > 2) {
        row.remove();
        reindexBellRows();
    } else {
        alert('You must have at least 2 period slots in the bell schedule.');
    }
}

function reindexBellRows() {
    const rows = document.querySelectorAll('.bell-slot-row');
    rows.forEach((row, idx) => {
        const num = idx + 1;
        row.dataset.slotIndex = num;
        row.querySelector('span').textContent = num;
        row.querySelector('input[type="text"]').name = `periods[${num}][label]`;
        const times = row.querySelectorAll('input[type="time"]');
        if (times[0]) times[0].name = `periods[${num}][start]`;
        if (times[1]) times[1].name = `periods[${num}][end]`;
        const chk = row.querySelector('input[type="checkbox"]');
        if (chk) chk.name = `periods[${num}][is_break]`;
    });
}

function addBellRow(label = '', start = '09:00', end = '09:45', isBreak = false) {
    const container = document.getElementById('bellSlotsContainer');
    const num = container.children.length + 1;
    label = label || `Period ${num}`;

    const div = document.createElement('div');
    div.className = 'bell-slot-row card p-2';
    div.dataset.slotIndex = num;
    div.style.cssText = 'background:var(--surface-2); border:1px solid var(--il-border); border-radius:10px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;';
    div.innerHTML = `
        <span style="font-weight:700; font-size:12px; width:24px; text-align:center; color:var(--il-muted);">${num}</span>
        <input type="text" name="periods[${num}][label]" class="form-control" value="${label}" placeholder="Slot Label" style="flex:1; min-width:140px; font-size:13px;" required>
        <div style="display:flex; align-items:center; gap:6px;">
            <span class="muted" style="font-size:12px;">Start:</span>
            <input type="time" name="periods[${num}][start]" class="form-control" value="${start}" style="width:115px; font-size:13px;" required>
        </div>
        <div style="display:flex; align-items:center; gap:6px;">
            <span class="muted" style="font-size:12px;">End:</span>
            <input type="time" name="periods[${num}][end]" class="form-control" value="${end}" style="width:115px; font-size:13px;" required>
        </div>
        <label style="display:flex; align-items:center; gap:4px; font-size:12px; cursor:pointer; color:var(--il-text);">
            <input type="checkbox" name="periods[${num}][is_break]" value="1" ${isBreak ? 'checked' : ''} style="accent-color:var(--il-warning);">
            Recess / Break?
        </label>
        <button type="button" class="btn btn-ghost btn-xs" style="color:var(--il-danger); font-size:14px;" onclick="removeBellRow(this)">🗑️</button>
    `;
    container.appendChild(div);
}

function applyPreset(preset) {
    const container = document.getElementById('bellSlotsContainer');
    container.innerHTML = '';

    if (preset === 'standard_45') {
        const slots = [
            { l: 'Period 1', s: '08:30', e: '09:15', b: false },
            { l: 'Period 2', s: '09:15', e: '10:00', b: false },
            { l: 'Recess / Snack', s: '10:00', e: '10:20', b: true },
            { l: 'Period 3', s: '10:20', e: '11:05', b: false },
            { l: 'Period 4', s: '11:05', e: '11:50', b: false },
            { l: 'Lunch Break', s: '11:50', e: '12:30', b: true },
            { l: 'Period 5', s: '12:30', e: '13:15', b: false },
            { l: 'Period 6', s: '13:15', e: '14:00', b: false },
            { l: 'Period 7', s: '14:00', e: '14:45', b: false }
        ];
        slots.forEach(s => addBellRow(s.l, s.s, s.e, s.b));
    } else if (preset === 'hour_60') {
        const slots = [
            { l: 'Period 1', s: '08:30', e: '09:30', b: false },
            { l: 'Period 2', s: '09:30', e: '10:30', b: false },
            { l: 'Morning Break', s: '10:30', e: '10:50', b: true },
            { l: 'Period 3', s: '10:50', e: '11:50', b: false },
            { l: 'Lunch Break', s: '11:50', e: '12:40', b: true },
            { l: 'Period 4', s: '12:40', e: '13:40', b: false },
            { l: 'Period 5', s: '13:40', e: '14:40', b: false },
            { l: 'Period 6', s: '14:40', e: '15:40', b: false }
        ];
        slots.forEach(s => addBellRow(s.l, s.s, s.e, s.b));
    } else if (preset === 'junior_30') {
        const slots = [
            { l: 'Period 1', s: '09:00', e: '09:30', b: false },
            { l: 'Period 2', s: '09:30', e: '10:00', b: false },
            { l: 'Fruit Break', s: '10:00', e: '10:20', b: true },
            { l: 'Period 3', s: '10:20', e: '10:50', b: false },
            { l: 'Period 4', s: '10:50', e: '11:20', b: false },
            { l: 'Lunch Break', s: '11:20', e: '12:00', b: true },
            { l: 'Period 5', s: '12:00', e: '12:30', b: false },
            { l: 'Period 6', s: '12:30', e: '13:00', b: false }
        ];
        slots.forEach(s => addBellRow(s.l, s.s, s.e, s.b));
    }
}

function openSubModal() {
    document.getElementById('subModal').style.display = 'flex';
    fetchFreeTeachers();
}
function closeSubModal() {
    document.getElementById('subModal').style.display = 'none';
}

function fetchFreeTeachers() {
    const day = document.getElementById('subDaySelect').value;
    const period = document.getElementById('subPeriodSelect').value;
    const container = document.getElementById('subResultsContainer');
    container.innerHTML = '<div style="text-align:center; padding:20px; color:var(--il-muted);">⏳ Checking school schedules...</div>';

    fetch(`/school/free-teachers-handler.php?day=${encodeURIComponent(day)}&period=${encodeURIComponent(period)}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                container.innerHTML = '<div class="alert alert-error" style="margin:10px 0;">' + (data.error || 'Failed to load teacher availability.') + '</div>';
                return;
            }
            let html = '';
            html += `<h4 style="margin:0 0 10px; font-size:14px; color:#15803D;">✅ Available / Free Teachers (${data.free.length})</h4>`;
            if (data.free.length === 0) {
                html += '<p class="muted" style="font-size:12.5px; margin-bottom:16px;">All teachers are currently assigned to classes during this period.</p>';
            } else {
                html += '<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:18px;">';
                data.free.forEach(t => {
                    html += `
                        <div class="card p-2" style="background:var(--surface-2); border:1px solid #BBF7D0; border-radius:10px; padding:10px 14px;">
                            <strong>${t.teacher_name}</strong>
                            <div style="font-size:12px; color:#15803D;">✨ Free to Substitute</div>
                            <div style="font-size:11.5px; color:var(--il-muted);">${t.primary_subject || 'General Studies'}</div>
                        </div>
                    `;
                });
                html += '</div>';
            }

            html += `<h4 style="margin:16px 0 10px; font-size:14px; color:#B45309;">⚠️ Teachers On Duty (${data.busy.length})</h4>`;
            if (data.busy.length > 0) {
                html += '<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">';
                data.busy.forEach(t => {
                    const slot = t.busy_slot || {};
                    html += `
                        <div class="card p-2" style="background:var(--surface-2); border:1px solid var(--il-border); border-radius:10px; padding:10px 14px; opacity:0.85;">
                            <strong>${t.teacher_name}</strong>
                            <div style="font-size:12px; color:#B45309;">Busy in ${slot.class_level || 'Class'} (${slot.section || 'A'})</div>
                            <div style="font-size:11.5px; color:var(--il-muted);">${slot.subject_name || ''} · ${slot.room_number ? 'Room ' + slot.room_number : ''}</div>
                        </div>
                    `;
                });
                html += '</div>';
            }
            container.innerHTML = html;
        })
        .catch(err => {
            container.innerHTML = '<div class="alert alert-error">Network error checking teacher schedules.</div>';
        });
}
</script>

<!-- MODAL FOR TEACHER SUBSTITUTION & FREE PERIODS -->
<div id="subModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:99999; align-items:flex-start; justify-content:center; padding:50px 20px; overflow-y:auto; backdrop-filter:blur(5px);">
    <div class="card modal" style="background:var(--surface-1); color:var(--il-text); border:1px solid var(--il-border); border-radius:18px; max-width:680px; width:100%; max-height:calc(100vh - 100px); overflow-y:auto; padding:26px; box-shadow:0 24px 60px rgba(0,0,0,0.35); margin:0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <div>
                <h3 style="margin:0; font-size:19px;">🔄 Teacher Substitution & Free Periods</h3>
                <p class="muted" style="margin:2px 0 0; font-size:13px;">Find available teachers who have a free period slot to substitute for absent staff.</p>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeSubModal()">&times;</button>
        </div>

        <div style="display:flex; gap:12px; margin-bottom:16px; background:var(--surface-2); padding:12px 16px; border-radius:12px; border:1px solid var(--il-border); align-items:center;">
            <div style="flex:1;">
                <label class="form-label" style="font-size:11.5px; margin-bottom:2px;">Select Day</label>
                <select id="subDaySelect" class="form-control" onchange="fetchFreeTeachers()" style="width:100%;">
                    <?php foreach (['monday','tuesday','wednesday','thursday','friday','saturday'] as $d): ?>
                        <option value="<?= $d ?>" <?= strtolower(date('l')) === $d ? 'selected' : '' ?>><?= ucfirst($d) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:1;">
                <label class="form-label" style="font-size:11.5px; margin-bottom:2px;">Select Period Slot</label>
                <select id="subPeriodSelect" class="form-control" onchange="fetchFreeTeachers()" style="width:100%;">
                    <?php foreach ($periods as $p): ?>
                        <option value="<?= (int)$p['period_number'] ?>"><?= Helper::e($p['label']) ?> (<?= Timetable::formatTime12($p['start']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div id="subResultsContainer" style="min-height:120px;"></div>

        <div style="display:flex; justify-content:flex-end; margin-top:16px; border-top:1px solid var(--il-border); padding-top:14px;">
            <button type="button" class="btn btn-secondary" onclick="closeSubModal()">Close</button>
        </div>
    </div>
</div>

<style>
@media print {
    body { background: #fff !important; color: #000 !important; padding: 0 !important; }
    .admin-side, .navbar, .dashboard-header, .card:first-of-type, .no-print, .theme-toggle-btn, .footer, button, .modal, #subModal, #bellModal, #addClassModal, #addSectionModal { display: none !important; }
    .admin-main { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    .only-print-header { display: block !important; }
    .card { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; }
    table { width: 100% !important; border: 1.5px solid #000 !important; border-collapse: collapse !important; font-size: 11px !important; }
    th, td { border: 1px solid #444 !important; padding: 8px 6px !important; color: #000 !important; }
    th { background: #f2f2f2 !important; font-weight: 800 !important; }
}
</style>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
