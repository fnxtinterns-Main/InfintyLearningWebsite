<?php
declare(strict_types=1);

namespace App;

final class StudentProfile
{
    /**
     * Return a full development profile for a student.
     * Combines academic, attendance, activities, achievements, certificates.
     */
    public static function build(int $studentUserId): ?array
    {
        $db = Database::connect();

        // --- Base user + profile ---
        $stmt = $db->prepare("
            SELECT u.id, u.email, u.phone, u.role, u.status, u.created_at, u.last_login_at,
                   s.*
            FROM users u
            LEFT JOIN students s ON s.user_id = u.id
            WHERE u.id = ? AND u.role = 'student'
            LIMIT 1
        ");
        $stmt->execute([$studentUserId]);
        $student = $stmt->fetch();
        if (!$student) return null;

        // --- Parents ---
        $stmt = $db->prepare("
            SELECT p.* FROM parents p
            JOIN student_parents sp ON sp.parent_id = p.id
            WHERE sp.student_user_id = ?
            ORDER BY sp.is_primary DESC, p.id ASC
        ");
        $stmt->execute([$studentUserId]);
        $student['parents'] = $stmt->fetchAll();

        // --- Batches ---
        $stmt = $db->prepare("
            SELECT b.id, b.name, b.subject, b.class_level, t.full_name AS teacher_name,
                   bs.joined_at
            FROM batch_students bs
            JOIN batches b ON b.id = bs.batch_id
            LEFT JOIN users u ON u.id = b.teacher_id
            LEFT JOIN teachers t ON t.user_id = u.id
            WHERE bs.student_user_id = ? AND b.status = 'active'
            ORDER BY bs.joined_at DESC
        ");
        $stmt->execute([$studentUserId]);
        $student['batches'] = $stmt->fetchAll();

        // --- Attendance summary ---
        $stmt = $db->prepare("
            SELECT status, COUNT(*) AS cnt
            FROM attendance WHERE student_user_id = ?
            GROUP BY status
        ");
        $stmt->execute([$studentUserId]);
        $att = ['present'=>0,'absent'=>0,'late'=>0,'excused'=>0];
        foreach ($stmt->fetchAll() as $r) {
            $att[$r['status']] = (int)$r['cnt'];
        }
        $total = array_sum($att);
        $student['attendance'] = [
            'raw'          => $att,
            'total'        => $total,
            'present_pct'  => $total > 0 ? (int)round($att['present'] / $total * 100) : 0,
        ];

        // --- Lesson progress ---
        $stmt = $db->prepare("
            SELECT
                COUNT(*) AS started,
                SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed
            FROM lesson_progress
            WHERE student_user_id = ?
        ");
        $stmt->execute([$studentUserId]);
        $lp = $stmt->fetch() ?: ['started'=>0,'completed'=>0];
        $student['lessons'] = [
            'started'   => (int)$lp['started'],
            'completed' => (int)$lp['completed'],
        ];

        // --- Activity participation by category ---
        $stmt = $db->prepare("
            SELECT a.id, a.title, a.slug, a.category, a.event_date, a.cover_image,
                   a.short_description,
                   ar.status AS participation_status, ar.registered_at,
                   ares.position AS result_position, ares.score AS result_score,
                   ares.remarks AS result_remarks
            FROM activity_registrations ar
            JOIN activities a ON a.id = ar.activity_id
            LEFT JOIN activity_results ares
                ON ares.activity_id = a.id AND ares.student_user_id = ar.user_id
            WHERE ar.user_id = ?
            ORDER BY a.event_date DESC, ar.registered_at DESC
        ");
        $stmt->execute([$studentUserId]);
        $allActivities = $stmt->fetchAll();

        // Group by "profile" per doc §14
        $profiles = [
            'sports'          => [],
            'creative'        => [],
            'communication'   => [],
            'problem_solving' => [],
            'academic'        => [],
        ];
        foreach ($allActivities as $a) {
            $cat = $a['category'];
            if (in_array($cat, ['sports'], true)) {
                $profiles['sports'][] = $a;
            } elseif (in_array($cat, ['creative','cultural'], true)) {
                $profiles['creative'][] = $a;
            } elseif (in_array($cat, ['communication'], true)) {
                $profiles['communication'][] = $a;
            } elseif (in_array($cat, ['technology','competition'], true)) {
                $profiles['problem_solving'][] = $a;
            } else {
                $profiles['academic'][] = $a;
            }
        }
        $student['activities']        = $allActivities;
        $student['activity_profiles'] = $profiles;

        // --- Achievements ---
        $stmt = $db->prepare("
            SELECT * FROM achievements
            WHERE student_user_id = ?
            ORDER BY earned_at DESC
        ");
        $stmt->execute([$studentUserId]);
        $student['achievements'] = $stmt->fetchAll();

        // --- Certificates ---
        $stmt = $db->prepare("
            SELECT * FROM certificates
            WHERE student_user_id = ?
            ORDER BY issued_at DESC
        ");
        $stmt->execute([$studentUserId]);
        $student['certificates'] = $stmt->fetchAll();

        // --- Doubts & Q&A Portal Data ---
        try {
            $student['doubts_stats'] = DoubtFeed::getStudentStats($studentUserId);
            $student['recent_doubts'] = DoubtFeed::listForStudent($studentUserId);
        } catch (\Throwable $e) {
            $student['doubts_stats'] = ['total' => 0, 'answered' => 0, 'open' => 0];
            $student['recent_doubts'] = [];
        }

        return $student;
    }

    /** Count of achievements grouped by category. */
    public static function achievementCounts(array $achievements): array
    {
        $out = ['academic'=>0,'sports'=>0,'creative'=>0,'communication'=>0,'problem_solving'=>0,'competition'=>0,'other'=>0];
        foreach ($achievements as $a) {
            $c = $a['category'] ?? 'other';
            if (!isset($out[$c])) $out[$c] = 0;
            $out[$c]++;
        }
        return $out;
    }

    /**
     * Calculate Daily Study Streak, XP, Level, and Rank Title for a student.
     */
    public static function calculateStreakAndXP(int $studentUserId): array
    {
        $db = Database::connect();

        // 1. Gather all unique activity dates (YYYY-MM-DD)
        $dates = [];

        // Attendance present
        try {
            $stmt = $db->prepare("SELECT DISTINCT date FROM attendance WHERE student_user_id = ? AND status = 'present'");
            $stmt->execute([$studentUserId]);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $d) {
                if ($d) $dates[substr((string)$d, 0, 10)] = true;
            }
        } catch (\Throwable $e) {}

        // Lesson progress
        try {
            $stmt = $db->prepare("SELECT DISTINCT DATE(updated_at) FROM lesson_progress WHERE student_user_id = ?");
            $stmt->execute([$studentUserId]);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $d) {
                if ($d) $dates[substr((string)$d, 0, 10)] = true;
            }
        } catch (\Throwable $e) {}

        // Quiz attempts
        try {
            $stmt = $db->prepare("SELECT DISTINCT DATE(created_at) FROM lesson_quiz_attempts WHERE student_user_id = ?");
            $stmt->execute([$studentUserId]);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $d) {
                if ($d) $dates[substr((string)$d, 0, 10)] = true;
            }
        } catch (\Throwable $e) {}

        // Assessment submissions
        try {
            $stmt = $db->prepare("SELECT DISTINCT DATE(submitted_at) FROM assessment_submissions WHERE student_user_id = ?");
            $stmt->execute([$studentUserId]);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $d) {
                if ($d) $dates[substr((string)$d, 0, 10)] = true;
            }
        } catch (\Throwable $e) {}

        // Activity registrations
        try {
            $stmt = $db->prepare("SELECT DISTINCT DATE(registered_at) FROM activity_registrations WHERE user_id = ?");
            $stmt->execute([$studentUserId]);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $d) {
                if ($d) $dates[substr((string)$d, 0, 10)] = true;
            }
        } catch (\Throwable $e) {}

        // Chess games
        try {
            $stmt = $db->prepare("SELECT DISTINCT DATE(played_at) FROM chess_games WHERE white_user_id = ? OR black_user_id = ?");
            $stmt->execute([$studentUserId, $studentUserId]);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $d) {
                if ($d) $dates[substr((string)$d, 0, 10)] = true;
            }
        } catch (\Throwable $e) {}

        $dateList = array_keys($dates);
        rsort($dateList); // newest first

        // Calculate Current Consecutive Streak
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $currentStreak = 0;
        $checkDate = in_array($today, $dateList, true) ? $today : (in_array($yesterday, $dateList, true) ? $yesterday : null);

        if ($checkDate !== null) {
            $currentStreak = 1;
            $curTimestamp = strtotime($checkDate);
            while (true) {
                $curTimestamp = strtotime('-1 day', $curTimestamp);
                $prevDateStr = date('Y-m-d', $curTimestamp);
                if (in_array($prevDateStr, $dateList, true)) {
                    $currentStreak++;
                } else {
                    break;
                }
            }
        }

        // Longest streak
        $longestStreak = $currentStreak;
        if (count($dateList) > 0) {
            $ascDates = array_values($dateList);
            sort($ascDates);
            $running = 1;
            for ($i = 0; $i < count($ascDates) - 1; $i++) {
                $diff = (strtotime($ascDates[$i + 1]) - strtotime($ascDates[$i])) / 86400;
                if ($diff == 1) {
                    $running++;
                    if ($running > $longestStreak) $longestStreak = $running;
                } else {
                    $running = 1;
                }
            }
        }

        // 2. XP Calculations
        $totalXp = 50; // Starter Bonus

        // Attendance (+15 XP each)
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM attendance WHERE student_user_id = ? AND status = 'present'");
            $stmt->execute([$studentUserId]);
            $totalXp += (int)$stmt->fetchColumn() * 15;
        } catch (\Throwable $e) {}

        // Lessons completed (+50 XP each)
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM lesson_progress WHERE student_user_id = ? AND status = 'completed'");
            $stmt->execute([$studentUserId]);
            $totalXp += (int)$stmt->fetchColumn() * 50;
        } catch (\Throwable $e) {}

        // Quizzes attempted (+30 XP each)
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM lesson_quiz_attempts WHERE student_user_id = ?");
            $stmt->execute([$studentUserId]);
            $totalXp += (int)$stmt->fetchColumn() * 30;
        } catch (\Throwable $e) {}

        // Assessment marks
        try {
            $stmt = $db->prepare("SELECT COALESCE(SUM(marks_obtained), 0) FROM assessment_submissions WHERE student_user_id = ?");
            $stmt->execute([$studentUserId]);
            $totalXp += (int)round((float)$stmt->fetchColumn() * 2);
        } catch (\Throwable $e) {}

        // Activity registrations (+25 XP each)
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM activity_registrations WHERE user_id = ?");
            $stmt->execute([$studentUserId]);
            $totalXp += (int)$stmt->fetchColumn() * 25;
        } catch (\Throwable $e) {}

        // Level & Title
        $level = max(1, (int)floor(sqrt($totalXp / 50)) + 1);
        $curLevelBaseXp = (int)(pow($level - 1, 2) * 50);
        $nextLevelXp = (int)(pow($level, 2) * 50);
        $progressPct = min(100, max(0, (int)round(($totalXp - $curLevelBaseXp) / max(1, ($nextLevelXp - $curLevelBaseXp)) * 100)));

        $titles = [
            1 => '🌱 Novice Scholar',
            2 => '⭐ Curious Explorer',
            3 => '📖 Active Learner',
            4 => '🚀 Rising Prodigy',
            5 => '⚡ Knowledge Master',
            6 => '🏆 Academic Champion',
            7 => '👑 Grandmaster Scholar',
        ];
        $rankTitle = $titles[min(7, $level)] ?? '👑 Grandmaster Scholar';

        return [
            'current_streak'   => $currentStreak,
            'longest_streak'   => max($longestStreak, $currentStreak),
            'has_today'        => in_array($today, $dateList, true),
            'total_xp'         => $totalXp,
            'level'            => $level,
            'level_progress'   => $progressPct,
            'cur_level_base'   => $curLevelBaseXp,
            'next_level_xp'    => $nextLevelXp,
            'rank_title'       => $rankTitle,
            'active_days'      => count($dateList),
        ];
    }
}