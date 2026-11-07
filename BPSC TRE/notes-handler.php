<?php
/**
 * Infinity Learning - Study Notes & Handouts Handler
 * SQL-Powered Notes Persistence with upload date & time
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

define('BPSC_PORTAL', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Auth.php';

function getStoredNotes(): array {
    $rows = Database::fetchAll("SELECT * FROM notes ORDER BY uploaded_at DESC");
    foreach ($rows as &$r) {
        $r['topicId'] = $r['topic_id'];
        $r['attached_file'] = !empty($r['attached_file']) && is_string($r['attached_file'])
            ? json_decode($r['attached_file'], true)
            : null;
        $r['quiz'] = !empty($r['quiz']) && is_string($r['quiz'])
            ? (json_decode($r['quiz'], true) ?: [])
            : [];
    }
    unset($r);
    return $rows;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $notes = getStoredNotes();
    echo json_encode([
        'success' => true,
        'count' => count($notes),
        'notes' => $notes
    ]);
    exit;
}

if ($method === 'POST') {
    $user = Auth::user();
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input) || empty($input)) {
        $input = $_POST;
    }

    $role = $user['role'] ?? $input['role'] ?? $input['user_role'] ?? '';
    // Students are strictly blocked from uploading notes - only teachers and admins are permitted
    if ($role === 'student' || ($user && ($user['role'] ?? '') === 'student')) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied: Study notes can only be uploaded by teachers and administrators. Students are authorized to read notes and take tests only.'
        ]);
        exit;
    }

    $title = trim($input['title'] ?? '');
    $topicId = trim($input['topicId'] ?? $input['topic_id'] ?? 'general_notes');
    $category = trim($input['category'] ?? 'BPSC TRE 4.0 Syllabus');
    $summary = trim($input['summary'] ?? '');
    $body = trim($input['body'] ?? '');
    $tips = trim($input['tips'] ?? '');
    $author = trim($input['author'] ?? '');

    if (empty($author)) {
        $author = $user ? ($user['name'] . ' (' . ucfirst($user['role']) . ')') : 'Faculty Terminal';
    }

    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Note title is required.']);
        exit;
    }

    $now = new DateTime();
    $uploadedAt = $now->format('Y-m-d H:i:s');
    $formattedDateTime = $now->format('d M Y, h:i A');

    $attachedFile = null;
    $rawAttach = !empty($input['attached_file']) ? $input['attached_file'] : (!empty($input['attachedFile']) ? $input['attachedFile'] : null);
    if (!empty($rawAttach) && is_array($rawAttach)) {
        $attachedFile = [
            'name' => htmlspecialchars($rawAttach['name'] ?? 'Document.pdf'),
            'size' => htmlspecialchars($rawAttach['size'] ?? 'PDF Document'),
            'type' => htmlspecialchars($rawAttach['type'] ?? 'application/pdf'),
            'content' => $rawAttach['content'] ?? null
        ];
    }

    $quiz = [];
    if (!empty($input['quiz']) && is_array($input['quiz'])) {
        $quiz = $input['quiz'];
    }

    $noteId = 'NOTE_' . strtoupper(bin2hex(random_bytes(3)));
    $attachedFileJson = $attachedFile ? json_encode($attachedFile, JSON_UNESCAPED_UNICODE) : null;
    $quizJson = json_encode($quiz, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $finalSummary = $summary ?: mb_substr(strip_tags($body), 0, 140) . '...';

    $existing = Database::fetchOne("SELECT id FROM notes WHERE topic_id = :tid", [':tid' => $topicId]);

    if ($existing) {
        Database::execute(
            "UPDATE notes 
             SET title = :title, category = :cat, summary = :sum, body = :body, tips = :tips, author = :author, attached_file = :att, quiz = :quiz, uploaded_at = :up, formatted_date_time = :fmt
             WHERE topic_id = :tid",
            [
                ':title' => $title,
                ':cat' => $category,
                ':sum' => $finalSummary,
                ':body' => $body,
                ':tips' => $tips,
                ':author' => $author,
                ':att' => $attachedFileJson,
                ':quiz' => $quizJson,
                ':up' => $uploadedAt,
                ':fmt' => $formattedDateTime,
                ':tid' => $topicId
            ]
        );
        $noteId = $existing['id'];
    } else {
        Database::execute(
            "INSERT INTO notes (id, topic_id, title, category, summary, body, tips, author, attached_file, quiz, uploaded_at, formatted_date_time)
             VALUES (:id, :tid, :title, :cat, :sum, :body, :tips, :author, :att, :quiz, :up, :fmt)",
            [
                ':id' => $noteId,
                ':tid' => $topicId,
                ':title' => $title,
                ':cat' => $category,
                ':sum' => $finalSummary,
                ':body' => $body,
                ':tips' => $tips,
                ':author' => $author,
                ':att' => $attachedFileJson,
                ':quiz' => $quizJson,
                ':up' => $uploadedAt,
                ':fmt' => $formattedDateTime
            ]
        );
    }

    $newNote = [
        'id' => $noteId,
        'topicId' => $topicId,
        'title' => $title,
        'category' => $category,
        'summary' => $finalSummary,
        'body' => $body,
        'tips' => $tips,
        'author' => $author,
        'attached_file' => $attachedFile,
        'quiz' => $quiz,
        'uploaded_at' => $uploadedAt,
        'formatted_date_time' => $formattedDateTime
    ];

    echo json_encode([
        'success' => true,
        'message' => 'New study notes uploaded successfully in SQL database!',
        'note' => $newNote
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
