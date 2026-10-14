<?php
use App\Auth;
use App\Helper;
use App\Timetable;

Auth::requireRole('teacher');
$user = Auth::user();
$teacherId = (int)$user['id'];

$slots = Timetable::getTeacherSchedule($teacherId);

$pageTitle = 'My Teaching Routine & Timetable — Teacher Hub';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/teacher/_sidebar.php'; ?>
    <div>
        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker">🗓️ WEEKLY PERIODS</span>
                <h1 style="margin:2px 0 0;">My Teaching Routine</h1>
                <p class="muted">Your weekly teaching schedule across assigned school classes, sections, and laboratories.</p>
            </div>
            <?php if (!empty($user['school_id'])): ?>
                <div style="display:flex; gap:8px; align-items:center;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="openTeacherAddSectionModal()">
                        ➕ Add Section
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <div class="card" style="padding:0; overflow:hidden;">
            <?php if (empty($slots)): ?>
                <div style="padding:48px 24px; text-align:center; color:var(--il-muted);">
                    <div style="font-size:38px; margin-bottom:8px;">📅</div>
                    <h3>No period slots assigned yet</h3>
                    <p class="muted">Your school administrator has not scheduled your classes in the school timetable yet.</p>
                </div>
            <?php else: ?>
                <table class="table" style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--il-border); text-align:left; background:var(--surface-0); color:var(--il-muted); font-size:12px; text-transform:uppercase;">
                            <th style="padding:12px 18px;">Day</th>
                            <th style="padding:12px 18px;">Period & Timing</th>
                            <th style="padding:12px 18px;">Class & Section</th>
                            <th style="padding:12px 18px;">Subject</th>
                            <th style="padding:12px 18px;">Room / Lab</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($slots as $s): ?>
                            <tr style="border-bottom:1px solid var(--il-border);">
                                <td style="padding:12px 18px; font-weight:700; text-transform:capitalize;">
                                    <?= Helper::e($s['day_of_week']) ?>
                                </td>
                                <td style="padding:12px 18px;">
                                    <strong>Period <?= (int)$s['period_number'] ?></strong>
                                    <div class="muted" style="font-size:12px; font-family:'JetBrains Mono', monospace;"><?= Timetable::formatTime12($s['start_time']) ?> - <?= Timetable::formatTime12($s['end_time']) ?></div>
                                </td>
                                <td style="padding:12px 18px; font-weight:600;">
                                    <?= Helper::e($s['class_level']) ?> (Sec <?= Helper::e($s['section']) ?>)
                                </td>
                                <td style="padding:12px 18px;">
                                    <span class="badge" style="background:#EFF6FF; color:#1E40AF; font-weight:700;">
                                        <?= Helper::e($s['subject_name']) ?>
                                    </span>
                                </td>
                                <td style="padding:12px 18px; color:var(--il-muted);">
                                    <?= !empty($s['room_number']) ? 'Room ' . Helper::e($s['room_number']) : 'Standard Classroom' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($user['school_id'])): ?>
<!-- MODAL TO ADD SECTION FOR TEACHER -->
<div id="teacherAddSectionModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(4px);">
    <div class="card" style="background:var(--surface-1); color:var(--il-text); border:1px solid var(--il-border); border-radius:16px; max-width:440px; width:100%; padding:24px; box-shadow:0 20px 50px rgba(0,0,0,0.3);">
        <h3 style="margin-top:0; font-size:18px;">➕ Add Section</h3>
        <p class="muted" style="font-size:13px; margin-bottom:16px;">Add a section to your school's class routine (e.g. Section F, Rose, Section Blue).</p>
        <form method="post" action="/school/timetable-save-handler.php">
            <input type="hidden" name="action" value="add_section">
            <input type="hidden" name="redirect_to" value="/teacher/timetable">
            <input type="hidden" name="csrf" value="<?= Helper::e(\App\Session::csrfToken()) ?>">

            <div class="form-group" style="margin-bottom:18px;">
                <label class="form-label" style="font-weight:600; display:block; margin-bottom:4px;">Section Name / Identifier *</label>
                <input type="text" name="section_name" class="form-control" placeholder="e.g. F or Rose or Section 2" required style="width:100%;">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-secondary" onclick="closeTeacherAddSectionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Section</button>
            </div>
        </form>
    </div>
</div>
<script>
function openTeacherAddSectionModal() {
    document.getElementById('teacherAddSectionModal').style.display = 'flex';
}
function closeTeacherAddSectionModal() {
    document.getElementById('teacherAddSectionModal').style.display = 'none';
}
</script>
<?php endif; ?>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
