<?php
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$active = fn($p) => $path === $p ? ' class="active"' : '';
?>
<aside class="admin-side">
    <a href="/school"<?= $active('/school') ?>><span class="ico">📊</span> Dashboard</a>
    <a href="/school/fees"<?= $active('/school/fees') ?>><span class="ico">💳</span> Student Fees</a>
    <a href="/school/timetable"<?= $active('/school/timetable') ?>><span class="ico">🗓️</span> Routine & Timetable</a>
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
</aside>