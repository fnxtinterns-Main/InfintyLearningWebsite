<?php
declare(strict_types=1);

namespace App\Chess;

use App\Database;
use App\Auth;
use App\Audit;

final class ChessLive
{
    public static function createSession(
        int $teacherUserId,
        int $schoolId,
        string $title,
        string $type = '1-on-1',
        ?int $studentUserId = null,
        ?int $batchId = null
    ): array {
        $db = Database::connect();
        $uuid = Auth::uuid();
        $initialFen = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';

        $stmt = $db->prepare("
            INSERT INTO chess_live_sessions
                (uuid, school_id, teacher_user_id, student_user_id, batch_id,
                 title, session_type, current_fen, status, started_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'live', NOW())
        ");
        $stmt->execute([
            $uuid,
            $schoolId,
            $teacherUserId,
            $studentUserId,
            $batchId,
            $title,
            $type,
            $initialFen,
        ]);

        $id = (int)$db->lastInsertId();
        Audit::log('chess.live_session_started', 'chess_session', $id, [
            'type'    => $type,
            'student' => $studentUserId,
            'batch'   => $batchId,
        ]);

        return self::getSession($id) ?? ['id' => $id, 'uuid' => $uuid];
    }

    public static function getSession(int $id): ?array
    {
        $stmt = Database::connect()->prepare("
            SELECT cls.*,
                   t.full_name AS teacher_name,
                   s.full_name AS student_name,
                   sch.name AS school_name,
                   b.name AS batch_name
            FROM chess_live_sessions cls
            LEFT JOIN teachers t ON t.user_id = cls.teacher_user_id
            LEFT JOIN students s ON s.user_id = cls.student_user_id
            LEFT JOIN schools sch ON sch.id = cls.school_id
            LEFT JOIN batches b ON b.id = cls.batch_id
            WHERE cls.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getSessionByUuid(string $uuid): ?array
    {
        $stmt = Database::connect()->prepare("
            SELECT cls.*,
                   t.full_name AS teacher_name,
                   s.full_name AS student_name,
                   sch.name AS school_name,
                   b.name AS batch_name
            FROM chess_live_sessions cls
            LEFT JOIN teachers t ON t.user_id = cls.teacher_user_id
            LEFT JOIN students s ON s.user_id = cls.student_user_id
            LEFT JOIN schools sch ON sch.id = cls.school_id
            LEFT JOIN batches b ON b.id = cls.batch_id
            WHERE cls.uuid = ? LIMIT 1
        ");
        $stmt->execute([$uuid]);
        return $stmt->fetch() ?: null;
    }

    public static function syncMoveAndState(
        int $sessionId,
        string $fen,
        ?string $pgn = null,
        ?array $annotations = null,
        ?string $orientation = null
    ): void {
        $db = Database::connect();
        $fields = ["current_fen = ?"];
        $params = [$fen];

        if ($pgn !== null) {
            $fields[] = "pgn = ?";
            $params[] = $pgn;
        }
        if ($annotations !== null) {
            $fields[] = "annotations_json = ?";
            $params[] = json_encode($annotations);
        }
        if ($orientation !== null) {
            $fields[] = "orientation = ?";
            $params[] = $orientation;
        }

        $params[] = $sessionId;
        $sql = "UPDATE chess_live_sessions SET " . implode(", ", $fields) . " WHERE id = ? AND status = 'live'";
        $db->prepare($sql)->execute($params);
    }

    public static function endSession(int $sessionId): void
    {
        Database::connect()->prepare("
            UPDATE chess_live_sessions SET status = 'ended', ended_at = NOW() WHERE id = ?
        ")->execute([$sessionId]);
        Audit::log('chess.live_session_ended', 'chess_session', $sessionId);
    }

    public static function getActiveSessionsForUser(int $userId, string $role): array
    {
        $db = Database::connect();
        if ($role === 'teacher') {
            $stmt = $db->prepare("
                SELECT cls.*, s.full_name AS student_name
                FROM chess_live_sessions cls
                LEFT JOIN students s ON s.user_id = cls.student_user_id
                WHERE cls.teacher_user_id = ? AND cls.status = 'live'
                ORDER BY cls.started_at DESC
            ");
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare("
                SELECT cls.*, t.full_name AS teacher_name
                FROM chess_live_sessions cls
                LEFT JOIN teachers t ON t.user_id = cls.teacher_user_id
                WHERE (cls.student_user_id = ? OR cls.batch_id IN (
                    SELECT batch_id FROM batch_students WHERE student_user_id = ?
                )) AND cls.status = 'live'
                ORDER BY cls.started_at DESC
            ");
            $stmt->execute([$userId, $userId]);
        }
        return $stmt->fetchAll();
    }
}
