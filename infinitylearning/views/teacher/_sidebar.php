<?php
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$active = fn($p) => $path === $p ? ' class="active"' : '';
?>
<aside class="admin-side">
    <a href="/teacher/batches"<?= $active('/teacher/batches') ?>><span class="ico">🏫</span> My Batches</a>
    <a href="/teacher/questions"<?= $active('/teacher/questions') ?>><span class="ico">❓</span> Question Bank</a>
    <a href="/teacher/assessments"<?= $active('/teacher/assessments') ?>><span class="ico">📝</span> Assessments & Tests</a>
    <a href="/teacher/fees"<?= $active('/teacher/fees') ?>><span class="ico">💳</span> Student Fees</a>
    <a href="/teacher/timetable"<?= $active('/teacher/timetable') ?>><span class="ico">🗓️</span> Class Routine</a>
    <a href="/teacher/doubts"<?= $active('/teacher/doubts') ?>><span class="ico">💬</span> Student Doubts</a>
    <a href="/live/whiteboard"<?= $active('/live/whiteboard') ?>><span class="ico">🎨</span> Live Whiteboard</a>
    <a href="/teacher/chess"<?= $active('/teacher/chess') ?>><span class="ico">♟️</span> Chess Coaching</a>
    <a href="/teacher/lessons"<?= $active('/teacher/lessons') ?>><span class="ico">📚</span> My Lessons</a>
    <a href="/teacher/assignments"<?= $active('/teacher/assignments') ?>><span class="ico">📋</span> Activity Assign</a>
    <a href="/teacher/activity_results"<?= $active('/teacher/activity_results') ?>><span class="ico">🏅</span> Results</a>
    <a href="/teacher/counseling"<?= $active('/teacher/counseling') ?>><span class="ico">🧭</span> Counseling</a>
    <a href="/teacher/analytics"<?= $active('/teacher/analytics') ?>><span class="ico">📈</span> Analytics</a>
</aside>