-- =========================================================
-- INFINITY LEARNING — Rising Rooks Chess Academy Schema
-- Fully integrated with existing users, schools, batches & certificates
-- =========================================================

-- 1. CHESS RATINGS & TIER PROGRESSION
CREATE TABLE IF NOT EXISTS `chess_ratings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `current_rating` int(10) UNSIGNED NOT NULL DEFAULT 400,
  `tier` enum('learner','rookie','intermediate','master','grandmaster') NOT NULL DEFAULT 'learner',
  `games_played` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `wins` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `draws` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `losses` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `peak_rating` int(10) UNSIGNED NOT NULL DEFAULT 400,
  `calibration_completed` tinyint(1) NOT NULL DEFAULT 0,
  `calibration_matches_played` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `in_promotion_battle` tinyint(1) NOT NULL DEFAULT 0,
  `promotion_matches_played` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `promotion_wins` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `promotion_draws` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `in_relegation_defense` tinyint(1) NOT NULL DEFAULT 0,
  `relegation_matches_played` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `relegation_wins` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `relegation_draws` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `season_number` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `season_reset_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_student_chess` (`student_user_id`),
  KEY `idx_tier` (`tier`),
  KEY `idx_rating` (`current_rating`),
  KEY `idx_school_chess` (`school_id`),
  CONSTRAINT `fk_chess_rating_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_chess_rating_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CHESS GAMES & MATCH HISTORY
CREATE TABLE IF NOT EXISTS `chess_games` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `white_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `black_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `game_type` enum('calibration','bot','pvp','promotion','relegation','live_coaching','tournament') NOT NULL DEFAULT 'bot',
  `bot_difficulty` tinyint(3) UNSIGNED DEFAULT NULL,
  `bot_rating_target` int(10) UNSIGNED DEFAULT NULL,
  `initial_fen` varchar(150) NOT NULL DEFAULT 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
  `current_fen` varchar(150) NOT NULL DEFAULT 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
  `pgn` longtext DEFAULT NULL,
  `moves_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `result` enum('1-0','0-1','1/2-1/2','*') NOT NULL DEFAULT '*',
  `winner_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `termination` varchar(100) DEFAULT NULL,
  `white_rating_before` int(10) UNSIGNED DEFAULT NULL,
  `black_rating_before` int(10) UNSIGNED DEFAULT NULL,
  `white_rating_change` int(11) DEFAULT 0,
  `black_rating_change` int(11) DEFAULT 0,
  `white_accuracy` decimal(5,2) DEFAULT NULL,
  `black_accuracy` decimal(5,2) DEFAULT NULL,
  `coach_notes` text DEFAULT NULL,
  `status` enum('in_progress','completed','aborted') NOT NULL DEFAULT 'in_progress',
  `created_at` datetime DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_chess_game_uuid` (`uuid`),
  KEY `idx_white_user` (`white_user_id`),
  KEY `idx_black_user` (`black_user_id`),
  KEY `idx_game_type` (`game_type`),
  KEY `idx_game_school` (`school_id`),
  CONSTRAINT `fk_cg_white` FOREIGN KEY (`white_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cg_black` FOREIGN KEY (`black_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cg_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CHESS CHALLENGES & LOBBY
CREATE TABLE IF NOT EXISTS `chess_challenges` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `challenger_user_id` bigint(20) UNSIGNED NOT NULL,
  `challenged_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `challenger_color` enum('white','black','random') NOT NULL DEFAULT 'random',
  `time_control_minutes` tinyint(3) UNSIGNED NOT NULL DEFAULT 10,
  `increment_seconds` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `rated` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('pending','accepted','declined','cancelled','expired') NOT NULL DEFAULT 'pending',
  `game_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_challenge_uuid` (`uuid`),
  KEY `idx_challenger` (`challenger_user_id`),
  KEY `idx_challenged` (`challenged_user_id`),
  KEY `idx_challenge_school` (`school_id`),
  CONSTRAINT `fk_cc_challenger` FOREIGN KEY (`challenger_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cc_challenged` FOREIGN KEY (`challenged_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cc_game` FOREIGN KEY (`game_id`) REFERENCES `chess_games` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. LIVE 1-ON-1 & BATCH INTERACTIVE SESSIONS
CREATE TABLE IF NOT EXISTS `chess_live_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `session_type` enum('1-on-1','batch_class') NOT NULL DEFAULT '1-on-1',
  `current_fen` varchar(150) NOT NULL DEFAULT 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
  `pgn` longtext DEFAULT NULL,
  `orientation` enum('white','black') NOT NULL DEFAULT 'white',
  `annotations_json` longtext DEFAULT NULL CHECK (json_valid(`annotations_json`)),
  `teacher_turn` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('scheduled','live','ended') NOT NULL DEFAULT 'scheduled',
  `scheduled_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_live_session_uuid` (`uuid`),
  KEY `idx_live_teacher` (`teacher_user_id`),
  KEY `idx_live_student` (`student_user_id`),
  KEY `idx_live_school` (`school_id`),
  CONSTRAINT `fk_cls_teacher` FOREIGN KEY (`teacher_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cls_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cls_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. CHESS COACH ASSIGNMENTS (Admin assigns coach to student or tier)
CREATE TABLE IF NOT EXISTS `chess_coach_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
  `student_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_tier` enum('learner','rookie','intermediate','master','grandmaster','all') DEFAULT 'all',
  `notes` text DEFAULT NULL,
  `assigned_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cca_teacher` (`teacher_user_id`),
  KEY `idx_cca_student` (`student_user_id`),
  KEY `idx_cca_school` (`school_id`),
  CONSTRAINT `fk_cca_teacher` FOREIGN KEY (`teacher_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cca_student` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cca_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cca_admin` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. CHESS CURRICULUM (Opening, Middlegame, Endgame, Tactics per tier)
CREATE TABLE IF NOT EXISTS `chess_curriculum` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tier` enum('learner','rookie','intermediate','master','grandmaster') NOT NULL,
  `category` enum('opening','middlegame','endgame','tactics') NOT NULL,
  `title` varchar(200) NOT NULL,
  `subtitle` varchar(300) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `initial_fen` varchar(150) NOT NULL DEFAULT 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1',
  `solution_pgn` text DEFAULT NULL,
  `key_concepts` longtext DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_curr_tier_cat` (`tier`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. SEED INITIAL CURRICULUM MODULES
INSERT INTO `chess_curriculum` (`tier`, `category`, `title`, `subtitle`, `description`, `initial_fen`, `solution_pgn`, `key_concepts`, `sort_order`) VALUES
('learner', 'opening', 'The Golden Rules of Opening', 'Control the center, develop pieces, castle early', 'Every master follows three golden rules in the opening: 1. Control the central squares (e4, d4, e5, d5). 2. Develop your knights and bishops actively. 3. Castle early to safeguard your king.', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '1. e4 e5 2. Nf3 Nc6 3. Bc4 Bc5 4. O-O Nf6', 'Center Control, Development, King Safety', 1),
('learner', 'tactics', 'Defending Against Scholar\'s Mate', 'How to stop the early Queen attack', 'Beginners often face 1.e4 e5 2.Qh5 or 2.Bc4 aiming for 4.Qxf7#. Learn how 2...Nc6 and 3...g6 neutralize White\'s attack immediately while winning developmental tempos.', 'rnbqkbnr/pppp1ppp/8/4p3/2B1P3/8/PPPP1PPP/RNBQK1NR b KQkq - 1 2', '2... Nc6 3. Qh5 g6 4. Qf3 Nf6', 'Early Queen attacks are premature. Defend with development.', 2),
('learner', 'endgame', 'The Ladder Checkmate (Two Rooks)', 'Rolling Rooks to trap the enemy King', 'The ladder checkmate pushes the opponent King row by row (or file by file) to the edge of the board using two active rooks cutting off adjacent escape ranks.', '8/8/8/4k3/8/8/r7/1r2K3 w - - 0 1', '1. Kd1 Ra1#', 'Cut off rank, roll the next rook, deliver mate on the edge.', 3),
('rookie', 'opening', 'The Italian Game (Giuoco Piano)', 'Classical development and peaceful harmony', 'The Italian Game is the best opening to understand open piece play. White targets the weak f7 square while developing harmoniously with rapid castling.', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '1. e4 e5 2. Nf3 Nc6 3. Bc4 Bc5 4. c3 Nf6 5. d4', 'Center occupation, d4 pawn push, c3 preparation.', 1),
('rookie', 'tactics', 'The Deadly Knight Fork', 'Attacking two valuable pieces simultaneously', 'A fork occurs when a single piece attacks two or more enemy pieces at the same time. The knight is the most lethal forking piece because it leaps over obstacles.', 'r1bqkb1r/pppp1ppp/2n5/4p3/2B1n3/5N2/PPPP1PPP/RNBQK2R w KQkq - 0 4', '4. Bxf7+ Kxf7 5. Nxe4', 'Look for geometric alignment where the knight can strike.', 2),
('rookie', 'endgame', 'King & Rook vs. Lone King', 'The standard box-shrinking technique', 'Use your Rook to build a box around the enemy king, walk your king over for opposition, and slide the rook in for the finish on the perimeter.', '8/8/8/3k4/8/8/8/R3K3 w - - 0 1', '1. Ra4 Kc5 2. Kd2 Kb5 3. Rh4', 'Shrink the box until the king reaches the edge, bring your king close.', 3),
('intermediate', 'opening', 'The Ruy Lopez (Spanish Opening)', 'The king of classical openings', 'One of the deepest and most respected openings in chess history. 1.e4 e5 2.Nf3 Nc6 3.Bb5 puts immediate pressure on the defender of the e5 pawn.', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', '1. e4 e5 2. Nf3 Nc6 3. Bb5 a6 4. Ba4 Nf6 5. O-O', 'Indirect pressure on e5, maneuvering the bishop, kingside castling.', 1),
('intermediate', 'middlegame', 'Outposts & Weak Squares', 'Anchoring knights in enemy territory', 'An outpost is a square on the 4th, 5th, or 6th rank that cannot be attacked by enemy pawns. A knight securely stationed on an outpost dominates the board.', 'r1b2rk1/pp1n1ppp/2p1pn2/3p4/2PP4/2N1PN2/PP2BPPP/R2Q1RK1 w - - 0 9', '9. cxd5 exd5 10. Ne5', 'Find holes in enemy pawn structure, occupy them with minor pieces.', 2),
('intermediate', 'endgame', 'Lucena Position (Winning Rook Endgames)', 'Building a bridge to queen the pawn', 'The Lucena Position is the fundamental winning technique in Rook and pawn endgames when your pawn has reached the 7th rank with your king in front.', '1K6/3P1k2/8/8/8/8/r7/3R4 w - - 0 1', '1. Rd4 Ke7 2. Kc7 Rc2+ 3. Kb6 Rb2+ 4. Kc6 Rc2+ 5. Kb5 Rb2+ 6. Rb4', 'Build the bridge on the 4th rank with Rd4 to block checks.', 3),
('master', 'opening', 'Sicilian Defense: Open Sicilian & Dragon', 'Counter-attacking dynamic asymmetry', 'Black refuses symmetry from move 1, fighting for the d4 square with 1...c5 and creating an imbalanced queenside pawn majority with active piece play.', 'rnbqkbnr/pp1ppppp/8/2p5/4P3/8/PPPP1PPP/RNBQKBNR w KQkq c6 0 2', '1. e4 c5 2. Nf3 d6 3. d4 cxd4 4. Nxd4 Nf6 5. Nc3 g6', 'Dynamic pawn structures, opposite-side castling races.', 1),
('master', 'middlegame', 'Minority Attack & Pawn Islands', 'Strategic pawn levers on the queenside', 'In pawn structures like the Carlsbad, White advances two queenside pawns (a-b) against three Black pawns (a-b-c) to create an isolated, targetable pawn on c6.', 'r1bq1rk1/pp1nbppp/2p1pn2/3p4/2PP4/2N1PN2/PP2BPPP/R1BQ1RK1 w - - 0 8', '8. Rb1 a5 9. a3 Re8 10. b4 axb4 11. axb4', 'Creating fixed weaknesses in the enemy camp.', 2),
('grandmaster', 'endgame', 'Theoretical Rook vs Rook & Bishop Endgames', 'Defense and precision in deep endgames', 'Mastering defensive setups (like the Cochrane defense and second-rank defenses) that determine the thin line between half-point saves and losses.', '8/8/8/8/8/4k3/4r3/4K1B1 w - - 0 1', '1. Kf1 Ra2 2. Bh2', 'Triangulation, preventing zugzwang, exact king-bishop coordination.', 1);
