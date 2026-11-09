-- ========================================================
-- INFINITY LEARNING — Rising Rooks Chess Academy Schema
-- Run this in phpMyAdmin if tables are not yet created
-- ========================================================

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
