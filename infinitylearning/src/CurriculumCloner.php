<?php
declare(strict_types=1);

namespace App;

use PDO;

final class CurriculumCloner
{
    public static function getPacks(): array
    {
        return [
            'cbse_math_8' => [
                'id' => 'cbse_math_8',
                'title' => 'CBSE Grade 8 Mathematics Mastery',
                'subject' => 'Mathematics',
                'level' => 'Grade 8',
                'badge' => '➗ Math Pack',
                'description' => 'Comprehensive 4-week module covering Rational Numbers, Linear Equations, Algebraic Identities, and Mensuration with practice quizzes.',
                'lessons' => [
                    [
                        'title' => 'Properties of Rational Numbers & Operations',
                        'summary' => 'Closure, commutativity, and associativity of rational numbers with number line visualization.',
                        'difficulty' => 'easy',
                        'minutes' => 20
                    ],
                    [
                        'title' => 'Solving Linear Equations in One Variable',
                        'summary' => 'Variable isolation techniques, transposition rule, and real-world word problems.',
                        'difficulty' => 'medium',
                        'minutes' => 25
                    ],
                    [
                        'title' => 'Algebraic Expressions & Standard Identities',
                        'summary' => 'Multiplication of binomials, (a+b)^2, (a-b)^2, and (a^2 - b^2) geometric representations.',
                        'difficulty' => 'medium',
                        'minutes' => 30
                    ],
                    [
                        'title' => 'Mensuration: Surface Areas & Volumes of 3D Shapes',
                        'summary' => 'Cubes, cuboids, cylinders: surface areas, lateral areas, and volume formulas with unit conversions.',
                        'difficulty' => 'hard',
                        'minutes' => 35
                    ]
                ]
            ],
            'ai_innovation' => [
                'id' => 'ai_innovation',
                'title' => 'AI, Robotics & Algorithmic Innovation',
                'subject' => 'AI & Innovation',
                'level' => 'All Grades',
                'badge' => '🤖 AI Pack',
                'description' => 'Modern STEM module introducing Artificial Intelligence, Machine Learning intuition, prompt engineering, and ethical innovation.',
                'lessons' => [
                    [
                        'title' => 'Demystifying AI: How Neural Networks Learn',
                        'summary' => 'From biological brains to artificial perceptrons: weights, biases, and visual training.',
                        'difficulty' => 'easy',
                        'minutes' => 20
                    ],
                    [
                        'title' => 'Generative AI & Large Language Models',
                        'summary' => 'Tokens, attention mechanisms, context windows, and responsible AI safety.',
                        'difficulty' => 'medium',
                        'minutes' => 25
                    ],
                    [
                        'title' => 'Computer Vision & Real-time Pattern Recognition',
                        'summary' => 'Pixel arrays, convolutions, object tracking, and self-driving car algorithms.',
                        'difficulty' => 'medium',
                        'minutes' => 25
                    ]
                ]
            ],
            'rising_rooks_tactics' => [
                'id' => 'rising_rooks_tactics',
                'title' => 'Rising Rooks: Tournament Tactics & Strategy',
                'subject' => 'Chess',
                'level' => 'Intermediate & Master',
                'badge' => '♟️ Chess Pack',
                'description' => 'Elite tactical drills covering pins, skewers, deflections, king hunts, and rook endgame Lucena bridge building.',
                'lessons' => [
                    [
                        'title' => 'Absolute vs Relative Pins in the Opening',
                        'summary' => 'Pinning knights to the king vs queen, exploiting pinned pieces with pawn levers.',
                        'difficulty' => 'medium',
                        'minutes' => 20
                    ],
                    [
                        'title' => 'Deflection & Decoy Combinations',
                        'summary' => 'Forcing defenders off vital squares to deliver rapid checkmates.',
                        'difficulty' => 'hard',
                        'minutes' => 25
                    ],
                    [
                        'title' => 'Mastering the Lucena & Philidor Endgame Systems',
                        'summary' => 'The definitive rook endgame guide: building the bridge on the 4th rank vs cutting off enemy kings.',
                        'difficulty' => 'hard',
                        'minutes' => 30
                    ]
                ]
            ]
        ];
    }

    public static function cloneToBatch(string $packId, int $batchId, int $teacherUserId): array
    {
        $packs = self::getPacks();
        if (!isset($packs[$packId])) {
            throw new \InvalidArgumentException("Curriculum pack not found");
        }

        $pack = $packs[$packId];
        $db = Database::connect();

        // Find subject id
        $subStmt = $db->prepare("SELECT id FROM subjects WHERE name = ? LIMIT 1");
        $subStmt->execute([$pack['subject']]);
        $subjectId = (int)($subStmt->fetchColumn() ?: 1);

        $clonedCount = 0;

        foreach ($pack['lessons'] as $idx => $l) {
            $slug = Helper::slug($l['title']) . '-' . substr(uniqid(), -4);
            $content = "<h2>{$l['title']}</h2><p>{$l['summary']}</p><p>This lesson has been cloned from the <strong>{$pack['title']}</strong> curriculum pack for your batch.</p>";

            $stmt = $db->prepare("
                INSERT INTO lessons
                (subject_id, batch_id, title, slug, summary, content, difficulty, estimated_minutes, published, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
            ");
            $stmt->execute([
                $subjectId,
                $batchId,
                $l['title'],
                $slug,
                $l['summary'],
                $content,
                $l['difficulty'],
                (int)$l['minutes'],
                $teacherUserId
            ]);
            $lessonId = (int)$db->lastInsertId();

            // Auto-generate 10-MCQ quiz for each cloned lesson
            LessonQuiz::getOrCreateQuiz($lessonId);
            $clonedCount++;
        }

        return [
            'success' => true,
            'pack_title' => $pack['title'],
            'cloned_count' => $clonedCount
        ];
    }
}
