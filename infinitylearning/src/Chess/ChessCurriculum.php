<?php
declare(strict_types=1);

namespace App\Chess;

use App\Database;

final class ChessCurriculum
{
    public static function getForTier(string $tier, ?string $category = null): array
    {
        $db = Database::connect();
        $sql = "SELECT * FROM chess_curriculum WHERE tier = ?";
        $params = [$tier];

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY sort_order ASC, id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getLesson(int $id): ?array
    {
        $stmt = Database::connect()->prepare("SELECT * FROM chess_curriculum WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
