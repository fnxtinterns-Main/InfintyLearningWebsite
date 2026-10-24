<?php
use App\Auth;
use App\School;

global $isAiverse;
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$active = fn($p) => $path === $p ? ' class="active"' : '';
$user   = Auth::user();
$schoolId = (int)($user['school_id'] ?? 0);
if (!$schoolId && isset($school) && !empty($school['id'])) {
    $schoolId = (int)$school['id'];
}
$isAiverseSidebar = !empty($isAiverse) || School::isAiverse($schoolId) || str_contains($path, '/courses') || str_contains($path, '/bundles') || str_contains($path, '/banners');
?>
<aside class="admin-side">
    <a href="/school"<?= $active('/school') ?>><span class="ico">📊</span> Dashboard</a>
    <?php if ($isAiverseSidebar): ?>
        <a href="/school/courses"<?= $active('/school/courses') ?>><span class="ico">🤖</span> AI Courses &amp; Curriculum</a>
        <a href="/school/bundles"<?= $active('/school/bundles') ?>><span class="ico">📦</span> Bundles &amp; Offers</a>
        <a href="/school/banners"<?= $active('/school/banners') ?>><span class="ico">📢</span> Promo Banners</a>
        <a href="/school/fees"<?= $active('/school/fees') ?>><span class="ico">💳</span> Course Fees &amp; Payments</a>
        <a href="/school/students"<?= $active('/school/students') ?>><span class="ico">👩‍🎓</span> Scholars</a>
        <a href="/school/results"<?= $active('/school/results') ?>><span class="ico">🏆</span> Results &amp; Analytics</a>
        <a href="/school/branding"<?= $active('/school/branding') ?>><span class="ico">🎨</span> Platform Branding</a>
    <?php else: ?>
        <a href="/school/fees"<?= $active('/school/fees') ?>><span class="ico">💳</span> Student Fees</a>
        <a href="/school/timetable"<?= $active('/school/timetable') ?>><span class="ico">🗓️</span> Routine &amp; Timetable</a>
        <a href="/school/results"<?= $active('/school/results') ?>><span class="ico">🏆</span> Results &amp; Analytics</a>
        <a href="/school/branding"<?= $active('/school/branding') ?>><span class="ico">🎨</span> School Branding</a>
        <a href="/school/teachers"<?= $active('/school/teachers') ?>><span class="ico">👩‍🏫</span> Teachers</a>
        <a href="/school/students"<?= $active('/school/students') ?>><span class="ico">👩‍🎓</span> Students</a>
        <a href="/school/batches"<?= $active('/school/batches') ?>><span class="ico">🎓</span> Batches</a>
        <a href="/school/chess"<?= $active('/school/chess') ?>><span class="ico">♟️</span> Chess Academy</a>
        <a href="/school/counseling"<?= $active('/school/counseling') ?>><span class="ico">🧭</span> Counseling</a>
        <a href="/school/activities"<?= $active('/school/activities') ?>><span class="ico">🏆</span> Activities</a>
        <a href="/school/lessons"<?= $active('/school/lessons') ?>><span class="ico">📚</span> Lessons</a>
        <a href="/school/announcements"<?= $active('/school/announcements') ?>><span class="ico">📣</span> Announcements</a>
        <a href="/school/certificates"<?= $active('/school/certificates') ?>><span class="ico">📜</span> Certificates</a>
    <?php endif; ?>
</aside>