<?php
declare(strict_types=1);

define('APP_ROOT', __DIR__ . '/../infinitylearning');
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\Session;
use App\Auth;
use App\Helper;
use App\Chess\ChessRating;
use App\Chess\ChessGame;

Session::start();
Auth::requireLogin();

header('Content-Type: application/json');

$user = Auth::user();
$userId = (int)$user['id'];
$schoolId = !empty($user['school_id']) ? (int)$user['school_id'] : null;

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $playerColor = strtolower(trim($_POST['player_color'] ?? 'white'));

    switch ($action) {
        case 'start_calibration':
            $rating = ChessRating::getOrCreate($userId, $schoolId);
            $matchNum = min(5, (int)$rating['calibration_matches_played'] + 1);
            $botInfo = ChessRating::CALIBRATION_BOTS[$matchNum];

            $isBlack = ($playerColor === 'black');
            $game = ChessGame::createGame([
                'white_user_id'     => $isBlack ? null : $userId,
                'black_user_id'     => $isBlack ? $userId : null,
                'school_id'         => $schoolId,
                'game_type'         => 'calibration',
                'bot_difficulty'    => $botInfo['bot_level'],
                'bot_rating_target' => $botInfo['target_rating'],
                'white_rating'      => $isBlack ? $botInfo['target_rating'] : (int)$rating['current_rating'],
                'black_rating'      => $isBlack ? (int)$rating['current_rating'] : $botInfo['target_rating'],
            ]);

            Helper::json([
                'success'   => true,
                'game'      => $game,
                'match_num' => $matchNum,
                'bot_info'  => $botInfo,
            ]);
            break;

        case 'start_bot':
            $targetRating = (int)($_POST['target_rating'] ?? 600);
            $botLevel = (int)($_POST['bot_level'] ?? 2);
            $rating = ChessRating::getOrCreate($userId, $schoolId);

            $isBlack = ($playerColor === 'black');
            $game = ChessGame::createGame([
                'white_user_id'     => $isBlack ? null : $userId,
                'black_user_id'     => $isBlack ? $userId : null,
                'school_id'         => $schoolId,
                'game_type'         => 'bot',
                'bot_difficulty'    => $botLevel,
                'bot_rating_target' => $targetRating,
                'white_rating'      => $isBlack ? $targetRating : (int)$rating['current_rating'],
                'black_rating'      => $isBlack ? (int)$rating['current_rating'] : $targetRating,
            ]);

            Helper::json([
                'success' => true,
                'game'    => $game,
            ]);
            break;

        case 'start_promotion':
            $rating = ChessRating::getOrCreate($userId, $schoolId);
            $tier = $rating['tier'];
            $tierMax = ChessRating::TIERS[$tier]['max'];
            $targetRating = $tierMax + 100; // Face next level benchmark

            $isBlack = ($playerColor === 'black');
            $game = ChessGame::createGame([
                'white_user_id'     => $isBlack ? null : $userId,
                'black_user_id'     => $isBlack ? $userId : null,
                'school_id'         => $schoolId,
                'game_type'         => 'promotion',
                'bot_difficulty'    => 6,
                'bot_rating_target' => $targetRating,
                'white_rating'      => $isBlack ? $targetRating : (int)$rating['current_rating'],
                'black_rating'      => $isBlack ? (int)$rating['current_rating'] : $targetRating,
            ]);

            Helper::json([
                'success' => true,
                'game'    => $game,
            ]);
            break;

        case 'save_move':
            $gameId = (int)($_POST['game_id'] ?? 0);
            $fen = trim($_POST['fen'] ?? '');
            $pgn = trim($_POST['pgn'] ?? '');
            $movesCount = (int)($_POST['moves_count'] ?? 0);

            if ($gameId <= 0 || !$fen) {
                Helper::json(['success' => false, 'error' => 'Invalid parameters'], 400);
            }

            ChessGame::updateBoardState($gameId, $fen, $pgn, $movesCount);
            Helper::json(['success' => true]);
            break;

        case 'finish_game':
            $gameId = (int)($_POST['game_id'] ?? 0);
            $result = $_POST['result'] ?? '*'; // '1-0', '0-1', '1/2-1/2'
            $termination = trim($_POST['termination'] ?? 'Checkmate');
            $accuracy = !empty($_POST['accuracy']) ? (float)$_POST['accuracy'] : null;

            if ($gameId <= 0) {
                Helper::json(['success' => false, 'error' => 'Missing game ID'], 400);
            }

            $loadedGame = ChessGame::getGame($gameId);
            $winnerId = null;
            if ($result === '1-0') {
                $winnerId = !empty($loadedGame['white_user_id']) ? (int)$loadedGame['white_user_id'] : null;
            } elseif ($result === '0-1') {
                $winnerId = !empty($loadedGame['black_user_id']) ? (int)$loadedGame['black_user_id'] : null;
            }

            $updatedGame = ChessGame::finishGame(
                $gameId,
                $result,
                $termination,
                $winnerId,
                $accuracy
            );

            // Fetch updated rating row
            $updatedRating = ChessRating::getOrCreate($userId, $schoolId);

            Helper::json([
                'success' => true,
                'game'    => $updatedGame,
                'rating'  => $updatedRating,
            ]);
            break;

        case 'get_stats':
            $rating = ChessRating::getOrCreate($userId, $schoolId);
            $recentGames = ChessGame::getStudentGames($userId, 10);
            $leaderboard = ChessGame::getSchoolLeaderboard($schoolId, 10);

            Helper::json([
                'success'      => true,
                'rating'       => $rating,
                'recent_games' => $recentGames,
                'leaderboard'  => $leaderboard,
            ]);
            break;

        default:
            Helper::json(['success' => false, 'error' => 'Invalid action'], 400);
    }
} catch (\Throwable $e) {
    Helper::json(['success' => false, 'error' => $e->getMessage()], 500);
}
