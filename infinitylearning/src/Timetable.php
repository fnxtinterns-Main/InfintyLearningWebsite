<?php
declare(strict_types=1);

namespace App;

use PDO;

final class Timetable
{
    private static bool $bootstrapped = false;

    public const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    public const DEFAULT_PERIODS = [
        1 => ['start' => '08:30:00', 'end' => '09:15:00'],
        2 => ['start' => '09:15:00', 'end' => '10:00:00'],
        3 => ['start' => '10:15:00', 'end' => '11:00:00'],
        4 => ['start' => '11:00:00', 'end' => '11:45:00'],
        5 => ['start' => '12:15:00', 'end' => '13:00:00'],
        6 => ['start' => '13:00:00', 'end' => '13:45:00'],
        7 => ['start' => '13:45:00', 'end' => '14:30:00'],
        8 => ['start' => '14:30:00', 'end' => '15:15:00'],
    ];

    public static function ensureTable(): void
    {
        if (self::$bootstrapped) return;
        $db = Database::connect();
        $db->exec("
            CREATE TABLE IF NOT EXISTS `timetables` (
              `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `school_id` bigint(20) UNSIGNED NOT NULL,
              `class_level` varchar(50) NOT NULL,
              `section` varchar(10) DEFAULT 'A',
              `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday') NOT NULL,
              `period_number` tinyint(3) UNSIGNED NOT NULL,
              `start_time` time NOT NULL,
              `end_time` time NOT NULL,
              `subject_id` bigint(20) UNSIGNED NOT NULL,
              `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
              `room_number` varchar(30) DEFAULT NULL,
              `created_at` datetime DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_timetable_slot` (`school_id`, `class_level`, `section`, `day_of_week`, `period_number`),
              KEY `idx_tt_teacher` (`teacher_user_id`),
              KEY `idx_tt_school` (`school_id`),
              KEY `idx_tt_day` (`day_of_week`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            ALTER TABLE `schools`
              ADD COLUMN IF NOT EXISTS `bell_schedule_json` longtext DEFAULT NULL,
              ADD COLUMN IF NOT EXISTS `custom_classes_json` longtext DEFAULT NULL,
              ADD COLUMN IF NOT EXISTS `custom_sections_json` longtext DEFAULT NULL;
        ");
        self::$bootstrapped = true;
    }

    public static function formatTime12(?string $time): string
    {
        if (empty($time)) return '';
        $ts = strtotime($time);
        return $ts !== false ? date('h:i A', $ts) : $time;
    }

    public static function getPeriodsForSchool(int $schoolId): array
    {
        return Cache::remember("tt:periods:{$schoolId}", 3600, function () use ($schoolId) {
            self::ensureTable();
            $db = Database::connect();
            $stmt = $db->prepare("SELECT bell_schedule_json FROM schools WHERE id = ? LIMIT 1");
            $stmt->execute([$schoolId]);
            $json = $stmt->fetchColumn();

            if ($json) {
                $decoded = json_decode((string)$json, true);
                if (is_array($decoded) && !empty($decoded)) {
                    return $decoded;
                }
            }

            // Standard 8 periods default
            $default = [];
            foreach (self::DEFAULT_PERIODS as $num => $t) {
                $default[] = [
                    'period_number' => $num,
                    'label'         => "Period {$num}",
                    'start'         => $t['start'],
                    'end'           => $t['end'],
                    'is_break'      => 0
                ];
            }
            return $default;
        });
    }

    public static function saveBellSchedule(int $schoolId, array $periods): bool
    {
        self::ensureTable();
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE schools SET bell_schedule_json = ? WHERE id = ?");
        $saved = $stmt->execute([json_encode(array_values($periods)), $schoolId]);
        if ($saved) {
            Cache::forget("tt:periods:{$schoolId}");
        }
        return (bool)$saved;
    }

    public static function getClassesForSchool(int $schoolId): array
    {
        self::ensureTable();
        $db = Database::connect();
        $classes = [];

        // 1. From timetables
        $q1 = $db->prepare("SELECT DISTINCT class_level FROM timetables WHERE school_id = ?");
        $q1->execute([$schoolId]);
        foreach ($q1->fetchAll(PDO::FETCH_COLUMN) as $c) {
            if ($c) $classes[] = trim($c);
        }

        // 2. From students
        $q2 = $db->prepare("
            SELECT DISTINCT s.current_class 
            FROM students s 
            JOIN users u ON u.id = s.user_id 
            WHERE u.school_id = ?
        ");
        $q2->execute([$schoolId]);
        foreach ($q2->fetchAll(PDO::FETCH_COLUMN) as $c) {
            if ($c) $classes[] = trim($c);
        }

        // 3. From custom_classes_json
        $q3 = $db->prepare("SELECT custom_classes_json FROM schools WHERE id = ? LIMIT 1");
        $q3->execute([$schoolId]);
        $customJson = $q3->fetchColumn();
        if ($customJson) {
            $customArr = json_decode((string)$customJson, true);
            if (is_array($customArr)) {
                foreach ($customArr as $c) {
                    if ($c) $classes[] = trim($c);
                }
            }
        }

        // Default set
        $defaults = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'];
        $merged = array_unique(array_merge($defaults, $classes));
        natsort($merged);
        return array_values($merged);
    }

    public static function addCustomClass(int $schoolId, string $className): bool
    {
        self::ensureTable();
        $db = Database::connect();
        $className = trim($className);
        if ($className === '') return false;

        $q = $db->prepare("SELECT custom_classes_json FROM schools WHERE id = ? LIMIT 1");
        $q->execute([$schoolId]);
        $json = $q->fetchColumn();
        $list = $json ? (json_decode((string)$json, true) ?: []) : [];

        if (!in_array($className, $list, true)) {
            $list[] = $className;
            $stmt = $db->prepare("UPDATE schools SET custom_classes_json = ? WHERE id = ?");
            return $stmt->execute([json_encode(array_values($list)), $schoolId]);
        }
        return true;
    }

    public static function getSectionsForSchool(?int $schoolId): array
    {
        self::ensureTable();
        $sections = [];
        $db = Database::connect();

        if ($schoolId) {
            // 1. From existing timetables
            $q1 = $db->prepare("SELECT DISTINCT section FROM timetables WHERE school_id = ?");
            $q1->execute([$schoolId]);
            foreach ($q1->fetchAll(PDO::FETCH_COLUMN) as $s) {
                if ($s) $sections[] = trim($s);
            }

            // 2. From custom_sections_json in schools
            $q2 = $db->prepare("SELECT custom_sections_json FROM schools WHERE id = ? LIMIT 1");
            $q2->execute([$schoolId]);
            $customJson = $q2->fetchColumn();
            if ($customJson) {
                $customArr = json_decode((string)$customJson, true);
                if (is_array($customArr)) {
                    foreach ($customArr as $s) {
                        if ($s) $sections[] = trim($s);
                    }
                }
            }
        }

        // Standard default sections
        $defaults = ['A', 'B', 'C', 'D', 'E'];
        $merged = array_unique(array_merge($defaults, $sections));
        natsort($merged);
        return array_values($merged);
    }

    public static function addCustomSection(int $schoolId, string $sectionName): bool
    {
        self::ensureTable();
        $db = Database::connect();
        $sectionName = trim($sectionName);
        if ($sectionName === '') return false;

        $q = $db->prepare("SELECT custom_sections_json FROM schools WHERE id = ? LIMIT 1");
        $q->execute([$schoolId]);
        $json = $q->fetchColumn();
        $list = $json ? (json_decode((string)$json, true) ?: []) : [];

        if (!in_array($sectionName, $list, true)) {
            $list[] = $sectionName;
            $stmt = $db->prepare("UPDATE schools SET custom_sections_json = ? WHERE id = ?");
            return $stmt->execute([json_encode(array_values($list)), $schoolId]);
        }
        return true;
    }

    public static function saveSlot(array $d, int $schoolId): array
    {
        self::ensureTable();
        $db = Database::connect();

        $classLevel = trim($d['class_level']);
        $section = strtoupper(trim($d['section'] ?: 'A'));
        $day = strtolower(trim($d['day_of_week']));
        $period = (int)$d['period_number'];
        $subjectId = (int)$d['subject_id'];
        $teacherId = (int)$d['teacher_user_id'];
        $room = trim($d['room_number'] ?? '');

        $periodTimes = self::DEFAULT_PERIODS[$period] ?? ['start' => '09:00:00', 'end' => '09:45:00'];
        $startTime = $d['start_time'] ?? $periodTimes['start'];
        $endTime = $d['end_time'] ?? $periodTimes['end'];

        // Conflict check 1: Teacher already assigned in another class for same school, day, and period
        $conflictTeacher = $db->prepare("
            SELECT t.*, s.name AS subject_name, tch.full_name AS teacher_name
            FROM timetables t
            LEFT JOIN subjects s ON s.id = t.subject_id
            LEFT JOIN teachers tch ON tch.user_id = t.teacher_user_id
            WHERE t.school_id = ? AND t.day_of_week = ? AND t.period_number = ?
              AND t.teacher_user_id = ?
              AND NOT (t.class_level = ? AND t.section = ?)
            LIMIT 1
        ");
        $conflictTeacher->execute([$schoolId, $day, $period, $teacherId, $classLevel, $section]);
        $existing = $conflictTeacher->fetch();
        if ($existing) {
            return [
                'success' => false,
                'error' => "Teacher {$existing['teacher_name']} is already booked in Class {$existing['class_level']}-{$existing['section']} during Period {$period} on " . ucfirst($day) . "!"
            ];
        }

        // Upsert slot
        $stmt = $db->prepare("
            INSERT INTO timetables
            (school_id, class_level, section, day_of_week, period_number, start_time, end_time, subject_id, teacher_user_id, room_number)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                subject_id = VALUES(subject_id),
                teacher_user_id = VALUES(teacher_user_id),
                start_time = VALUES(start_time),
                end_time = VALUES(end_time),
                room_number = VALUES(room_number)
        ");

        $stmt->execute([
            $schoolId, $classLevel, $section, $day, $period, $startTime, $endTime, $subjectId, $teacherId, $room
        ]);

        $cacheKey = "tt:grid:{$schoolId}:" . md5(strtolower("{$classLevel}_{$section}"));
        Cache::forget($cacheKey);
        return ['success' => true];
    }

    public static function deleteSlot(int $slotId, int $schoolId): bool
    {
        self::ensureTable();
        $db = Database::connect();
        $q = $db->prepare("SELECT class_level, section FROM timetables WHERE id = ? AND school_id = ? LIMIT 1");
        $q->execute([$slotId, $schoolId]);
        $row = $q->fetch();

        $stmt = $db->prepare("DELETE FROM timetables WHERE id = ? AND school_id = ?");
        $deleted = $stmt->execute([$slotId, $schoolId]);

        if ($deleted && $row) {
            $cacheKey = "tt:grid:{$schoolId}:" . md5(strtolower("{$row['class_level']}_{$row['section']}"));
            Cache::forget($cacheKey);
        }
        return (bool)$deleted;
    }

    public static function getGridForClass(int $schoolId, string $classLevel, string $section = 'A'): array
    {
        $cacheKey = "tt:grid:{$schoolId}:" . md5(strtolower("{$classLevel}_{$section}"));
        return Cache::remember($cacheKey, 1800, function () use ($schoolId, $classLevel, $section) {
            self::ensureTable();
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT t.*, s.name AS subject_name, s.color AS subject_color, s.icon AS subject_icon,
                       tch.full_name AS teacher_name
                FROM timetables t
                JOIN subjects s ON s.id = t.subject_id
                JOIN teachers tch ON tch.user_id = t.teacher_user_id
                WHERE t.school_id = ? AND t.class_level = ? AND t.section = ?
                ORDER BY t.period_number ASC
            ");
            $stmt->execute([$schoolId, $classLevel, $section]);
            $rows = $stmt->fetchAll();

            $periods = self::getPeriodsForSchool($schoolId);
            $grid = [];
            foreach (self::DAYS as $d) {
                $grid[$d] = [];
                foreach ($periods as $pInfo) {
                    $p = (int)$pInfo['period_number'];
                    $grid[$d][$p] = null;
                }
            }

            foreach ($rows as $r) {
                $grid[$r['day_of_week']][(int)$r['period_number']] = $r;
            }

            return $grid;
        });
    }

    public static function getTeacherSchedule(int $teacherUserId): array
    {
        self::ensureTable();
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT t.*, s.name AS subject_name, sch.name AS school_name
            FROM timetables t
            JOIN subjects s ON s.id = t.subject_id
            JOIN schools sch ON sch.id = t.school_id
            WHERE t.teacher_user_id = ?
            ORDER BY FIELD(t.day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday'), t.period_number ASC
        ");
        $stmt->execute([$teacherUserId]);
        return $stmt->fetchAll();
    }

    public static function getStudentSchedule(int $studentUserId): array
    {
        self::ensureTable();
        $db = Database::connect();
        $student = Auth::findStudentByUserId($studentUserId);
        $user = Auth::findById($studentUserId);
        if (!$student || empty($user['school_id'])) {
            return [];
        }

        $classLevel = $student['current_class'] ?: 'Grade 8';
        $section = 'A';
        if (preg_match('/([0-9]+|Grade\s*[0-9]+|Class\s*[0-9]+)\s*[- ]*\s*([A-Z])/i', $classLevel, $m)) {
            $classLevel = trim($m[1]);
            $section = strtoupper(trim($m[2]));
        }

        $today = strtolower(date('l'));
        if (!in_array($today, self::DAYS, true)) {
            $today = 'monday';
        }

        $stmt = $db->prepare("
            SELECT t.*, s.name AS subject_name, s.color AS subject_color, s.icon AS subject_icon,
                   tch.full_name AS teacher_name
            FROM timetables t
            JOIN subjects s ON s.id = t.subject_id
            JOIN teachers tch ON tch.user_id = t.teacher_user_id
            WHERE t.school_id = ? AND t.class_level = ? AND t.section = ? AND t.day_of_week = ?
            ORDER BY t.period_number ASC
        ");
        $stmt->execute([(int)$user['school_id'], $classLevel, $section, $today]);
        return $stmt->fetchAll();
    }

    public static function getForClass(int $schoolId, string $classLevel, ?string $dayOfWeek = null, string $section = 'A'): array
    {
        self::ensureTable();
        $db = Database::connect();

        $day = strtolower($dayOfWeek ?: date('l'));
        if (!in_array($day, self::DAYS, true)) {
            $day = 'monday';
        }

        $stmt = $db->prepare("
            SELECT t.*, s.name AS subject_name, s.color AS subject_color, s.icon AS subject_icon,
                   tch.full_name AS teacher_name
            FROM timetables t
            JOIN subjects s ON s.id = t.subject_id
            JOIN teachers tch ON tch.user_id = t.teacher_user_id
            WHERE t.school_id = ? AND t.class_level = ? AND t.section = ? AND t.day_of_week = ?
            ORDER BY t.period_number ASC
        ");
        $stmt->execute([$schoolId, $classLevel, $section, $day]);
        return $stmt->fetchAll();
    }

    public static function getFreeTeachersForSlot(int $schoolId, string $day, int $periodNumber): array
    {
        self::ensureTable();
        $db = Database::connect();

        // 1. All active teachers of this school
        $qAll = $db->prepare("
            SELECT u.id AS teacher_user_id, t.full_name AS teacher_name,
                   COALESCE(t.expert_field, 'General Studies') AS primary_subject,
                   t.expert_field, u.email
            FROM teachers t
            JOIN users u ON u.id = t.user_id
            WHERE u.school_id = ? AND u.status = 'active'
            ORDER BY t.full_name ASC
        ");
        $qAll->execute([$schoolId]);
        $allTeachers = $qAll->fetchAll();

        // 2. Teachers who are assigned in another class during this exact slot
        $qBusy = $db->prepare("
            SELECT tt.teacher_user_id, tt.class_level, tt.section, tt.room_number, s.name AS subject_name
            FROM timetables tt
            JOIN subjects s ON s.id = tt.subject_id
            WHERE tt.school_id = ? AND tt.day_of_week = ? AND tt.period_number = ?
        ");
        $qBusy->execute([$schoolId, strtolower($day), $periodNumber]);
        $busyMap = [];
        foreach ($qBusy->fetchAll() as $b) {
            $busyMap[(int)$b['teacher_user_id']] = $b;
        }

        $free = [];
        $busy = [];
        foreach ($allTeachers as $t) {
            $uid = (int)$t['teacher_user_id'];
            if (isset($busyMap[$uid])) {
                $busy[] = array_merge($t, ['busy_slot' => $busyMap[$uid]]);
            } else {
                $free[] = $t;
            }
        }

        return ['free' => $free, 'busy' => $busy];
    }
}
