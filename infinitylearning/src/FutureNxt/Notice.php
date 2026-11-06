<?php
declare(strict_types=1);

namespace App\FutureNxt;

use PDO;

final class Notice
{
    public static function create(array $data, int $authorId): array
    {
        try {
            $db = Database::connect();
            $title = trim((string)($data['title'] ?? ''));
            if ($title === '') {
                return ['success' => false, 'error' => 'Notice title is required.'];
            }

            $content = trim((string)($data['content'] ?? ''));
            if ($content === '') {
                return ['success' => false, 'error' => 'Notice content is required.'];
            }

            $category = in_array(($data['category'] ?? ''), ['holiday', 'event', 'notice', 'urgent', 'policy'], true)
                ? $data['category']
                : 'notice';

            $audience = in_array(($data['target_audience'] ?? ''), ['all', 'employees', 'interns', 'managers'], true)
                ? $data['target_audience']
                : 'all';

            $noticeDate = !empty($data['notice_date']) ? $data['notice_date'] : date('Y-m-d');
            $isPinned = !empty($data['is_pinned']) ? 1 : 0;

            $stmt = $db->prepare("
                INSERT INTO fnx_notices (title, category, target_audience, content, notice_date, is_pinned, status, created_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, 'published', ?, NOW(), NOW())
            ");
            $stmt->execute([
                $title,
                $category,
                $audience,
                $content,
                $noticeDate,
                $isPinned,
                $authorId
            ]);

            $id = (int)$db->lastInsertId();
            Audit::log('create_notice', 'notice', $id, "Published {$category} notice: '{$title}'", $authorId);

            // Optionally send email notification if checkbox is ticked
            if (!empty($data['send_email_notification'])) {
                EmailNotifier::broadcastNotice($id);
            }

            return ['success' => true, 'id' => $id];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public static function list(array $filters = []): array
    {
        $db = Database::connect();
        $sql = "
            SELECT n.*, 
                   e.full_name AS author_name, 
                   e.emp_code AS author_code,
                   e.role AS author_role
            FROM fnx_notices n
            LEFT JOIN fnx_employees e ON e.id = n.created_by
            WHERE n.status = 'published'
        ";
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= " AND n.category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['audience'])) {
            $sql .= " AND (n.target_audience = 'all' OR n.target_audience = ?)";
            $params[] = $filters['audience'];
        }

        if (!empty($filters['q'])) {
            $q = '%' . trim($filters['q']) . '%';
            $sql .= " AND (n.title LIKE ? OR n.content LIKE ?)";
            $params[] = $q;
            $params[] = $q;
        }

        $sql .= " ORDER BY n.is_pinned DESC, n.notice_date DESC, n.id DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public static function getRecent(int $limit = 5): array
    {
        return self::list(['limit' => $limit]);
    }

    public static function delete(int $id, int $userId): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE fnx_notices SET status = 'archived' WHERE id = ?");
        $stmt->execute([$id]);
        Audit::log('delete_notice', 'notice', $id, "Archived notice #{$id}", $userId);
        return true;
    }

    public static function pin(int $id, bool $pin, int $userId): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE fnx_notices SET is_pinned = ? WHERE id = ?");
        $stmt->execute([$pin ? 1 : 0, $id]);
        Audit::log('pin_notice', 'notice', $id, ($pin ? "Pinned" : "Unpinned") . " notice #{$id}", $userId);
        return true;
    }
}
