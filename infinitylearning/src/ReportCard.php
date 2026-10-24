<?php
declare(strict_types=1);

namespace App;

use PDO;

final class ReportCard
{
    public static function generateForStudent(int $studentUserId): ?array
    {
        $db = Database::connect();

        // 1. Fetch student & school details safely
        $stmt = $db->prepare("
            SELECT u.id AS user_id, u.email, u.phone, u.school_id,
                   s.id AS student_table_id,
                   COALESCE(s.full_name, u.email) AS full_name,
                   s.current_class, s.profile_image, s.city, s.state, s.school_board, s.academic_year,
                   sch.name AS school_name, sch.crest_logo, sch.address AS school_address,
                   sch.phone AS school_phone, sch.contact_email AS school_email,
                   sch.principal_signature, sch.official_seal
            FROM users u
            LEFT JOIN students s ON s.user_id = u.id
            LEFT JOIN schools sch ON sch.id = u.school_id
            WHERE u.id = ? OR s.id = ?
            LIMIT 1
        ");
        $stmt->execute([$studentUserId, $studentUserId]);
        $student = $stmt->fetch();
        if (!$student) {
            return null;
        }

        $userId = (int)$student['user_id'];
        $schoolId = (int)($student['school_id'] ?? 0);
        $isAiverse = School::isAiverse($schoolId) || (strcasecmp((string)($student['school_name'] ?? ''), 'Aiverse') === 0);

        // Generate clean admission number fallback
        $student['admission_number'] = 'ADM-' . date('Y') . '-' . str_pad((string)$userId, 4, '0', STR_PAD_LEFT);

        // 2. Check Aiverse Course Enrollments
        $aiEnrolledCourses = [];
        try {
            Aiverse::ensureSchema();
            $aiStmt = $db->prepare("
                SELECT ac.id, ac.title, ac.duration, ac.level, ae.status, ae.created_at
                FROM aiverse_enrollments ae
                JOIN aiverse_courses ac ON ac.id = ae.course_id
                WHERE ae.student_user_id = ? AND ae.status != 'cancelled'
                ORDER BY ae.created_at ASC
            ");
            $aiStmt->execute([$userId]);
            $aiEnrolledCourses = $aiStmt->fetchAll();
        } catch (\Throwable $e) {
            error_log('ReportCard AI course check: ' . $e->getMessage());
        }

        // If this is an Aiverse scholar who has NOT enrolled in any course yet:
        if ($isAiverse && empty($aiEnrolledCourses)) {
            return [
                'student' => $student,
                'is_aiverse' => true,
                'has_enrollments' => false,
                'enrolled_courses' => [],
                'subjects' => [],
                'attendance' => [
                    'label' => 'Awaiting Enrollment',
                    'percentage' => 0.0,
                    'present_days' => 0,
                    'total_days' => 0
                ],
                'overall' => [
                    'total_obtained' => 0.0,
                    'total_max' => 0.0,
                    'percentage' => 0.0,
                    'grade' => 'N/A',
                    'result' => 'NOT ENROLLED IN ANY COURSE',
                    'conduct' => 'Registered Scholar',
                    'academic_session' => date('Y') . '–' . (date('Y') + 1)
                ]
            ];
        }

        // 3. Attendance Summary (real data only, no hardcoded fake counts)
        $att = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0];
        try {
            $realAtt = Attendance::summaryForStudent($userId);
            if (!empty($realAtt)) {
                $att = array_merge($att, $realAtt);
            }
        } catch (\Throwable $e) {
            error_log('ReportCard attendance summary: ' . $e->getMessage());
        }

        $totalDays = ($att['present'] ?? 0) + ($att['absent'] ?? 0) + ($att['late'] ?? 0) + ($att['excused'] ?? 0);
        $attendancePercent = $totalDays > 0 ? round(((($att['present'] ?? 0) + ($att['late'] ?? 0)) / $totalDays) * 100, 1) : 100.0;

        // 4. Assessment Submissions
        $submissions = [];
        try {
            AcademicAssessment::ensureTable();
            $subStmt = $db->prepare("
                SELECT 
                    sub.name AS subject_name,
                    sub.id AS subject_id,
                    aa.assessment_type,
                    aa.total_marks,
                    asub.score_obtained
                FROM assessment_submissions asub
                JOIN academic_assessments aa ON aa.id = asub.assessment_id
                JOIN subjects sub ON sub.id = aa.subject_id
                WHERE asub.student_user_id = ?
                ORDER BY sub.name ASC, aa.created_at ASC
            ");
            $subStmt->execute([$userId]);
            $submissions = $subStmt->fetchAll();
        } catch (\Throwable $e) {
            error_log('ReportCard assessment query: ' . $e->getMessage());
        }

        $hasRealMarks = count($submissions) > 0;
        $subjectsMap = [];

        if ($isAiverse) {
            // ONLY show courses the scholar has ACTUALLY enrolled in!
            foreach ($aiEnrolledCourses as $ac) {
                $cTitle = $ac['title'];
                $subjectsMap[$cTitle] = [
                    'subject' => $cTitle,
                    'unit_test_obtained' => null,
                    'unit_test_max' => 20.0,
                    'term_exam_obtained' => null,
                    'term_exam_max' => 80.0,
                    'total_obtained' => null,
                    'total_max' => 100.0,
                    'percentage' => null,
                    'grade' => 'In Progress',
                    'remarks' => 'Enrolled · Coursework in progress'
                ];
            }
        } else {
            // Traditional school subjects
            $defaultSubjects = ['Mathematics', 'Science', 'English Literature', 'Social Studies', 'Computer Applications'];
            foreach ($defaultSubjects as $ds) {
                $subjectsMap[$ds] = [
                    'subject' => $ds,
                    'unit_test_obtained' => null,
                    'unit_test_max' => 20.0,
                    'term_exam_obtained' => null,
                    'term_exam_max' => 80.0,
                    'total_obtained' => null,
                    'total_max' => 100.0,
                    'percentage' => null,
                    'grade' => 'Pending',
                    'remarks' => 'Awaiting evaluation'
                ];
            }
        }

        // Map real assessment scores if available
        foreach ($submissions as $s) {
            $sName = $s['subject_name'] ?? '';
            if (empty($sName)) continue;

            if (!isset($subjectsMap[$sName])) {
                $subjectsMap[$sName] = [
                    'subject' => $sName,
                    'unit_test_obtained' => 0.0,
                    'unit_test_max' => 20.0,
                    'term_exam_obtained' => 0.0,
                    'term_exam_max' => 80.0,
                    'total_obtained' => 0.0,
                    'total_max' => 100.0,
                    'percentage' => 0.0,
                    'grade' => 'Pending',
                    'remarks' => ''
                ];
            }

            $score = (float)($s['score_obtained'] ?? 0.0);
            $maxMarks = (float)($s['total_marks'] ?: 1.0);
            $type = $s['assessment_type'] ?? 'homework';

            if (in_array($type, ['unit_test', 'pen_paper', 'homework', 'practice_quiz'], true)) {
                $scaled = ($score / $maxMarks) * 20.0;
                $current = (float)($subjectsMap[$sName]['unit_test_obtained'] ?? 0.0);
                $subjectsMap[$sName]['unit_test_obtained'] = min(20.0, max($current, $scaled));
            } else {
                $scaled = ($score / $maxMarks) * 80.0;
                $current = (float)($subjectsMap[$sName]['term_exam_obtained'] ?? 0.0);
                $subjectsMap[$sName]['term_exam_obtained'] = min(80.0, max($current, $scaled));
            }
        }

        $overallTotalObtained = 0.0;
        $overallTotalMax = 0.0;

        foreach ($subjectsMap as &$sm) {
            if ($hasRealMarks && ($sm['unit_test_obtained'] !== null || $sm['term_exam_obtained'] !== null)) {
                $ut = (float)($sm['unit_test_obtained'] ?? 0.0);
                $te = (float)($sm['term_exam_obtained'] ?? 0.0);
                $sm['total_obtained'] = round($ut + $te, 1);
                $sm['total_max'] = 100.0;
                $sm['percentage'] = round(($sm['total_obtained'] / $sm['total_max']) * 100, 1);
                $sm['grade'] = self::calculateGrade($sm['percentage']);
                $sm['remarks'] = self::getRemarks($sm['grade']);

                $overallTotalObtained += $sm['total_obtained'];
                $overallTotalMax += $sm['total_max'];
            }
        }
        unset($sm);

        $hasCalculatedScores = ($overallTotalMax > 0);
        $overallPercentage = $hasCalculatedScores ? round(($overallTotalObtained / $overallTotalMax) * 100, 1) : null;
        $overallGrade = $overallPercentage !== null ? self::calculateGrade($overallPercentage) : 'In Progress';

        if ($hasCalculatedScores) {
            $resultStatus = ($overallPercentage >= 40.0) ? 'PASSED & PROMOTED' : 'NEEDS IMPROVEMENT';
        } else {
            $resultStatus = $isAiverse ? 'COHORT IN PROGRESS' : 'EVALUATIONS PENDING';
        }

        return [
            'student' => $student,
            'is_aiverse' => $isAiverse,
            'has_enrollments' => true,
            'enrolled_courses' => $aiEnrolledCourses,
            'has_real_marks' => $hasRealMarks,
            'subjects' => array_values($subjectsMap),
            'attendance' => [
                'total_days' => $totalDays,
                'present_days' => ($att['present'] ?? 0),
                'percentage' => $totalDays > 0 ? $attendancePercent : 100.0
            ],
            'overall' => [
                'total_obtained' => $overallTotalObtained,
                'total_max' => $overallTotalMax,
                'percentage' => $overallPercentage,
                'grade' => $overallGrade,
                'result' => $resultStatus,
                'conduct' => 'Exemplary',
                'academic_session' => date('Y') . '–' . (date('Y') + 1)
            ]
        ];
    }

    public static function calculateGrade(float $pct): string
    {
        if ($pct >= 90.0) return 'A+';
        if ($pct >= 80.0) return 'A';
        if ($pct >= 70.0) return 'B+';
        if ($pct >= 60.0) return 'B';
        if ($pct >= 50.0) return 'C';
        if ($pct >= 40.0) return 'D';
        return 'F';
    }

    public static function getRemarks(string $grade): string
    {
        return match($grade) {
            'A+' => 'Outstanding mastery & analytical proficiency',
            'A'  => 'Excellent conceptual clarity and performance',
            'B+' => 'Very good grasp, consistent performance',
            'B'  => 'Good understanding, scope for higher accuracy',
            'C'  => 'Satisfactory, requires consistent revision',
            'D'  => 'Needs structured guidance in core areas',
            default => 'Remedial guidance recommended'
        };
    }
}
