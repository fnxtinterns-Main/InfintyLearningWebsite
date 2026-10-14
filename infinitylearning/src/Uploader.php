<?php
declare(strict_types=1);

namespace App;

final class Uploader
{
    /**
     * Handle a single image upload. Kept for back-compat.
     * Returns relative web path or null.
     * @throws \RuntimeException on invalid file
     */
    public static function image(array $file, string $subdir = 'misc'): ?string
    {
        if (empty($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed (code ' . $file['error'] . ').');
        }

        $config   = require BASE_PATH . '/config/config.php';
        $maxBytes = $config['uploads']['max_image_mb'] * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            throw new \RuntimeException('Image too large (max ' . $config['uploads']['max_image_mb'] . 'MB).');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($config['uploads']['allowed_mimes'][$mime])) {
            throw new \RuntimeException('Invalid image type.');
        }
        $ext = $config['uploads']['allowed_mimes'][$mime];
        if (!in_array($ext, $config['uploads']['image_extensions'], true)) {
            throw new \RuntimeException('Only images are allowed here.');
        }

        return self::store($file['tmp_name'], $subdir, $ext);
    }

    /**
     * Handle a general document upload (image, PDF, Word, Excel, PowerPoint).
     * Returns ['path' => '/uploads/lessons/xxx.pdf', 'mime' => 'application/pdf',
     *          'ext' => 'pdf', 'name' => 'original-name.pdf', 'size' => 123456,
     *          'bucket' => 'pdf'] or null if no file was uploaded.
     *
     * @throws \RuntimeException on invalid file
     */
    public static function document(array $file, string $subdir = 'lessons'): ?array
    {
        if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed for "' . ($file['name'] ?? '') . '" (code ' . $file['error'] . ').');
        }

        $config = require BASE_PATH . '/config/config.php';

        // ---- Detect real MIME via finfo (not the browser-provided type) ----
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']) ?: '';
        finfo_close($finfo);

        if (!isset($config['uploads']['allowed_mimes'][$mime])) {
            throw new \RuntimeException(
                'Unsupported file type for "' . ($file['name'] ?? '') . '" (' . $mime . '). ' .
                'Allowed: JPG, PNG, WebP, GIF, PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX.'
            );
        }
        $ext = $config['uploads']['allowed_mimes'][$mime];

        // ---- Extra magic-byte validation for PDF + Office Open XML ----
        self::validateMagicBytes($file['tmp_name'], $ext);

        // ---- Apply the correct size limit based on bucket ----
        $bucket   = self::bucketFor($ext);
        $maxMb    = self::limitFor($bucket);
        $maxBytes = $maxMb * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            throw new \RuntimeException(
                'File "' . ($file['name'] ?? '') . '" is too large. ' .
                'Max for ' . strtoupper($ext) . ' is ' . $maxMb . 'MB.'
            );
        }

        // ---- Store the file ----
        $webPath = self::store($file['tmp_name'], $subdir, $ext);

        return [
            'path'   => $webPath,
            'mime'   => $mime,
            'ext'    => $ext,
            'name'   => $file['name'] ?? basename($webPath),
            'size'   => (int)$file['size'],
            'bucket' => $bucket,
        ];
    }

    /**
     * Handle an entire PHP $_FILES[...] array where the input name is
     * a multi-file input like name="attachments[]".
     *
     * Returns an array of "document()" results, skipping empty slots.
     * Throws on the FIRST invalid file so the whole submit fails loudly.
     *
     * @return array<int, array>
     */
    public static function documents(array $filesArray, string $subdir = 'lessons'): array
    {
        if (!isset($filesArray['name'])) return [];

        // Normalize single-file inputs to array shape
        if (!is_array($filesArray['name'])) {
            $filesArray = [
                'name'     => [$filesArray['name']],
                'type'     => [$filesArray['type']],
                'tmp_name' => [$filesArray['tmp_name']],
                'error'    => [$filesArray['error']],
                'size'     => [$filesArray['size']],
            ];
        }

        $config = require BASE_PATH . '/config/config.php';
        $maxFiles = (int)($config['uploads']['max_lesson_files'] ?? 10);
        $maxTotal = (int)($config['uploads']['max_lesson_total_mb'] ?? 40) * 1024 * 1024;

        $out = [];
        $totalBytes = 0;
        $count = 0;

        foreach ($filesArray['name'] as $i => $name) {
            $one = [
                'name'     => $filesArray['name'][$i]     ?? '',
                'type'     => $filesArray['type'][$i]     ?? '',
                'tmp_name' => $filesArray['tmp_name'][$i] ?? '',
                'error'    => $filesArray['error'][$i]    ?? UPLOAD_ERR_NO_FILE,
                'size'     => $filesArray['size'][$i]     ?? 0,
            ];
            // Skip empty slots
            if ($one['error'] === UPLOAD_ERR_NO_FILE || $one['tmp_name'] === '') continue;

            $count++;
            if ($count > $maxFiles) {
                throw new \RuntimeException('Too many files. Maximum ' . $maxFiles . ' per lesson.');
            }
            $totalBytes += (int)$one['size'];
            if ($totalBytes > $maxTotal) {
                throw new \RuntimeException('Total upload size exceeds ' . ($maxTotal / 1024 / 1024) . 'MB.');
            }

            $doc = self::document($one, $subdir);
            if ($doc) $out[] = $doc;
        }

        return $out;
    }

    // =========================================================
    // HELPERS
    // =========================================================

    private static function store(string $tmpPath, string $subdir, string $ext): string
    {
        $dir = UPLOAD_PATH . '/' . trim($subdir, '/');
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException('Cannot create upload directory.');
        }

        // Automatic WebP compression for images (saves 80-90% disk space & bandwidth)
        $lowerExt = strtolower($ext);
        if (in_array($lowerExt, ['jpg', 'jpeg', 'png', 'webp'], true) && function_exists('imagewebp')) {
            $webpName = bin2hex(random_bytes(8)) . '_' . time() . '.webp';
            $destWebp = $dir . '/' . $webpName;
            if (self::compressImageToWebp($tmpPath, $destWebp, 82)) {
                return '/uploads/' . trim($subdir, '/') . '/' . $webpName;
            }
        }

        // Standard storage fallback for PDFs, docs, or if GD fails
        $name = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($tmpPath, $dest)) {
            throw new \RuntimeException('Could not save uploaded file.');
        }
        return '/uploads/' . trim($subdir, '/') . '/' . $name;
    }

    /**
     * Compress and downsample high-res camera photos to WebP format.
     */
    private static function compressImageToWebp(string $sourcePath, string $targetPath, int $quality = 82): bool
    {
        try {
            $info = @getimagesize($sourcePath);
            if (!$info) return false;

            $src = null;
            switch ($info[2]) {
                case IMAGETYPE_JPEG:
                    $src = @imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $src = @imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_WEBP:
                    $src = @imagecreatefromwebp($sourcePath);
                    break;
            }

            if (!$src) return false;

            $origW = imagesx($src);
            $origH = imagesy($src);

            // Cap dimensions to max 1920px (prevents 4K/8K camera phone images from bloating disk)
            $maxDim = 1920;
            if ($origW > $maxDim || $origH > $maxDim) {
                if ($origW >= $origH) {
                    $newW = $maxDim;
                    $newH = (int)round(($origH / $origW) * $maxDim);
                } else {
                    $newH = $maxDim;
                    $newW = (int)round(($origW / $origH) * $maxDim);
                }
                $resized = imagecreatetruecolor($newW, $newH);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                imagedestroy($src);
                $src = $resized;
            } else {
                imagealphablending($src, false);
                imagesavealpha($src, true);
            }

            $saved = @imagewebp($src, $targetPath, $quality);
            imagedestroy($src);
            return (bool)$saved;
        } catch (\Throwable $e) {
            error_log('WebP compression failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify the file actually starts with the expected signature.
     * Prevents someone renaming an executable to .pdf.
     */
    private static function validateMagicBytes(string $tmpPath, string $ext): void
    {
        // Images: trust finfo — it already did a full parse
        if (in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) {
            return;
        }

        $fh = fopen($tmpPath, 'rb');
        if (!$fh) return;
        $header = fread($fh, 8);
        fclose($fh);

        switch ($ext) {
            case 'pdf':
                if (strncmp($header, '%PDF-', 5) !== 0) {
                    throw new \RuntimeException('File is not a valid PDF.');
                }
                break;

            case 'docx':
            case 'xlsx':
            case 'pptx':
                // All Open XML formats are ZIP containers → start with PK\x03\x04
                if (strncmp($header, "PK\x03\x04", 4) !== 0) {
                    throw new \RuntimeException('File is not a valid Office Open XML document.');
                }
                break;

            case 'doc':
            case 'xls':
            case 'ppt':
                // Legacy Office binary formats start with D0 CF 11 E0 A1 B1 1A E1
                if (strncmp($header, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1", 8) !== 0) {
                    // Some older docs are XML wrappers — accept if they look like text
                    $sniff = substr($header, 0, 5);
                    if (strncmp($sniff, '<?xml', 5) !== 0 && strncmp($header, '<', 1) !== 0) {
                        throw new \RuntimeException('File is not a valid legacy Office document.');
                    }
                }
                break;
        }
    }

    private static function bucketFor(string $ext): string
    {
        $config = require BASE_PATH . '/config/config.php';
        return $config['uploads']['mime_bucket'][$ext] ?? 'document';
    }

    private static function limitFor(string $bucket): int
    {
        $config = require BASE_PATH . '/config/config.php';
        return match ($bucket) {
            'image' => (int)$config['uploads']['max_image_mb'],
            'pdf'   => (int)$config['uploads']['max_pdf_mb'],
            'word'  => (int)$config['uploads']['max_word_mb'],
            'excel' => (int)$config['uploads']['max_excel_mb'],
            'ppt'   => (int)$config['uploads']['max_ppt_mb'],
            default => (int)$config['uploads']['max_document_mb'],
        };
    }

    /**
     * Human-readable size limit text for UI ("4MB", "10MB", etc.)
     */
    public static function maxLabel(string $bucket): string
    {
        return self::limitFor($bucket) . 'MB';
    }
}