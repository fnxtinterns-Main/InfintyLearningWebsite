<?php
declare(strict_types=1);

namespace App\Chess;

use App\Database;
use App\Auth;
use App\Audit;

final class ChessGame
{
    public static function createGame(array $d): array
    {
        $db = Database::connect();
        $uuid = Auth::uuid();

        $stmt = $db->prepare("
            INSERT INTO chess_games
                (uuid, white_user_id, black_user_id, school_id, batch_id,
                 game_type, bot_difficulty, bot_rating_target, initial_fen, current_fen,
                 white_rating_before, black_rating_before, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'in_progress')
        ");

        $whiteId = !empty($d['white_user_id']) ? (int)$d['white_user_id'] : null;
        $blackId = !empty($d['black_user_id']) ? (int)$d['black_user_id'] : null;
        $initialFen = $d['initial_fen'] ?? 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';

        $stmt->execute([
            $uuid,
            $whiteId,
            $blackId,
            !empty($d['school_id']) ? (int)$d['school_id'] : null,
            !empty($d['batch_id']) ? (int)$d['batch_id'] : null,
            $d['game_type'] ?? 'bot',
            !empty($d['bot_difficulty']) ? (int)$d['bot_difficulty'] : null,
            !empty($d['bot_rating_target']) ? (int)$d['bot_rating_target'] : null,
            $initialFen,
            $initialFen,
            !empty($d['white_rating']) ? (int)$d['white_rating'] : null,
            !empty($d['black_rating']) ? (int)$d['black_rating'] : null,
        ]);

        $id = (int)$db->lastInsertId();
        return self::getGame($id) ?? ['id' => $id, 'uuid' => $uuid];
    }

    public static function getGame(int $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT cg.*,
                   sw.full_name AS white_name,
                   sb.full_name AS black_name,
                   sch.name AS school_name
            FROM chess_games cg
            LEFT JOIN students sw ON sw.user_id = cg.white_user_id
            LEFT JOIN students sb ON sb.user_id = cg.black_user_id
            LEFT JOIN schools sch ON sch.id = cg.school_id
            WHERE cg.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getGameByUuid(string $uuid): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT cg.*,
                   sw.full_name AS white_name,
                   sb.full_name AS black_name,
                   sch.name AS school_name
            FROM chess_games cg
            LEFT JOIN students sw ON sw.user_id = cg.white_user_id
            LEFT JOIN students sb ON sb.user_id = cg.black_user_id
            LEFT JOIN schools sch ON sch.id = cg.school_id
            WHERE cg.uuid = ? LIMIT 1
        ");
        $stmt->execute([$uuid]);
        return $stmt->fetch() ?: null;
    }

    public static function updateBoardState(int $gameId, string $currentFen, string $pgn, int $movesCount): void
    {
        Database::connect()->prepare("
            UPDATE chess_games
            SET current_fen = ?, pgn = ?, moves_count = ?
            WHERE id = ? AND status = 'in_progress'
        ")->execute([$currentFen, $pgn, $movesCount, $gameId]);
    }

    public static function finishGame(
        int $gameId,
        string $result, // '1-0', '0-1', '1/2-1/2'
        string $termination,
        ?int $winnerUserId = null,
        ?float $whiteAccuracy = null,
        ?float $blackAccuracy = null
    ): array {
        $db = Database::connect();
        $game = self::getGame($gameId);
        if (!$game) {
            throw new \RuntimeException('Game not found.');
        }

        $whiteId = $game['white_user_id'] ? (int)$game['white_user_id'] : null;
        $blackId = $game['black_user_id'] ? (int)$game['black_user_id'] : null;
        $gameType = $game['game_type'];

        $whiteRatingChange = 0;
        $blackRatingChange = 0;

        // Process rating updates
        if ($gameType === 'calibration') {
            $studentId = $whiteId ?: $blackId;
            if ($studentId) {
                $isWhite = ($studentId === $whiteId);
                $studentResult = $isWhite ? $result : ($result === '1-0' ? '0-1' : ($result === '0-1' ? '1-0' : '1/2-1/2'));
                $calib = ChessRating::processCalibrationMatch($studentId, $studentResult, $isWhite ? $whiteAccuracy : $blackAccuracy);
            }
        } elseif ($gameType === 'promotion') {
            $studentId = $whiteId ?: $blackId;
            if ($studentId) {
                $isWhite = ($studentId === $whiteId);
                $studentResult = $isWhite ? $result : ($result === '1-0' ? '0-1' : ($result === '0-1' ? '1-0' : '1/2-1/2'));
                $opponentRating = (int)($game['bot_rating_target'] ?? 1000);
                $res = ChessRating::processMatchResult($studentId, $opponentRating, $studentResult, true, false);
                if ($isWhite) {
                    $whiteRatingChange = $res['rating_change'];
                } else {
                    $blackRatingChange = $res['rating_change'];
                }
            }
        } elseif ($gameType === 'bot') {
            $studentId = $whiteId ?: $blackId;
            if ($studentId) {
                $isWhite = ($studentId === $whiteId);
                $studentResult = $isWhite ? $result : ($result === '1-0' ? '0-1' : ($result === '0-1' ? '1-0' : '1/2-1/2'));
                $opponentRating = (int)($game['bot_rating_target'] ?? 600);
                $res = ChessRating::processMatchResult($studentId, $opponentRating, $studentResult);
                if ($isWhite) {
                    $whiteRatingChange = $res['rating_change'];
                } else {
                    $blackRatingChange = $res['rating_change'];
                }
            }
        } elseif ($gameType === 'pvp' && $whiteId && $blackId) {
            $whiteRating = (int)($game['white_rating_before'] ?? 500);
            $blackRating = (int)($game['black_rating_before'] ?? 500);

            $resWhite = ChessRating::processMatchResult($whiteId, $blackRating, $result);
            $whiteRatingChange = $resWhite['rating_change'];

            $flippedResults = ['1-0' => '0-1', '0-1' => '1-0', '1/2-1/2' => '1/2-1/2'];
            $blackResult = $flippedResults[$result] ?? '1/2-1/2';
            $resBlack = ChessRating::processMatchResult($blackId, $whiteRating, $blackResult);
            $blackRatingChange = $resBlack['rating_change'];
        }

        $stmt = $db->prepare("
            UPDATE chess_games SET
                result = ?,
                winner_user_id = ?,
                termination = ?,
                white_rating_change = ?,
                black_rating_change = ?,
                white_accuracy = ?,
                black_accuracy = ?,
                status = 'completed',
                completed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $result,
            $winnerUserId,
            $termination,
            $whiteRatingChange,
            $blackRatingChange,
            $whiteAccuracy,
            $blackAccuracy,
            $gameId,
        ]);

        return self::getGame($gameId) ?? [];
    }

    public static function getStudentGames(int $studentUserId, int $limit = 20): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT cg.*,
                   sw.full_name AS white_name,
                   sb.full_name AS black_name
            FROM chess_games cg
            LEFT JOIN students sw ON sw.user_id = cg.white_user_id
            LEFT JOIN students sb ON sb.user_id = cg.black_user_id
            WHERE (cg.white_user_id = ? OR cg.black_user_id = ?)
            ORDER BY cg.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $studentUserId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $studentUserId, \PDO::PARAM_INT);
        $stmt->bindValue(3, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function addCoachNotes(int $gameId, string $notes): void
    {
        Database::connect()->prepare("
            UPDATE chess_games SET coach_notes = ? WHERE id = ?
        ")->execute([$notes, $gameId]);
    }

    public static function getSchoolLeaderboard(?int $schoolId = null, int $limit = 25): array
    {
        $db = Database::connect();
        $sql = "
            SELECT cr.*,
                   s.full_name,
                   s.profile_image,
                   s.current_class,
                   sch.name AS school_name
            FROM chess_ratings cr
            JOIN students s ON s.user_id = cr.student_user_id
            LEFT JOIN schools sch ON sch.id = cr.school_id
            WHERE 1=1
        ";
        $params = [];
        if ($schoolId) {
            $sql .= " AND cr.school_id = ?";
            $params[] = $schoolId;
        }
        $sql .= " ORDER BY cr.current_rating DESC LIMIT " . (int)$limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
