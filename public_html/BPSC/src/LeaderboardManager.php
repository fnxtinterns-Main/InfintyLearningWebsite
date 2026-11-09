<?php
/**
 * Infinity Learning - BPSC TRE 4.0 Leaderboard Manager
 * SQL-Powered evaluation & merit ranking engine (District & State)
 */

if (!defined('BPSC_PORTAL')) {
    define('BPSC_PORTAL', true);
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/UserManager.php';

class LeaderboardManager {
    /**
     * Retrieve all recorded test attempts from SQL storage
     */
    public static function getAllTestResults(): array {
        return Database::fetchAll("SELECT * FROM test_results ORDER BY submitted_at DESC");
    }

    /**
     * Record a completed CBT assessment score and recalculate candidate standing
     */
    public static function recordTest(string $userId, string $paperId, string $paperTitle, float $score, float $totalMarks, float $accuracy): array {
        $student = UserManager::findByIdentifier($userId);
        if (!$student) {
            return ['success' => false, 'message' => 'Candidate not found in portal database.'];
        }

        $normScore = round($score, 2);
        $normMax = $totalMarks > 0 ? $totalMarks : 150.0;
        $normAcc = round(max(0.0, min(100.0, $accuracy)), 1);
        $testId = 'TR_' . strtoupper(bin2hex(random_bytes(4)));
        $now = date('Y-m-d H:i:s');
        $cadre = $student['level_label'] ?? 'Classes 6–8 (Middle)';

        $record = [
            'id' => $testId,
            'user_id' => $student['id'],
            'user_name' => $student['name'],
            'user_email' => $student['email'],
            'district' => $student['district'] ?? 'Patna',
            'cadre' => $cadre,
            'paper_id' => $paperId,
            'paper_title' => $paperTitle,
            'score' => $normScore,
            'max_marks' => $normMax,
            'accuracy' => $normAcc,
            'submitted_at' => $now
        ];

        Database::execute(
            "INSERT INTO test_results (id, user_id, user_name, user_email, district, cadre, paper_id, paper_title, score, max_marks, accuracy, submitted_at)
             VALUES (:id, :uid, :uname, :uemail, :dist, :cadre, :pid, :title, :score, :max, :acc, :sub)",
            [
                ':id' => $record['id'],
                ':uid' => $student['id'],
                ':uname' => $student['name'],
                ':uemail' => $student['email'],
                ':dist' => $record['district'],
                ':cadre' => $record['cadre'],
                ':pid' => $paperId,
                ':title' => $paperTitle,
                ':score' => $normScore,
                ':max' => $normMax,
                ':acc' => $normAcc,
                ':sub' => $record['submitted_at']
            ]
        );

        // Aggregate attempts for this candidate from SQL
        $attempts = Database::fetchAll("SELECT score, accuracy FROM test_results WHERE user_id = :uid", [':uid' => $student['id']]);
        $testsTaken = count($attempts);
        $highestScore = 0.0;
        $totalAcc = 0.0;

        foreach ($attempts as $att) {
            $attScore = (float)($att['score'] ?? 0.0);
            if ($attScore > $highestScore) {
                $highestScore = $attScore;
            }
            $totalAcc += (float)($att['accuracy'] ?? 0.0);
        }

        $avgAcc = $testsTaken > 0 ? round($totalAcc / $testsTaken, 1) : 0.0;
        $bestScore = round($highestScore, 2);

        $achievements = $student['achievements'] ?? [];
        $achievements['tests_taken'] = $testsTaken;
        $achievements['best_score'] = "{$bestScore} / {$normMax}";
        $achievements['avg_accuracy'] = "{$avgAcc}%";

        // Update student profile in SQL database
        UserManager::updateUserStats($student['id'], [
            'overall_score' => $bestScore,
            'best_score' => $bestScore,
            'tests_taken' => $testsTaken,
            'avg_accuracy' => $avgAcc,
            'achievements' => $achievements
        ]);

        // Synchronize active session if logged-in user matches
        if (isset($_SESSION['bpsc_user']) && is_array($_SESSION['bpsc_user'])) {
            if (($_SESSION['bpsc_user']['id'] ?? '') === $student['id']) {
                $_SESSION['bpsc_user']['overall_score'] = $bestScore;
                $_SESSION['bpsc_user']['best_score'] = $bestScore;
                $_SESSION['bpsc_user']['tests_taken'] = $testsTaken;
                $_SESSION['bpsc_user']['avg_accuracy'] = $avgAcc;
                $_SESSION['bpsc_user']['achievements'] = $achievements;
            }
        }

        return [
            'success' => true,
            'message' => 'Assessment score recorded successfully in SQL database.',
            'record' => $record,
            'stats' => [
                'overall_score' => $bestScore,
                'tests_taken' => $testsTaken,
                'avg_accuracy' => $avgAcc
            ]
        ];
    }

    /**
     * Compute comprehensive state-wide and district merit rankings
     */
    public static function computeAllRankings(): array {
        $users = UserManager::getAllUsers();
        $students = array_filter($users, fn($u) => ($u['role'] ?? '') === ROLE_STUDENT);
        $results = self::getAllTestResults();

        // Index test attempts by user ID
        $attemptsByUser = [];
        foreach ($results as $r) {
            $uid = $r['user_id'] ?? '';
            if ($uid !== '') {
                $attemptsByUser[$uid][] = $r;
            }
        }

        $rankedList = [];
        foreach ($students as $stu) {
            $uid = $stu['id'];
            $attempts = $attemptsByUser[$uid] ?? [];

            if (!empty($attempts)) {
                $maxScore = 0.0;
                $sumAcc = 0.0;
                foreach ($attempts as $att) {
                    $s = (float)($att['score'] ?? 0.0);
                    if ($s > $maxScore) {
                        $maxScore = $s;
                    }
                    $sumAcc += (float)($att['accuracy'] ?? 0.0);
                }
                $bestScore = round($maxScore, 2);
                $testsTaken = count($attempts);
                $avgAcc = round($sumAcc / $testsTaken, 1);
            } else {
                $bestScore = (float)($stu['best_score'] ?? $stu['overall_score'] ?? 0.0);
                $testsTaken = (int)($stu['tests_taken'] ?? 0);
                $avgAcc = (float)($stu['avg_accuracy'] ?? 0.0);

                if ($bestScore === 0.0 && !empty($stu['achievements']['best_score']) && $stu['achievements']['best_score'] !== '--') {
                    $parts = explode('/', (string)$stu['achievements']['best_score']);
                    $bestScore = (float)trim($parts[0]);
                    $testsTaken = (int)($stu['achievements']['tests_taken'] ?? 1);
                    $avgAcc = (float)str_replace('%', '', (string)($stu['achievements']['avg_accuracy'] ?? '85'));
                }
            }

            $district = trim($stu['district'] ?? 'Patna') ?: 'Patna';
            $levelLabel = $stu['level_label'] ?? 'Classes 6–8 (Middle)';

            $rankedList[] = [
                'id' => $uid,
                'full_name' => $stu['name'] ?? 'Candidate',
                'email' => $stu['email'] ?? '',
                'district' => $district,
                'cadre_label' => $levelLabel,
                'tests_taken' => $testsTaken,
                'overall_score' => $bestScore,
                'best_score' => $bestScore,
                'avg_accuracy' => $avgAcc,
                'created_at' => $stu['created_at'] ?? '2026-11-01'
            ];
        }

        // Multi-tier sort: overall_score DESC, avg_accuracy DESC, tests_taken DESC
        usort($rankedList, fn($a, $b) =>
            ($b['overall_score'] <=> $a['overall_score'])
            ?: ($b['avg_accuracy'] <=> $a['avg_accuracy'])
            ?: ($b['tests_taken'] <=> $a['tests_taken'])
        );

        // Assign State & District Ranks in a single efficient pass
        $stateRank = 1;
        $districtRanks = [];
        foreach ($rankedList as &$item) {
            $item['state_rank'] = $stateRank++;
            $dist = $item['district'];
            $districtRanks[$dist] = ($districtRanks[$dist] ?? 0) + 1;
            $item['district_rank'] = $districtRanks[$dist];
        }
        unset($item);

        return $rankedList;
    }

    /**
     * Retrieve filtered leaderboard by scope (district or state)
     */
    public static function getLeaderboard(string $scope = 'district', ?string $district = null, ?string $currentUserId = null): array {
        $all = self::computeAllRankings();
        $scope = strtolower($scope) === 'state' ? 'state' : 'district';

        // Find current user standing
        $currentUserStanding = null;
        if ($currentUserId) {
            foreach ($all as $item) {
                if ($item['id'] === $currentUserId || strcasecmp($item['email'], $currentUserId) === 0) {
                    $currentUserStanding = $item;
                    break;
                }
            }
        }

        // Resolve default district
        if ($scope === 'district' && (empty($district) || $district === 'all')) {
            $district = (!empty($currentUserStanding['district'])) ? $currentUserStanding['district'] : 'Patna';
        }

        // Build scoped rankings list
        $displayList = [];
        $isDistrict = ($scope === 'district');

        foreach ($all as $item) {
            if ($isDistrict && strcasecmp($item['district'], $district) !== 0) {
                continue;
            }

            $rank = $isDistrict ? $item['district_rank'] : $item['state_rank'];
            $isCurrent = $currentUserId && (
                $item['id'] === $currentUserId
                || strcasecmp($item['email'], $currentUserId) === 0
            );

            $entry = $item;
            $entry['display_rank'] = $rank;
            $entry['badge'] = self::generateBadge($rank, $isDistrict ? 'District' : 'State', $item['district']);
            $entry['is_current_user'] = (bool)$isCurrent;

            $displayList[] = $entry;
        }

        return [
            'scope' => $scope,
            'selected_district' => $district,
            'total_in_scope' => count($displayList),
            'total_state' => count($all),
            'current_user_standing' => $currentUserStanding,
            'rankings' => $displayList,
            'districts_list' => UserManager::getBiharDistricts()
        ];
    }

    /**
     * Generate authentic merit badge based on rank and scope
     */
    private static function generateBadge(int $rank, string $type, ?string $district = null): string {
        return match ($rank) {
            1 => $type === 'State' ? '🥇 State Rank #1 (Bihar Topper)' : "🥇 #1 in {$district}",
            2 => $type === 'State' ? '🥈 State Rank #2 (Merit Elite)' : "🥈 #2 in {$district}",
            3 => $type === 'State' ? '🥉 State Rank #3 (High Honors)' : "🥉 #3 in {$district}",
            4, 5 => "⭐ Top 5 {$type} Merit",
            6, 7, 8, 9, 10 => "Top 10 {$type} Aspirant",
            default => 'Qualified Candidate'
        };
    }
}
