<?php
declare(strict_types=1);

namespace App\Chess;

use App\Database;
use App\Auth;
use App\Batch;
use App\Audit;

final class ChessRating
{
    public const TIERS = [
        'learner' => [
            'key'         => 'learner',
            'label'       => 'Learner',
            'academy'     => 'Starter Rooks',
            'min'         => 0,
            'max'         => 500,
            'badge'       => '♙',
            'color'       => '#CD7F32', // Bronze
            'description' => 'Basics, board rules, piece values & checkmate fundamentals',
        ],
        'rookie' => [
            'key'         => 'rookie',
            'label'       => 'Rookie',
            'academy'     => 'Starter Rooks',
            'min'         => 501,
            'max'         => 1000,
            'badge'       => '♘',
            'color'       => '#B87333', // Copper
            'description' => 'Tactics, forks, pins, simple mates & opening principles',
        ],
        'intermediate' => [
            'key'         => 'intermediate',
            'label'       => 'Intermediate',
            'academy'     => 'Middlegame Masters',
            'min'         => 1001,
            'max'         => 1500,
            'badge'       => '♗',
            'color'       => '#94A3B8', // Silver
            'description' => 'Openings, tactical combinations & rook endgames',
        ],
        'master' => [
            'key'         => 'master',
            'label'       => 'Master',
            'academy'     => 'Elite Rooks',
            'min'         => 1501,
            'max'         => 2000,
            'badge'       => '♖',
            'color'       => '#EAB308', // Gold
            'description' => 'Advanced positional play, clock mastery & tournament prep',
        ],
        'grandmaster' => [
            'key'         => 'grandmaster',
            'label'       => 'Grandmaster',
            'academy'     => 'Elite Rooks Pro',
            'min'         => 2001,
            'max'         => 3000,
            'badge'       => '♔',
            'color'       => '#38BDF8', // Diamond Sky
            'description' => 'Master theory, FIDE rating pursuit & championship play',
        ],
    ];

    /** 5 Calibrated Academy Bots (100, 750, 1250, 2000, 2700) */
    public const BOT_PROFILES = [
        'rising_pawn' => [
            'id'          => 'rising_pawn',
            'name'        => 'Rising Pawn',
            'rating'      => 100,
            'avatar'      => '♟️',
            'tier'        => 'Learner',
            'skill_level' => 0,
            'bot_level'   => 1,
            'description' => 'Gentle beginner sparring partner. Teaches piece values and basic board defense.',
        ],
        'rising_knight' => [
            'id'          => 'rising_knight',
            'name'        => 'Rising Knight',
            'rating'      => 750,
            'avatar'      => '♞',
            'tier'        => 'Rookie',
            'skill_level' => 4,
            'bot_level'   => 3,
            'description' => 'Tactical apprentice bot that actively hunts for knight forks and center outposts.',
        ],
        'rising_bishop' => [
            'id'          => 'rising_bishop',
            'name'        => 'Rising Bishop',
            'rating'      => 1250,
            'avatar'      => '♝',
            'tier'        => 'Intermediate',
            'skill_level' => 9,
            'bot_level'   => 5,
            'description' => 'Sharp intermediate player utilizing diagonal pins, discovered attacks, and mating nets.',
        ],
        'rising_rook' => [
            'id'          => 'rising_rook',
            'name'        => 'Rising Rook',
            'rating'      => 2000,
            'avatar'      => '♜',
            'tier'        => 'Master',
            'skill_level' => 15,
            'bot_level'   => 8,
            'description' => 'Relentless master calculating 10+ moves deep with punishing positional squeeze.',
        ],
        'rising_queen' => [
            'id'          => 'rising_queen',
            'name'        => 'Rising Queen',
            'rating'      => 2700,
            'avatar'      => '♛',
            'tier'        => 'Grandmaster',
            'skill_level' => 20,
            'bot_level'   => 10,
            'description' => 'Superhuman Grandmaster titan running full-strength Stockfish calculation.',
        ],
    ];

    /** Calibration bot schedule (5 matches mapped to the 5 Academy Bots) */
    public const CALIBRATION_BOTS = [
        1 => ['target_rating' => 100,  'bot_level' => 1, 'bot_key' => 'rising_pawn',   'name' => 'Rising Pawn (100 Rating)'],
        2 => ['target_rating' => 750,  'bot_level' => 3, 'bot_key' => 'rising_knight', 'name' => 'Rising Knight (750 Rating)'],
        3 => ['target_rating' => 1250, 'bot_level' => 5, 'bot_key' => 'rising_bishop', 'name' => 'Rising Bishop (1250 Rating)'],
        4 => ['target_rating' => 2000, 'bot_level' => 8, 'bot_key' => 'rising_rook',   'name' => 'Rising Rook (2000 Rating)'],
        5 => ['target_rating' => 2700, 'bot_level' => 10,'bot_key' => 'rising_queen',  'name' => 'Rising Queen (2700 Rating)'],
    ];

    public static function ensureTables(): void
    {
        try {
            $db = Database::connect();
            $db->exec("
                CREATE TABLE IF NOT EXISTS `chess_ratings` (
                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `student_user_id` bigint(20) UNSIGNED NOT NULL,
                  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
                  `current_rating` int(11) NOT NULL DEFAULT 400,
                  `peak_rating` int(11) NOT NULL DEFAULT 400,
                  `tier` enum('learner','rookie','intermediate','master','grandmaster') NOT NULL DEFAULT 'learner',
                  `calibration_matches_played` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
                  `calibration_completed` tinyint(1) NOT NULL DEFAULT 0,
                  `promotion_in_progress` tinyint(1) NOT NULL DEFAULT 0,
                  `promotion_matches_played` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
                  `promotion_score` decimal(3,1) NOT NULL DEFAULT 0.0,
                  `wins` int(10) UNSIGNED NOT NULL DEFAULT 0,
                  `draws` int(10) UNSIGNED NOT NULL DEFAULT 0,
                  `losses` int(10) UNSIGNED NOT NULL DEFAULT 0,
                  `last_quarter_reset` date DEFAULT NULL,
                  `assigned_teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
                  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_student` (`student_user_id`),
                  KEY `idx_tier_school` (`tier`, `school_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS `chess_games` (
                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `white_user_id` bigint(20) UNSIGNED DEFAULT NULL,
                  `black_user_id` bigint(20) UNSIGNED DEFAULT NULL,
                  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
                  `game_type` enum('calibration','bot','pvp','friendly','promotion','coaching') NOT NULL DEFAULT 'bot',
                  `bot_difficulty` tinyint(3) UNSIGNED DEFAULT 3,
                  `bot_rating_target` int(11) DEFAULT 600,
                  `result` enum('1-0','0-1','1/2-1/2','in_progress','aborted') NOT NULL DEFAULT 'in_progress',
                  `winner_user_id` bigint(20) UNSIGNED DEFAULT NULL,
                  `termination` varchar(100) DEFAULT NULL,
                  `white_rating_before` int(11) DEFAULT NULL,
                  `black_rating_before` int(11) DEFAULT NULL,
                  `white_rating_change` int(11) DEFAULT 0,
                  `black_rating_change` int(11) DEFAULT 0,
                  `fen` varchar(255) DEFAULT 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
                  `pgn` longtext DEFAULT NULL,
                  `moves_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
                  `white_accuracy` decimal(5,2) DEFAULT NULL,
                  `black_accuracy` decimal(5,2) DEFAULT NULL,
                  `started_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `ended_at` datetime DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `idx_white` (`white_user_id`),
                  KEY `idx_black` (`black_user_id`),
                  KEY `idx_game_type` (`game_type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (\Throwable $e) {}
    }

    public static function defaultProfile(int $userId, ?int $schoolId = null): array
    {
        return [
            'id' => 0,
            'student_user_id' => $userId,
            'school_id' => $schoolId,
            'current_rating' => 400,
            'peak_rating' => 400,
            'tier' => 'learner',
            'calibration_matches_played' => 0,
            'calibration_completed' => 0,
            'promotion_in_progress' => 0,
            'promotion_matches_played' => 0,
            'promotion_score' => 0.0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'assigned_teacher_id' => null,
        ];
    }

    public static function getOrCreate(int $studentUserId, ?int $schoolId = null): array
    {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT * FROM chess_ratings WHERE student_user_id = ? LIMIT 1");
            $stmt->execute([$studentUserId]);
            $row = $stmt->fetch();

            if ($row) {
                return $row;
            }

            // Initialize record
            $stmt = $db->prepare("
                INSERT INTO chess_ratings (student_user_id, school_id, current_rating, tier, peak_rating)
                VALUES (?, ?, 400, 'learner', 400)
            ");
            $stmt->execute([$studentUserId, $schoolId]);

            $stmt = $db->prepare("SELECT * FROM chess_ratings WHERE student_user_id = ? LIMIT 1");
            $stmt->execute([$studentUserId]);
            return $stmt->fetch() ?: self::defaultProfile($studentUserId, $schoolId);
        } catch (\Throwable $e) {
            // Auto create missing tables on first run
            self::ensureTables();
            try {
                $db = Database::connect();
                $stmt = $db->prepare("SELECT * FROM chess_ratings WHERE student_user_id = ? LIMIT 1");
                $stmt->execute([$studentUserId]);
                $row = $stmt->fetch();
                if ($row) return $row;

                $stmt = $db->prepare("
                    INSERT INTO chess_ratings (student_user_id, school_id, current_rating, tier, peak_rating)
                    VALUES (?, ?, 400, 'learner', 400)
                ");
                $stmt->execute([$studentUserId, $schoolId]);

                $stmt = $db->prepare("SELECT * FROM chess_ratings WHERE student_user_id = ? LIMIT 1");
                $stmt->execute([$studentUserId]);
                return $stmt->fetch() ?: self::defaultProfile($studentUserId, $schoolId);
            } catch (\Throwable $ex) {
                return self::defaultProfile($studentUserId, $schoolId);
            }
        }
    }

    public static function tierForRating(int $rating): string
    {
        if ($rating <= 500) return 'learner';
        if ($rating <= 1000) return 'rookie';
        if ($rating <= 1500) return 'intermediate';
        if ($rating <= 2000) return 'master';
        return 'grandmaster';
    }

    public static function nextTier(string $currentTier): ?string
    {
        $map = [
            'learner'      => 'rookie',
            'rookie'       => 'intermediate',
            'intermediate' => 'master',
            'master'       => 'grandmaster',
        ];
        return $map[$currentTier] ?? null;
    }

    public static function previousTier(string $currentTier): ?string
    {
        $map = [
            'grandmaster'  => 'master',
            'master'       => 'intermediate',
            'intermediate' => 'rookie',
            'rookie'       => 'learner',
        ];
        return $map[$currentTier] ?? null;
    }

    /**
     * Process calibration match result (matches 1-5)
     */
    public static function processCalibrationMatch(int $studentUserId, string $result, ?float $accuracy = null): array
    {
        $db = Database::connect();
        $ratingRow = self::getOrCreate($studentUserId);

        $matchesPlayed = (int)$ratingRow['calibration_matches_played'] + 1;
        $currentRating = (int)$ratingRow['current_rating'];
        $wins = (int)$ratingRow['wins'];
        $draws = (int)$ratingRow['draws'];
        $losses = (int)$ratingRow['losses'];

        $targetBotRating = self::CALIBRATION_BOTS[min($matchesPlayed, 5)]['target_rating'];

        // Rating calculation during calibration
        if ($result === '1-0') { // Student won
            $wins++;
            $currentRating = (int)round(($currentRating + $targetBotRating + 200) / 2);
        } elseif ($result === '1/2-1/2') { // Draw
            $draws++;
            $currentRating = (int)round(($currentRating + $targetBotRating) / 2);
        } else { // Loss
            $losses++;
            $currentRating = max(100, (int)round(($currentRating + $targetBotRating - 200) / 2));
        }

        // Slight bonus if accuracy was tracked and strong
        if ($accuracy !== null && $accuracy >= 75.0) {
            $currentRating += (int)round(($accuracy - 70) * 2);
        }

        $isCompleted = ($matchesPlayed >= 5);
        $newTier = self::tierForRating($currentRating);

        $db->prepare("
            UPDATE chess_ratings SET
                current_rating = ?,
                tier = ?,
                peak_rating = GREATEST(peak_rating, ?),
                games_played = games_played + 1,
                wins = ?,
                draws = ?,
                losses = ?,
                calibration_matches_played = ?,
                calibration_completed = ?
            WHERE student_user_id = ?
        ")->execute([
            $currentRating,
            $newTier,
            $currentRating,
            $wins,
            $draws,
            $losses,
            $matchesPlayed,
            $isCompleted ? 1 : 0,
            $studentUserId,
        ]);

        if ($isCompleted) {
            self::syncStudentToChessBatch($studentUserId, $newTier, (int)($ratingRow['school_id'] ?? 0));
            Audit::log('chess.calibration_completed', 'user', $studentUserId, [
                'final_rating' => $currentRating,
                'tier'         => $newTier,
            ]);
        }

        return [
            'matches_played' => $matchesPlayed,
            'completed'      => $isCompleted,
            'current_rating' => $currentRating,
            'tier'           => $newTier,
        ];
    }

    /**
     * Process a rated standard match (Bot or PvP)
     */
    public static function processMatchResult(
        int $studentUserId,
        int $opponentRating,
        string $result, // '1-0' (win), '0-1' (loss), '1/2-1/2' (draw)
        bool $isPromotionMatch = false,
        bool $isRelegationMatch = false
    ): array {
        $db = Database::connect();
        $r = self::getOrCreate($studentUserId);

        $myRating = (int)$r['current_rating'];
        $tier = $r['tier'];

        // Standard Elo calculation
        $expectedScore = 1 / (1 + pow(10, ($opponentRating - $myRating) / 400));
        $scores = ['1-0' => 1.0, '1/2-1/2' => 0.5, '0-1' => 0.0];
        $actualScore = $scores[$result] ?? 0.5;

        $kFactor = 32;
        $ratingChange = (int)round($kFactor * ($actualScore - $expectedScore));
        $newRating = max(100, $myRating + $ratingChange);

        $wins = (int)$r['wins'] + ($result === '1-0' ? 1 : 0);
        $draws = (int)$r['draws'] + ($result === '1/2-1/2' ? 1 : 0);
        $losses = (int)$r['losses'] + ($result === '0-1' ? 1 : 0);

        // Check if student qualifies for Promotion Gauntlet
        $tierMax = self::TIERS[$tier]['max'];
        $inPromotion = (bool)$r['in_promotion_battle'];
        $promoMatches = (int)$r['promotion_matches_played'];
        $promoWins = (int)$r['promotion_wins'];
        $promoDraws = (int)$r['promotion_draws'];

        $promoted = false;
        $relegated = false;

        if ($isPromotionMatch) {
            $promoMatches++;
            if ($result === '1-0') $promoWins++;
            if ($result === '1/2-1/2') $promoDraws++;

            // Check if gauntlet completed (3 matches)
            if ($promoMatches >= 3) {
                // Must score at least 1 win and 1 draw (or 2+ wins)
                $score = $promoWins + ($promoDraws * 0.5);
                if ($score >= 1.5) {
                    // Promotion earned!
                    $next = self::nextTier($tier);
                    if ($next) {
                        $tier = $next;
                        $newRating = max($newRating, self::TIERS[$next]['min']);
                        $promoted = true;
                    }
                } else {
                    // Failed promotion defense; reset rating slightly below threshold
                    $newRating = $tierMax - 20;
                }
                // Reset promotion status
                $inPromotion = false;
                $promoMatches = 0;
                $promoWins = 0;
                $promoDraws = 0;
            }
        } elseif (!$inPromotion && $newRating >= $tierMax && self::nextTier($tier)) {
            // Trigger Promotion Gauntlet!
            $inPromotion = true;
            $promoMatches = 0;
            $promoWins = 0;
            $promoDraws = 0;
        } else {
            // Natural tier boundary check
            $calculatedTier = self::tierForRating($newRating);
            if ($calculatedTier !== $tier) {
                $tier = $calculatedTier;
            }
        }

        $db->prepare("
            UPDATE chess_ratings SET
                current_rating = ?,
                tier = ?,
                peak_rating = GREATEST(peak_rating, ?),
                games_played = games_played + 1,
                wins = ?,
                draws = ?,
                losses = ?,
                in_promotion_battle = ?,
                promotion_matches_played = ?,
                promotion_wins = ?,
                promotion_draws = ?
            WHERE student_user_id = ?
        ")->execute([
            $newRating,
            $tier,
            $newRating,
            $wins,
            $draws,
            $losses,
            $inPromotion ? 1 : 0,
            $promoMatches,
            $promoWins,
            $promoDraws,
            $studentUserId,
        ]);

        if ($promoted) {
            self::syncStudentToChessBatch($studentUserId, $tier, (int)($r['school_id'] ?? 0));
            Audit::log('chess.promoted', 'user', $studentUserId, ['new_tier' => $tier, 'rating' => $newRating]);
        }

        return [
            'old_rating'    => $myRating,
            'new_rating'    => $newRating,
            'rating_change' => $ratingChange,
            'tier'          => $tier,
            'in_promotion'  => $inPromotion,
            'promo_matches' => $promoMatches,
            'promoted'      => $promoted,
        ];
    }

    /**
     * Automatic sync: enroll student into the appropriate Chess batch for their school and tier.
     */
    public static function syncStudentToChessBatch(int $studentUserId, string $tier, int $schoolId): void
    {
        if ($schoolId <= 0) return;

        $db = Database::connect();
        $tierInfo = self::TIERS[$tier] ?? self::TIERS['learner'];
        $batchName = 'Chess - ' . $tierInfo['academy'] . ' (' . $tierInfo['label'] . ')';

        // Check if batch exists
        $stmt = $db->prepare("SELECT id FROM batches WHERE school_id = ? AND name = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$schoolId, $batchName]);
        $batchId = $stmt->fetchColumn();

        if (!$batchId) {
            $uuid = Auth::uuid();
            $stmt = $db->prepare("
                INSERT INTO batches (uuid, school_id, name, subject, class_level, description, status)
                VALUES (?, ?, ?, 'Chess', ?, ?, 'active')
            ");
            $stmt->execute([
                $uuid,
                $schoolId,
                $batchName,
                $tierInfo['label'],
                'Rising Rooks Chess Academy batch for ' . $tierInfo['label'] . ' tier (' . $tierInfo['min'] . '-' . $tierInfo['max'] . ' Elo).'
            ]);
            $batchId = (int)$db->lastInsertId();
        }

        // Add student to this batch if not already enrolled
        $stmt = $db->prepare("
            INSERT IGNORE INTO batch_students (batch_id, student_user_id)
            VALUES (?, ?)
        ");
        $stmt->execute([(int)$batchId, $studentUserId]);
    }

    /**
     * Quarterly Season Reset (every 3 months)
     */
    public static function checkAndPerformSeasonalReset(int $studentUserId): void
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM chess_ratings WHERE student_user_id = ? LIMIT 1");
        $stmt->execute([$studentUserId]);
        $r = $stmt->fetch();
        if (!$r || empty($r['season_reset_at'])) return;

        $lastReset = new \DateTime($r['season_reset_at']);
        $now = new \DateTime();
        $diff = $now->diff($lastReset);

        if ($diff->days >= 90) { // 3 months elapsed
            $tier = $r['tier'];
            $tierMin = self::TIERS[$tier]['min'];
            $softResetRating = (int)round(($r['current_rating'] + $tierMin) / 2);

            $db->prepare("
                UPDATE chess_ratings SET
                    current_rating = ?,
                    season_number = season_number + 1,
                    season_reset_at = NOW(),
                    in_promotion_battle = 0,
                    promotion_matches_played = 0
                WHERE student_user_id = ?
            ")->execute([$softResetRating, $studentUserId]);

            Audit::log('chess.season_reset', 'user', $studentUserId, [
                'season'     => (int)$r['season_number'] + 1,
                'new_rating' => $softResetRating,
            ]);
        }
    }
}
