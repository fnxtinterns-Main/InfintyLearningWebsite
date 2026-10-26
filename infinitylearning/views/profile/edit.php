<?php
use App\Auth;
use App\Helper;
use App\Session;
use App\Database;

Auth::requireLogin();
$loggedInUser = Auth::user();
$loggedInRole = $loggedInUser['role'];

$isManagingStudent = false;
$targetUserId = (int)($_GET['user_id'] ?? 0);

if ($targetUserId > 0 && in_array($loggedInRole, ['admin', 'school'], true)) {
    $targetUser = Auth::findById($targetUserId);
    if ($targetUser && ($loggedInRole === 'admin' || (int)$targetUser['school_id'] === (int)$loggedInUser['school_id'])) {
        $user = $targetUser;
        $role = $targetUser['role'];
        $isManagingStudent = ($role === 'student');
    } else {
        $user = $loggedInUser;
        $role = $loggedInRole;
    }
} else {
    $user = $loggedInUser;
    $role = $loggedInRole;
}

$profile = $role === 'student'
    ? Auth::findStudentByUserId((int)$user['id'])
    : ($role === 'teacher' ? Auth::findTeacherByUserId((int)$user['id']) : null);

$school = null;
if ($role === 'school' || !empty($user['school_id'])) {
    $school = \App\School::find((int)($user['school_id'] ?? $user['id']));
}

$parent = null;
if ($role === 'student') {
    $stmt = Database::connect()->prepare("
        SELECT p.* FROM parents p
        JOIN student_parents sp ON sp.parent_id = p.id
        WHERE sp.student_user_id = ? AND sp.is_primary = 1
        LIMIT 1
    ");
    $stmt->execute([(int)$user['id']]);
    $parent = $stmt->fetch() ?: null;
}

$pageTitle = ($isManagingStudent ? 'Edit Student Record — ' : 'Edit Profile — ') . 'Infinity Learning';
$error   = Session::flash('error');
$success = Session::flash('success');
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section-sm">
    <div class="container-narrow" style="max-width:760px; margin:0 auto; width:100%;">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php if (!empty($profile['profile_image'])): ?>
                    <img src="<?= Helper::e($profile['profile_image']) ?>" alt="">
                <?php else: ?>
                    <span class="avatar-initial" data-role="<?= Helper::e($role) ?>"
                          style="width:100%;height:100%;font-size:2rem">
                        <?= Helper::e(mb_strtoupper(mb_substr($profile['full_name'] ?? $user['email'], 0, 1))) ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="profile-header-text">
                <h1><?= Helper::e($profile['full_name'] ?? ($school['name'] ?? 'Your Profile')) ?></h1>
                <p class="muted"><?= Helper::e($user['email']) ?> · <span class="badge badge-brand"><?= Helper::e(ucfirst($role)) ?></span></p>
                <?php if ($role === 'student'): ?>
                    <div style="display:flex; gap:8px; margin-top:8px; flex-wrap:wrap;">
                        <a href="/student/doubts" class="btn btn-primary btn-sm" style="font-weight:700;">💬 Student Doubt Portal</a>
                        <a href="/student?id=<?= (int)$user['id'] ?>" class="btn btn-secondary btn-sm">👀 View Full Profile</a>
                        <a href="/parent-pass?student_id=<?= (int)$user['id'] ?>" class="btn btn-outline-primary btn-sm">📱 Parent Progress Pass</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($error):   ?><div class="alert alert-error"><?= Helper::e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= Helper::e($success) ?></div><?php endif; ?>

        <!-- ============ PROFILE FORM ============ -->
        <div class="card mt-3">
            <h3>👤 Profile Information</h3>
            <div class="divider"></div>

            <?php if ($role === 'student' && !$isManagingStudent): ?>
                <div class="alert alert-info" style="margin-bottom:18px; border-left:4px solid #2563EB; background:rgba(37,99,235,0.06); color:var(--il-text);">
                    <strong style="color:#2563EB;">🔒 Official School Student Record (Read-Only)</strong>
                    <p style="margin:4px 0 0; font-size:13px; line-height:1.5; color:var(--il-muted);">
                        Your student profile is maintained by your school administration and cannot be edited directly. To request updates to your official student record, please contact your school office or class teacher.
                    </p>
                </div>

                <?php 
                $autoAge = Helper::calculateStudentAge(!empty($profile['age']) ? (int)$profile['age'] : null, $profile['created_at'] ?? null);
                ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?= Helper::e($user['email']) ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" value="<?= Helper::e(Helper::displayPhone($user['phone'])) ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                    </div>
                </div>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" value="<?= Helper::e($profile['full_name'] ?? '') ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Current Class</label>
                        <input type="text" value="<?= Helper::e($profile['current_class'] ?? '—') ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Registration Number</label>
                        <input type="text" value="<?= Helper::e($profile['registration_number'] ?? '—') ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Gender</label>
                        <input type="text" value="<?= Helper::e(ucfirst(str_replace('_',' ',(string)($profile['gender'] ?? '—')))) ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Age (Auto-updated)</label>
                        <input type="text" value="<?= $autoAge ? (int)$autoAge . ' years' : '—' ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                    </div>
                </div>

                <div class="form-group">
                    <label>School Name</label>
                    <input type="text" value="<?= Helper::e($profile['school_name'] ?? '—') ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>School Board</label>
                        <input type="text" value="<?= Helper::e($profile['school_board'] ?? '—') ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Academic Year</label>
                        <input type="text" value="<?= Helper::e($profile['academic_year'] ?? '—') ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" value="<?= Helper::e($profile['city'] ?? '—') ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <input type="text" value="<?= Helper::e($profile['state'] ?? '—') ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                    </div>
                </div>

                <?php if (!empty($parent)): ?>
                    <div class="divider"></div>
                    <h3 style="font-size:1rem;margin-bottom:.5rem">Parent / Guardian</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Parent Name</label>
                            <input type="text" value="<?= Helper::e($parent['full_name'] ?? '—') ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                        </div>
                        <div class="form-group">
                            <label>Relationship</label>
                            <input type="text" value="<?= Helper::e(ucfirst($parent['relationship'] ?? '—')) ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Parent Phone</label>
                            <input type="text" value="<?= Helper::e($parent['phone'] ?? '—') ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                        </div>
                        <div class="form-group">
                            <label>Parent Email</label>
                            <input type="text" value="<?= Helper::e($parent['email'] ?? '—') ?>" readonly style="background:var(--surface-2); cursor:not-allowed;">
                        </div>
                    </div>
                <?php endif; ?>

            <?php elseif ($role === 'student' && $isManagingStudent): ?>
                <!-- ADMIN EDITING STUDENT FORM -->
                <form method="post" action="/profile-handler.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                    <input type="hidden" name="target_user_id" value="<?= (int)$user['id'] ?>">

                    <div class="alert alert-info" style="margin-bottom:18px;">
                        <strong>✏️ School Administrator Mode</strong>
                        <p style="margin:4px 0 0; font-size:13px;">Editing official student record for <strong><?= Helper::e($profile['full_name'] ?? $user['email']) ?></strong>.</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="<?= Helper::e($user['email']) ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" value="<?= Helper::e(Helper::displayPhone($user['phone'])) ?>" disabled>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Profile Image</label>
                        <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp">
                        <small>JPG/PNG/WebP, max 4MB.</small>
                    </div>

                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" required value="<?= Helper::e($profile['full_name'] ?? '') ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Current Class / Grade *</label>
                            <select name="current_class">
                                <option value="">— select grade —</option>
                                <option value="Kindergarten" <?= ($profile['current_class'] ?? '') === 'Kindergarten' ? 'selected' : '' ?>>Kindergarten / Pre-K</option>
                                <?php for ($g = 1; $g <= 12; $g++): ?>
                                    <?php $gVal = "Grade {$g}"; ?>
                                    <option value="<?= $gVal ?>" <?= ($profile['current_class'] ?? '') === $gVal ? 'selected' : '' ?>><?= $gVal ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>School Board</label>
                            <select name="school_board">
                                <option value="">— select —</option>
                                <?php foreach (['CBSE','ICSE','State Board','IB','IGCSE','Other'] as $b): ?>
                                    <option value="<?= $b ?>" <?= ($profile['school_board'] ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Registration Number</label>
                            <input type="text" name="registration_number" value="<?= Helper::e($profile['registration_number'] ?? '') ?>" placeholder="e.g. REG-2026-001">
                        </div>
                        <div class="form-group">
                            <label>Roll Number</label>
                            <input type="text" name="roll_number" value="<?= Helper::e($profile['roll_number'] ?? '') ?>" placeholder="e.g. 15">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender">
                                <option value="">— select —</option>
                                <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other','prefer_not_to_say'=>'Prefer not to say'] as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= ($profile['gender'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Age</label>
                            <input type="number" name="age" min="4" max="80" value="<?= Helper::e((string)($profile['age'] ?? '')) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Academic Year</label>
                            <input type="text" name="academic_year" value="<?= Helper::e($profile['academic_year'] ?? '2025-2026') ?>" placeholder="e.g. 2025-2026">
                        </div>
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" value="<?= Helper::e($profile['city'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>State</label>
                            <input type="text" name="state" value="<?= Helper::e($profile['state'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Interests</label>
                            <input type="text" name="interests" value="<?= Helper::e($profile['interests'] ?? '') ?>" placeholder="e.g. Maths, Science, Robotics">
                        </div>
                    </div>

                    <div class="divider"></div>
                    <h3 style="font-size:1rem;margin-bottom:.5rem">Parent / Guardian</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Parent / Guardian Name</label>
                            <input type="text" name="guardian_name" value="<?= Helper::e($parent['full_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Relationship</label>
                            <select name="guardian_relationship">
                                <option value="">— select —</option>
                                <?php foreach (['father'=>'Father','mother'=>'Mother','guardian'=>'Guardian','other'=>'Other'] as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= ($parent['relationship'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Parent Phone</label>
                            <input type="tel" name="guardian_phone" value="<?= Helper::e($parent['phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Parent Email</label>
                            <input type="email" name="guardian_email" value="<?= Helper::e($parent['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Parent Occupation</label>
                        <input type="text" name="guardian_occupation" value="<?= Helper::e($parent['occupation'] ?? '') ?>">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">💾 Save Student Profile Changes</button>
                </form>

            <?php elseif ($role === 'teacher'): ?>
                <form method="post" action="/profile-handler.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="<?= Helper::e($user['email']) ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" value="<?= Helper::e(Helper::displayPhone($user['phone'])) ?>" disabled>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Profile Image</label>
                        <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp">
                        <small>JPG/PNG/WebP, max 4MB.</small>
                    </div>

                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" required value="<?= Helper::e($profile['full_name'] ?? '') ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender">
                                <option value="">— select —</option>
                                <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other','prefer_not_to_say'=>'Prefer not to say'] as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= ($profile['gender'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Years of Experience</label>
                            <input type="number" name="years_experience" min="0" max="60" value="<?= Helper::e((string)($profile['years_experience'] ?? '')) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Expert Field</label>
                        <input type="text" name="expert_field" value="<?= Helper::e($profile['expert_field'] ?? '') ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" value="<?= Helper::e($profile['city'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <input type="text" name="state" value="<?= Helper::e($profile['state'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Additional Expertise</label>
                        <input type="text" name="additional_expertise" value="<?= Helper::e($profile['additional_expertise'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Short Bio</label>
                        <textarea name="bio" rows="3"><?= Helper::e($profile['bio'] ?? '') ?></textarea>
                    </div>

                    <!-- TEACHER DIGITAL SIGNATURE -->
                    <div class="form-group" style="border:1px dashed var(--il-border); padding:14px; border-radius:10px; background:var(--surface-1);">
                        <label style="font-weight:700;">✍️ Teacher Official Signature</label>
                        <?php if (!empty($profile['signature_image'])): ?>
                            <div style="margin:8px 0;">
                                <img src="<?= Helper::e($profile['signature_image']) ?>" alt="Teacher Signature" style="max-height:48px; background:#fff; padding:4px 10px; border:1px solid var(--il-border); border-radius:6px; object-fit:contain;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="signature_image" accept="image/jpeg,image/png,image/webp">
                        <small class="muted" style="display:block; margin-top:4px;">Upload transparent PNG or scan of your signature (auto-populated on student report cards &amp; evaluations).</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">Save changes</button>
                </form>

            <?php elseif ($role === 'school'): ?>
                <form method="post" action="/profile-handler.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">

                    <div class="form-group">
                        <label>School Name *</label>
                        <input type="text" name="full_name" required value="<?= Helper::e($school['name'] ?? $user['email']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Principal / Head of Institution Name</label>
                        <input type="text" name="principal_name" value="<?= Helper::e($school['principal_name'] ?? '') ?>" placeholder="e.g. Dr. A. K. Sharma">
                    </div>

                    <div class="form-group" style="border:1px dashed var(--il-border); padding:14px; border-radius:10px; background:var(--surface-1);">
                        <label style="font-weight:700;">🖋️ Official Principal Signature</label>
                        <?php if (!empty($school['principal_signature'])): ?>
                            <div style="margin:8px 0;">
                                <img src="<?= Helper::e($school['principal_signature']) ?>" alt="Principal Signature" style="max-height:48px; background:#fff; padding:4px 10px; border:1px solid var(--il-border); border-radius:6px; object-fit:contain;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="principal_signature" accept="image/jpeg,image/png,image/webp">
                        <small class="muted" style="display:block; margin-top:4px;">Official signature of the Principal / Head of Institution (auto-populated on student report cards &amp; fee receipts).</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">Save School Details</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- ============ CHANGE PASSWORD ============ -->
        <div class="card mt-3" id="change-password">
            <h3>🔑 Change Password</h3>
            <p class="muted" style="font-size:.88rem">Keep your account secure.</p>
            <div class="divider"></div>

            <form method="post" action="/change-password-handler.php" autocomplete="off">
                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">

                <div class="form-group">
                    <div class="label-row">
                        <label>Current Password *</label>
                        <a href="/forgot" class="label-hint">Forgot password?</a>
                    </div>
                    <input type="password" name="current_password" required autocomplete="current-password">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>New Password *</label>
                        <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password *</label>
                        <input type="password" name="new_password_confirm" required autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="btn btn-secondary btn-block">Update password</button>
            </form>
        </div>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>