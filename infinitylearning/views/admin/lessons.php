<?php
use App\Auth;
use App\Helper;
use App\Lesson;

Auth::requireLogin();
if (Auth::role() !== 'admin') { http_response_code(403); exit('Forbidden'); }

$isSuper = Auth::isSuperAdmin();
$isSchool = Auth::isSchoolAdmin();

$scope = $isSuper ? -1 : (int)Auth::schoolId();
$lessons = Lesson::list(['limit' => 500], $scope);

$pageTitle = 'Lessons — ' . ($isSuper ? 'Admin' : 'School Admin');
require VIEW_PATH . '/layouts/header.php';
?>
<div class="admin-shell">
    <?php
    if ($isSuper) require VIEW_PATH . '/admin/_sidebar.php';
    else          require VIEW_PATH . '/school/_sidebar.php';
    ?>
    <div>
        <div class="flex between" style="align-items:flex-start">
            <div>
                <h1>📚 Lessons</h1>
                <p class="muted">
                    <?php if ($isSuper): ?>
                        All lessons across every school + global.
                    <?php else: ?>
                        Your school's lessons + global lessons.
                    <?php endif; ?>
                </p>
            </div>
            <a href="/admin/lessons/new" class="btn btn-primary">+ New lesson</a>
        </div>

        <?php if (!$lessons): ?>
            <div class="card text-center mt-3">
                <p class="muted">No lessons yet. Click "New lesson" to publish your first one.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap mt-3">
                <table class="data">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Subject</th>
                            <th>Scope</th>
                            <th>Difficulty</th>
                            <th>Files</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($lessons as $l):
                        $files = $l['attachments_array'] ?? [];
                        $fileCount = count($files);
                        $exts = array_unique(array_map(fn($f) => strtoupper($f['ext'] ?? ''), $files));
                    ?>
                        <tr>
                            <td><?= (int)$l['id'] ?></td>
                            <td>
                                <a href="/lesson?id=<?= (int)$l['id'] ?>" style="color:inherit;text-decoration:none">
                                    <strong><?= Helper::e($l['title']) ?></strong>
                                </a>
                                <?php if ($l['is_ai_generated']): ?>
                                    <span class="badge badge-info">AI</span>
                                <?php endif; ?>
                            </td>
                            <td><?= Helper::e($l['subject_name']) ?></td>
                            <td>
                                <?php if (!empty($l['school_name'])): ?>
                                    <span class="badge badge-brand"><?= Helper::e($l['school_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-info">Global</span>
                                <?php endif; ?>
                                <?php if (!empty($l['batch_name'])): ?>
                                    <div class="muted" style="font-size:.75rem;margin-top:.15rem">
                                        🎓 <?= Helper::e($l['batch_name']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="pill"><?= Helper::e($l['difficulty']) ?></span></td>
                            <td>
                                <?php if ($fileCount > 0): ?>
                                    <span class="pill" title="<?= Helper::e(implode(', ', $exts)) ?>">
                                        📎 <?= $fileCount ?>
                                    </span>
                                <?php else: ?>
                                    <span class="muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $l['published'] ? 'success' : 'muted' ?>">
                                    <?= $l['published'] ? 'published' : 'draft' ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($l['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>