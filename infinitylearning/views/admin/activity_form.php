<?php
use App\Activity;
use App\Auth;
use App\Helper;
use App\Session;
use App\Database;

Auth::requireLogin();
if (Auth::role() !== 'admin') { http_response_code(403); exit('Forbidden'); }

$editing = isset($_GET['id']);
$activity = $editing ? Activity::find((int)$_GET['id']) : null;
if ($editing && !$activity) { Helper::redirect('/admin/activities'); }

// School admin can only edit their own school's activities
if ($editing && Auth::isSchoolAdmin() && (int)$activity['school_id'] !== (int)Auth::schoolId()) {
    http_response_code(403); exit('You cannot edit this activity.');
}

// Super admin sees schools dropdown for scope
$schools = Auth::isSuperAdmin()
    ? Database::connect()->query("SELECT id, name FROM schools WHERE status='active' ORDER BY name")->fetchAll()
    : [];

$pageTitle = ($editing ? 'Edit' : 'New') . ' Activity — Admin';
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php
    if (Auth::isSchoolAdmin()) {
        require VIEW_PATH . '/school/_sidebar.php';
    } else {
        require VIEW_PATH . '/admin/_sidebar.php';
    }
    ?>
    <div>
        <h1><?= $editing ? 'Edit Activity' : 'New Activity' ?></h1>
        <p class="muted">
            <?php if (Auth::isSchoolAdmin()): ?>
                Only your school's students and teachers will see this activity.
            <?php else: ?>
                Leave scope as "Global" to publish to everyone, or pick a school to scope it.
            <?php endif; ?>
        </p>

        <form method="post" action="/admin/activity-save-handler.php" enctype="multipart/form-data" class="card mt-2">
            <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
            <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int)$activity['id'] ?>"><?php endif; ?>

            <?php if (Auth::isSuperAdmin()): ?>
                <div class="form-group">
                    <label>Scope *</label>
                    <select name="school_id" required>
                        <option value="" <?= !$editing || empty($activity['school_id']) ? 'selected' : '' ?>>
                            🌐 Global — everyone sees this
                        </option>
                        <?php foreach ($schools as $s): ?>
                            <option value="<?= (int)$s['id'] ?>"
                                <?= $editing && (int)$activity['school_id'] === (int)$s['id'] ? 'selected' : '' ?>>
                                🏫 <?= Helper::e($s['name']) ?> only
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Choose "Global" for platform-wide events, or select a school to keep it private.</small>
                </div>
            <?php else: ?>
                <div class="alert alert-info" style="margin-bottom:1rem">
                    This activity will be visible to <strong><?= Helper::e(Auth::user()['email']) ?></strong>'s school only.
                </div>
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required value="<?= Helper::e($activity['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <?php foreach (ACTIVITY_CATEGORIES as $slug => $label): ?>
                            <option value="<?= $slug ?>" <?= ($activity['category'] ?? '') === $slug ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Short description</label>
                <input type="text" name="short_description" maxlength="300"
                       value="<?= Helper::e($activity['short_description'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Full description</label>
                <textarea name="description" rows="6"><?= Helper::e($activity['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Event date</label>
                    <input type="date" name="event_date" value="<?= Helper::e($activity['event_date'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Registration deadline</label>
                    <input type="date" name="registration_deadline"
                           value="<?= Helper::e($activity['registration_deadline'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Start time</label>
                    <input type="time" name="start_time"
                           value="<?= Helper::e(substr($activity['start_time'] ?? '', 0, 5)) ?>">
                </div>
                <div class="form-group">
                    <label>End time</label>
                    <input type="time" name="end_time"
                           value="<?= Helper::e(substr($activity['end_time'] ?? '', 0, 5)) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Venue</label>
                    <input type="text" name="venue" value="<?= Helper::e($activity['venue'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Organizer</label>
                    <input type="text" name="organizer" value="<?= Helper::e($activity['organizer'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Eligibility</label>
                    <input type="text" name="eligibility" value="<?= Helper::e($activity['eligibility'] ?? '') ?>"
                           placeholder="e.g. Ages 4-18, Grades 5-8">
                </div>
                <div class="form-group">
                    <label>Max participants</label>
                    <input type="number" name="max_participants" min="0"
                           value="<?= Helper::e((string)($activity['max_participants'] ?? '')) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Fee (₹)</label>
                    <input type="number" name="fee" step="0.01" min="0"
                           value="<?= Helper::e((string)($activity['fee'] ?? '0')) ?>">
                </div>
                <div class="form-group">
                    <label>External registration URL</label>
                    <input type="url" name="registration_url"
                           value="<?= Helper::e($activity['registration_url'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Contact email</label>
                    <input type="email" name="contact_email"
                           value="<?= Helper::e($activity['contact_email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Contact phone</label>
                    <input type="tel" name="contact_phone"
                           value="<?= Helper::e($activity['contact_phone'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Tags (comma separated)</label>
                <input type="text" name="tags" value="<?= Helper::e($activity['tags'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Cover image <?= $editing ? '(leave empty to keep current)' : '' ?></label>
                <input type="file" name="cover_image" accept="image/*">
                <?php if ($editing && $activity['cover_image']): ?>
                    <small>Current: <img src="<?= Helper::e($activity['cover_image']) ?>" alt=""
                                         style="height:40px;vertical-align:middle;margin-left:.4rem;border-radius:6px"></small>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['published'=>'Published','draft'=>'Draft','closed'=>'Closed','archived'=>'Archived'] as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($activity['status'] ?? 'published') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Featured</label>
                    <select name="featured">
                        <option value="0" <?= empty($activity['featured']) ? 'selected' : '' ?>>No</option>
                        <option value="1" <?= !empty($activity['featured']) ? 'selected' : '' ?>>Yes (pin to top)</option>
                    </select>
                </div>
            </div>

            <div class="flex mt-2">
                <button class="btn btn-primary"><?= $editing ? 'Save changes' : 'Publish activity' ?></button>
                <a href="/admin/activities" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>