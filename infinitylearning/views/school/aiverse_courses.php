<?php
declare(strict_types=1);

use App\Auth;
use App\Aiverse;
use App\Helper;
use App\School;
use App\Session;
use App\Database;

$schoolId = Auth::requireSchoolAdmin();
$school = School::find($schoolId);

$allCourses = Aiverse::getCourses('all');
$allEnrollments = Aiverse::getAllEnrollments();

// Group enrollments by course_id
$scholarsByCourse = [];
$enrollCounts = [];
foreach ($allEnrollments as $en) {
    $cid = (int)$en['course_id'];
    if (!isset($scholarsByCourse[$cid])) {
        $scholarsByCourse[$cid] = [];
    }
    $scholarsByCourse[$cid][] = [
        'student_user_id' => (int)$en['student_user_id'],
        'student_name'    => $en['student_name'] ?: 'Scholar #' . $en['student_user_id'],
        'student_email'   => $en['student_email'] ?? '',
        'student_phone'   => $en['student_phone'] ?? '',
        'city'            => $en['city'] ?? '',
        'district'        => $en['district'] ?? '',
        'state'           => $en['state'] ?? '',
        'amount_due'      => (float)($en['amount_due'] ?? $en['course_fee']),
        'amount_paid'     => (float)($en['amount_paid'] ?? 0),
        'payment_status'  => $en['payment_status'] ?? 'pending',
        'enrolled_at'     => date('d M Y', strtotime($en['created_at'])),
    ];
}

foreach ($allCourses as $c) {
    $cid = (int)$c['id'];
    $enrollCounts[$cid] = count($scholarsByCourse[$cid] ?? []);
}

$pageTitle = 'Manage AI Courses — ' . ($school['name'] ?? 'AIVERSE');
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php require VIEW_PATH . '/school/_sidebar.php'; ?>
    <div style="min-width:0; width:100%;">
        <!-- AIVERSE ADMIN SUB-NAVIGATION TABS -->
        <div style="display:flex; gap:8px; border-bottom:1.5px solid var(--il-border); margin-bottom:20px; padding-bottom:10px; overflow-x:auto;">
            <a href="/school/courses" class="btn btn-sm btn-primary" style="border-radius:999px; font-weight:700;">🤖 AI Courses &amp; Curriculum</a>
            <a href="/school/bundles" class="btn btn-sm btn-outline-secondary" style="border-radius:999px; font-weight:700;">📦 Course Bundles &amp; Discounts</a>
            <a href="/school/banners" class="btn btn-sm btn-outline-secondary" style="border-radius:999px; font-weight:700;">📢 Promo Banners</a>
        </div>

        <div class="dashboard-header" style="flex-wrap:wrap; gap:16px;">
            <div>
                <span class="kicker">🤖 AIVERSE CURRICULUM</span>
                <h1 style="margin:2px 0 0;">AI Courses &amp; Curriculum</h1>
                <p class="muted">Publish new AI programs, edit course modules, configure fees, and manage enrolled scholars.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openCourseModal(null)">+ Publish New AI Course</button>
        </div>

        <?php if ($msg = Session::flash('success')): ?>
            <div class="alert alert-success" style="margin-bottom:20px; font-weight:600; font-size:14px; border-radius:10px;">
                <?= Helper::e($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($msg = Session::flash('info')): ?>
            <div class="alert alert-info" style="margin-bottom:20px; font-weight:600; font-size:14px; border-radius:10px;">
                <?= Helper::e($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($msg = Session::flash('error')): ?>
            <div class="alert alert-danger" style="margin-bottom:20px; font-weight:600; font-size:14px; border-radius:10px;">
                <?= Helper::e($msg) ?>
            </div>
        <?php endif; ?>

        <!-- COURSES LIST TABLE -->
        <div class="card" style="border:1.5px solid var(--il-border); border-radius:16px; overflow:hidden; padding:0; margin-bottom:28px;">
            <div style="padding:16px 20px; border-bottom:1px solid var(--il-border); display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0; font-size:1.15rem;">All Published &amp; Draft Courses</h3>
                <span class="badge badge-brand"><?= count($allCourses) ?> Total Tracks</span>
            </div>

            <div style="overflow-x:auto;">
                <table class="table" style="width:100%; border-collapse:collapse; font-size:13px; margin:0;">
                    <thead>
                        <tr style="background:var(--surface-0); border-bottom:2px solid var(--il-border); text-align:left; font-size:11px; text-transform:uppercase; color:var(--il-muted);">
                            <th style="padding:12px 14px;">Course Track</th>
                            <th style="padding:12px 14px;">Level</th>
                            <th style="padding:12px 14px;">Duration</th>
                            <th style="padding:12px 14px;">Fee</th>
                            <th style="padding:12px 14px;">Enrolled Scholars</th>
                            <th style="padding:12px 14px;">Status</th>
                            <th style="padding:12px 14px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allCourses)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:36px; color:var(--il-muted);">
                                    No courses found. Click "+ Publish New AI Course" to add one.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allCourses as $c): ?>
                                <?php $count = $enrollCounts[(int)$c['id']] ?? 0; ?>
                                <tr style="border-bottom:1px solid var(--il-border);">
                                    <td style="padding:12px 14px; max-width:280px;">
                                        <strong><?= Helper::e($c['title']) ?></strong>
                                        <?php if (!empty($c['badge'])): ?>
                                            <span class="badge badge-primary" style="font-size:9.5px; padding:2px 6px; margin-left:4px;"><?= Helper::e($c['badge']) ?></span>
                                        <?php endif; ?>
                                        <div class="muted" style="font-size:11.5px; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                            <?= Helper::e($c['tagline'] ?: $c['overview']) ?>
                                        </div>
                                    </td>
                                    <td style="padding:12px 14px; white-space:nowrap;">
                                        <?= Helper::e($c['level'] ?? 'All Levels') ?>
                                    </td>
                                    <td style="padding:12px 14px; white-space:nowrap;">
                                        <?= Helper::e($c['duration'] ?? '8 Weeks') ?>
                                    </td>
                                    <td style="padding:12px 14px; font-family:'JetBrains Mono', monospace; font-weight:800; color:var(--il-primary); white-space:nowrap;">
                                        ₹<?= number_format((float)$c['fee'], 2) ?>
                                    </td>
                                    <td style="padding:12px 14px; white-space:nowrap;">
                                        <?php if ($count > 0): ?>
                                            <button type="button" class="btn btn-xs btn-outline-primary" style="font-size:11px; font-weight:700; padding:3px 8px;" onclick="viewCourseScholars(<?= (int)$c['id'] ?>, '<?= Helper::e(addslashes($c['title'])) ?>')">
                                                👥 <?= $count ?> Scholar<?= $count === 1 ? '' : 's' ?> (Manage)
                                            </button>
                                        <?php else: ?>
                                            <span class="muted" style="font-size:11.5px;">0 Scholars</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:12px 14px; white-space:nowrap;">
                                        <span class="badge badge-<?= $c['status'] === 'published' ? 'success' : 'warning' ?>" style="text-transform:uppercase; font-size:10px;">
                                            <?= Helper::e($c['status']) ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px 14px; text-align:right; white-space:nowrap;">
                                        <div style="display:flex; justify-content:flex-end; gap:6px; align-items:center;">
                                            <button type="button" class="btn btn-xs btn-secondary" style="font-size:11px; padding:3px 8px;" onclick="openCourseModal(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>)">
                                                ✏️ Edit
                                            </button>
                                            <form method="POST" action="/school/courses/delete" style="display:inline; margin:0;" onsubmit="return confirm('Are you sure you want to delete <?= Helper::e(addslashes($c['title'])) ?>?');">
                                                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                                                <input type="hidden" name="course_id" value="<?= (int)$c['id'] ?>">
                                                <button type="submit" class="btn btn-xs btn-outline-danger" style="font-size:11px; padding:3px 8px; color:#DC2626; border-color:#FECACA;" title="Delete Course">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- MANAGE / REMOVE ENROLLED SCHOLARS MODAL -->
<div id="scholarsModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:16px; overflow-y:auto; backdrop-filter:blur(4px);">
    <div style="background:var(--surface-0); border:1px solid var(--il-border); border-radius:18px; max-width:760px; width:100%; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px rgba(0,0,0,0.25); position:relative; padding:28px;">
        
        <button type="button" onclick="closeScholarsModal()" style="position:absolute; top:20px; right:20px; background:var(--surface-2); border:none; width:34px; height:34px; border-radius:50%; font-size:18px; cursor:pointer; color:var(--il-text); display:flex; align-items:center; justify-content:center;">&times;</button>

        <span class="kicker" style="color:var(--il-primary); font-size:11px; font-weight:800; letter-spacing:0.04em;">ENROLLED SCHOLARS</span>
        <h2 id="scholarsModalTitle" style="font-size:1.45rem; margin:2px 0 16px;">Course Enrolled Scholars</h2>

        <div id="scholarsListContainer"></div>

        <div style="display:flex; justify-content:flex-end; margin-top:20px; border-top:1px solid var(--il-border); padding-top:14px;">
            <button type="button" class="btn btn-secondary" onclick="closeScholarsModal()">Close</button>
        </div>
    </div>
</div>

<!-- PUBLISH / EDIT COURSE MODAL -->
<div id="courseEditModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:9999; align-items:center; justify-content:center; padding:16px; overflow-y:auto; backdrop-filter:blur(4px);">
    <div style="background:var(--surface-0); border:1px solid var(--il-border); border-radius:18px; max-width:740px; width:100%; max-height:92vh; overflow-y:auto; box-shadow:0 20px 40px rgba(0,0,0,0.25); position:relative; padding:28px;">
        
        <button type="button" onclick="closeCourseModal()" style="position:absolute; top:20px; right:20px; background:var(--surface-2); border:none; width:34px; height:34px; border-radius:50%; font-size:18px; cursor:pointer; color:var(--il-text); display:flex; align-items:center; justify-content:center;">&times;</button>

        <h2 id="modalHeading" style="font-size:1.45rem; margin:0 0 16px;">Publish New AI Course</h2>

        <form method="POST" action="/school/courses/save">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
            <input type="hidden" name="course_id" id="editCourseId" value="">

            <div class="grid grid-2" style="gap:16px; margin-bottom:14px;">
                <div>
                    <label class="label">Course Title *</label>
                    <input type="text" name="title" id="inpTitle" class="form-control" required placeholder="e.g. Generative AI &amp; LLM Engineering">
                </div>
                <div>
                    <label class="label">Course Slug / URL Identifier</label>
                    <input type="text" name="slug" id="inpSlug" class="form-control" placeholder="e.g. gen-ai-llm-engineering">
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label class="label">Short Tagline *</label>
                <input type="text" name="tagline" id="inpTagline" class="form-control" required placeholder="e.g. Master Prompt Engineering, LangChain, RAG architectures &amp; Vector DBs">
            </div>

            <div class="grid grid-3" style="gap:14px; margin-bottom:14px;">
                <div>
                    <label class="label">Category</label>
                    <input type="text" name="category" id="inpCategory" class="form-control" value="Artificial Intelligence">
                </div>
                <div>
                    <label class="label">Level</label>
                    <select name="level" id="inpLevel" class="form-control">
                        <option value="Complete Beginner">Complete Beginner</option>
                        <option value="Beginner to Intermediate" selected>Beginner to Intermediate</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Intermediate to Advanced">Intermediate to Advanced</option>
                        <option value="Advanced">Advanced</option>
                        <option value="All Levels">All Levels</option>
                    </select>
                </div>
                <div>
                    <label class="label">Duration</label>
                    <input type="text" name="duration" id="inpDuration" class="form-control" placeholder="e.g. 8 Weeks · 32 Live Hours">
                </div>
            </div>

            <div class="grid grid-3" style="gap:14px; margin-bottom:14px;">
                <div>
                    <label class="label">Course Fee (₹) *</label>
                    <input type="number" step="0.01" name="fee" id="inpFee" class="form-control" required placeholder="4999.00">
                </div>
                <div>
                    <label class="label">Badge Tag</label>
                    <input type="text" name="badge" id="inpBadge" class="form-control" placeholder="e.g. Featured Track, Trending, Popular">
                </div>
                <div>
                    <label class="label">Publish Status</label>
                    <select name="status" id="inpStatus" class="form-control">
                        <option value="published" selected>Published (Visible to Students)</option>
                        <option value="draft">Draft (Hidden)</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label class="label">Course Overview / Description</label>
                <textarea name="overview" id="inpOverview" class="form-control" rows="3" placeholder="Explain what students will experience in this course..."></textarea>
            </div>

            <div style="margin-bottom:14px;">
                <label class="label">What This Course Provides (Key Outcomes - 1 per line)</label>
                <textarea name="outcomes" id="inpOutcomes" class="form-control" rows="4" placeholder="• Master prompt engineering and few-shot reasoning&#10;• Build production RAG pipelines&#10;• Deploy with FastAPI and Docker"></textarea>
            </div>

            <div style="margin-bottom:14px;">
                <label class="label">Curriculum / Modules Breakdown (1 per line)</label>
                <textarea name="curriculum" id="inpCurriculum" class="form-control" rows="4" placeholder="• Module 1: Foundations of Foundation Models&#10;• Module 2: Prompt Engineering &amp; JSON Outputs&#10;• Module 3: Vector Embeddings &amp; RAG&#10;• Module 4: Capstone Deployment"></textarea>
            </div>

            <div class="grid grid-2" style="gap:14px; margin-bottom:20px;">
                <div>
                    <label class="label">Prerequisites</label>
                    <input type="text" name="prerequisites" id="inpPrerequisites" class="form-control" placeholder="e.g. Basic Python knowledge">
                </div>
                <div>
                    <label class="label">What's Included</label>
                    <input type="text" name="includes" id="inpIncludes" class="form-control" placeholder="e.g. 32 Live Hours, Verified Certificate, Discord">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--il-border); padding-top:16px;">
                <button type="button" class="btn btn-secondary" onclick="closeCourseModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btnSubmitCourse">Publish Course</button>
            </div>
        </form>

    </div>
</div>

<script>
var scholarsData = <?= json_encode($scholarsByCourse) ?>;

function viewCourseScholars(courseId, courseTitle) {
    document.getElementById('scholarsModalTitle').innerText = 'Scholars Enrolled in: ' + courseTitle;
    var list = scholarsData[courseId] || [];
    var container = document.getElementById('scholarsListContainer');

    if (list.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:30px; color:var(--il-muted);">No scholars currently enrolled in this track.</div>';
    } else {
        var html = `
            <table class="table" style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="border-bottom:2px solid var(--il-border); text-align:left; font-size:11px; text-transform:uppercase; color:var(--il-muted); background:var(--surface-0);">
                        <th style="padding:10px 12px;">Scholar Details</th>
                        <th style="padding:10px 12px;">Location</th>
                        <th style="padding:10px 12px;">Enrolled Date</th>
                        <th style="padding:10px 12px;">Payment Status</th>
                        <th style="padding:10px 12px; text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
        `;
        list.forEach(function(s) {
            var badgeClass = s.payment_status === 'paid' ? 'success' : (s.payment_status === 'partial' ? 'primary' : 'warning');
            html += `
                <tr style="border-bottom:1px solid var(--il-border);">
                    <td style="padding:12px 10px;">
                        <strong>${escapeHtml(s.student_name)}</strong>
                        <div class="muted" style="font-size:11.5px;">${escapeHtml(s.student_email)} · ${escapeHtml(s.student_phone)}</div>
                    </td>
                    <td style="padding:12px 10px;">
                        <div>${escapeHtml(s.city || '—')}</div>
                        <span class="muted" style="font-size:11.5px;">${escapeHtml(s.district || '')} ${escapeHtml(s.state ? ', ' + s.state : '')}</span>
                    </td>
                    <td style="padding:12px 10px;">${escapeHtml(s.enrolled_at)}</td>
                    <td style="padding:12px 10px;">
                        <span class="badge badge-${badgeClass}" style="text-transform:uppercase; font-size:10px;">${escapeHtml(s.payment_status)}</span>
                    </td>
                    <td style="padding:12px 10px; text-align:right;">
                        <form method="POST" action="/school/courses/unenroll" style="margin:0; display:inline;" onsubmit="return confirm('Remove scholar ${escapeHtml(s.student_name)} from this course? This will cancel their enrollment.');">
                            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                            <input type="hidden" name="course_id" value="${courseId}">
                            <input type="hidden" name="student_user_id" value="${s.student_user_id}">
                            <input type="hidden" name="redirect_to" value="/school/courses">
                            <button type="submit" class="btn btn-xs btn-outline-danger" style="color:#DC2626; border-color:#FECACA; font-weight:700; font-size:11px; padding:4px 8px;">
                                Remove Scholar ✕
                            </button>
                        </form>
                    </td>
                </tr>
            `;
        });
        html += '</tbody></table>';
        container.innerHTML = html;
    }

    document.getElementById('scholarsModal').style.display = 'flex';
}

function closeScholarsModal() {
    document.getElementById('scholarsModal').style.display = 'none';
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function openCourseModal(course) {
    if (course) {
        document.getElementById('modalHeading').innerText = 'Edit AI Course: ' + course.title;
        document.getElementById('btnSubmitCourse').innerText = 'Save Changes';
        document.getElementById('editCourseId').value = course.id;
        document.getElementById('inpTitle').value = course.title || '';
        document.getElementById('inpSlug').value = course.slug || '';
        document.getElementById('inpTagline').value = course.tagline || '';
        document.getElementById('inpCategory').value = course.category || 'Artificial Intelligence';
        document.getElementById('inpLevel').value = course.level || 'Beginner to Intermediate';
        document.getElementById('inpDuration').value = course.duration || '8 Weeks';
        document.getElementById('inpFee').value = course.fee || '0';
        document.getElementById('inpBadge').value = course.badge || '';
        document.getElementById('inpStatus').value = course.status || 'published';
        document.getElementById('inpOverview').value = course.overview || '';
        document.getElementById('inpOutcomes').value = course.outcomes || '';
        document.getElementById('inpCurriculum').value = course.curriculum || '';
        document.getElementById('inpPrerequisites').value = course.prerequisites || '';
        document.getElementById('inpIncludes').value = course.includes || '';
    } else {
        document.getElementById('modalHeading').innerText = 'Publish New AI Course';
        document.getElementById('btnSubmitCourse').innerText = 'Publish Course';
        document.getElementById('editCourseId').value = '';
        document.getElementById('inpTitle').value = '';
        document.getElementById('inpSlug').value = '';
        document.getElementById('inpTagline').value = '';
        document.getElementById('inpCategory').value = 'Artificial Intelligence';
        document.getElementById('inpLevel').value = 'Beginner to Intermediate';
        document.getElementById('inpDuration').value = '8 Weeks';
        document.getElementById('inpFee').value = '4999.00';
        document.getElementById('inpBadge').value = 'Featured Track';
        document.getElementById('inpStatus').value = 'published';
        document.getElementById('inpOverview').value = '';
        document.getElementById('inpOutcomes').value = '';
        document.getElementById('inpCurriculum').value = '';
        document.getElementById('inpPrerequisites').value = 'Curiosity and interest in Artificial Intelligence.';
        document.getElementById('inpIncludes').value = 'Live Classes, Verified Certificate, Capstone Project';
    }
    document.getElementById('courseEditModal').style.display = 'flex';
}

function closeCourseModal() {
    document.getElementById('courseEditModal').style.display = 'none';
}

window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('courseEditModal')) {
        closeCourseModal();
    }
    if (e.target === document.getElementById('scholarsModal')) {
        closeScholarsModal();
    }
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
