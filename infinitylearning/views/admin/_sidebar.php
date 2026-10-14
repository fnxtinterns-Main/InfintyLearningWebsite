<?php
use App\Auth;

$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$active = fn($p) => $path === $p ? ' class="active"' : '';
$isSuper = Auth::isSuperAdmin();
?>
<aside class="admin-side">
    <?php if ($isSuper): ?>
        <a href="/admin"<?= $active('/admin') ?>><span class="ico">📊</span> Dashboard</a>
        <a href="/admin/chess"<?= $active('/admin/chess') ?>><span class="ico">♟️</span> Chess Academy</a>
        <a href="/admin/analytics"<?= $active('/admin/analytics') ?>><span class="ico">📈</span> Analytics</a>
        <a href="/admin/schools"<?= $active('/admin/schools') ?>><span class="ico">🏫</span> Schools</a>
        <a href="/admin/teachers"<?= $active('/admin/teachers') ?>><span class="ico">👩‍🏫</span> All teachers</a>
        <a href="/admin/users"<?= $active('/admin/users') ?>><span class="ico">👥</span> All users</a>
        <a href="/admin/batches"<?= $active('/admin/batches') ?>><span class="ico">🎓</span> All batches</a>
        <a href="/admin/activities"<?= $active('/admin/activities') ?>><span class="ico">🏆</span> Activities</a>
        <a href="/admin/lessons"<?= $active('/admin/lessons') ?>><span class="ico">📚</span> Lessons</a>
        <a href="/admin/announcements"<?= $active('/admin/announcements') ?>><span class="ico">📣</span> Announcements</a>
    <?php else: ?>
        <a href="/school"<?= $active('/school') ?>><span class="ico">📊</span> Dashboard</a>
        <a href="/school/teachers"<?= $active('/school/teachers') ?>><span class="ico">👩‍🏫</span> Teachers</a>
        <a href="/school/students"<?= $active('/school/students') ?>><span class="ico">👩‍🎓</span> Students</a>
        <a href="/school/batches"<?= $active('/school/batches') ?>><span class="ico">🎓</span> Batches</a>
    <?php endif; ?>
</aside>