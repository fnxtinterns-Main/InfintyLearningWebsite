<?php
declare(strict_types=1);

$appRoot = is_dir(__DIR__ . '/../../infinitylearning')
    ? __DIR__ . '/../../infinitylearning'
    : __DIR__ . '/../infinitylearning';

if (!defined('APP_ROOT')) {
    define('APP_ROOT', $appRoot);
}
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Uploader, Helper};

header('Content-Type: application/json; charset=utf-8');

Session::start();
if (!Auth::check() || !in_array(Auth::user()['role'] ?? '', ['teacher', 'admin'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

if (!Session::verifyCsrf($_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token.']);
    exit;
}

if (empty($_FILES['image']['tmp_name'])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'No image file uploaded.']);
    exit;
}

try {
    // 1. Upload & save the image
    $imageUrl = Uploader::image($_FILES['image'], 'questions');
    if (!$imageUrl) {
        throw new \RuntimeException('Failed to save uploaded image.');
    }

    $fullImagePath = PUBLIC_PATH . $imageUrl;
    $extractedData = [
        'image_url'       => $imageUrl,
        'question_text'   => '',
        'question_type'   => 'descriptive',
        'options'         => null,
        'correct_answer'  => '',
        'explanation'     => '',
        'marks'           => 3.0,
        'difficulty'      => 'medium',
        'extracted_by_ai' => false
    ];

    // 2. Attempt AI Vision OCR / Transcription if Gemini API Key is available
    $config = require APP_ROOT . '/config/config.php';
    $geminiApiKey = getenv('GEMINI_API_KEY') ?: ($config['gemini_api_key'] ?? $config['ai_agent']['api_key'] ?? '');

    if (!empty($geminiApiKey) && file_exists($fullImagePath)) {
        $imgData = file_get_contents($fullImagePath);
        $mime = mime_content_type($fullImagePath) ?: 'image/jpeg';
        $base64 = base64_encode($imgData);

        $prompt = "You are an expert pedagogical exam question parser and OCR system.
Inspect this uploaded exam/curriculum image (which contains a diagram, textbook problem, handwritten question, or test item).
Transcribe and extract the full pedagogical question data.

Return ONLY a valid JSON object with this exact schema:
{
  \"question_text\": \"The complete transcribed question text, instructions, and diagram description if present\",
  \"question_type\": \"mcq|diagram_based|equation_problem|short_answer|descriptive|numerical\",
  \"options\": [\"Option A\", \"Option B\", \"Option C\", \"Option D\"], // 4 options if MCQ, null otherwise
  \"correct_answer\": \"The accurate correct answer, final solution, or proof key\",
  \"explanation\": \"Step-by-step mathematical, scientific, or conceptual working and explanation\",
  \"marks\": 3.0, // 1 for MCQ, 2-3 for short/equations, 4-5 for diagrams/descriptive
  \"difficulty\": \"easy|medium|hard\"
}";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($geminiApiKey);
        $payload = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inlineData' => [
                                'mimeType' => $mime,
                                'data' => $base64
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'responseMimeType' => 'application/json'
            ]
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $respData = json_decode($response, true);
            $text = $respData['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $parsed = json_decode($text, true);
            if (is_array($parsed) && !empty($parsed['question_text'])) {
                $extractedData['question_text']  = trim((string)$parsed['question_text']);
                $extractedData['question_type']  = $parsed['question_type'] ?? 'descriptive';
                $extractedData['options']        = !empty($parsed['options']) && is_array($parsed['options']) ? array_values($parsed['options']) : null;
                $extractedData['correct_answer'] = trim((string)($parsed['correct_answer'] ?? ''));
                $extractedData['explanation']    = trim((string)($parsed['explanation'] ?? ''));
                $extractedData['marks']          = (float)($parsed['marks'] ?? 3.0);
                $extractedData['difficulty']     = $parsed['difficulty'] ?? 'medium';
                $extractedData['extracted_by_ai']= true;
            }
        }
    }

    // 3. Fallback heuristic if AI was not active
    if (empty($extractedData['question_text'])) {
        $origName = pathinfo($_FILES['image']['name'] ?? 'Question Diagram', PATHINFO_FILENAME);
        $cleanName = ucwords(str_replace(['_', '-'], ' ', $origName));
        $extractedData['question_text'] = "[Diagram / Attached Figure: {$cleanName}]\nRefer to the attached image above. Explain the concept and solve the question as illustrated.";
        $extractedData['question_type'] = 'diagram_based';
        $extractedData['correct_answer'] = 'Refer to attached diagram key and curriculum solution.';
        $extractedData['explanation'] = 'Step-by-step analytical working based on attached figure.';
        $extractedData['marks'] = 4.0;
    }

    echo json_encode([
        'success' => true,
        'data' => $extractedData,
        'message' => $extractedData['extracted_by_ai']
            ? 'Question successfully scanned and auto-filled with AI!'
            : 'Image successfully uploaded and attached! Fields pre-populated for review.'
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
