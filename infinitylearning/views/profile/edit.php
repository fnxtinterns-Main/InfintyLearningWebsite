<?php
use App\Auth;
use App\Helper;
use App\Session;
use App\Database;

Auth::requireLogin();
$user = Auth::user();
$role = $user['role'];
$profile = $role === 'student'
    ? Auth::findStudentByUserId((int)$user['id'])
    : ($role === 'teacher' ? Auth::findTeacherByUserId((int)$user['id']) : null);

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

$pageTitle = 'Edit Profile — Infinity Learning';
$error   = Session::flash('error');
$success = Session::flash('success');
require VIEW_PATH . '/layouts/header.php';
?>
<section class="section-sm">
    <div class="container-narrow">
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
                <h1><?= Helper::e($profile['full_name'] ?? 'Your Profile') ?></h1>
                <p class="muted"><?= Helper::e($user['email']) ?> · <span class="badge badge-brand"><?= Helper::e(ucfirst($role)) ?></span></p>
                <?php if ($role === 'student'): ?>
                    <div style="display:flex; gap:8px; margin-top:8px; flex-wrap:wrap;">
                        <a href="/student/doubts" class="btn btn-primary btn-sm" style="font-weight:700;">💬 Student Doubt Portal</a>
                        <a href="/student?id=<?= (int)$user['id'] ?>" class="btn btn-secondary btn-sm">👀 View Full Profile</a>
                        <a href="/parent-pass" class="btn btn-outline-primary btn-sm">📱 Parent Progress Pass</a>
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
                    <?php if ($role === 'student'): ?>
                        <div class="form-group">
                            <label>Age</label>
                            <input type="number" name="age" min="4" max="80" value="<?= Helper::e((string)($profile['age'] ?? '')) ?>">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>School Name</label>
                    <input type="text" name="school_name" value="<?= Helper::e($profile['school_name'] ?? '') ?>">
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

                <?php if ($role === 'student'): ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label>School Board</label>
                            <select name="school_board">
                                <option value="">— select —</option>
                                <?php foreach (['CBSE','ICSE','State Board','IB','IGCSE','Other'] as $b): ?>
                                    <option value="<?= $b ?>" <?= ($profile['school_board'] ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Academic Year</label>
                            <input type="text" name="academic_year" value="<?= Helper::e($profile['academic_year'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Current Class</label>
                        <input type="text" name="current_class" value="<?= Helper::e($profile['current_class'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Interests</label>
                        <input type="text" name="interests" value="<?= Helper::e($profile['interests'] ?? '') ?>">
                    </div>

                    <div class="divider"></div>
                    <h3 style="font-size:1rem;margin-bottom:.5rem">Parent / Guardian</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Parent Name</label>
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
                <?php elseif ($role === 'teacher'): ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Expert Field</label>
                            <input type="text" name="expert_field" value="<?= Helper::e($profile['expert_field'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Years of Experience</label>
                            <input type="number" name="years_experience" min="0" max="60" value="<?= Helper::e((string)($profile['years_experience'] ?? '')) ?>">
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
                <?php endif; ?>

                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">Save changes</button>
            </form>
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