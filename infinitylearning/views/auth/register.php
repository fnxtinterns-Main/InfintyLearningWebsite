<?php
use App\Helper;
use App\Session;
use App\School;

$type = $_GET['type'] ?? 'student';
$type = in_array($type, ['student','teacher'], true) ? $type : 'student';
$pageTitle = ucfirst($type) . ' Registration — Infinity Learning';
$error = Session::flash('error');

$schools = School::activeForDropdown();

require VIEW_PATH . '/layouts/header.php';
?>
<section class="auth-split">
    <div class="auth-split-side">
        <div class="auth-split-side-inner">
            <a href="/" class="auth-split-brand">
                <span class="brand-infinity">Infinity</span>
                <span class="brand-learning">L E A R N I N G</span>
            </a>
            <h2><?= $type === 'student' ? 'Start your learning journey' : 'Teach with Infinity' ?></h2>
            <p class="auth-split-sub">
                <?= $type === 'student'
                    ? 'Lessons, activities, certificates and competitions — all in one place.'
                    : 'Manage batches, upload lessons, track attendance and reward achievements.' ?>
            </p>

            <ul class="auth-split-list">
                <?php if ($type === 'student'): ?>
                    <li><span class="feature-check">✓</span> Structured lessons across all subjects</li>
                    <li><span class="feature-check">✓</span> Join school &amp; global competitions</li>
                    <li><span class="feature-check">✓</span> Earn PDF certificates instantly</li>
                    <li><span class="feature-check">✓</span> Parents can follow your progress</li>
                <?php else: ?>
                    <li><span class="feature-check">✓</span> Create batches in seconds</li>
                    <li><span class="feature-check">✓</span> Upload PDFs, videos &amp; Word docs</li>
                    <li><span class="feature-check">✓</span> Mark attendance with one click</li>
                    <li><span class="feature-check">✓</span> Generate certificates automatically</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="auth-split-main">
        <div class="auth-card" style="max-width:640px">
            <h2><?= ucfirst($type) ?> Registration</h2>
            <p class="muted" style="margin-bottom:1rem">
                Fill in your details. We'll verify your phone with an OTP next.
            </p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= Helper::e($error) ?></div>
            <?php endif; ?>

            <?php if (!$schools): ?>
                <div class="alert alert-warning">
                    No schools are registered yet. Ask your school to contact Infinity Learning to onboard first.
                </div>
            <?php else: ?>
                <div class="alert alert-info" style="font-size:.88rem">
                    Choose your school below. Your <strong>school admin</strong> will approve your account before you can access the dashboard.
                </div>
            <?php endif; ?>

            <form method="post" action="/register-handler.php" id="registerForm">
                <input type="hidden" name="csrf" value="<?= Helper::e(Session::csrfToken()) ?>">
                <input type="hidden" name="role" value="<?= Helper::e($type) ?>">

                <div class="form-group">
                    <label>School *</label>
                    <select name="school_id" required <?= !$schools ? 'disabled' : '' ?>>
                        <option value="">— select your school —</option>
                        <?php foreach ($schools as $s): ?>
                            <option value="<?= (int)$s['id'] ?>"
                                <?= (string)Helper::old('school_id') === (string)$s['id'] ? 'selected' : '' ?>>
                                <?= Helper::e($s['name']) ?>
                                <?php if ($s['city']): ?> · <?= Helper::e($s['city']) ?><?php endif; ?>
                                <?php if ($s['board']): ?> (<?= Helper::e($s['board']) ?>)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required value="<?= Helper::old('full_name') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required value="<?= Helper::old('email') ?>">
                    </div>
                    <div class="form-group">
                        <label>Phone *</label>
                        <input type="tel" name="phone" required placeholder="+91XXXXXXXXXX" value="<?= Helper::old('phone') ?>">
                    </div>
                </div>
                <small class="muted" style="display:block;margin-top:-.5rem;margin-bottom:.85rem">
                    We'll send a 6-digit OTP to this number to verify it.
                </small>

                <div class="form-row">
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" required minlength="8">
                        <small>At least 8 characters with letters and numbers.</small>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <input type="password" name="password_confirm" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="">— select —</option>
                            <option value="male" <?= Helper::old('gender') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= Helper::old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= Helper::old('gender') === 'other' ? 'selected' : '' ?>>Other</option>
                            <option value="prefer_not_to_say" <?= Helper::old('gender') === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Age<?= $type === 'student' ? ' *' : '' ?></label>
                        <input type="number" name="age" min="4" max="80"
                               value="<?= Helper::old('age') ?>"<?= $type === 'student' ? ' required' : '' ?>>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" value="<?= Helper::old('city') ?>">
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <input type="text" name="state" value="<?= Helper::old('state') ?>">
                    </div>
                </div>

                <?php if ($type === 'student'): ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label>School Board</label>
                            <select name="school_board">
                                <option value="">— select —</option>
                                <?php foreach (['CBSE','ICSE','State Board','IB','IGCSE','Other'] as $b): ?>
                                    <option value="<?= $b ?>" <?= Helper::old('school_board') === $b ? 'selected' : '' ?>><?= $b ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Academic Year</label>
                            <input type="text" name="academic_year" placeholder="e.g. 2025-2026" value="<?= Helper::old('academic_year') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Current Class *</label>
                        <input type="text" name="current_class" required value="<?= Helper::old('current_class') ?>" placeholder="e.g. Grade 8">
                    </div>

                    <div class="form-group">
                        <label>Interests (comma separated)</label>
                        <input type="text" name="interests" value="<?= Helper::old('interests') ?>" placeholder="e.g. Maths, Chess, Drawing">
                    </div>

                    <div class="divider"></div>
                    <h3 style="font-size:1rem;margin-bottom:.5rem">Parent / Guardian</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Parent/Guardian Name *</label>
                            <input type="text" name="guardian_name" required value="<?= Helper::old('guardian_name') ?>">
                        </div>
                        <div class="form-group">
                            <label>Relationship *</label>
                            <select name="guardian_relationship" required>
                                <option value="">— select —</option>
                                <?php foreach (['father'=>'Father','mother'=>'Mother','guardian'=>'Guardian','other'=>'Other'] as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= Helper::old('guardian_relationship') === $k ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Parent Phone *</label>
                            <input type="tel" name="guardian_phone" required value="<?= Helper::old('guardian_phone') ?>">
                        </div>
                        <div class="form-group">
                            <label>Parent Email</label>
                            <input type="email" name="guardian_email" value="<?= Helper::old('guardian_email') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Parent Occupation</label>
                            <input type="text" name="guardian_occupation" value="<?= Helper::old('guardian_occupation') ?>">
                        </div>
                        <div class="form-group">
                            <label>Parent City</label>
                            <input type="text" name="guardian_city" value="<?= Helper::old('guardian_city') ?>">
                        </div>
                    </div>
                <?php else: ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Expert Field *</label>
                            <input type="text" name="expert_field" required value="<?= Helper::old('expert_field') ?>" placeholder="e.g. Mathematics">
                        </div>
                        <div class="form-group">
                            <label>Years of Experience</label>
                            <input type="number" name="years_experience" min="0" max="60" value="<?= Helper::old('years_experience') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Additional Expertise</label>
                        <input type="text" name="additional_expertise" value="<?= Helper::old('additional_expertise') ?>" placeholder="e.g. Abacus, Vedic Maths">
                    </div>

                    <div class="form-group">
                        <label>Short Bio</label>
                        <textarea name="bio" rows="3" placeholder="A one or two line description about you."><?= Helper::old('bio') ?></textarea>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2"<?= !$schools ? ' disabled' : '' ?>>
                    Create Account
                </button>
            </form>

            <p class="auth-switch">
                <?php if ($type === 'student'): ?>
                    Are you a teacher? <a href="/register?type=teacher">Register here</a>
                <?php else: ?>
                    Are you a student? <a href="/register?type=student">Register here</a>
                <?php endif; ?>
            </p>
        </div>
    </div>
</section>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>