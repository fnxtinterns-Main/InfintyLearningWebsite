<?php
/**
 * Infinity Learning - CBT Question Papers & Test Publishing Handler
 * SQL-Powered Question Paper Management & Persistence
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (!defined('BPSC_PORTAL')) {
    define('BPSC_PORTAL', true);
}
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Auth.php';

function getStoredQuestionPapers(?string $statusFilter = null): array {
    $sql = "SELECT * FROM question_papers";
    $params = [];
    if (!empty($statusFilter)) {
        $sql .= " WHERE status = :status";
        $params[':status'] = $statusFilter;
    }
    $sql .= " ORDER BY published_at DESC";

    $rows = Database::fetchAll($sql, $params);
    foreach ($rows as &$r) {
        $r['questions'] = !empty($r['questions']) && is_string($r['questions'])
            ? (json_decode($r['questions'], true) ?: [])
            : [];
        $r['totalMarks'] = (float)($r['total_marks'] ?? 150);
        $r['duration'] = (int)($r['duration'] ?? 120);
        $r['penalty'] = $r['penalty'] !== null ? (string)$r['penalty'] : '-0.33';
        $firstQ = !empty($r['questions']) ? $r['questions'][0] : null;
        $r['marksPerQuestion'] = (float)($firstQ['posMarks'] ?? $firstQ['marks'] ?? (count($r['questions']) > 0 ? ($r['totalMarks'] / count($r['questions'])) : 1.0));
    }
    unset($r);
    return $rows;
}

function getStoredQuestionBank(): array {
    $rows = Database::fetchAll("SELECT * FROM question_bank ORDER BY topic ASC, id ASC");
    foreach ($rows as &$r) {
        $r['options'] = !empty($r['options']) && is_string($r['options'])
            ? (json_decode($r['options'], true) ?: [])
            : [];
        $r['ans'] = (int)($r['correct_answer'] ?? 0);
        $r['posMarks'] = (float)($r['marks'] ?? 1.0);
        $r['negMarks'] = abs((float)($r['penalty'] ?? 0.33));
        $r['section'] = $r['topic'] ?? 'General Studies';
    }
    unset($r);
    return $rows;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'papers';
    if ($action === 'question_bank') {
        $qb = getStoredQuestionBank();
        echo json_encode([
            'success' => true,
            'count' => count($qb),
            'questions' => $qb
        ]);
        exit;
    }

    $statusFilter = $_GET['status'] ?? null;
    $papers = getStoredQuestionPapers($statusFilter);
    echo json_encode([
        'success' => true,
        'count' => count($papers),
        'papers' => $papers
    ]);
    exit;
}

if ($method === 'POST') {
    $user = Auth::user();
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input) || empty($input)) {
        $input = $_POST;
    }

    $action = $input['action'] ?? 'publish';

    // --- Action: Get Question Bank ---
    if ($action === 'get_question_bank') {
        $qb = getStoredQuestionBank();
        echo json_encode(['success' => true, 'questions' => $qb]);
        exit;
    }

    // --- Action: Add Single Question to Question Bank ---
    if ($action === 'add_question') {
        $topic = trim($input['topic'] ?? 'General Studies');
        $questionText = trim($input['question'] ?? '');
        $options = $input['options'] ?? [];
        $correctAns = (int)($input['correct_answer'] ?? $input['ans'] ?? 0);
        $marks = (float)($input['marks'] ?? $input['posMarks'] ?? 1.0);
        $penalty = trim($input['penalty'] ?? $input['negMarks'] ?? '-0.33');
        $explanation = trim($input['explanation'] ?? '');
        $diagram = $input['diagram'] ?? null;

        if (empty($questionText)) {
            echo json_encode(['success' => false, 'message' => 'Question text is required.']);
            exit;
        }

        $now = date('Y-m-d H:i:s');
        $authorName = $user ? ($user['name'] . ' (' . ucfirst($user['role']) . ')') : 'Academic Faculty';
        $newId = 'QB_' . strtoupper(bin2hex(random_bytes(3)));

        Database::execute(
            "INSERT INTO question_bank (id, topic, question, options, correct_answer, marks, penalty, explanation, diagram, author, created_at)
             VALUES (:id, :topic, :q, :opts, :ans, :marks, :pen, :exp, :dia, :author, :now)",
            [
                ':id' => $newId,
                ':topic' => $topic,
                ':q' => $questionText,
                ':opts' => json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ':ans' => $correctAns,
                ':marks' => $marks,
                ':pen' => $penalty,
                ':exp' => $explanation,
                ':dia' => $diagram,
                ':author' => $authorName,
                ':now' => $now
            ]
        );

        $qb = getStoredQuestionBank();
        echo json_encode([
            'success' => true,
            'message' => "Question added successfully to Question Bank under topic '{$topic}'!",
            'new_id' => $newId,
            'questions' => $qb
        ]);
        exit;
    }

    // --- Action: Bulk Upload Questions to Question Bank ---
    if ($action === 'bulk_upload_questions') {
        $questionsList = $input['questions'] ?? [];
        $defaultTopic = trim($input['topic'] ?? 'General Studies');
        $now = date('Y-m-d H:i:s');
        $authorName = $user ? ($user['name'] . ' (' . ucfirst($user['role']) . ')') : 'Academic Faculty';
        $insertedCount = 0;

        foreach ($questionsList as $item) {
            $qText = trim($item['question'] ?? '');
            if (empty($qText)) continue;

            $qTopic = trim($item['section'] ?? $item['topic'] ?? $defaultTopic);
            $qOpts = $item['options'] ?? [];
            $qAns = (int)($item['ans'] ?? $item['correct_answer'] ?? 0);
            $qMarks = (float)($item['posMarks'] ?? $item['marks'] ?? 1.0);
            $qPen = trim((string)($item['negMarks'] ?? $item['penalty'] ?? '-0.33'));
            $qExp = trim($item['explanation'] ?? '');
            $qDia = $item['diagram'] ?? null;
            $newId = 'QB_' . strtoupper(bin2hex(random_bytes(3)));

            Database::execute(
                "INSERT INTO question_bank (id, topic, question, options, correct_answer, marks, penalty, explanation, diagram, author, created_at)
                 VALUES (:id, :topic, :q, :opts, :ans, :marks, :pen, :exp, :dia, :author, :now)",
                [
                    ':id' => $newId,
                    ':topic' => $qTopic,
                    ':q' => $qText,
                    ':opts' => json_encode($qOpts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ':ans' => $qAns,
                    ':marks' => $qMarks,
                    ':pen' => $qPen,
                    ':exp' => $qExp,
                    ':dia' => $qDia,
                    ':author' => $authorName,
                    ':now' => $now
                ]
            );
            $insertedCount++;
        }

        $qb = getStoredQuestionBank();
        echo json_encode([
            'success' => true,
            'message' => "Successfully uploaded {$insertedCount} questions into the Master Question Bank!",
            'inserted_count' => $insertedCount,
            'questions' => $qb
        ]);
        exit;
    }

    // --- Action: Toggle status (published <-> draft) ---
    if ($action === 'toggle_status') {
        $targetId = trim($input['id'] ?? '');
        $paper = Database::fetchOne("SELECT id, status FROM question_papers WHERE id = :id", [':id' => $targetId]);
        if ($paper) {
            $newStatus = ($paper['status'] === 'published') ? 'draft' : 'published';
            Database::execute("UPDATE question_papers SET status = :status WHERE id = :id", [':status' => $newStatus, ':id' => $targetId]);
            $papers = getStoredQuestionPapers();
            echo json_encode([
                'success' => true,
                'message' => 'Question paper status updated successfully.',
                'papers' => $papers
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Paper not found.']);
        }
        exit;
    }

    // --- Action: Publish or Create paper ---
    $title = trim($input['title'] ?? '');
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Paper title is required.']);
        exit;
    }

    $now = new DateTime();
    $publishedAt = $now->format('Y-m-d H:i:s');
    $formattedDateTime = $now->format('d M Y, h:i A');

    $author = trim($input['author'] ?? '');
    if (empty($author)) {
        $author = $user ? ($user['name'] . ' (' . ucfirst($user['role']) . ')') : 'Faculty Terminal';
    }

    $targetId = trim($input['id'] ?? '');
    $cadre = trim($input['cadre'] ?? 'Classes 6–8 (Middle)');
    $duration = (int)($input['duration'] ?? 120);
    if ($duration <= 0) $duration = 120;
    $visibility = trim($input['visibility'] ?? 'all');
    $penalty = trim($input['penalty'] ?? '-0.33');
    $status = trim($input['status'] ?? 'published');

    // Questions list
    $questions = [];
    if (!empty($input['questions']) && is_array($input['questions'])) {
        $questions = $input['questions'];
    }

    $totalMarks = 0;
    if (isset($input['totalMarks']) && is_numeric($input['totalMarks']) && (float)$input['totalMarks'] > 0) {
        $totalMarks = (float)$input['totalMarks'];
    } else {
        foreach ($questions as $q) {
            $totalMarks += (float)($q['posMarks'] ?? $q['marks'] ?? 1);
        }
        if ($totalMarks <= 0) $totalMarks = count($questions);
    }

    $questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $existing = (!empty($targetId) && $targetId !== '__NEW_PAPER__')
        ? Database::fetchOne("SELECT id FROM question_papers WHERE id = :id", [':id' => $targetId])
        : null;

    if ($existing) {
        // Update existing in SQL
        Database::execute(
            "UPDATE question_papers 
             SET title = :title, cadre = :cadre, duration = :dur, total_marks = :marks, penalty = :pen, status = :status, visibility = :vis, questions = :qs, updated_at = :now
             WHERE id = :id",
            [
                ':title' => $title,
                ':cadre' => $cadre,
                ':dur' => $duration,
                ':marks' => $totalMarks,
                ':pen' => $penalty,
                ':status' => $status,
                ':vis' => $visibility,
                ':qs' => $questionsJson,
                ':now' => $publishedAt,
                ':id' => $targetId
            ]
        );
        $paperId = $targetId;
    } else {
        // Insert new into SQL
        $paperId = (!empty($targetId) && $targetId !== '__NEW_PAPER__') ? $targetId : ('QP_' . mt_rand(1001, 9999));
        Database::execute(
            "INSERT INTO question_papers (id, title, cadre, duration, total_marks, penalty, status, visibility, author, questions, published_at, formatted_date_time)
             VALUES (:id, :title, :cadre, :dur, :marks, :pen, :status, :vis, :author, :qs, :pub, :fmt)",
            [
                ':id' => $paperId,
                ':title' => $title,
                ':cadre' => $cadre,
                ':dur' => $duration,
                ':marks' => $totalMarks,
                ':pen' => $penalty,
                ':status' => $status,
                ':vis' => $visibility,
                ':author' => $author,
                ':qs' => $questionsJson,
                ':pub' => $publishedAt,
                ':fmt' => $formattedDateTime
            ]
        );
    }

    $allPapers = getStoredQuestionPapers();
    $updatedPaper = null;
    foreach ($allPapers as $p) {
        if ($p['id'] === $paperId) {
            $updatedPaper = $p;
            break;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Assessment published successfully and is now LIVE for all candidates!',
        'paper' => $updatedPaper,
        'papers' => $allPapers
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
