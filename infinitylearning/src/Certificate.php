<?php
declare(strict_types=1);

namespace App;

final class Certificate
{
    private const OUT_DIR  = UPLOAD_PATH . '/certificates';
    private const OUT_URL  = '/uploads/certificates';
    private const LOGO_DIR = PUBLIC_PATH . '/assets/img/partners';

    // Brand colors
    private const NAVY      = [18, 42, 90];
    private const GOLD      = [190, 148, 32];
    private const DARK_GOLD = [140, 105, 20];
    private const TEXT_DARK = [30, 30, 30];

    public static function generate(
        int $studentUserId,
        string $studentName,
        string $type,
        string $eventTitle,
        string $issuerLine = 'Infinity Learning',
        ?string $rank = null,
        ?string $dateCompleted = null,
        ?string $schoolName = null
    ): array {
        $dateCompleted = $dateCompleted ?: date('d M Y');

        $pdf = new InfinityPDF();
        $pdf->AddPage('L', 'A4');
        $pdf->SetAutoPageBreak(false);

        self::render($pdf, $studentName, $type, $eventTitle, $rank, $dateCompleted);

        if (!is_dir(self::OUT_DIR)) {
            @mkdir(self::OUT_DIR, 0755, true);
        }
        $fname = 'cert_' . $type . '_' . $studentUserId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.pdf';
        $fullPath = self::OUT_DIR . '/' . $fname;

        $pdf->Output('F', $fullPath);
        $relativeUrl = self::OUT_URL . '/' . $fname;

        $db = Database::connect();
        $db->prepare("
            INSERT INTO certificates
                (uuid, student_user_id, title, type, certificate_type, certificate_url,
                 file_path, issued_for, issued_by, issued_at, metadata)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'activity', ?, NOW(), ?)
        ")->execute([
            Auth::uuid(),
            $studentUserId,
            'Certificate of ' . self::friendlyType($type),
            $type === 'participant' ? 'participation' : 'winner',
            $type,
            $relativeUrl,
            $relativeUrl,
            $_SESSION['user_id'] ?? null,
            json_encode([
                'event_title'    => $eventTitle,
                'rank'           => $rank,
                'date_completed' => $dateCompleted,
                'school_name'    => $schoolName,
            ]),
        ]);
        $certId = (int)$db->lastInsertId();

        Audit::log('certificate.generated', 'certificate', $certId, [
            'student_id' => $studentUserId, 'type' => $type,
        ]);

        return [
            'path'           => $relativeUrl,
            'file'           => $fname,
            'certificate_id' => $certId,
        ];
    }

    // =========================================================
    // RENDER
    // =========================================================

    private static function render(
        InfinityPDF $pdf,
        string $studentName,
        string $type,
        string $eventTitle,
        ?string $rank,
        string $date
    ): void {
        $W = 297;
        $H = 210;

        // ---- 1. Background ----
        $pdf->SetFillColor(255, 254, 250);
        $pdf->Rect(0, 0, $W, $H, 'F');

        // ---- 2. Navy outer border ----
        $pdf->SetFillColor(...self::NAVY);
        $pdf->Rect(0, 0, $W, 10, 'F');
        $pdf->Rect(0, $H - 10, $W, 10, 'F');
        $pdf->Rect(0, 0, 10, $H, 'F');
        $pdf->Rect($W - 10, 0, 10, $H, 'F');

        // ---- 3. Gold inner frame (double line) ----
        $pdf->SetDrawColor(...self::GOLD);
        $pdf->SetLineWidth(1.5);
        $pdf->Rect(14, 14, $W - 28, $H - 28);
        $pdf->SetLineWidth(0.3);
        $pdf->Rect(16.5, 16.5, $W - 33, $H - 33);

        // ---- 4. Corner flourishes ----
        self::drawCornerFlourish($pdf, 20, 20, 1, 1);
        self::drawCornerFlourish($pdf, $W - 20, 20, -1, 1);
        self::drawCornerFlourish($pdf, 20, $H - 20, 1, -1);
        self::drawCornerFlourish($pdf, $W - 20, $H - 20, -1, -1);

        // ---- 5. Title ----
        $titleTop = 24;

        if ($type === 'participant') {
            $pdf->SetTextColor(...self::NAVY);
            $pdf->SetFont('Times', 'B', 42);
            $pdf->SetXY(0, $titleTop);
            $pdf->Cell($W, 18, 'CERTIFICATE', 0, 1, 'C');

            $pdf->SetTextColor(...self::GOLD);
            $pdf->SetFont('Times', 'B', 26);
            $pdf->SetXY(0, $titleTop + 20);
            $pdf->Cell($W, 12, 'OF PARTICIPATION', 0, 1, 'C');

            $afterTitle = $titleTop + 38;
        } else {
            $pdf->SetTextColor(...self::GOLD);
            $pdf->SetFont('Times', 'B', 22);
            $pdf->SetXY(0, $titleTop);
            $pdf->Cell($W, 12, 'EXCELLENCE', 0, 1, 'C');

            $pdf->SetTextColor(...self::NAVY);
            $pdf->SetFont('Times', 'B', 50);
            $pdf->SetXY(0, $titleTop + 14);
            $pdf->Cell($W, 22, 'CERTIFICATE', 0, 1, 'C');

            $afterTitle = $titleTop + 42;
        }

        // ---- 6. Subtitle ----
        $pdf->SetTextColor(...self::TEXT_DARK);
        $pdf->SetFont('Times', 'B', 13);
        $pdf->SetXY(0, $afterTitle);
        $subtitle = $type === 'participant'
            ? 'This certificate is proudly presented to our Participant.'
            : 'This certificate is proudly presented to our Winner & Competitor.';
        $pdf->Cell($W, 8, $subtitle, 0, 1, 'C');

        // ---- 7. Competition name ----
        $pdf->SetTextColor(...self::GOLD);
        $pdf->SetFont('Times', 'BI', 18);
        $pdf->SetXY(0, $afterTitle + 12);
        $pdf->Cell($W, 10, $eventTitle, 0, 1, 'C');

        // ---- 8. Wishes line ----
        $pdf->SetTextColor(...self::TEXT_DARK);
        $pdf->SetFont('Times', 'I', 12);
        $pdf->SetXY(0, $afterTitle + 24);
        $pdf->Cell($W, 8, 'Infinity Learning wishes you the best for your future endeavours.', 0, 1, 'C');

        // ---- 9. Details block ----
        $pdf->SetTextColor(...self::TEXT_DARK);
        $pdf->SetFont('Times', '', 13);

        $labelX = 50;
        $valueX = 115;
        $rowY   = $afterTitle + 42;

        if ($type === 'participant') {
            $pdf->SetXY($labelX, $rowY);
            $pdf->Cell(0, 8, 'This certificate is awarded for your active participation in the competition.', 0, 1, 'L');
            $rowY += 12;
        }

        // Name row
        $pdf->SetFont('Times', '', 13);
        $pdf->SetXY($labelX, $rowY);
        $pdf->Cell(0, 8, 'This is to certify that Mr./Ms.', 0, 0, 'L');
        $pdf->SetFont('Times', 'B', 14);
        $pdf->SetXY($valueX, $rowY);
        $pdf->Cell(130, 8, $studentName, 0, 0, 'L');
        self::drawDottedLine($pdf, $valueX, $rowY + 8, 130);
        $rowY += 14;

        // Rank row (skip for participant)
        if ($type !== 'participant') {
            $pdf->SetFont('Times', '', 13);
            $pdf->SetXY($labelX, $rowY);
            $pdf->Cell(0, 8, 'Winner/Rank', 0, 0, 'L');
            $rankText = $rank ?: ($type === 'winner' ? '1st Place' : 'Runner-up');
            $pdf->SetFont('Times', 'B', 14);
            $pdf->SetXY($valueX, $rowY);
            $pdf->Cell(130, 8, $rankText, 0, 0, 'L');
            self::drawDottedLine($pdf, $valueX, $rowY + 8, 130);
            $rowY += 14;
        }

        // Date row
        $pdf->SetFont('Times', '', 13);
        $pdf->SetXY($labelX, $rowY);
        $pdf->Cell(0, 8, 'Date of Completion', 0, 0, 'L');
        $pdf->SetFont('Times', 'B', 14);
        $pdf->SetXY($valueX, $rowY);
        $pdf->Cell(130, 8, $date, 0, 0, 'L');
        self::drawDottedLine($pdf, $valueX, $rowY + 8, 130);

        // ---- 10. Signatures (moved up to make room for logos) ----
        $sigY = $H - 55;

        $pdf->SetDrawColor(...self::TEXT_DARK);
        $pdf->SetLineWidth(0.4);

        $pdf->SetFont('Times', 'B', 12);
        $pdf->SetXY(38, $sigY);
        $pdf->Cell(30, 6, 'Sign:', 0, 0, 'L');
        $pdf->Line(54, $sigY + 6, 120, $sigY + 6);

        $pdf->SetXY(180, $sigY);
        $pdf->Cell(30, 6, 'Sign:', 0, 0, 'L');
        $pdf->Line(196, $sigY + 6, 262, $sigY + 6);

        // ---- 11. Logo strip (inside the gold frame) ----
        self::drawLogoStrip($pdf, $W, $H);
    }

    // =========================================================
    // DECORATIVE HELPERS
    // =========================================================

    private static function drawCornerFlourish(InfinityPDF $pdf, float $x, float $y, int $sx, int $sy): void
    {
        $pdf->SetDrawColor(...self::GOLD);
        $pdf->SetLineWidth(0.4);

        for ($i = 1; $i <= 3; $i++) {
            $r = 2.5 + $i * 1.3;
            $startAngle = $sy > 0 ? ($sx > 0 ? 180 : 270) : ($sx > 0 ? 90 : 0);
            $endAngle   = $sy > 0 ? ($sx > 0 ? 270 : 360) : ($sx > 0 ? 180 : 90);
            $pdf->Arc($x, $y, $r, $r, $startAngle, $endAngle);
        }
    }

    /**
     * Show only 6 main logos, sized to fit comfortably inside the gold frame.
     */
    private static function drawLogoStrip(InfinityPDF $pdf, float $W, float $H): void
    {
        // Which logos to show, in order
        $preferred = [
            'futurenxt.jpeg',
            'infinity.png',
            'mpowerin.jpeg',
            'quizwiz.jpeg',
            'angrezi.jpeg',
            'skitre.jpeg',
        ];

        $available = [];
        foreach ($preferred as $file) {
            if (is_file(self::LOGO_DIR . '/' . $file)) {
                $available[] = $file;
            }
        }
        if (!$available && is_dir(self::LOGO_DIR)) {
            foreach (scandir(self::LOGO_DIR) as $f) {
                if (preg_match('/\.(png|jpg|jpeg)$/i', $f)) {
                    $available[] = $f;
                    if (count($available) >= 6) break;
                }
            }
        }
        if (!$available) return;

        // ---- Layout (fits inside the gold frame safely) ----
        $stripH = 14;
        $stripY = $H - 32;            // above the gold frame inner edge
        $slotW  = 38;
        $gap    = 4;
        $count  = count($available);
        $totalW = $count * $slotW + ($count - 1) * $gap;
        $startX = ($W - $totalW) / 2;
        $y      = $stripY;

        // Divider line above strip
        $pdf->SetDrawColor(...self::GOLD);
        $pdf->SetLineWidth(0.4);
        $pdf->Line(24, $stripY - 3, $W - 24, $stripY - 3);

        foreach ($available as $i => $file) {
            $path  = self::LOGO_DIR . '/' . $file;
            $slotX = $startX + $i * ($slotW + $gap);

            // White card with gold border
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetDrawColor(...self::GOLD);
            $pdf->SetLineWidth(0.2);
            $pdf->Rect($slotX, $y, $slotW, $stripH, 'DF');

            // Logo inside
            self::drawLogoFitted(
                $pdf,
                $path,
                $slotX + 2,
                $y + 1.5,
                $slotW - 4,
                $stripH - 3,
                0.9
            );
        }
    }

    /**
     * Place an image inside a bounding box, preserving aspect ratio.
     * Detects the real image type from file contents (PNG/JPEG/etc.), ignoring the extension.
     */
    private static function drawLogoFitted(
        InfinityPDF $pdf,
        string $path,
        float $x,
        float $y,
        float $w,
        float $h,
        float $maxWidthRatio = 1.0
    ): void {
        if (!is_file($path)) return;

        $type = self::detectImageType($path);
        if (!$type) {
            error_log('Logo not a valid image: ' . basename($path));
            return;
        }

        $info = @getimagesize($path);
        if (!$info) return;

        $imgW = (float)$info[0];
        $imgH = (float)$info[1];
        if ($imgW <= 0 || $imgH <= 0) return;

        $imgRatio = $imgW / $imgH;
        $boxRatio = $w / $h;

        if ($imgRatio > $boxRatio) {
            $drawW = $w * $maxWidthRatio;
            $drawH = $drawW / $imgRatio;
            $drawX = $x + ($w - $drawW) / 2;
            $drawY = $y + ($h - $drawH) / 2;
        } else {
            $drawH = $h;
            $drawW = $h * $imgRatio;
            $drawX = $x + ($w - $drawW) / 2;
            $drawY = $y;
        }

        try {
            $pdf->Image($path, $drawX, $drawY, $drawW, $drawH, $type);
        } catch (\Throwable $e) {
            error_log('Logo draw failed: ' . basename($path) . ' — ' . $e->getMessage());
        }
    }

    /**
     * Detect the real image type from file contents.
     * Returns 'PNG' | 'JPEG' | 'GIF' | 'WEBP' | null
     */
    private static function detectImageType(string $path): ?string
    {
        $fh = @fopen($path, 'rb');
        if (!$fh) return null;
        $bytes = fread($fh, 12);
        fclose($fh);
        if ($bytes === false || strlen($bytes) < 4) return null;

        if (strncmp($bytes, "\x89PNG", 4) === 0) return 'PNG';
        if (strncmp($bytes, "\xFF\xD8\xFF", 3) === 0) return 'JPEG';
        if (strncmp($bytes, 'GIF8', 4) === 0) return 'GIF';
        if (strncmp($bytes, 'RIFF', 4) === 0 && substr($bytes, 8, 4) === 'WEBP') return 'WEBP';

        return null;
    }

    private static function drawDottedLine(InfinityPDF $pdf, float $x1, float $y, float $length): void
    {
        $pdf->SetDrawColor(...self::TEXT_DARK);
        $pdf->SetLineWidth(0.3);

        $x = $x1;
        $end = $x1 + $length;
        while ($x < $end) {
            $pdf->Line($x, $y, min($x + 1.2, $end), $y);
            $x += 1.8;
        }
    }

    // =========================================================
    // MISC HELPERS
    // =========================================================

    private static function friendlyType(string $type): string
    {
        return match ($type) {
            'winner'      => 'Achievement',
            'runner_up'   => 'Runner-up',
            'participant' => 'Participation',
            default       => 'Participation',
        };
    }

    public static function resolveFile(string $publicUrl): ?string
    {
        if (!str_starts_with($publicUrl, self::OUT_URL)) return null;
        $file = basename($publicUrl);
        $path = self::OUT_DIR . '/' . $file;
        return is_file($path) ? $path : null;
    }

    public static function listForStudent(int $studentUserId, int $limit = 100): array
    {
        $stmt = Database::connect()->prepare("
            SELECT c.*, u.email AS student_email,
                   s.full_name AS student_name,
                   sc.name AS school_name
            FROM certificates c
            LEFT JOIN users u    ON u.id = c.student_user_id
            LEFT JOIN students s ON s.user_id = u.id
            LEFT JOIN schools sc ON sc.id = u.school_id
            WHERE c.student_user_id = ?
            ORDER BY c.issued_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $studentUserId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function listForAdmin(?int $schoolId = null, ?string $type = null, ?string $q = null, int $limit = 200): array
    {
        $db = Database::connect();
        $sql = "
            SELECT c.*, u.email AS student_email, u.school_id AS student_school_id,
                   s.full_name AS student_name, s.profile_image,
                   sc.name AS school_name
            FROM certificates c
            LEFT JOIN users u    ON u.id = c.student_user_id
            LEFT JOIN students s ON s.user_id = u.id
            LEFT JOIN schools sc ON sc.id = u.school_id
            WHERE 1=1
        ";
        $params = [];
        if ($schoolId === -1) {
            // super admin
        } elseif (!empty($schoolId)) {
            $sql .= " AND u.school_id = ?";
            $params[] = (int)$schoolId;
        }
        if ($type && in_array($type, ['winner','runner_up','participant'], true)) {
            $sql .= " AND c.certificate_type = ?";
            $params[] = $type;
        }
        if ($q) {
            $sql .= " AND (c.title LIKE ? OR s.full_name LIKE ? OR u.email LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        $sql .= " ORDER BY c.issued_at DESC LIMIT " . (int)$limit;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countForStudent(int $studentUserId, ?string $type = null): int
    {
        $sql = "SELECT COUNT(*) FROM certificates WHERE student_user_id = ?";
        $params = [$studentUserId];
        if ($type) { $sql .= " AND certificate_type = ?"; $params[] = $type; }
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function typeMeta(string $type): array
    {
        return match ($type) {
            'winner'      => ['label' => '🏆 Winner',      'badge' => 'success'],
            'runner_up'   => ['label' => '🥈 Runner-up',   'badge' => 'primary'],
            'participant' => ['label' => '🎓 Participant', 'badge' => 'info'],
            default       => ['label' => '🎓 Participant', 'badge' => 'muted'],
        };
    }
}