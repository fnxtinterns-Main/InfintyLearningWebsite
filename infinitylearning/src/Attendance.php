<?php
declare(strict_types=1);

namespace App;

final class Attendance
{
    public static function mark(int $batchId, int $studentUserId, string $date, string $status, int $byUserId, string $remarks = ''): void
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO attendance (batch_id, student_user_id, attendance_date, status, marked_by, remarks)
            VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE status=VALUES(status), marked_by=VALUES(marked_by), remarks=VALUES(remarks)
        ");
        $stmt->execute([$batchId, $studentUserId, $date, $status, $byUserId, $remarks ?: null]);
    }

    public static function forBatchDate(int $batchId, string $date): array
    {
        $stmt = Database::connect()->prepare("
            SELECT a.* FROM attendance a
            WHERE a.batch_id=? AND a.attendance_date=?
        ");
        $stmt->execute([$batchId, $date]);
        $rows = $stmt->fetchAll();
        // Re-key by student_user_id for easy lookup
        $out = [];
        foreach ($rows as $r) $out[(int)$r['student_user_id']] = $r;
        return $out;
    }

    public static function summaryForStudent(int $userId): array
    {
        $stmt = Database::connect()->prepare("
            SELECT status, COUNT(*) AS cnt FROM attendance
            WHERE student_user_id=? GROUP BY status
        ");
        $stmt->execute([$userId]);
        $out = ['present'=>0,'absent'=>0,'late'=>0,'excused'=>0];
        foreach ($stmt->fetchAll() as $r) $out[$r['status']] = (int)$r['cnt'];
        return $out;
    }

    /** Recent 30 attendance rows for a student, for a mini history view. */
    public static function recentForStudent(int $userId, int $limit = 10): array
    {
        $stmt = Database::connect()->prepare("
            SELECT a.*, b.name AS batch_name
            FROM attendance a
            LEFT JOIN batches b ON b.id = a.batch_id
            WHERE a.student_user_id = ?
            ORDER BY a.attendance_date DESC LIMIT ?
        ");
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Percent present/absent/etc for a batch over a period. */
    public static function summaryForBatch(int $batchId, ?string $from = null, ?string $to = null): array
    {
        $db = Database::connect();
        $sql = "SELECT status, COUNT(*) AS cnt FROM attendance WHERE batch_id = ?";
        $params = [$batchId];
        if ($from) { $sql .= " AND attendance_date >= ?"; $params[] = $from; }
        if ($to)   { $sql .= " AND attendance_date <= ?"; $params[] = $to; }
        $sql .= " GROUP BY status";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $out = ['present'=>0,'absent'=>0,'late'=>0,'excused'=>0];
        foreach ($stmt->fetchAll() as $r) $out[$r['status']] = (int)$r['cnt'];
        return $out;
    }
}