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

        // --- Base user + profile (support lookup by users.id or students.id) ---
        $stmt = $db->prepare("
            SELECT u.id AS u_user_id, u.id AS user_id, u.email, u.phone, u.role, u.status, u.created_at, u.last_login_at,
                   s.id AS s_student_id, s.id AS student_table_id, s.user_id AS student_user_id,
                   s.full_name, s.school_id, s.city, s.state, s.gender, s.profile_image,
                   s.age, s.date_of_birth, s.school_board, s.academic_year, s.current_class, s.interests,
                   s.registration_number, s.roll_number, s.mentor_user_id, s.district, s.pincode
            FROM users u
            LEFT JOIN students s ON s.user_id = u.id
            WHERE u.id = ?
            LIMIT 1
        ");
        $stmt->execute([$studentUserId]);
        $student = $stmt->fetch();

        // Fallback: lookup by students.id if not found or if student profile not yet attached
        if (!$student || empty($student['s_student_id'])) {
            $stmt2 = $db->prepare("
                SELECT u.id AS u_user_id, u.id AS user_id, u.email, u.phone, u.role, u.status, u.created_at, u.last_login_at,
                       s.id AS s_student_id, s.id AS student_table_id, s.user_id AS student_user_id,
                       s.full_name, s.school_id, s.city, s.state, s.gender, s.profile_image,
                       s.age, s.date_of_birth, s.school_board, s.academic_year, s.current_class, s.interests,
                       s.registration_number, s.roll_number, s.mentor_user_id, s.district, s.pincode
                FROM students s
                LEFT JOIN users u ON u.id = s.user_id
                WHERE s.id = ?
                LIMIT 1
            ");
            $stmt2->execute([$studentUserId]);
            $alt = $stmt2->fetch();
            if ($alt) {
                $student = $alt;
            }
        }

        if (!$student) return null;

        $actualUserId = (int)($student['u_user_id'] ?? $student['user_id'] ?? $student['student_user_id'] ?? $studentUserId);
        $studentTableId = (int)($student['s_student_id'] ?? $student['student_table_id'] ?? 0);
        if ($actualUserId <= 0 && $studentTableId > 0) {
            $actualUserId = $studentTableId;
        }
        $candidateIds = array_values(array_unique(array_filter([$actualUserId, $studentTableId, $studentUserId])));
        $inClause = implode(',', array_fill(0, count($candidateIds), '?'));

        // Ensure user_id and id are explicitly set
        $student['id'] = $actualUserId;
        $student['user_id'] = $actualUserId;
        $student['student_table_id'] = $studentTableId;
        if (empty($student['full_name'])) {
            $student['full_name'] = !empty($student['email']) ? explode('@', $student['email'])[0] : 'Student';
        }

        // --- Parents ---
        $student['parents'] = [];
        try {
            $stmt = $db->prepare("
                SELECT p.* FROM parents p
                JOIN student_parents sp ON sp.parent_id = p.id
                WHERE sp.student_user_id IN ($inClause)
                ORDER BY sp.is_primary DESC, p.id ASC
            ");
            $stmt->execute($candidateIds);
            $student['parents'] = $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('StudentProfile parents query error: ' . $e->getMessage());
        }

        // --- Batches ---
        $student['batches'] = [];
        try {
            $student['batches'] = Batch::forStudent($actualUserId) ?: [];
        } catch (\Throwable $e) {
            error_log('StudentProfile batches query error: ' . $e->getMessage());
        }

        // --- Attendance summary & Daily records ---
        $att = ['present'=>0,'absent'=>0,'late'=>0,'excused'=>0];
        $dailyAttendance = [];
        try {
            $att = Attendance::summaryForStudent($actualUserId) ?: $att;
            $attStmt = $db->prepare("
                SELECT a.id, a.batch_id, a.attendance_date, a.status, a.remarks, b.name AS batch_name
                FROM attendance a
                LEFT JOIN batches b ON b.id = a.batch_id
                WHERE a.student_user_id IN ($inClause)
                ORDER BY a.attendance_date ASC
            ");
            $attStmt->execute($candidateIds);
            $dailyAttendance = $attStmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('StudentProfile attendance query error: ' . $e->getMessage());
        }
        $totalAtt = array_sum($att);
        $student['attendance'] = [
            'raw'          => $att,
            'total'        => $totalAtt,
            'present_pct'  => $totalAtt > 0 ? (int)round(($att['present'] ?? 0) / $totalAtt * 100) : 0,
            'daily_records'=> $dailyAttendance,
        ];

        // --- Lesson progress ---
        $lp = ['started'=>0,'completed'=>0];
        $availableLessons = [];
        try {
            $stmt = $db->prepare("
                SELECT
                    COUNT(*) AS started,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed
                FROM lesson_progress
                WHERE student_user_id IN ($inClause)
            ");
            $stmt->execute($candidateIds);
            $lp = $stmt->fetch() ?: ['started'=>0,'completed'=>0];
            $availableLessons = Lesson::list([], -1, $actualUserId, 'student') ?: [];
        } catch (\Throwable $e) {
            error_log('StudentProfile lesson_progress query error: ' . $e->getMessage());
        }
        $student['lessons'] = [
            'started'   => (int)($lp['started'] ?? 0),
            'completed' => (int)($lp['completed'] ?? 0),
            'total'     => max(count($availableLessons), (int)($lp['started'] ?? 0)),
        ];

        // --- Daily Routine (Today's Class Schedule) ---
        $student['daily_routine'] = [];
        try {
            $student['daily_routine'] = Timetable::getStudentSchedule($actualUserId) ?: [];
        } catch (\Throwable $e) {
            error_log('StudentProfile daily_routine query error: ' . $e->getMessage());
        }

        // --- Academic Examinations & Tests ---
        $student['exams'] = [
            'categories' => [],
            'all_tests'  => [],
            'summary'    => ['total_tests' => 0, 'total_obtained' => 0, 'total_max' => 0, 'average_pct' => 0]
        ];
        try {
            $student['exams'] = AcademicAssessment::getStudentExamHistory($actualUserId);
        } catch (\Throwable $e) {
            error_log('StudentProfile exams query error: ' . $e->getMessage());
        }

        // --- Activity participation by category ---
        $allActivities = [];
        try {
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
                WHERE ar.user_id IN ($inClause)
                ORDER BY a.event_date DESC, ar.registered_at DESC
            ");
            $stmt->execute($candidateIds);
            $allActivities = $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('StudentProfile activities query error: ' . $e->getMessage());
        }

        // Group by "profile" per doc §14
        $profiles = [
            'sports'          => [],
            'creative'        => [],
            'communication'   => [],
            'problem_solving' => [],
            'academic'        => [],
        ];
        foreach ($allActivities as $a) {
            $cat = $a['category'] ?? '';
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
        $student['achievements'] = [];
        try {
            $stmt = $db->prepare("
                SELECT * FROM achievements
                WHERE student_user_id IN ($inClause)
                ORDER BY earned_at DESC
            ");
            $stmt->execute($candidateIds);
            $student['achievements'] = $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('StudentProfile achievements query error: ' . $e->getMessage());
        }

        // --- Certificates ---
        $student['certificates'] = [];
        try {
            $stmt = $db->prepare("
                SELECT * FROM certificates
                WHERE student_user_id IN ($inClause)
                ORDER BY issued_at DESC
            ");
            $stmt->execute($candidateIds);
            $student['certificates'] = $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('StudentProfile certificates query error: ' . $e->getMessage());
        }

        // --- Doubts & Q&A Portal Data ---
        try {
            $student['doubts_stats'] = DoubtFeed::getStudentStats($actualUserId);
            $student['recent_doubts'] = DoubtFeed::listForStudent($actualUserId);
        } catch (\Throwable $e) {
            $student['doubts_stats'] = ['total' => 0, 'answered' => 0, 'open' => 0];
            $student['recent_doubts'] = [];
        }

        // --- Mentor details ---
        $student['mentor_name'] = null;
        if (!empty($student['mentor_user_id'])) {
            try {
                $mStmt = $db->prepare("SELECT full_name, email FROM users WHERE id = ?");
                $mStmt->execute([(int)$student['mentor_user_id']]);
                if ($mRow = $mStmt->fetch()) {
                    $student['mentor_name'] = $mRow['full_name'] ?: $mRow['email'];
                }
            } catch (\Throwable $e) {}
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

        $ids = [$studentUserId];
        try {
            $s = $db->prepare("SELECT user_id, id FROM students WHERE user_id = ? OR id = ? LIMIT 1");
            $s->execute([$studentUserId, $studentUserId]);
            $row = $s->fetch();
            if ($row) {
                if (!empty($row['user_id'])) $ids[] = (int)$row['user_id'];
                if (!empty($row['id'])) $ids[] = (int)$row['id'];
            }
        } catch (\Throwable $e) {}
        $ids = array_values(array_unique(array_filter($ids)));
        $inClause = implode(',', array_fill(0, count($ids), '?'));

        // 1. Gather all unique activity dates (YYYY-MM-DD)
        $dates = [];

        // Attendance present
        try {
            $stmt = $db->prepare("SELECT DISTINCT attendance_date FROM attendance WHERE student_user_id IN ($inClause) AND status = 'present'");
            $stmt->execute($ids);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $d) {
                if ($d) $dates[substr((string)$d, 0, 10)] = true;
            }
        } catch (\Throwable $e) {}

        // Lesson progress
        try {
            $stmt = $db->prepare("SELECT DISTINCT DATE(updated_at) FROM lesson_progress WHERE student_user_id IN ($inClause)");
            $stmt->execute($ids);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $d) {
                if ($d) $dates[substr((string)$d, 0, 10)] = true;
            }
        } catch (\Throwable $e) {}

        // Quiz attempts
        try {
            $stmt = $db->prepare("SELECT DISTINCT DATE(created_at) FROM lesson_quiz_attempts WHERE student_user_id IN ($inClause)");
            $stmt->execute($ids);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $d) {
                if ($d) $dates[substr((string)$d, 0, 10)] = true;
            }
        } catch (\Throwable $e) {}

        // Assessment submissions
        try {
            $stmt = $db->prepare("SELECT DISTINCT DATE(submitted_at) FROM assessment_submissions WHERE student_user_id IN ($inClause)");
            $stmt->execute($ids);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $d) {
                if ($d) $dates[substr((string)$d, 0, 10)] = true;
            }
        } catch (\Throwable $e) {}

        // Activity registrations
        try {
            $stmt = $db->prepare("SELECT DISTINCT DATE(registered_at) FROM activity_registrations WHERE user_id IN ($inClause)");
            $stmt->execute($ids);
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $d) {
                if ($d) $dates[substr((string)$d, 0, 10)] = true;
            }
        } catch (\Throwable $e) {}

        // Chess games
        try {
            $stmt = $db->prepare("SELECT DISTINCT DATE(played_at) FROM chess_games WHERE white_user_id IN ($inClause) OR black_user_id IN ($inClause)");
            $stmt->execute(array_merge($ids, $ids));
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
            $stmt = $db->prepare("SELECT COUNT(*) FROM attendance WHERE student_user_id IN ($inClause) AND status = 'present'");
            $stmt->execute($ids);
            $totalXp += (int)$stmt->fetchColumn() * 15;
        } catch (\Throwable $e) {}

        // Lessons completed (+50 XP each)
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM lesson_progress WHERE student_user_id IN ($inClause) AND status = 'completed'");
            $stmt->execute($ids);
            $totalXp += (int)$stmt->fetchColumn() * 50;
        } catch (\Throwable $e) {}

        // Quizzes attempted (+30 XP each)
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM lesson_quiz_attempts WHERE student_user_id IN ($inClause)");
            $stmt->execute($ids);
            $totalXp += (int)$stmt->fetchColumn() * 30;
        } catch (\Throwable $e) {}

        // Assessment marks
        try {
            $stmt = $db->prepare("SELECT COALESCE(SUM(score_obtained), 0) FROM assessment_submissions WHERE student_user_id IN ($inClause)");
            $stmt->execute($ids);
            $totalXp += (int)round((float)$stmt->fetchColumn() * 2);
        } catch (\Throwable $e) {}

        // Activity registrations (+25 XP each)
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM activity_registrations WHERE user_id IN ($inClause)");
            $stmt->execute($ids);
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